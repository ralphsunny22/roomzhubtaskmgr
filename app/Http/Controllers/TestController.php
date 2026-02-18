<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Stripe\StripeClient;
use Illuminate\Validation\ValidationException;

class PaymentsController extends Controller
{
    public function __construct(private StripeClient $stripe) {}

    /** Create/get or attach a Stripe Customer for a user */
    protected function upsertCustomer(array $user): string
    {
        // You likely store stripe_customer_id on users table.
        if (!empty($user['stripe_customer_id'])) {
            return $user['stripe_customer_id'];
        }
        $customer = $this->stripe->customers->create([
            'name'  => $user['name'] ?? null,
            'email' => $user['email'] ?? null,
            'metadata' => ['app_user_id' => (string)($user['id'] ?? '')],
        ]);
        // persist $customer->id on your users table
        // User::where('id', $user['id'])->update(['stripe_customer_id' => $customer->id]);
        return $customer->id;
    }

    /** ===================== HANDYMAN (Task/Offer) ===================== */

    // 1) Create a PaymentIntent client_secret for the lister to pay when accepting an offer
    public function createTaskPaymentIntent(Request $req)
    {
        $data = $req->validate([
            'task_id' => 'required|integer',
            'offer_id' => 'required|integer',
            'amount_cents' => 'required|integer|min:50',
            'currency' => 'nullable|string',
            'customer' => 'required|array', // {id,name,email,stripe_customer_id?}
        ]);

        $currency = strtoupper($data['currency'] ?? config('services.stripe.currency', 'AUD'));
        $customerId = $this->upsertCustomer($data['customer']);

        // Idempotency key: one per accept action
        $idem = "task_accept:{$data['task_id']}:offer:{$data['offer_id']}";

        $pi = $this->stripe->paymentIntents->create([
            'amount' => $data['amount_cents'],
            'currency' => $currency,
            'customer' => $customerId,
            'automatic_payment_methods' => ['enabled' => true],
            'capture_method' => 'automatic', // charge now
            'metadata' => [
                'owner_type' => 'task',
                'task_id' => (string)$data['task_id'],
                'offer_id' => (string)$data['offer_id'],
            ],
        ], ['idempotency_key' => $idem]);

        // persist to payments table with status=requires_action|requires_confirmation|processing depending on pi.status
        return response()->json([
            'client_secret' => $pi->client_secret,
            'payment_intent_id' => $pi->id,
            'status' => $pi->status,
        ]);
    }

    // Optional: server-side confirmation with a PaymentMethod from frontend
    public function confirmTaskPayment(Request $req)
    {
        $data = $req->validate([
            'payment_intent_id' => 'required|string',
            'payment_method_id' => 'required|string',
        ]);

        $pi = $this->stripe->paymentIntents->confirm($data['payment_intent_id'], [
            'payment_method' => $data['payment_method_id'],
            // return_url for 3DS can be added for web flows if using redirect
        ]);
        return response()->json(['payment_intent' => $pi]);
    }

    // 2) Refund (full or partial) if task fails/cancels
    public function refundTask(Request $req)
    {
        $data = $req->validate([
            'payment_intent_id' => 'required_without:charge_id|string|nullable',
            'charge_id' => 'required_without:payment_intent_id|string|nullable',
            'amount_cents' => 'nullable|integer|min:1',
            'reason' => 'nullable|string',
        ]);

        // Get charge id if only PI id is provided
        $chargeId = $data['charge_id'] ?? null;
        if (!$chargeId && $data['payment_intent_id']) {
            $pi = $this->stripe->paymentIntents->retrieve($data['payment_intent_id']);
            $chargeId = $pi->latest_charge;
            if (!$chargeId) {
                throw ValidationException::withMessages(['payment_intent_id' => 'PaymentIntent has no charge to refund.']);
            }
        }

        $params = ['charge' => $chargeId];
        if (!empty($data['amount_cents'])) $params['amount'] = $data['amount_cents'];
        if (!empty($data['reason'])) $params['reason'] = $data['reason'];

        $refund = $this->stripe->refunds->create($params, [
            'idempotency_key' => 'refund:' . ($chargeId ?? $data['payment_intent_id']) . ':' . ($data['amount_cents'] ?? 'full')
        ]);

        // update payments row -> status=refunded (if full), or track partials
        return response()->json(['refund' => $refund]);
    }

    /** ===================== HOTEL (Booking) ===================== */

    // A) Near-term bookings: authorize now (manual capture)
    public function authorizeBooking(Request $req)
    {
        $data = $req->validate([
            'booking_id'   => 'required|integer',
            'amount_cents' => 'required|integer|min:50',
            'currency'     => 'nullable|string',
            'customer'     => 'required|array', // {id,name,email,stripe_customer_id?}
            // If you already created a PaymentMethod on the client, include it:
            'payment_method_id' => 'nullable|string',
        ]);

        $currency = strtoupper($data['currency'] ?? config('services.stripe.currency', 'AUD'));
        $customerId = $this->upsertCustomer($data['customer']);

        $params = [
            'amount' => $data['amount_cents'],
            'currency' => $currency,
            'customer' => $customerId,
            'capture_method' => 'manual',
            'automatic_payment_methods' => ['enabled' => true],
            'metadata' => [
                'owner_type' => 'booking',
                'booking_id' => (string)$data['booking_id'],
            ],
        ];
        if (!empty($data['payment_method_id'])) {
            $params['payment_method'] = $data['payment_method_id'];
            $params['confirm'] = true;
        }

        $pi = $this->stripe->paymentIntents->create($params, [
            'idempotency_key' => "booking_auth:{$data['booking_id']}"
        ]);

        return response()->json([
            'client_secret' => $pi->client_secret,
            'payment_intent_id' => $pi->id,
            'status' => $pi->status, // 'requires_payment_method'|'requires_action'|'requires_capture' etc.
        ]);
    }

    // Capture authorized funds (can be partial)
    public function captureBooking(Request $req)
    {
        $data = $req->validate([
            'payment_intent_id' => 'required|string',
            'amount_cents' => 'nullable|integer|min:50',
        ]);

        $params = [];
        if (!empty($data['amount_cents'])) {
            $params['amount_to_capture'] = $data['amount_cents'];
        }

        $captured = $this->stripe->paymentIntents->capture($data['payment_intent_id'], $params, [
            'idempotency_key' => "booking_capture:{$data['payment_intent_id']}:" . ($data['amount_cents'] ?? 'full')
        ]);

        return response()->json(['captured' => $captured]);
    }

    // Cancel the authorization before capture (guest cancels, etc.)
    public function cancelBookingAuth(Request $req)
    {
        $data = $req->validate(['payment_intent_id' => 'required|string']);

        $canceled = $this->stripe->paymentIntents->cancel($data['payment_intent_id'], [], [
            'idempotency_key' => "booking_cancel:{$data['payment_intent_id']}"
        ]);

        return response()->json(['canceled' => $canceled]);
    }

    // B) Far-future bookings: collect a PM now (SetupIntent), charge at check-in
    public function createBookingSetupIntent(Request $req)
    {
        $data = $req->validate([
            'booking_id' => 'required|integer',
            'customer'   => 'required|array',
        ]);
        $customerId = $this->upsertCustomer($data['customer']);

        $si = $this->stripe->setupIntents->create([
            'customer' => $customerId,
            'automatic_payment_methods' => ['enabled' => true],
            'usage' => 'off_session',
            'metadata' => [
                'owner_type' => 'booking',
                'booking_id' => (string)$data['booking_id'],
            ],
        ], [
            'idempotency_key' => "booking_setup:{$data['booking_id']}"
        ]);

        return response()->json([
            'client_secret' => $si->client_secret,
            'setup_intent_id' => $si->id,
            'status' => $si->status,
        ]);
    }

    // Later (e.g., at check-in), create & confirm a PI off-session using the saved default payment method
    public function chargeSavedPaymentMethod(Request $req)
    {
        $data = $req->validate([
            'booking_id'     => 'required|integer',
            'customer_id'    => 'required|string',
            'amount_cents'   => 'required|integer|min:50',
            'currency'       => 'nullable|string',
        ]);

        $customer = $this->stripe->customers->retrieve($data['customer_id']);
        if (!$customer->invoice_settings?->default_payment_method) {
            throw ValidationException::withMessages(['customer_id' => 'No default payment method saved.']);
        }

        $pi = $this->stripe->paymentIntents->create([
            'customer' => $data['customer_id'],
            'amount' => $data['amount_cents'],
            'currency' => strtoupper($data['currency'] ?? config('services.stripe.currency', 'AUD')),
            'confirm' => true,
            'off_session' => true,
            'automatic_payment_methods' => ['enabled' => true],
            'metadata' => [
                'owner_type' => 'booking',
                'booking_id' => (string)$data['booking_id'],
                'charge_source' => 'saved_pm',
            ],
        ], [
            'idempotency_key' => "booking_charge_savedpm:{$data['booking_id']}:{$data['amount_cents']}"
        ]);

        return response()->json(['payment_intent' => $pi]);
    }
}

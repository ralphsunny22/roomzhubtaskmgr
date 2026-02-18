<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe\StripeClient;

class PaymentsController extends Controller
{
    public function __construct(private StripeClient $stripe) {}

    // Helper: create/get Stripe Customer (store on users table in your app)
    protected function upsertCustomer(array $user): string
    {
        if (!empty($user['stripe_customer_id'])) return $user['stripe_customer_id'];
        $c = $this->stripe->customers->create([
            'name'    => $user['name']  ?? null,
            'email'   => $user['email'] ?? null,
            'metadata'=> ['app_user_id' => (string)($user['id'] ?? '')],
        ]);
        // persist $c->id on your users table
        return $c->id;
    }

    // Create-or-Reuse PI by offer_id (Handyman flow)
    public function createIntentForOffer(Request $req)
    {
        $data = $req->validate([
            'task_id'      => 'required|integer',
            'offer_id'     => 'required|integer',
            'customer'     => 'required|array', // {id,name,email,stripe_customer_id?}
            // Server should decide these from DB; accept optionally if you must:
            'amount_cents' => 'nullable|integer|min:50',
            'currency'     => 'nullable|string',
        ]);

        // 🔒 Pull authoritative values from your DB (recommended)
        // $offer = Offer::with('task')->findOrFail($data['offer_id']);
        // $amountCents = $offer->amount_cents;
        // $currency    = $offer->currency ?? 'AUD';
        // $taskId      = $offer->task_id;

        // If you don’t have the models handy, fall back to request:
        $amountCents = (int)($data['amount_cents'] ?? throw new \Exception('amount_cents required from server'));
        $currency    = strtoupper($data['currency'] ?? config('services.stripe.currency', 'AUD'));
        $taskId      = (int)$data['task_id'];

        $customerId  = $this->upsertCustomer($data['customer']);
        $offerId     = (int)$data['offer_id'];

        // Concurrency-safe “create or reuse”
        $payment = DB::transaction(function () use ($offerId, $taskId, $amountCents, $currency, $customerId) {

            // Lock row if exists to avoid race
            $payment = Payment::where('offer_id', $offerId)->lockForUpdate()->first();

            if ($payment && $payment->stripe_payment_intent_id) {
                // Refresh from Stripe; reuse if still valid
                $pi = $this->stripe->paymentIntents->retrieve($payment->stripe_payment_intent_id);

                // If customer/currency/amount drifted, try to update or replace
                $needsReplace = false;

                // If not confirmed yet, you may update amount/currency/customer
                $updatableStatuses = ['requires_payment_method','requires_confirmation'];
                if (in_array($pi->status, $updatableStatuses, true)) {
                    $update = [];
                    if ($pi->amount   !== $amountCents) $update['amount']   = $amountCents;
                    if (strtoupper($pi->currency) !== strtolower($currency)) $update['currency'] = $currency;
                    if (($pi->customer ?? null) !== $customerId) $update['customer'] = $customerId;

                    if ($update) {
                        $pi = $this->stripe->paymentIntents->update($pi->id, $update);
                    }
                } else {
                    // Already confirmed / requires_action / processing / succeeded / canceled…
                    // Replace only if canceled; if succeeded, just return it (prevent double charge).
                    if ($pi->status === 'canceled') $needsReplace = true;
                }

                if (!$needsReplace) {
                    // Sync & return the existing PI
                    $payment->update([
                        'amount_cents'  => $amountCents,
                        'currency'      => $currency,
                        'status'        => $pi->status,
                        'client_secret' => $pi->client_secret,
                        'stripe_customer_id' => $customerId,
                    ]);
                    return $payment;
                }

                // Fallthrough to replacement if needed
            }

            // No existing usable PI → create new one
            $createParams = [
                'amount'    => $amountCents,
                'currency'  => $currency,
                'customer'  => $customerId,
                'capture_method' => 'automatic', // charge-now for handyman
                'automatic_payment_methods' => ['enabled' => true],
                'metadata'  => [
                    'owner_type' => 'task',
                    'task_id'    => (string)$taskId,
                    'offer_id'   => (string)$offerId,
                ],
            ];

            // Random idempotency key just for network retries of THIS call
            $idem = 'pi_create:' . $offerId . ':' . bin2hex(random_bytes(6));

            $pi = $this->stripe->paymentIntents->create($createParams, [
                'idempotency_key' => $idem,
            ]);

            // Upsert the row keyed by offer_id (unique)
            $payment = Payment::updateOrCreate(
                ['offer_id' => $offerId],
                [
                    'owner_type'             => 'task',
                    'owner_id'               => $taskId,
                    'stripe_customer_id'     => $customerId,
                    'stripe_payment_intent_id'=> $pi->id,
                    'client_secret'          => $pi->client_secret,
                    'amount_cents'           => $amountCents,
                    'currency'               => $currency,
                    'status'                 => $pi->status,
                    'params'                 => $createParams,
                ]
            );

            return $payment;
        });

        return response()->json([
            'payment_intent_id' => $payment->stripe_payment_intent_id,
            'client_secret'     => $payment->client_secret,
            'status'            => $payment->status,
            'offer_id'          => $payment->offer_id,
        ]);
    }
}

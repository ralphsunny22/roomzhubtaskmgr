<?php
namespace App\Http\Controllers\Web\V1;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;
// use App\Models\ConnectedAccount; // Assume you have a model storing acct_ IDs

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        // $endpointSecret = config('services.stripe.webhook_secret_connect'); // Store in .env: STRIPE_WEBHOOK_SECRET_CONNECT=whsec_xxx
        $endpointSecret = env('STRIPE_WEBHOOK_SECRET_CONNECT');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                $endpointSecret
            );
        } catch (SignatureVerificationException $e) {
            // Invalid signature → attack or wrong secret
            return response()->json(['error' => 'Webhook signature verification failed'], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        // Handle the event

        switch ($event->type) {
            case 'account.updated':
                $this->handleAccountUpdated($event->data->object);
                break;

            case 'payout.created':
                $payout = $event->data->object;
                $connectedAcctId = $event->account;  // ← This is the connected account ID!

                // Log creation, update DB: "Payout started for seller X - ID: {$payout->id}, amount: {$payout->amount}"
                // Optional: Notify seller "Your payout of $XX is processing"
                break;

            case 'payout.paid':
                $payout = $event->data->object;
                $connectedAcctId = $event->account;

                // Success! Funds sent → update DB status to "paid"
                // Notify seller: "Your payout has been sent to your bank!"
                // You might want to record the arrival date or reconcile
                break;

            case 'payout.failed':
                $payout = $event->data->object;
                $connectedAcctId = $event->account;
                $failureCode = $payout->failure_code;  // e.g., 'account_closed', 'insufficient_funds'
                $failureMessage = $payout->failure_message;

                // Critical: Alert admin/seller
                // Disable payouts temporarily in your app until fixed
                // The bank account is auto-disabled → seller needs to update it
                // Log: "Payout failed for acct_{$connectedAcctId}: {$failureMessage}"
                break;

            default:
                // Log unknown for debugging
        }

            return response()->json(['status' => 'success'], 200);
    }

    private function handleAccountUpdated($account)
    {
        $acctId = $account->id;

        // Find your DB record
        // $connected = ConnectedAccount::where('stripe_account_id', $acctId)->first();
        // if (!$connected) return;

        // Update DB status
        // $connected->update([
        //     'details_submitted' => $account->details_submitted,
        //     'payouts_enabled' => $account->payouts_enabled,
        //     'requirements_currently_due' => json_encode($account->requirements->currently_due ?? []),
        //     'verification_status' => $account->requirements->disabled_reason ?? 'ok',
        // ]);

        // Optional: Check if ready for payouts
        if ($account->payouts_enabled && empty($account->requirements->currently_due)) {
            // Email seller: "Your account is fully verified and ready!"
            // Or enable features in your app
        }

        // If new requirements appeared
        if (!empty($account->requirements->currently_due)) {
            // Notify seller: "Please provide more info: " . implode(', ', $account->requirements->currently_due)
        }
    }
}

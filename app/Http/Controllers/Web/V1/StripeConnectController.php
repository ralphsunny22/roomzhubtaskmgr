<?php
namespace App\Http\Controllers\Web\V1;
// namespace App\Http\Controllers;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Account;
use Stripe\Payout;
use Stripe\Balance;
use Stripe\Exception\ApiErrorException;

class StripeConnectController extends Controller
{
    public function __construct()
    {
        // Stripe::setApiKey(config('services.stripe.secret')); // Your platform's LIVE or TEST secret key
        Stripe::setApiKey(env('STRIPE_SECRET_KEY'));
    }

    /**
     * Step 1: Create a Custom connected account for Australia
     */
    public function createConnectedAccount(Request $request)
    {
        $user = auth()->user();
        try {
            $account = Account::create([
                'type' => 'custom',
                'country' => 'AU',               // ← Australia
                'email' => $request->email,
                'business_type' => 'individual',
                'individual' => [
                    'first_name' => $request->first_name,      // Sample data - in prod collect real
                    'last_name' => $request->last_name,
                    'dob' => [
                        'day' => $request->dob_day,
                        'month' => $request->dob_month,
                        'year' => $request->dob_year,
                    ],
                    'address' => [
                        'city' => $request->city,
                        'country' => 'AU',
                        'line1' => $request->address_line1,
                        'line2' => $request->address_line2, // optional
                        'postal_code' => $request->postal_code,
                        'state' => $request->state,           // Must be valid AU state
                    ],
                    'phone' => $request->phone,
                    'email' => $request->email,
                ],
                'capabilities' => [
                    'transfers' => ['requested' => true],
                    // Add 'card_payments' => ['requested' => true] if they need to accept cards
                ],
                'settings' => [
                    'payouts' => [
                        'schedule' => [
                            // 'interval' => 'manual', //  // We'll trigger manually in sample, other interval options: daily, weekly, monthly
                            'interval' => 'daily', //  // We'll trigger manually in sample, other interval options: daily, weekly, monthly
                            'delay_days' => 2,    // e.g., 7-day rolling delay // optional // null for no delay
                        ],
                    ],
                ],
                'tos_acceptance' => [
                    'date' => time(), //1736948220
                    'ip' => $request->ip(), // Assumes you're getting user's IP address "203.0.113.42"
                    // 'ip' => '203.0.113.42', // Assumes you're getting user's IP address "203.0.113.42"
                ],
                'business_profile' => [
                    'mcc' => '7399', // Miscellaneous Business Services //5734
                    'url' => 'www.wavezio.com',
                ],
            ]);

            // In production: Save $account->id to your ConnectedAccount table
            $connectedAccount = new \App\Models\ConnectedAccount();
            $connectedAccount->user_id = $user->id; // Assuming you pass user_id in request

            $connectedAccount->email = $request->email ?? $user->email;
            $connectedAccount->first_name = $request->first_name ?? $user->first_name;
            $connectedAccount->last_name = $request->last_name ?? $user->last_name;
            $connectedAccount->dob_day = $request->dob_day ?? null;
            $connectedAccount->dob_month = $request->dob_month ?? null;
            $connectedAccount->dob_year = $request->dob_year ?? null;
            $connectedAccount->address_line1 = $request->address_line1 ?? null;
            $connectedAccount->city = $request->city ?? null;
            $connectedAccount->state = $request->state ?? null;
            $connectedAccount->postal_code = $request->postal_code ?? null;
            $connectedAccount->phone = $request->phone ?? null;
            $connectedAccount->tos_ip_address = $request->ip();
            $connectedAccount->tos_date = time();
            $connectedAccount->business_profile_mcc = $request->business_profile_mcc ?? null;
            $connectedAccount->business_profile_url = $request->business_profile_url ?? null;
            $connectedAccount->business_profile_name = $request->business_profile_name ?? null;

            $connectedAccount->stripe_account_id = $account->id;
            $connectedAccount->details_submitted = $account->details_submitted;
            $connectedAccount->charges_enabled = $account->charges_enabled;
            $connectedAccount->payouts_enabled = $account->payouts_enabled;
            $connectedAccount->requirements_currently_due = json_encode($account->requirements->currently_due);
            $connectedAccount->requirements_eventually_due = json_encode($account->requirements->eventually_due);
            $connectedAccount->save();

            return response()->json([
                'success' => true,
                'account_id' => $account->id,
                'details_submitted' => $account->details_submitted, // false until full KYC
                'requirements' => $account->requirements
            ]);
        } catch (ApiErrorException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Step 2: Add Australian external bank account (BSB + Account Number)
     */
    public function addBankAccount(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'account_id' => 'required|string|starts_with:acct_',
            'bsb_number' => 'required|string|size:6|regex:/^\d{6}$/',  // 6 digits, no dash
            'account_number' => 'required|string|between:6,9',
            'account_holder_name' => 'required|string|max:255',
            'account_holder_type' => 'nullable|in:individual,company',
        ]);

        try {
            $external = \Stripe\Account::createExternalAccount(
                $request->account_id,
                [
                    'external_account' => [
                        'object' => 'bank_account',
                        'country' => 'AU',
                        'currency' => 'aud',
                        'account_holder_name' => $request->account_holder_name,
                        'account_holder_type' => $request->account_holder_type ?? 'individual',
                        'routing_number' => $request->bsb_number,  // ← Key change: BSB goes here!
                        'account_number' => $request->account_number,
                    ]
                ]
            );

            //update in DB if needed
            $connectedAccount = \App\Models\ConnectedAccount::where('stripe_account_id', $request->account_id)->first();

            if ($connectedAccount) {
                $connectedAccount->bsb_number = $request->bsb_number;
                $connectedAccount->account_number = $request->account_number;
                $connectedAccount->account_holder_name = $request->account_holder_name;
                $connectedAccount->save();
            }

            return response()->json([
                'success' => true,
                'bank_id' => $external->id,
                'status' => $external->status,  // 'new' → then 'validated' after checks
                'last4' => $external->last4     // for reference
            ]);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'code' => $e->getCode() ?? null
            ], 400);
        }
    }

    //step 3: upload verification documents
    public function uploadVerificationDocuments(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'account_id' => 'required|string|starts_with:acct_',
            'document_front' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',  // 10MB max recommended by Stripe
            'document_back' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'document_type' => 'required|string|in:passport,drivers_license,national_id', // Optional: for your logic
        ]);

        try {
            // Upload front document
            $frontFile = $request->file('document_front');
            $front = \Stripe\File::create([
                'purpose' => 'identity_document',
                'file' => fopen($frontFile->getRealPath(), 'r'),  // ← This is correct – no 'filename'
            ]);

            $backFileId = null;
            if ($request->hasFile('document_back')) {
                $backFile = $request->file('document_back');
                $back = \Stripe\File::create([
                    'purpose' => 'identity_document',
                    'file' => fopen($backFile->getRealPath(), 'r'),  // No 'filename' here either
                ]);
                $backFileId = $back->id;
            }

            // Now attach the file IDs to the connected account's individual verification
            $account = \Stripe\Account::update($request->account_id, [
                'individual' => [
                    'verification' => [
                        'document' => [
                            'front' => $front->id,
                            'back' => $backFileId ?? null,  // null if no back file
                        ]
                    ]
                ]
            ]);

            return response()->json([
                'success' => true,
                'front_file_id' => $front->id,
                'back_file_id' => $backFileId,
                'details_submitted' => $account->details_submitted,
                'requirements' => $account->requirements  // Check if 'pending_verification' is cleared
            ]);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'stripe_code' => $e->getStripeCode() ?? null  // e.g., 'invalid_request_error'
            ], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    public function checkAccountStatus(Request $request)
    {
        $request->validate([
            'account_id' => 'required|string|starts_with:acct_',
        ]);

        try {
            $account = \Stripe\Account::retrieve($request->account_id);

            return response()->json([
                'success' => true,
                'details_submitted' => $account->details_submitted,
                'charges_enabled' => $account->charges_enabled,
                'payouts_enabled' => $account->payouts_enabled,
                'requirements' => $account->requirements,
                // 'account' => $account,
            ]);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function transferToWorker(Request $request)
    {
        $request->validate([
            'account_id' => 'required|string',   // connected acct
            'amount' => 'required|integer|min:100',
            'description' => 'nullable|string',
        ]);

        try {

            //check $request->account_id status first?
            $account = \Stripe\Account::retrieve($request->account_id);
            if (!$account->payouts_enabled) {
                return response()->json(['error' => 'Connected account payouts not enabled'], 400);
            }

            $transfer = \Stripe\Transfer::create([
                'amount' => $request->amount,
                'currency' => 'aud',
                'destination' => $request->account_id,
                'description' => $request->description ?? 'Worker payout share',
                // Optional: 'source_transaction' => 'ch_xxx' to tie to a specific charge
            ]);

            return response()->json([
                'success' => true,
                'transfer_id' => $transfer->id,
                'status' => $transfer->status,
                'transfer' => $transfer,
            ]);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Step 3: Trigger a manual payout to the default/external bank (test with small amount)
     */
    public function triggerPayout1(Request $request)
    {
        $request->validate([
            'account_id' => 'required|string',
            'amount' => 'required|integer|min:100', // in cents, min ~$1 AUD
        ]);

        try {
            $payout = Payout::create([
                'amount' => $request->amount,      // e.g. 500 = $5.00 AUD
                'currency' => 'aud',
                'method' => 'standard',            // or 'instant' if eligible
                // 'destination' => 'ba_xxx'       // optional: specific bank, null = default
            ], ['stripe_account' => $request->account_id]); // ← Important: on behalf of connected account

            return response()->json([
                'success' => true,
                'payout_id' => $payout->id,
                'status' => $payout->status,
            ]);
        } catch (ApiErrorException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Step 3: Trigger a manual payout to the default/external bank (test with small amount)
     */
    // In your triggerPayout method (or a helper function)
    public function triggerPayout(Request $request)
    {
        $request->validate([
            'account_id' => 'required|string',
            'amount' => 'required|integer|min:100', // in cents
        ]);

        $accountId = $request->account_id;
        $requestedAmount = $request->amount; // in cents

        try {
            // Step 1: Retrieve the connected account's balance
            $balance = Balance::retrieve([], ['stripe_account' => $accountId]);

            // Get the available amount in AUD (adjust currency if needed)
            $availableAud = 0;
            foreach ($balance->available as $bal) {
                if ($bal->currency === 'aud') {
                    $availableAud = $bal->amount; // in cents
                    break;
                }
            }

            // Optional: You can also check pending if you want
            // $pendingAud = 0;
            // foreach ($balance->pending as $pend) {
            //     if ($pend->currency === 'aud') {
            //         $pendingAud = $pend->amount;
            //     }
            // }

            // Step 2: Compare available balance with requested payout amount
            if ($availableAud < $requestedAmount) {
                return response()->json([
                    'error' => 'Insufficient funds in the connected account. ' .
                            "Available: {$availableAud} cents, Requested: {$requestedAmount} cents"
                ], 400);
            }

            // Step 3: Safe to proceed — create the payout
            $payout = \Stripe\Payout::create([
                'amount'   => $requestedAmount,
                'currency' => 'aud',
                'method'   => 'standard', // or 'instant' if eligible
                // 'destination' => 'ba_xxx' // optional
            ], ['stripe_account' => $accountId]);

            return response()->json([
                'success'    => true,
                'payout_id'  => $payout->id,
                'status'     => $payout->status,
                'available_before' => $availableAud // for logging/debug
            ]);

        } catch (ApiErrorException $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'stripe_code' => $e->getStripeCode() ?? null
            ], 400);
        }
    }

}

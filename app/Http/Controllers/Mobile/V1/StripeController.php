<?php

namespace App\Http\Controllers\Mobile\V1;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\CentralLogics\Helpers;

use Stripe\Stripe;
use Stripe\Account;
use Stripe\AccountLink;
use App\Models\FreelancerAccount as AccountModel; // Assuming you have an Eloquent model for the accounts table

class StripeController extends Controller
{
    public function createStripeCustomConnectedAccount(Request $request)
    {
        try {
            // Initialize Stripe with your secret key
            Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

            // Get authenticated user (assuming user is authenticated)
            // $authUser = $request->user();
            $authUser = Auth::user();

            // Create a custom connected account
            $account = Account::create([
                'type' => 'custom',
                'country' => 'AU', // Replace with desired country
                'capabilities' => [
                    'transfers' => ['requested' => true],
                ],
            ]);

            // Get base URL dynamically or define it in the environment configuration
            // $baseUrl = config('app.url'); // Or dynamically from request: $request->getSchemeAndHttpHost();

            // Create an account link for onboarding
            $accountLink = AccountLink::create([
                'account' => $account->id,
                'refresh_url' => route('stripe.reauth'), // Named Laravel route for re-authentication
                'return_url' => route('stripe.onboarding-success'), // Named Laravel route for successful onboarding
                'type' => 'account_onboarding', // For onboarding, adjust if needed
            ]);

            // Check if user already has an account in the database
            $existingAcct = AccountModel::where('user_id', $authUser->id)->first();

            // Prepare new account data
            $newAccount = [
                'user_id' => $authUser->id,
                'account_id' => $account->id,
                'status' => 'restricted',
            ];

            // Update or create new account record in the database
            if ($existingAcct) {
                $existingAcct->update($newAccount);
            } else {
                AccountModel::create($newAccount);
            }

            // Return the account link for onboarding
            return response()->json(['url' => $accountLink->url]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    // public function reauth()
    // {
    //     return response()->json(['message' => 'Reauth initiated']);
    // }

    // public function onboardingSuccess()
    // {
    //     return response()->json(['message' => 'Onboarding successful']);
    // }
    public function onboardingSuccess()
    {
        // Redirect user to a frontend React success page
        return redirect('https://your-frontend-url.com/onboarding-success');
    }

    //for mobile
    public function reauth()
    {
        // Redirect user to a frontend React page for retrying the onboarding
        return redirect('https://your-frontend-url.com/reauth');
    }

    // public function onboardingSuccess()
    // {
    //     // Redirect to a deep link that opens the Flutter app
    //     return redirect('myapp://onboarding-success');
    // }

    // public function reauth()
    // {
    //     // Redirect to a deep link that opens the Flutter app
    //     return redirect('myapp://reauth');
    // }



}



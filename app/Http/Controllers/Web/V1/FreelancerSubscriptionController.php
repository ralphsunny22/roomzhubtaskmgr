<?php

namespace App\Http\Controllers\Web\V1;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\FreelancerSubscriptionPlan;
use Carbon\Carbon;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class FreelancerSubscriptionController extends Controller
{
    /**
     * Create or renew a yearly subscription for the authenticated user.
     */
    public function createOrRenew(Request $request)
    {
        Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

        $request->validate([
            'plan_name' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();

        $existingUser = FreelancerSubscriptionPlan::where(['user_id'=>$user->id, 'plan_name'=>$request->plan_name, 'status'=>'active'])->first();
        if ($existingUser) {
            return response()->json(['status' => 'error', 'message' => 'You are currenlty active on this plan'.$request->plan_name], 400);
        }

        $stripe = new \Stripe\StripeClient(
            env('STRIPE_SECRET_KEY')
        );

        $amount = 0; $duration_in_days = 0;
        if ($request->plan_name=='basic') {
            $subscription = FreelancerSubscriptionPlan::create(
                [
                    'plan_name' => $request->plan_name,
                    'user_id' => $user->id,
                    'amount' => 0.00,
                    'start_date' => null,
                    'end_date' => null,
                    'payment_intent' => null,
                    'status' => 'active',
                ]
            );
            return response()->json([
                'success' => true,
                'clientSecret' => null,
                'subscription' => $subscription,
            ]);
        } elseif ($request->plan_name=='bronze') {
            $amount = 9.99 * 100;
            $duration_in_days = 7;
        } elseif ($request->plan_name=='silver') {
            $amount = 14.00 * 100;
            $duration_in_days = 30;
        } elseif ($request->plan_name=='gold') {
            $amount = 39.99 * 100;
            $duration_in_days = 90;
        } elseif ($request->plan_name=='platinum') {
            $amount = 99.99 * 100;
            $duration_in_days = 180;
        } elseif ($request->plan_name=='diamond') {
            $amount = 149.99 * 100;
            $duration_in_days = 365;
        } else {
            return response()->json(['status' => 'error', 'message' => 'Invalid plan name'], 400);
        }

        $startDate = Carbon::now();
        $endDate = $startDate->copy()->addDays($duration_in_days);

        // Create PaymentIntent
        $paymentIntent = $stripe->paymentIntents->create([
            'amount' => $amount, // Convert to cents
            'currency' => 'aud',
            'payment_method_types' => ['card'],
        ]);

        if (!isset($paymentIntent->client_secret)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid Payment Initialization'], 400);
        }

        $paymentIntent = PaymentIntent::create([
            'amount' => $amount,
            'currency' => 'aud',
            'payment_method_types' => ['card'],
        ]);

        if ($paymentIntent->client_secret) {
            $subscription = FreelancerSubscriptionPlan::create(
                [
                    'plan_name' => $request->plan_name,
                    'user_id' => $user->id,
                    'amount' => $amount,
                    'duration_in_days' => $duration_in_days,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'payment_intent' => $paymentIntent->client_secret,
                    'status' => 'pending',
                ]
            );
        }

        return response()->json([
            'success' => true,
            'clientSecret' => $paymentIntent->client_secret,
            'subscription' => $subscription,
        ]);
    }

    /**
     * Simulate payment or handle real payment gateway callback.
     *
     * After payment success, create or renew the subscription.
     */
    public function handlePaymentSuccess(Request $request)
    {
        $request->validate([
            'subscription_plan_id' => 'required',
            'payment_intent' => 'nullable|string', // e.g. from Stripe/Paystack
        ]);

        $user = Auth::user();

        // Check if user already has a subscription
        $existingPlan = FreelancerSubscriptionPlan::where('user_id', $user->id)->where('id', $request->subscription_plan_id)->latest()->first();

        if ($existingPlan && !$existingPlan->is_expired) {
            // User is trying to pay again while still active
            return response()->json([
                'success' => false,
                'message' => 'You already have an active subscription plan.',
                'data' => $existingPlan,
            ], 409);
        }

        // Cancel the existing plan if any
        FreelancerSubscriptionPlan::where('user_id', $user->id)->where(['id'=>$request->subscription_plan_id, 'status','active'])->update(['status' => 'cancelled']);

        //update existingPlan status to expired
        $existingPlan->status = 'active';
        $existingPlan->save();

        return response()->json([
            'success' => true,
            'message' => 'Payment successful. Subscription activated.',
            'data' => $existingPlan,
        ]);
    }

    /**
     * Return the user’s current active plan, indicating if expired.
     */
    public function currentActivePlan()
    {
        $user = Auth::user();

        $subscription = FreelancerSubscriptionPlan::where('user_id', $user->id)
            ->latest()
            ->first();

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'No active subscription found.',
            ]);
        }

        $isExpired = $subscription->is_expired;

        if ($isExpired && $subscription->status !== 'expired') {
            $subscription->update(['status' => 'expired']);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'subscription' => $subscription,
                'is_expired' => $isExpired,
            ],
        ]);
    }
}

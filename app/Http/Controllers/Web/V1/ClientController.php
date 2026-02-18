<?php

namespace App\Http\Controllers\Web\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\CentralLogics\Helpers;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\StripeClient;
use App\Rules\ValidCoupon;
use Illuminate\Support\Str;

use App\Models\Task;
use App\Models\TaskOffer;
use App\Models\Payment;
use App\Models\User;

class ClientController extends Controller
{
    private StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    /** Create/get or attach a Stripe Customer for a user */
    protected function upsertCustomer($user): string
    {
        // You likely store stripe_customer_id on users table.
        if ($user->stripe_customer_id) {
            return $user->stripe_customer_id;
        }
        $customer = $this->stripe->customers->create([
            'name'  => $user->name ?? null,
            'email' => $user->email ?? null,
            'metadata' => [
                'app_user_id' => (string)($user->id ?? ''),
                'phone_number' => (string)($user->phone_number ?? ''),
            ],
        ]);
        // persist $customer->id on your users table
        User::where('id', $user->id)->update(['stripe_customer_id' => $customer->id]);
        return $customer->id;
    }

    /**
     * Display a listing of the resource.
     */
    public function myTasks()
    {
        $perPage = 30; // Adjust perPage value as needed
        $user = Auth::user();
        $tasks = $user->clientTasks()->orderBy('id', 'desc')->paginate($perPage);
        return response()->json([
            'success' => true,
            'data' => $tasks
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function createTask(Request $request)
    {
        try {
            //code...
            $data = $request->all();

            // Get the authenticated user
            $user = Auth::user();

            $task = new Task();
            $task->created_by = $user->id;
            $task->freelancer_id = null;
            $task->task_title = $data['task_title'] ?? null;
            $task->task_date_preceed = $data['task_date_preceed'] ?? null;
            $task->task_date = $data['task_date'] ? Carbon::parse($data['task_date'])->format('Y-m-d') : null;
            $task->task_part_of_day = $data['task_part_of_day'] ?? null;
            $task->task_time_of_day = $data['task_time_of_day'] ?? null;
            $task->is_removal_task = $data['is_removal_task'] === 'true' ? true : false;

            $task->pickup_latitude = $data['pickup_latitude'] ?? null;
            $task->pickup_longitude = $data['pickup_longitude'] ?? null;
            $task->pickup_city = $data['pickup_city'] ?? null;
            $task->pickup_state = $data['pickup_state'] ?? null;
            $task->pickup_country = $data['pickup_country'] ?? null;
            $task->pickup_address = $data['pickup_address'] ?? null;

            $task->dropoff_latitude = $data['dropoff_latitude'] ?? null;
            $task->dropoff_longitude = $data['dropoff_longitude'] ?? null;
            $task->dropoff_city = $data['dropoff_city'] ?? null;
            $task->dropoff_state = $data['dropoff_state'] ?? null;
            $task->dropoff_country = $data['dropoff_country'] ?? null;
            $task->dropoff_address = $data['dropoff_address'] ?? null;

            $task->is_done_online = $data['is_done_online'] === 'true' ? true : false;
            $task->is_done_inperson = $data['is_done_inperson'] === 'true' ? true : false;
            $task->task_description = $data['task_description'] ?? null;

            $task->task_budget = $data['task_budget'] ? (int) $data['task_budget'] : null;

            $task->status = 'pending';

            $task_images = [];
            if ($request->file('task_images')) {
                foreach ($request->file('task_images') as $image) {
                    $task_images[] = Helpers::upload('tasks/', 'png', $image, 'noimage.png');
                }
            }

            $task->task_images = !empty($task_images) ? json_encode($task_images) : null;

            $task->save();

            //make user a client
            if(!$user->is_client){
                $user->is_client = true;
                $user->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Task Created Successfully',
                'data' => $task
            ]);

        } catch (\Exception $e) {
            //throw $th;
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function fromEstore(Request $request)
    {
        try {
            $data = $request->all();
            $user = User::where('email',$data['created_by'])->firstOrFail();

            $task = new Task();
            $task->source = $data['source'] ?? 'estore';
            $task->meta_data = $data['meta_data'] ?? null;

            $task->created_by = $user->id; // external
            $task->task_title = $data['task_title'] ?? 'Estore Job';
            $task->task_date = $data['task_date'] ?? now()->format('Y-m-d');
            $task->task_description = $data['task_description'] ?? null;
            $task->task_budget = $data['task_budget'] ?? null;

            // Pickup/Dropoff mapping
            $task->pickup_address = $data['pickup_address'] ?? null;
            $task->pickup_city = $data['pickup_city'] ?? null;
            $task->pickup_state = $data['pickup_state'] ?? null;
            $task->pickup_country = $data['pickup_country'] ?? null;
            $task->pickup_latitude = $data['pickup_latitude'] ?? null;
            $task->pickup_longitude = $data['pickup_longitude'] ?? null;

            $task->dropoff_address = $data['dropoff_address'] ?? null;
            $task->dropoff_city = $data['dropoff_city'] ?? null;
            $task->dropoff_state = $data['dropoff_state'] ?? null;
            $task->dropoff_country = $data['dropoff_country'] ?? null;
            $task->dropoff_latitude = $data['dropoff_latitude'] ?? null;
            $task->dropoff_longitude = $data['dropoff_longitude'] ?? null;

            $task->status = 'pending';
            $task->save();

            return response()->json([
                'success' => true,
                'message' => 'Task Created from Estore',
                'data' => $task,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Single Task for client
     */
    public function singleTask($id)
    {
        try {
            $user = Auth::user();

            $task = Task::findOrFail($id);
            if ($task->createdBy->id == $user->id) {
                $task['offers'] = $task->offers;
                return response()->json([
                    'success' => true,
                    'message' => $task,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized request',
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }

    }

    /**
     * Update the specified resource in storage.
     */
    public function updateTask(Request $request, $id)
    {
        try {
            // Fetch the task by ID
            $task = Task::findOrFail($id);

            $data = $request->all();

            // Get the authenticated user
            $user = Auth::user();

            // Update task details
            $task->task_title = $data['task_title'] ?? $task->task_title;
            $task->task_date_preceed = $data['task_date_preceed'] ?? $task->task_date_preceed;
            $task->task_date = $data['task_date'] ? Carbon::parse($data['task_date'])->format('Y-m-d') : $task->task_date;
            $task->task_part_of_day = $data['task_part_of_day'] ?? $task->task_part_of_day;
            $task->task_time_of_day = $data['task_time_of_day'] ?? $task->task_time_of_day;
            $task->is_removal_task = $data['is_removal_task'] === 'true' ? true : $task->is_removal_task;

            // Update pickup and dropoff details
            $task->pickup_latitude = $data['pickup_latitude'] ?? $task->pickup_latitude;
            $task->pickup_longitude = $data['pickup_longitude'] ?? $task->pickup_longitude;
            $task->pickup_city = $data['pickup_city'] ?? $task->pickup_city;
            $task->pickup_state = $data['pickup_state'] ?? $task->pickup_state;
            $task->pickup_country = $data['pickup_country'] ?? $task->pickup_country;
            $task->pickup_address = $data['pickup_address'] ?? $task->pickup_address;

            $task->dropoff_latitude = $data['dropoff_latitude'] ?? $task->dropoff_latitude;
            $task->dropoff_longitude = $data['dropoff_longitude'] ?? $task->dropoff_longitude;
            $task->dropoff_city = $data['dropoff_city'] ?? $task->dropoff_city;
            $task->dropoff_state = $data['dropoff_state'] ?? $task->dropoff_state;
            $task->dropoff_country = $data['dropoff_country'] ?? $task->dropoff_country;
            $task->dropoff_address = $data['dropoff_address'] ?? $task->dropoff_address;

            $task->is_done_online = $data['is_done_online'] === 'true' ? true : $task->is_done_online;
            $task->is_done_inperson = $data['is_done_inperson'] === 'true' ? true : $task->is_done_inperson;
            $task->task_description = $data['task_description'] ?? $task->task_description;

            $task->task_budget = isset($data['task_budget']) ? (int) $data['task_budget'] : $task->task_budget;

            $new_task_images = [];
            if ($request->file('task_images')) {
                foreach ($request->file('task_images') as $image) {
                    $new_task_images[] = Helpers::upload('tasks/', 'png', $image, 'noimage.png');
                }
            }
            //merge before updating in db
            $remaining_former_images = $data['task_former_images'];

            $formerImageNames = [];
            if (count($remaining_former_images) > 0) {
                foreach ($remaining_former_images as $key => $img) {
                    $pathInfo = pathinfo($img);
                    $formerImageNames[] = $pathInfo['basename'];
                }
            }
            $mergedTaskImages = count($remaining_former_images) > 0 ? array_merge($new_task_images, $formerImageNames) : $new_task_images;

            $existingTaskImages = $task->task_images;

            // Find the difference and remove
            $imagesToBeRemoved = array_diff($existingTaskImages, $remaining_former_images);
            if (count($imagesToBeRemoved) > 0) {
                foreach ($imagesToBeRemoved as $key => $img) {
                    $pathInfo = pathinfo($img);
                    $imageName = $pathInfo['basename'];
                    Helpers::removeFile('tasks/', $imageName, 'noimage.png');
                }
            }

            // return response()->json([
            //     'success' => true,
            //     'message' => 'Task Updated Successfully',
            //     'existingTaskImages' => $existingTaskImages,
            //     'currentImagesArray' => $new_task_images,
            //     'currentImagesJsonEncode' => json_encode($new_task_images),
            //     'formerImagesArray' => $request->task_former_images,
            //     'formerImagesJsonEncode' => json_encode($request->task_former_images),
            //     'mergedTaskImages' => count($mergedTaskImages),
            //     'imagesToBeRemoved' => $imagesToBeRemoved,
            // ]);

            // Save updated task
            $task->task_images = json_encode($mergedTaskImages);
            $task->save();

            return response()->json([
                'success' => true,
                'message' => 'Task Updated Successfully',
                'data' => $task
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    //all offers regardless of tasks
    public function taskOffers1($task_id="")
    {
        $perPage = 30; // Adjust perPage value as needed
        $user = Auth::user();
        $taskOffers = $task_id ?
        $user->clientTaskOffers()->with('freelancer')->where('task_id', $task_id)->orderBy('id', 'desc')->paginate($perPage) :
        $user->clientTaskOffers()->with('freelancer')->orderBy('id', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $taskOffers
        ]);
    }

    public function taskOffers2($task_id = "")
    {
        $perPage = 30;
        $user = Auth::user();

        $query = $user->clientTaskOffers()
            ->with([
                'freelancer',
                'rating', // rating for this offer
                'freelancerRatings' => function($q) {
                    $q->select('task_freelancer_id', \DB::raw('AVG(rating_value) as avg_rating'), \DB::raw('COUNT(*) as total_reviews'))
                    ->groupBy('task_freelancer_id');
                }
            ])
            ->orderBy('id', 'desc');

        if ($task_id) {
            $query->where('task_id', $task_id);
        }

        $taskOffers = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $taskOffers
        ]);
    }

    public function taskOffers($task_id = "")
    {
        $perPage = 100;
        $user = Auth::user();

        try {
            $query = $user->clientTaskOffers()->orderBy('id', 'desc');

            if ($task_id) {
                $query->where('task_id', $task_id);
            }

            return response()->json([
                'success' => true,
                'data' => $query->paginate($perPage)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }


    }

    public function singleOffer($task_offer_id)
    {
        try {
            $user = Auth::user();

            $taskOffer = TaskOffer::findOrFail($task_offer_id);
            if ($taskOffer->client->id == $user->id) {
                return response()->json([
                    'success' => true,
                    'message' => $taskOffer,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized request',
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }

    }

    //create payment intent
    public function acceptOffer1(Request $request, $task_offer_id)
    {
        try {
            $user = Auth::user();

            $taskOffer = TaskOffer::findOrFail($task_offer_id);

            //valid task owner
            if ($taskOffer->client->id == $user->id) {

                Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

                $amount = $request->amount * 100; // Amount in cents

                $paymentIntent = PaymentIntent::create([
                    'amount' => $amount,
                    'currency' => 'usd',
                    'payment_method_types' => ['card'],
                ]);

                return response()->json([
                    'clientSecret' => $paymentIntent->client_secret,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized request',
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    //create payment intent
    public function acceptOffer(Request $request)
    {
        try {
            $user = Auth::user();

            $validator = Validator::make($request->all(), [
                'task_offer_id' => 'required|exists:task_offers,id',
                'amount' => 'required|integer|min:10',
                'currency' => 'nullable|string',
            ], [
                'amount.required' => 'amount is required.',
                'currency.string' => 'currency must be a string.',
            ]);

            // Proceed with the rest of the logic
            if ($validator->fails()) {
                return response()->json(['status' => false, 'errors' => Helpers::error_processor($validator)], 403);
            }

            $data = $request->all();
            $task_offer_id = $data['task_offer_id'];

            $currency = strtoupper($data['currency'] ?? config('services.stripe.currency', 'AUD'));
            // $customer = User::where('id', $user->id)->select('id', 'name', 'email', 'stripe_customer_id');

            $customerId = $this->upsertCustomer($user);

            $taskOffer = TaskOffer::findOrFail($task_offer_id);
            $task = Task::findOrFail($taskOffer->task_id);

            // Idempotency key: one per accept action
            $random = Str::random(10);
            $idem = "task_accept:{$taskOffer->task_id}:offer:{$task_offer_id}:rand:{$random}";
            // if ($task->idempotency_key===$idem) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Unauthorized request',
            //     ]);
            // }
            $amountCents = $data['amount'] * 100;
            // $payment = Payment::where(['created_by'=>$user->id, 'task_id'=>$task->id, 'task_offer_id'=>$user->id])->first();
            // if ($payment->stripe_client_secret && $payment->stripe_payment_intent_id && $payment->stripe_payment_status) {
            //     // Refresh from Stripe; reuse if still valid
            //     $pi = $this->stripe->paymentIntents->retrieve($payment->stripe_payment_intent_id);

            //     // If customer/currency/amount drifted, try to update or replace
            //     $needsReplace = false;

            //     // If not confirmed yet, you may update amount/currency/customer
            //     $updatableStatuses = ['requires_payment_method','requires_confirmation'];
            //     if (in_array($pi->status, $updatableStatuses, true)) {
            //         $update = [];
            //         if ($pi->total !== $amountCents) $update['amount'] = $amountCents;
            //         if (strtoupper($pi->currency) !== strtolower($currency)) $update['currency'] = $currency;
            //         if (($pi->customer ?? null) !== $customerId) $update['customer'] = $customerId;

            //         if ($update) {
            //             $pi = $this->stripe->paymentIntents->update($pi->id, $update);
            //         }
            //     } else {
            //         // Already confirmed / requires_action / processing / succeeded / canceled…
            //         // Replace only if canceled; if succeeded, just return it (prevent double charge).
            //         if ($pi->status === 'canceled') $needsReplace = true;
            //     }

            //     if (!$needsReplace) {
            //         // Sync & return the existing PI
            //         $payment->update([
            //             'total' => $amountCents,
            //             'currency' => $currency,
            //             'stripe_payment_status' => $pi->status,
            //             'stripe_client_secret' => $pi->client_secret,
            //             // 'stripe_customer_id' => $customerId,
            //         ]);
            //         // return $payment;
            //     }

            // }

            // No existing usable PI → create new one
            $createParams = [
                'amount' => $amountCents,
                'currency' => $currency,
                'customer' => $customerId,
                // 'capture_method' => 'automatic', // charge-now for handyman
                'capture_method' => 'manual', // charge-later for handyman
                'automatic_payment_methods' => ['enabled' => true],
                'metadata'  => [
                    'owner_type' => 'task',
                    'task_id'    => (string)$task->id,
                    'offer_id'   => (string)$taskOffer->id,
                ],
            ];

            //valid task owner
            if ($taskOffer->client?->id == $user->id) {
                $pi = $this->stripe->paymentIntents->create($createParams, [
                    'idempotency_key' => $idem,
                ]);
                // $pi = $this->stripe->paymentIntents->create([
                //     'amount' => $data['amount'] * 100,
                //     'currency' => $currency,
                //     'customer' => $customerId,
                //     'automatic_payment_methods' => ['enabled' => true],
                //     // 'capture_method' => 'automatic', // charge now
                //     'capture_method' => 'manual', // charge later
                //     'metadata' => [
                //         'owner_type' => 'task',
                //         'task_id' => (string)$taskOffer->task_id,
                //         'offer_id' => (string)$task_offer_id,
                //     ],
                // ], ['idempotency_key' => $idem]);

                // persist to payments table with status=requires_action|requires_confirmation|processing depending on pi.status
                return response()->json([
                    'success' => true,
                    'stripe_client_secret' => $pi->client_secret,
                    'stripe_payment_intent_id' => $pi->id,
                    'stripe_payment_status' => $pi->status,
                ]);

            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized request',
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    //save payment after intent
    public function confirmPayment(Request $request)
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'stripe_payment_method_id' => 'nullable',
            'stripe_client_secret' => 'required',
            'stripe_payment_intent_id' => 'required',
            'stripe_payment_status' => 'required',

            'task_id' => 'required|exists:tasks,id',
            'task_offer_id' => 'required|exists:task_offers,id',

            'currency' => 'nullable|string',
            'subtotal' => 'required|numeric',
            'tax' => 'nullable|numeric',
            'payment_method_type' => 'required|string',

            'coupon_value' => 'nullable|numeric|min:1',
            'coupon_code' => ['nullable', 'string', 'max:10', new ValidCoupon, 'required_with:coupon_value'],

            'status' => 'nullable|string',
        ], [
            'task_id.required' => 'The task-id is required.',
            'task_id.exists' => 'The selected task does not exist.',

            'task_offer_id.required' => 'The task-offer-id is required.',
            'task_offer_id.exists' => 'The selected task-offer does not exist.',

            'subtotal.required' => 'The subtotal is required.',
            'subtotal.numeric' => 'The subtotal must be a number.',
            'tax.numeric' => 'The tax must be a number.',
            'payment_method_type.required' => 'Payment method type is required.',
            'payment_method_type.string' => 'Payment method type must be a string.',

            'coupon_code.string' => 'The coupon code must be a string.',
            'coupon_code.max' => 'The coupon code may not be greater than 10 characters.',
            'coupon_code.exists' => 'The selected coupon code is invalid.',
            'coupon_code.required_with' => 'The coupon code is required when a coupon value is present.',
        ]);

        // Handle validation failure
        // if ($validator->fails()) {
        //     return response()->json([
        //         'errors' => $validator->errors(),
        //     ], 422);
        // }

        // Proceed with the rest of the logic
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => Helpers::error_processor($validator)], 403);
        }

        $task = Task::where('id', $request->task_id)->first();
        $taskOffer = TaskOffer::where('id', $request->task_offer_id)->first();

        $invoiceId = 100000 + Payment::count() + 1;
        $invoiceId = (string)$invoiceId . 'T';

        $coupon = !empty($request->coupon_code) ? Coupon::where('code', $request->coupon_code)->first() : null;

        $subtotal = $request->subtotal ?? 0;
        $tax = $request->tax ?? 0;
        $couponValue = $request->coupon_value ?? 0;
        $total = $subtotal + $tax + $couponValue;

        //check if payment already exists for this task and offer by user
        $existingPayment = Payment::where(['created_by' => $user->id, 'task_id' => $task->id, 'task_offer_id' => $taskOffer->id])->first();
        // if ($existingPayment) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Payment already exists for this task and offer',
        //     ]);
        // }

        $payment = $existingPayment ? $existingPayment : new Payment();
        $payment->created_by = $user->id;
        // $payment->unique_key = $user->id;
        $payment->payment_invoice_id = $invoiceId;
        $payment->task_id = $task->id;
        $payment->task_offer_id = $taskOffer->id;
        $payment->description = $request->description ?? null;
        $payment->currency = $request->currency ?? 'USD';
        $payment->subtotal = $subtotal;
        $payment->tax = $tax;
        $payment->has_coupon = $coupon ? true : false;
        $payment->coupon_id = $coupon ? $coupon->id : null;
        $payment->coupon_value = $couponValue;
        $payment->total = $total;
        $payment->payment_method_type = $request->payment_method_type ? $request->payment_method_type : 'card';
        $payment->status = (empty($request->stripe_client_secret) || empty($request->stripe_payment_intent_id) || empty($request->stripe_payment_status)) ? 'failed' : 'success'; ////success, failed

        $payment->stripe_payment_method_id = $request->stripe_payment_method_id ? $request->stripe_payment_method_id : null;
        $payment->stripe_client_secret = $request->stripe_client_secret ? $request->stripe_client_secret : null;
        $payment->stripe_payment_intent_id = $request->stripe_payment_intent_id ? $request->stripe_payment_intent_id : null;
        $payment->stripe_payment_status = $request->stripe_payment_status;
        $payment->save();

        //update task
        if ($request->status !== 'failed') {
            $task->freelancer_id = $taskOffer->freelancer_id;
            $task->status = 'accepted';
            $task->accepted_at = now();
            $task->save();

            $taskOffer->status = 'accepted';
            $taskOffer->save();

            return response()->json([
                'success' => true,
                'message' => 'Task Offer Accepted and Paid Successfully!',
                'data' => $payment
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => 'Payment Failed',
        ]);

    }

    // Capture authorized funds (can be partial)
    public function captureBooking1(Request $req)
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

    public function markTaskAsComplete(Request $req)
    {
        $data = $req->validate([
            'task_offer_id' => 'required',
        ]);

        $offer = TaskOffer::findOrFail($data['task_offer_id']);
        $task = Task::findOrFail($offer->task_id);

        $payment = Payment::where('task_offer_id', $task_offer_id)->first();

        if (!$payment?->stripe_payment_intent_id) {
            return response()->json([
                'success' => false,
                'message' => 'Payment has no intent record'
            ], 400);
        }

        // Fetch intent first
        $intent = $this->stripe->paymentIntents->retrieve($payment->stripe_payment_intent_id);

        // Check if already captured (succeeded) or no amount left to capture
        if ($intent->status === 'succeeded' || $intent->amount_capturable == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Payment has already been captured',
                'intent'  => $intent,
            ], 400);
        }

        // Build params
        $params = [];
        // if (!empty($data['amount'])) {
        //     $params['amount_to_capture'] = $data['amount'];
        // }
        $params['amount_to_capture'] = $payment->total;

        // Capture
        $captured = $this->stripe->paymentIntents->capture(
            $payment->stripe_payment_intent_id,
            $params,
            [
                'idempotency_key' => "task_offer_capture:{$payment->stripe_payment_intent_id}:" . ($payment->total ?? 'full')
            ]
        );

        // return response()->json(['captured' => $captured]);

        $offer->status = $captured ? 'paid' : 'completed';
        $offer->save();

        $task->client_completed_at = now();
        $task->status = $captured ? 'paid' : 'completed';
        $task->save();

        $payment->stripe_payment_status = $captured ? 'paid' : 'completed';
        $payment->status = $captured ? 'paid' : 'completed';
        $payment->save();

        return response()->json([
            'success' => true,
            'message' => 'Task Offer Completed Successfully',
            'captured' => $captured,
            'offer' => $offer,
            'task' => $task
        ]);

    }

    public function cancelTaskOffer(Request $req)
    {
        $data = $req->validate(['task_offer_id' => 'required']);

        $user = Auth::user();

        $offer = TaskOffer::findOrFail($data['task_offer_id']);
        $task = Task::findOrFail($offer->task_id);

        //check ownership
        if($task->created_by !== $user->id){
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized request',
            ]);
        }

        $payment = Payment::where(['task_offer_id'=>$offer->id]);

        $canceled = null;
        if ($payment->stripe_payment_intent_id) {
            // Retrieve intent first
            $intent = $this->stripe->paymentIntents->retrieve($payment->stripe_payment_intent_id);

            // Check if cancel is possible
            if (in_array($intent->status, ['succeeded', 'canceled'])) {
                return response()->json([
                    'message' => "PaymentIntent cannot be canceled because its status is '{$intent->status}'",
                    'intent'  => $intent,
                ], 400);
            }

            // Proceed with cancel
            $canceled = $this->stripe->paymentIntents->cancel(
                $payment->stripe_payment_intent_id,
                [],
                [
                    'idempotency_key' => "task_offer_cancel:{$payment->stripe_payment_intent_id}"
                ]
            );
        }

        $offer->status = 'declined';
        $offer->save();

        //check if there's a freelancer then update the task
        if ($task->freelancer_id == $offer->freelancer_id) {
            // $task->client_cancelled_at = now();
            $task->freelancer_id = null;
            $task->status = 'pending';
        }

        return response()->json([
            'success' => true,
            'message' => 'Task Offer Cancelled Successfully',
            'stripe_payment_canceled' => $canceled,
            'offer' => $offer,
            'task' => $task
        ]);
    }

    public function updateTaskStatus(string $task_id, $status)
    {
        try{
            $task = Task::findOrFail($task_id);

            //check ownership
            $user = Auth::user();
            if($task->created_by !== $user->id){
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized request',
                ]);
            }
           //freelancer status: pending, accepted, started, completed, cancelled, abandoned
            if ($status=="pending") {
                $task->freelancer_id = null;
                $task->status = 'pending';
            }
            if ($status=="accepted") {
                $task->accepted_at = now();
                $task->status = 'accepted';
            }
            if ($status=="started") {
                $task->client_started_at = now();
                $task->status = 'started';
            }
            // if ($status=="completed") {
            //     $task->client_completed_at = now();
            //     $task->status = 'completed';
            // }
            // if ($status=="paid") {
            //     $task->client_paid_at = now();
            //     $task->status = 'paid';
            // }
            // if ($status=="cancelled") {
            //     $task->client_cancelled_at = now();
            //     $task->status = 'cancelled';
            // }
            if ($status=="abandoned") {
                $task->freelancer_abandoned_at = now(); //if freelancer abandoned the task
                $task->status = 'abandoned';
            }

            if ($status=="completed") {
                $task->client_completed_at = now();
                $task->status = 'completed';
            }

            $task->save();

            return response()->json([
                'success' => true,
                'message' => 'Task Status Updated Successfully',
                'data' => $task,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }

    }

    //release payment to handyman
    public function releasePaymentToHandyman(Request $req)
    {
        $user = auth()->user();
        $data = $req->validate([
            'offer_id' => 'required',
        ]);

        $taskOffer = TaskOffer::find($data['offer_id']);
        if(!$taskOffer){
            return response()->json([
                'success' => false,
                'message' => 'Offer does not exist',
            ], 404);
        }

        $task = Task::find($taskOffer->task_id);
        if($task->created_by !== $user->id){
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized request',
            ], 403);
        }

        $pi = $this->stripe->paymentIntents->retrieve($data['payment_intent_id']);

        if ($pi->status !== 'succeeded') {
            return response()->json([
                'success' => false,
                'message' => "PaymentIntent status must be 'succeeded' to release funds. Current status: '{$pi->status}'",
                'intent'  => $pi,
            ], 400);
        }

        $connectedAccount = \App\Models\ConnectedAccount::where('user_id', $taskOffer->freelancer_id)->first();
        if (!$connectedAccount || !$connectedAccount->stripe_account_id) {
            return response()->json([
                'success' => false,
                'message' => 'Handyman does not have a connected Stripe account.',
            ], 400);
        }

        //check $request->account_id status first?
        $account = \Stripe\Account::retrieve($connectedAccount->stripe_account_id);
        if (!$account->payouts_enabled) {
            return response()->json(['error' => 'Connected account payouts not enabled'], 400);
        }

        $payment = Payment::where(['task_id'=>$taskOffer->task_id, 'task_offer_id'=>$taskOffer->id])->first();

        $transfer = \Stripe\Transfer::create([
                'amount' => $payment->amount * 100, // in cents
                'currency' => 'aud',
                'destination' => $connectedAccount->stripe_account_id,
                'description' => 'Worker payout share',
                // Optional: 'source_transaction' => 'ch_xxx' to tie to a specific charge
            ]);

        return response()->json([
            'success' => true,
            'transfer_id' => $transfer->id,
            'status' => $transfer->status,
            'transfer' => $transfer,
            'message' => 'Funds released to handyman successfully.',
        ]);
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

    public function destroy(string $id)
    {
        //
    }


}

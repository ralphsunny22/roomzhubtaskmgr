<?php

namespace App\Http\Controllers\Web\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\CentralLogics\Helpers;
use Carbon\Carbon;

use Stripe\Stripe;
use Stripe\PaymentIntent;
use App\Rules\ValidCoupon;

use App\Models\Task;
use App\Models\TaskOffer;
use App\Models\Payment;

class ClientController extends Controller
{
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
    public function taskOffers($task_id="")
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
    public function acceptOffer(Request $request, $task_offer_id)
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

    //save payment after intent
    public function confirmPayment(Request $request)
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'task_id' => 'required|exists:tasks,id',
            'task_offer_id' => 'required|exists:task_offers,id',

            'currency' => 'nullable|string',
            'subtotal' => 'required|numeric',
            'tax' => 'nullable|numeric',
            'payment_method_type' => 'required|string',

            'coupon_value' => 'nullable|numeric|min:1',
            'coupon_code' => ['nullable', 'string', 'max:10', new ValidCoupon, 'required_with:coupon_value'],

            'status' => 'required|string',
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

        $payment = new Payment();
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
        $payment->status = 'success'; ////success, failed
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
            if ($status=="completed") {
                $task->client_completed_at = now();
                $task->status = 'completed';
            }
            if ($status=="cancelled") {
                $task->client_cancelled_at = now();
                $task->status = 'cancelled';
            }
            if ($status=="abandoned") {
                $task->freelancer_abandoned_at = now(); //if freelancer abandoned the task
                $task->status = 'abandoned';
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

    public function destroy(string $id)
    {
        //
    }


}

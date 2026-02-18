<?php

namespace App\Http\Controllers\Web\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\CentralLogics\Helpers;

use App\Models\Task;
use App\Models\TaskOffer;

class FreelanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function makeOffer(Request $request, $task_id)
    {
        try {
            $user = Auth::user();

            $validator = Validator::make($request->all(), [
            'amount_offered_by_freelancer' => 'nullable|integer|min:10',
            'freelancer_date_availability' => 'nullable|string',
            'freelancer_start_time_available' => 'nullable|string',
            'freelancer_end_time_available' => 'nullable|string',
            'freelancer_proposal' => 'nullable|string',
            ], [
                'amount_offered_by_freelancer.integer' => 'amount_offered_by_freelancer is must be an integer.',
                'currency.string' => 'currency must be a string.',
            ]);

            $task = Task::findOrFail($task_id);

            //u cannot make-offer on ur own task
            if ($task->created_by == $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => "Unauthorized process",
                ]);
            }

            $data = $request->all();

            $offerExists = TaskOffer::where(['task_id' => $task->id, 'freelancer_id' => $user->id])->first();

            if ($offerExists && $offerExists->status === 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => "You've already made offer for this task",
                ]);
            }

            // if ($offerExists->status !== 'pending' && in_array($offerExists->status, ['accepted','in_progress','completed'])) {
            if ($offerExists && $offerExists->status !== 'pending') {
                $taskOffer = $offerExists;
                $taskOffer->task_id = $task->id;
                $taskOffer->amount_offered_by_freelancer = $data['amount_offered_by_freelancer'] ? (int) $data['amount_offered_by_freelancer'] : (int) $task->task_budget;
                $taskOffer->client_id = $task->created_by;
                $taskOffer->freelancer_id = $user->id;

                $taskOffer->freelancer_date_availability = $data['freelancer_date_availability'] ? $data['freelancer_date_availability'] : null;
                $taskOffer->freelancer_start_time_available = $data['freelancer_start_time_available'] ? $data['freelancer_start_time_available'] : null;
                $taskOffer->freelancer_end_time_available = $data['freelancer_end_time_available'] ? $data['freelancer_end_time_available'] : null;
                $taskOffer->freelancer_proposal = $data['freelancer_proposal'] ? $data['freelancer_proposal'] : null;

                $taskOffer->save();
                //make user a freelancer
                if(!$user->is_freelancer){
                    $user->is_freelancer = true;
                    $user->save();
                }
                return response()->json([
                    'success' => true,
                    'message' => 'Offer Made Successfully',
                    'data' => $taskOffer
                ]);

            }

            $taskOffer = new TaskOffer();
            $taskOffer->task_id = $task->id;
            $taskOffer->amount_offered_by_freelancer = $data['amount_offered_by_freelancer'] ? (int) $data['amount_offered_by_freelancer'] : (int) $task->task_budget;
            $taskOffer->client_id = $task->created_by;
            $taskOffer->freelancer_id = $user->id;

            $taskOffer->freelancer_date_availability = $data['freelancer_date_availability'] ? $data['freelancer_date_availability'] : null;
            $taskOffer->freelancer_start_time_available = $data['freelancer_start_time_available'] ? $data['freelancer_start_time_available'] : null;
            $taskOffer->freelancer_end_time_available = $data['freelancer_end_time_available'] ? $data['freelancer_end_time_available'] : null;
            $taskOffer->freelancer_proposal = $data['freelancer_proposal'] ? $data['freelancer_proposal'] : null;

            $taskOffer->save();

            //make user a freelancer
            if(!$user->is_freelancer){
                $user->is_freelancer = true;
                $user->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Offer Made Successfully',
                'data' => $taskOffer
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function updateOffer(Request $request, $task_id, $task_offer_id)
    {
        try {
            $user = Auth::user();

            $task = Task::findOrFail($task_id);
            $taskOffer = TaskOffer::findOrFail($task_offer_id);

            $data = $request->all();

            $taskOffer->task_id = $task->id;
            $taskOffer->amount_offered_by_freelancer = $data['amount_offered_by_freelancer'];
            $taskOffer->client_id = $task->created_by;
            $taskOffer->freelancer_id = $user->id;

            $taskOffer->save();

            return response()->json([
                'success' => true,
                'message' => 'Offer Updated Successfully',
                'data' => $taskOffer
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                // 'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function myOffers()
    {
        $perPage = 30; // Adjust perPage value as needed
        $user = Auth::user();
        $taskOffers = $user->freelancerTaskOffers()->orderBy('id', 'desc')->paginate($perPage);;

        return response()->json([
            'success' => true,
            'data' => $taskOffers
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function singleOffer($task_offer_id)
    {
        try {
            $user = Auth::user();

            $taskOffer = TaskOffer::findOrFail($task_offer_id);
            if ($taskOffer->freelancer->id == $user->id) {
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

    public function updateTaskOfferStatus(string $task_offer_id, $status)
    {
        try{
            $taskOffer = TaskOffer::findOrFail($task_offer_id);
            $task = $taskOffer->task;
            //pending, accepted, decline, cancelled
            //check ownership
            $user = Auth::user();
            if($task->freelancer_id !== $user->id){
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized request',
                ]);
            }
            //freelancer status: started, completed, cancelled, abandoned
            // if ($status=="started") {
            //     $task->freelancer_started_at = now();
            //     $task->status = 'freelancer_started';
            // }
            // if ($status=="completed") {
            //     $task->freelancer_completed_at = now();
            //     $task->status = 'freelancer_completed';
            // }
            if ($taskOffer->status=="accepted" && $status=="cancelled") {
                $taskOffer->status = 'cancelled';
                $taskOffer->save();

                $task->freelancer_cancelled_at = now();
                // $task->status = 'freelancer_cancelled';
                $task->status = 'pending'; //reset to pending so client can accept other offers
                $task->freelancer_id = null; //remove freelancer from task
                $task->save();
            }

            if ($taskOffer->status !== "accepted" && $status=="abandoned") {
                $taskOffer->status = 'cancelled';
                $taskOffer->save();
            }

            $data = [
                'task' => $task,
                'taskOffer' => $taskOffer,
            ];
            return response()->json([
                'success' => true,
                'message' => 'Task Status Updated Successfully',
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

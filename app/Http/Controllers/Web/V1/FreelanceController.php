<?php

namespace App\Http\Controllers\Web\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
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

            $task = Task::findOrFail($task_id);

            //u cannot make-offer on ur own price
            if ($task->created_by == $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => "Unauthorized process",
                ]);
            }

            $offerExists = TaskOffer::where(['task_id' => $task->id, 'freelancer_id' => $user->id])->first();

            if ($offerExists) {
                return response()->json([
                    'success' => false,
                    'message' => "You've already made offer for this task",
                ]);
            }

            $data = $request->all();

            $taskOffer = new TaskOffer();
            $taskOffer->task_id = $task->id;
            $taskOffer->amount_offered_by_freelancer = (int) $data['amount_offered_by_freelancer'];
            $taskOffer->client_id = $task->created_by;
            $taskOffer->freelancer_id = $user->id;

            $taskOffer->freelancer_date_availability = $data['freelancer_date_availability'] ? $data['freelancer_date_availability'] : null;
            $taskOffer->freelancer_start_time_available = $data['freelancer_start_time_available'] ? $data['freelancer_start_time_available'] : null;
            $taskOffer->freelancer_end_time_available = $data['freelancer_end_time_available'] ? $data['freelancer_end_time_available'] : null;
            $taskOffer->freelancer_proposal = $data['freelancer_proposal'] ? $data['freelancer_proposal'] : null;

            $taskOffer->save();

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
            'data' => $freelancerTaskOffers
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function singleOffer($id)
    {
        try {
            $user = Auth::user();

            $taskOffer = TaskOffer::findOrFail($id);
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

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
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

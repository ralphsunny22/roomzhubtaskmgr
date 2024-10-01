<?php

namespace App\Http\Controllers\Web\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\CentralLogics\Helpers;

use App\Models\Task;

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
            $task->task_date = $data['task_date'] ?? null;
            $task->task_part_of_day = $data['task_part_of_day'] ?? null;
            $task->task_time_of_day = $data['task_time_of_day'] ?? null;
            $task->is_removal_task = $data['is_removal_task'] ?? false;
            $task->task_date = $data['task_date'] ?? null;

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

            $task->is_done_online = $data['is_done_online'] ?? false;
            $task->is_done_inperson = $data['is_done_inperson'] ?? false;
            $task->task_description = $data['task_description'] ?? null;

            $task->task_budget = (int) $data['task_budget'] ?? null;

            $task->status = 'pending';

            $task_images = [];
            if ($request->file('task_images')) {
                foreach ($request->file('task_images') as $image) {
                    $task_images[] = Helpers::upload('tasks/', $image->getClientOriginalExtension(), $image, 'noimage.png');
                }
            }

            $task->task_images = !empty($task_images) ? json_encode($task_images) : null;

            $task->save();

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

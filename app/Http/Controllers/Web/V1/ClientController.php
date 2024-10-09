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
            $task->is_removal_task = $data['is_removal_task'] === 'true' ? true : false;
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
            $task->task_date = $data['task_date'] ?? $task->task_date;
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



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }


}

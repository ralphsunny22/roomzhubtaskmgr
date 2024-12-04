<?php

namespace App\Http\Controllers\Mobile\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use App\CentralLogics\Helpers;

use App\Models\Task;
use App\Models\TaskOffer;

class TaskController extends Controller
{

    /**
     * Store a newly created resource in storage.
     */
    public function allTask()
    {
        $perPage = 30; // Adjust perPage value as needed

        $tasks = Task::orderBy('id', 'desc')->paginate($perPage);
        return response()->json([
            'success' => true,
            'data' => $tasks
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function singleTask($id)
    {
        try {
            $user = Auth::user();

            $task = Task::findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => $task,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }

    }

    public function search(Request $request)
    {
        $user = Auth::user();
        try {
            $section = $request->section;
            $result = collect(); // Default empty collection

            if ($section == "categories") {
                $category_option = $request->category_option;
                if ($category_option == "all") {
                    // $result = Task::where(['is_done_online' => true, 'is_done_inperson' => true])->get();
                    $result = Task::all();
                } elseif ($category_option == "in_person") {
                    $result = Task::where('is_done_inperson', true)->get();
                } elseif ($category_option == "remotely") {
                    $result = Task::where('is_done_online', true)->get();
                }
            }

            if ($section == "distance") {
                $subhurb_latitude = $request->subhurb_latitude;
                $subhurb_longitude = $request->subhurb_longitude;
                $coord_distance = $request->coord_distance; // Distance in kilometers

                if ($coord_distance && $user->current_latitude && $user->current_longitude) {
                    $userLat = $user->current_latitude;
                    $userLon = $user->current_longitude;

                    // First filter tasks around suburb coordinates with a rough bounding box
                    $nearbyTasks = Task::whereBetween('task_current_latitude', [$subhurb_latitude - 0.5, $subhurb_latitude + 0.5])
                        ->whereBetween('task_current_longitude', [$subhurb_longitude - 0.5, $subhurb_longitude + 0.5])
                        ->get();

                    // Further filter tasks by calculating distance from the user's current location
                    $result = $nearbyTasks->filter(function ($task) use ($userLat, $userLon, $coord_distance) {
                        if ($task->task_current_latitude && $task->task_current_longitude) {
                            $distance = Helpers::calculateDistance(
                                $userLat, $userLon,
                                $task->task_current_latitude, $task->task_current_longitude
                            );
                            return abs($distance - $coord_distance) <= 1; // Allow 1 km tolerance
                        }
                        return false;
                    })->values(); // Re-index filtered results
                } else {
                    $result = collect(); // Return empty if coordinates or distance is missing
                }
            }

            if ($section == "any_price") {
                $min_price = $request->min_price;
                $max_price = $request->max_price;
                $result = Task::whereBetween('task_budget', [$min_price, $max_price])->get();
            }

            if ($section == "other_filters") {
                // $task_with_offers = $request->task_with_offers;
                // $task_without_offers = $request->task_without_offers;
                $other_filters_option = $request->other_filters_option;

                $taskWithOffersQuery = Task::whereHas('offers');
                $taskWithoutOffersQuery = Task::whereDoesntHave('offers');

                // if ($task_with_offers && !$task_without_offers) {
                //     $result = $taskWithOffersQuery->get();
                // } elseif ($task_without_offers && !$task_with_offers) {
                //     $result = $taskWithoutOffersQuery->get();
                // } elseif ($task_with_offers && $task_without_offers) {
                //     $result = $taskWithOffersQuery->union($taskWithoutOffersQuery)->get();
                // }
                if ($other_filters_option=="task_with_offers") {
                    $result = $taskWithOffersQuery->get();
                } elseif ($other_filters_option=="task_without_offers") {
                    $result = $taskWithoutOffersQuery->get();
                } elseif ($other_filters_option=="all") {
                    $result = $taskWithOffersQuery->union($taskWithoutOffersQuery)->get();
                }
            }

            if ($section == "sort") {
                $sort_option = $request->sort_option;
                $query = Task::query();

                if ($sort_option == "price_high_to_low") {
                    $query->orderBy('task_budget', 'desc');
                } elseif ($sort_option == "price_low_to_high") {
                    $query->orderBy('task_budget', 'asc');
                } elseif ($sort_option == "due_date_earliest") {
                    $query->orderBy('task_date', 'asc');
                } elseif ($sort_option == "due_date_latest") {
                    $query->orderBy('task_date', 'desc');
                } elseif ($sort_option == "newest_jobs") {
                    $query->orderBy('id', 'desc');
                } elseif ($sort_option == "oldest_jobs") {
                    $query->orderBy('id', 'asc');
                } elseif ($sort_option == "closest_to_me") {
                    $userLat = $user->current_latitude;
                    $userLon = $user->current_longitude;

                    $query->selectRaw("
                        tasks.*,
                        (6371 * acos(
                            cos(radians(?)) * cos(radians(task_current_latitude)) *
                            cos(radians(task_current_longitude) - radians(?)) +
                            sin(radians(?)) * sin(radians(task_current_latitude))
                        )) AS distance
                    ", [$userLat, $userLon, $userLat])
                        ->orderBy('distance');
                }

                $result = $query->get();
            }

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
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

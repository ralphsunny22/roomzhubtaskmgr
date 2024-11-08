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

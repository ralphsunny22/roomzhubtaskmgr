<?php

namespace App\Http\Controllers\Mobile\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Task;
use App\Models\TaskOffer;
use App\Models\Rating;

class RatingController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        try {
            $request->validate([
                'task_id' => 'required|exists:tasks,id',
                'task_offer_id' => 'required|exists:task_offers,id',
                'rating_value' => 'required|integer',
                'review' => 'nullable|string',
            ]);

            $offer = ListOffer::find($task_offer_id);
            $rating = new Rating();
            $rating->created_by = $user->id;
            $rating->task_owner_id = $offer->client_id;
            $rating->task_freelancer_id = $offer->freelancer_id;

            $rating->task_id = $request->task_id;
            $rating->task_offer_id = $request->task_offer_id;
            $rating->rating_value = (int) $request->rating_value;
            $rating->review = $request->review ?? null;
            $rating->save();

            return response()->json([
                'success' => true,
                'data' => $rating
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }

    }

    /**
     * Display the specified resource.
     */
    public function myCreatedRatings(string $id)
    {
        $user = Auth::user();
        $ratings = $user->myCreatedRatings;
        return response()->json([
            'success' => true,
            'data' => $ratings
        ]);
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

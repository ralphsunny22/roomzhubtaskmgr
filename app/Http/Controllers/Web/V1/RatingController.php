<?php

namespace App\Http\Controllers\Web\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Task;
use App\Models\TaskOffer;
use App\Models\Rating;
use App\Models\Review;

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
                'task_id' => 'nullable|exists:tasks,id',
                'task_offer_id' => 'nullable|exists:task_offers,id',
                'rating_value' => 'required|integer',
                'review' => 'nullable|string',
            ]);

            $offer = TaskOffer::find($request->task_offer_id);
            $rating = new Rating();
            $rating->created_by = $user->id;
            $rating->task_owner_id = $offer->client_id;
            $rating->task_freelancer_id = $offer->freelancer_id;

            $rating->task_id = $request->task_id;
            $rating->task_offer_id = isset($request->task_offer_id) ? $request->task_offer_id : null;
            $rating->rating_value = (int) $request->rating_value;
            $rating->review = $request->review ?? null;
            $rating->save();

            if (isset($request->review)) {
                $review = new Review();
                $review->client_id = $offer->client_id;
                $review->freelancer_id = $offer->freelancer_id;
                $review->task_id = $request->task_id ? (int) $request->task_id : null;
                $review->task_offer_id = $request->task_offer_id ? (int) $request->task_offer_id : null;
                $review->content = $request->review;
                $review->save();
            }

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
    public function myCreatedRatings()
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
    public function freelancerRatings()
    {
        $user = Auth::user();
        $ratings = $user->myCreatedRatings;
        return response()->json([
            'success' => true,
            'data' => $ratings
        ]);
    }

    public function singleTaskRating(string $task_id)
    {
        $user = Auth::user();
        $rating = Rating::where('task_id', $task_id)->first();
        if (($rating->created_by==$user->id) || ($rating->task_freelancer_id==$user->id)) {
            return response()->json([
                'success' => true,
                'data' => $rating
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request'
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

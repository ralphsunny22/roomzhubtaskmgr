<?php

namespace App\Http\Controllers\Web\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
// use App\Services\ClickSendService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;
use App\CentralLogics\Helpers;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ForgotPassword;
use App\Models\User;
use App\Models\Review;
use App\Models\ProductVariant;

class ReviewController extends Controller
{
    ///sendResetLink
    // public function review(Request $request, ClickSendService $clickSend)
    public function review(Request $request)
    {
        $auth = Auth::user();
        $validator = Validator::make($request->all(), [
            'content' => 'required'
        ], [
            'content.required' => 'You must provide the content of what you are reporting.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => Helpers::error_processor($validator)], 403);
        }

        $auth = User::where('id', $auth->id)->first();
        $reportee = User::where('id', $request->reportee_id)->first();
        $content = !empty($request->content) ? $request->content : null;

        $review = new Review();
        $review->reviewer_id = $auth->id;
        $review->task_id = $request->task_id ? (int) $request->task_id : null;
        $review->task_offer_id = $request->task_offer_id ? (int) $request->task_offer_id : null;
        $review->content = !empty($request->content) ? $request->content : null;
        $review->save();

        return response()->json([
            'success' => true,
            'message' => 'User reported successfully',
            // 'result' => $result,
        ]);

    }

    // Send email logic
    // Mail::send('emails.password-reset', ['token' => $token], function ($message) use ($user) {
    //     $message->to($user->email);
    //     $message->subject('Password Reset Request');
    // });


}

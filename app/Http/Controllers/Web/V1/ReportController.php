<?php

namespace App\Http\Controllers\Web\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\ClickSendService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;
use App\CentralLogics\Helpers;
use Illuminate\Support\Facades\Notification;
use App\Models\User;
use App\Models\ReportUser;
use App\Models\TaskOffer;

class ReportController extends Controller
{
    ///sendResetLink
    public function reportUser(Request $request, ClickSendService $clickSend)
    {
        try {
            $reporter = Auth::user();
            $validator = Validator::make($request->all(), [
                'task_offer_id' => 'required',
                'reportee_id' => 'required',
            ], [
                'task_offer_id.required' => 'You must provide the offer id of the task.',
                'reportee_id.required' => 'You must provide the reportee id of the task.',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => Helpers::error_processor($validator)], 403);
            }

            $taskOffer = TaskOffer::findOrFail($request->task_offer_id);
            $task = $taskOffer->task;

            $reporter = User::where('id', $reporter->id)->first();
            $reportee = User::where('id', $request->reportee_id)->first();
            $content = !empty($request->content) ? $request->content : null;

            $reportUser = new ReportUser();
            $reportUser->reporter_id = $reporter->id;
            $reportUser->reportee_id = (int) $request->reportee_id;
            $reportUser->task_offer_id = (int) $request->task_offer_id;
            $reportUser->content = !empty($request->content) ? $request->content : null;
            $reportUser->save();

            $emailHtml = View::make('emails.reportuser', [
                'reporterName' => $reporter->name,
                'reporterEmail' => $reporter->email ? $reporter->email : null,
                'reporterPhone' => $reporter->phone_number ? $reporter->phone_number : null,
                'reporteeName' => $reportee->name,
                'reporteeEmail' => $reportee->email ? $reportee->email : null,
                'reporteePhone' => $reportee->phone_number ? $reportee->phone_number : null,
                'content' => $content,

                'taskTitle' => $task->task_title,
                'taskAddress' => $task->pickup_address ? $task->pickup_address : null,
                'clientTaskStatus' => $task->status ? $task->status : null,
                // 'freelancerTaskStatus' => $task->pickup_address ? $task->pickup_address : null,
            ])->render();

            $result = $clickSend->sendEmail([
                'to' => [
                    ['email' => 'contactus@wavezio.com', 'name' => $reporter->name],
                    // ['email' => 'santosralph2022@gmail.com', 'name' => $reporter->name],
                ],
                'from' => [
                    'email_address_id' => 31182, // Replace with your actual email_address_id
                    'name' => 'Wavezio',
                ],
                'subject' => 'Report User',
                'body' => $emailHtml,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User reported successfully',
                // 'result' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                // 'message' => $e->getMessage(),
            ]);
        }

    }

    // Send email logic
    // Mail::send('emails.password-reset', ['token' => $token], function ($message) use ($user) {
    //     $message->to($user->email);
    //     $message->subject('Password Reset Request');
    // });


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

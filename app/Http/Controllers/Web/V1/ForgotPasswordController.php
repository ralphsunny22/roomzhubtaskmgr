<?php

namespace App\Http\Controllers\Web\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;
use App\CentralLogics\Helpers;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ForgotPassword;
use App\Models\User;

class ForgotPasswordController extends Controller
{
    ///sendResetLink
    public function sendResetLink(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email'
        ], [
            'email.required' => 'The email field is required.',
            'email.email' => 'The email field must have "@" symbol.',
            'email.exists' => 'This email does not exist.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => Helpers::error_processor($validator)], 403);
        }

        $user = User::where('email', $request->email)->first();
        // $token = bcrypt($user->email . now());
        $token = Crypt::encryptString($user->email . '|' . now());

        // Construct the reset password URL using APP_URL
        $appUrl = env('APP_FRONT_URL', 'https://handymanhub.roomzhub.com'); // Default to 'https://handymanhub.roomzhub.com' if APP_URL is not set
        $resetUrl = $appUrl . '/reset-password/' . $request->email . '/' . $token;

        $data = [
            'user' => $user,
            'token' => $token,
            'resetUrl' => $resetUrl
        ];

        try {
            Notification::route('mail', [$request->email])->notify(new ForgotPassword($data));
        } catch (\Throwable $th) {
            //throw $th;
        }

        return response()->json([
            'success' => true,
            // 'token' => $token,
            // 'url' => url('reset-password/'. $request->email . '/' . $token),
            'message' => 'Password reset link has been sent to your email',

        ]);

    }

    // Send email logic
    // Mail::send('emails.password-reset', ['token' => $token], function ($message) use ($user) {
    //     $message->to($user->email);
    //     $message->subject('Password Reset Request');
    // });

    /**
     * Store a newly created resource in storage.
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'new_password' => 'required|min:8',
            'confirm_password' => 'required|min:8|same:new_password',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 403);
        }

        try {
            // Decrypt the token to get the email and timestamp
            $decrypted = Crypt::decryptString($request->token);
            list($email, $timestamp) = explode('|', $decrypted);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Invalid token.'], 403);
        }

        // Check if the email matches and the token is not expired (e.g., 60 minutes validity)
        if ($email !== $request->email || Carbon::parse($timestamp)->addMinutes(60)->isPast()) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired token.'], 403);
        }

        // Find the user and reset the password
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->new_password);
        // $user->signin_type = 'email';
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password has been reset successfully.'
        ]);
    }

    //renewOldPassword
    public function changePassword(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|min:8',
            'new_password' => 'required|min:8',
            'confirm_password' => 'required|min:8|same:new_password',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 403);
        }

        // Get the authenticated user
        $user = Auth::user();
        $user = User::findOrFail($user->id);

        // Check if the old password matches the current password
        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json(['status' => false, 'message' => 'Old password is incorrect.'], 403);
        }

        // Update the password
        $user->password = Hash::make($request->new_password);
        $user->save();

        Auth::logout(); // Revoke the old token

        // Re-authenticate to issue a new token
        Auth::login($user);

        // Respond with the new token
        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully.',
            'user' => $user,
            'authorisation' => [
                'token' => Auth::refresh(), // Refresh the token
                'type' => 'bearer',
            ]
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

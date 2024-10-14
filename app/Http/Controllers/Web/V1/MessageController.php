<?php

namespace App\Http\Controllers\Web\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\FCMService;
use App\Models\Message;
use App\Models\User;

class MessageController extends Controller
{
    protected $fcmService;

    public function __construct(FCMService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    public function sendMessage(Request $request)
    {
        // Store the message in the database
        $message = Message::create([
            'date_time' => now(),
            'sender_id' => 1,
            'receiver_id' => 2,
            'message' => $request->message,
        ]);

        // Retrieve recipient device token
        $token = $this->getRecipientDeviceToken($message->receiver_id);

        // Send FCM notification, tis wil return true
        $this->fcmService->sendNotification(
            $token,
            'New Message',
            'You have received a new message',
            ['message_id' => $message->id]
        );

        return response()->json(['success' => true]);
    }

    private function getRecipientDeviceToken($receiverId)
    {
        // Fetch the recipient's FCM token from the database
        $user = User::find($receiverId);
        return $user->fcm_device_token;
    }
}

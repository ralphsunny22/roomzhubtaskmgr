<?php

namespace App\Http\Controllers\Web\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\CentralLogics\Helpers;

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

    public function chatHistory($task_offer_id, $selected_user_id)
    {
        // Validate the incoming request to ensure receiver_id is provided and exists

        try {
            // Get the authenticated user's ID
            $authUserId = Auth::id();

            // Retrieve the messages exchanged between the authenticated user and the specified receiver
            $messages = Message::where(function ($query) use ($authUserId, $task_offer_id, $selected_user_id) {
                $query->where('sender_id', $authUserId)
                    ->where('receiver_id', $selected_user_id)
                    ->where('task_offer_id', $task_offer_id);
            })
            ->orWhere(function ($query) use ($authUserId, $task_offer_id, $selected_user_id) {
                $query->where('sender_id', $selected_user_id)
                    ->where('receiver_id', $authUserId)
                    ->where('task_offer_id', $task_offer_id);
            })
            ->orderBy('created_at', 'asc') // Order messages by creation date in ascending order
            ->get();

            $receivedMessageIds = $messages->where('receiver_id', $authUserId)->pluck('id');

            if ($receivedMessageIds->isNotEmpty()) {
                Message::whereIn('id', $receivedMessageIds)->update(['is_received' => 1]);
            }

            // Return the messages as a JSON response
            return response()->json(['success'=>true, 'data'=>$messages]);
        } catch (\Exception $e) {
            //throw $th;
            return response()->json(['success'=>false, 'message'=>'Something went wrong', 'error'=>$e->getMessage()]);
        }

    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'sender_id' => 'required|exists:users,id',
            'receiver_id' => 'required|exists:users,id',

            'task_id' => 'required|exists:tasks,id',
            'task_offer_id' => 'required|exists:task_offers,id',

            'message' => 'required|string',
        ]);

        try {
            // Store the message in the database
            $message = Message::create([
                'date_time' => now(),
                'sender_id' => $request->sender_id,
                'receiver_id' => $request->receiver_id,

                'task_id' => $request->task_id,
                'task_offer_id' => $request->task_offer_id,
                'message' => $request->message,
            ]);

            // Retrieve recipient device token
            $token = $this->getRecipientDeviceToken($message->receiver_id);

            //Using package Send FCM notification, tis wil return true
            // $this->fcmService->sendNotification(
            //     $token,
            //     'New Message',
            //     'You have received a new message',
            //     ['message_id' => $message->id]
            // );

            //using core firebase
            if ($token) {
                // Construct the message payload
                $msg = [
                    'message' => [
                        'token' => $token,
                        "data" => [
                            "title" => 'New Message',
                            "body" => (string) $message->message,
                            "sender_id" => (string) $message->sender_id,
                            "receiver_id" => (string) $message->receiver_id,

                            'task_id' => (string) $message->task_id,
                            'task_offer_id' => (string) $message->task_offer_id,
                        ],
                        'notification' => [
                            'title' => 'New Message',
                            'body' => (string) $message->message,
                            // 'sound' => 'notification.wav', // Specify the sound file name
                        ],
                    ],
                ];

                // Call the sendToFirebase function
                if (Helpers::sendToFirebase($msg)) {
                    return response()->json(['success' => true, 'message' => 'Notification sent successfully.', 'data'=>$message]);
                }
            }

            return response()->json(['success' => true, 'message' => 'No Receiver token.', 'data'=>$message]);

        } catch (\Exception $e) {
            //throw $th;
            return response()->json(['success'=>false, 'error'=>$e->getMessage()],400);
        }

    }

    private function getRecipientDeviceToken($receiverId)
    {
        // Fetch the recipient's FCM token from the database
        $user = User::find($receiverId);
        return $user->fcm_device_token;
    }
    ///////////////////////////////////////////////////////

    // Sample usage within your controller or service
    public function sendMessage1()
    {
        // $credentials = Helpers::getFirebaseCredentials();
        // return response()->json(['message' => $credentials['project_id']]);

        // Get the user's FCM token from the database
        // $token = DB::table('user_tokens')->where('user_id', '1')->value('fcm_token');
        $token = 'cLtQcMBZ-U3U5chyw1lpTU:APA91bH3WeRBJEdgv_dsfSM0dHKM8O0e0mjMdUq9em-_F-SXEeIyq41H6fzUzSzPqxDOQOXWxVllVzfYyebH4qsXW1014dePtretOJlfRTJfLn0X949jOiGHoUoY2iKz3LHmsMi8XUcd';

        if (!$token) {
            return response()->json(['message' => 'User token not found.'], 404);
        }

        // Construct the message payload
        // $message = [
        //     'message' => [
        //         'token' => $token,
        //         'notification' => [
        //             'title' => 'New Order',
        //             'body' => 'You have a new order!',
        //             // 'sound' => 'notification.wav', // Specify the sound file name
        //         ],
        //     ],
        // ];

        $message = [
            'message' => [
                'token' => $token,
                "data" => [
                    "title" => 'New Message',
                    "body" => 'You have a new order!',
                    "sender_id" => '1',
                    "receiver_id" => '2',

                    'task_id' => '1',
                    'task_offer_id' => '2',
                ],
                'notification' => [
                    'title' => 'New Message',
                    'body' => 'You have a new order!',
                    // 'sound' => 'notification.wav', // Specify the sound file name
                ],
            ],
        ];

        // Call the sendToFirebase function
        if (Helpers::sendToFirebase($message)) {
            return response()->json(['message' => 'Notification sent successfully.']);
            return response()->json(['success' => true, 'message' => 'Notification sent successfully.', 'data'=>$message]);
        }

        return response()->json(['message' => 'Failed to send notification.'], 500);
    }
}

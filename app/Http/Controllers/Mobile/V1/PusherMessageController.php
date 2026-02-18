<?php

namespace App\Http\Controllers\Mobile\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\CentralLogics\Helpers;
use Illuminate\Support\Facades\DB;
use Pusher\Pusher;

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

    public function chatContacts()
    {
        // $users = User::select('id', 'display_name')->get();
        $loggedInUserId = Auth::id();

        try {
            $chatUsers = DB::table('messages')
            ->select(DB::raw('DISTINCT IF(sender_id = ' . $loggedInUserId . ', receiver_id, sender_id) AS user_id'))
            ->where(function ($query) use ($loggedInUserId) {
                $query->where('sender_id', $loggedInUserId)
                    ->orWhere('receiver_id', $loggedInUserId);
            })
            ->get();

            $userIds = $chatUsers->pluck('user_id')->toArray();

            $users = User::whereIn('id', $userIds)->select('id', 'name', 'profile_picture')->get();

            // $activeUserId = null;
            // if ($user_id) {
            //     $activeUserId = $user_id;
            // }

            return response()->json(['success'=>true, 'data'=>$users]);
        } catch (\Exception $e) {
            return response()->json(['success'=>false, 'message'=>'Something went wrong', 'exp'=>$e->getMessage()]);
        }

    }

    public function chatHistory($order_id, $selected_user_id)
    {
        // Validate the incoming request to ensure receiver_id is provided and exists


        try {
            // Get the authenticated user's ID
            $authUserId = Auth::id();

            // Retrieve the messages exchanged between the authenticated user and the specified receiver
            $messages = Message::where(function ($query) use ($authUserId, $order_id, $selected_user_id) {
                $query->where('sender_id', $authUserId)
                    ->where('receiver_id', $selected_user_id)
                    ->where('order_id', $order_id);
            })
            ->orWhere(function ($query) use ($authUserId, $order_id, $selected_user_id) {
                $query->where('sender_id', $selected_user_id)
                    ->where('receiver_id', $authUserId)
                    ->where('order_id', $order_id);
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

    public function sendMessage2(Request $request)
    {
        $request->validate([
            'sender_id' => 'required|exists:users,id',
            'receiver_id' => 'required|exists:users,id',

            'vendor_id' => 'required|exists:users,id',
            'order_id' => 'required|exists:orders,id',

            'message' => 'required|string',
        ]);

        // return response()->json(['success' => true, 'message' => 'Notification sent successfully.', 'data'=>$request->all()]);

        try {
            // Store the message in the database
            $message = Message::create([
                'date_time' => now(),
                'sender_id' => $request->sender_id,
                'receiver_id' => $request->receiver_id,

                'vendor_id' => $request->vendor_id,
                'order_id' => $request->order_id,
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

                            'vendor_id' => (string) $message->vendor_id,
                            'order_id' => (string) $message->order_id,
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

    public function sendMessage(Request $request)
    {
        $request->validate([
            'sender_id' => 'required|exists:users,id',
            'receiver_id' => 'required|exists:users,id',
            // 'vendor_id' => 'required|exists:users,id',
            'order_id' => 'required|exists:orders,id',
            'message' => 'required|string',
        ]);

        try {
            $vendor = User::where('created_by',$request->sender_id)->orWhere('created_by',$request->receiver_id)->first();
            // Store the message in the database
            $message = Message::create([
                'date_time'   => now(),
                'sender_id'   => $request->sender_id,
                'receiver_id' => $request->receiver_id,
                'vendor_id'   => $vendor->id,
                'order_id'    => $request->order_id,
                'message'     => $request->message,
            ]);

            // Generate a unique conversation identifier
            // Using order_id + vendor_id is perfect for your use case
            $conversationId = "order_{$request->order_id}_vendor_{$request->vendor_id}";

            // Initialize Pusher
            $pusher = new Pusher(
                config('broadcasting.connections.pusher.key'),
                config('broadcasting.connections.pusher.secret'),
                config('broadcasting.connections.pusher.app_id'),
                [
                    'cluster' => config('broadcasting.connections.pusher.options.cluster'),
                    'useTLS'  => true
                ]
            );

            // Trigger the event on a public channel - exactly like your Node.js code
            $pusher->trigger($conversationId, 'newMessage', [
                'newMessage' => [
                    'id'           => $message->id,
                    'sender_id'    => $message->sender_id,
                    'receiver_id'  => $message->receiver_id,
                    'message'      => $message->message,
                    'date_time'    => $message->date_time->toISOString(), // ISO format like JS
                ],
                'timestamp' => now()->toISOString(),
            ]);

            // Your existing FCM code (unchanged)
            $token = $this->getRecipientDeviceToken($message->receiver_id);

            if ($token) {
                $msg = [
                    'message' => [
                        'token' => $token,
                        'data' => [
                            'title'       => 'New Message',
                            'body'        => (string) $message->message,
                            'sender_id'   => (string) $message->sender_id,
                            'receiver_id' => (string) $message->receiver_id,
                            'vendor_id'   => (string) $message->vendor_id,
                            'order_id'    => (string) $message->order_id,
                            'type'        => 'new_message', // optional: for handling in app
                        ],
                        'notification' => [
                            'title' => 'New Message',
                            'body'  => (string) $message->message,
                        ],
                    ],
                ];

                if (Helpers::sendToFirebase($msg)) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Message sent and notification delivered.',
                        'data'    => $message
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Message sent (no FCM token).',
                'data'    => $message
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage()
            ], 400);
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

                    'vendor_id' => '1',
                    'order_id' => '2',
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

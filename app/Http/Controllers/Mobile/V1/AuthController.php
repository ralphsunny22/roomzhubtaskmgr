<?php

namespace App\Http\Controllers\Mobile\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\CentralLogics\Helpers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

use Illuminate\Support\Facades\Auth;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

use App\Models\User;
use App\Models\Task;
use App\Models\Message;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register','socialLogin', 'handleAutoLogin']]);
    }

    //
    public function socialLogin(Request $request){

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'profile_picture' => 'nullable|string',
            // 'password' => 'required|string|min:6',
            // 'password' => ['required', Password::min(8)],
        ], [
            'name.required' => 'The name field is required.',
            'email.required' => 'The email name field is required.',
        ]);

        if ($validator->fails()) {
            // return response()->json(['status' => false, 'errors' => Helpers::error_processor($validator)], 403);
            return response()->json(Helpers::error_processor($validator), 403);
        }
        $savedUser = User::where('email', $request->email)->first();

        if (!$savedUser) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'profile_picture' => !empty($request->profile_picture) ? $request->profile_picture : null,
                'signin_type' => 'social',
                'password' => Hash::make($request->email),
            ]);
            $token = Auth::login($user);

            // try {
            //     Notification::route('mail', [$request->email])->notify(new UserLogin($user));
            // } catch (\Throwable $th) {
            //     //throw $th;
            // }

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'user' => $user,
                'authorisation' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
            ]);
        }

        if ($savedUser->signin_type == 'email') {
            return response()->json([
                'success' => false,
                'message' => 'Please login with your email and password',

            ],401);
        }
        //
        $user = $savedUser;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->profile_picture = !empty($request->profile_picture) ? $request->profile_picture : null;
        $user->signin_type = 'social';
        $user->password = Hash::make($request->email);
        $user->save();

        $token = Auth::login($user);

        // try {
        //     Notification::route('mail', [$request->email])->notify(new UserLogin($user));
        // } catch (\Throwable $th) {
        //     //throw $th;
        // }

        return response()->json([
            'success' => true,
            'message' => 'Logged in successfully',
            'user' => $user,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    public function register(Request $request){

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'profile_picture' => 'nullable|string',
            'password' => 'required|string|min:6',
            // 'password' => ['required', Password::min(8)],
        ], [
            'name.required' => 'The name field is required.',
            'email.required' => 'The email name field is required.',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => Helpers::error_processor($validator)], 403);
        }

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->signin_type = !empty($request->signin_type) ? $request->signin_type : 'email';
        $profile_picture = !empty($request->profile_picture) ? $request->profile_picture : null;
        $user->password = Hash::make($request->password);
        $user->save();

        $auto_login_token = Helpers::generateJWT($user);

        $user->auto_login_token = $auto_login_token;
        $user->save();

        $token = Auth::login($user);

        // try {
        //     Notification::route('mail', [$request->email])->notify(new UserLogin($user));
        // } catch (\Throwable $th) {
        //     //throw $th;
        // }

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
        ];

        $secretKey = env('LOGIN_SECRET'); // Make sure this is set in the .env file

        // Serialize data to a JSON string
        $encryptedData = Helpers::encryptData(json_encode($data), $secretKey);

        return response()->json([
            'success' => true,
            'encryptedData' => $encryptedData,
            'message' => 'User created successfully',
            'user' => $user,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    //login
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
            'fcm_device_token' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => Helpers::error_processor($validator)], 403);
        }

        $savedUser = User::where('email', $request->email)->first();
        if (!$savedUser) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        } else {
            if($savedUser->signin_type !== 'email') {
                return response()->json([
                    'success' => false,
                    'message' => 'This account uses a social login',
                ], 401);
            }
        }

        $credentials = $request->only('email', 'password');
        /////
        $token = Auth::attempt($credentials);
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $user = Auth::user();
        $user = User::findOrFail($user->id);

        $user->signin_type = 'email';

        $user->fcm_device_token = isset($request->fcm_device_token) ? $request->fcm_device_token : $user->fcm_device_token;
        $auto_login_token = Helpers::generateJWT($user);
        $user->auto_login_token = $auto_login_token;
        $user->save();

        return response()->json([
            'success' => true,
            'user' => $user,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ],200);

    }

    //refresh
    public function refresh()
    {
        return response()->json([
            'status' => true,
            'user' => Auth::user(),
            'authorisation' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }

    //logout
    public function logout()
    {
        Auth::logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function profile($selected_user_id="")
    {
        try {
            $user = $selected_user_id ? User::findOrFail($selected_user_id) : Auth::user();

            $data = [
                'user' => $user,
                'task_posted_rating' => 0.0,
                'task_done_rating' => 0.0,
                'portfolio' => [],
                'skills' => $user->skills,
                'task_posted_reviews' => [],
                'task_done_reviews' => [],
            ];

            // Fetch completed tasks created by or assigned to the user
            $data['portfolio'] = Task::where('status', 'completed')
                ->where(function ($query) use ($user) {
                    $query->where('created_by', $user->id)
                        ->orWhere('freelancer_id', $user->id);
                })
                ->get();

            // Fetch messages with task relationships
            $messages = Message::where('sender_id', $user->id)
                ->orWhere('receiver_id', $user->id)
                ->with('task') // Assuming a `task` relationship in the `Message` model
                ->get();

            $taskPostedReviews = [];
            $taskDoneReviews = [];

            foreach ($messages as $msg) {
                if ($msg->task && $msg->task->status === 'completed') {
                    if ($msg->task->created_by === $user->id) {
                        $taskPostedReviews[] = [
                            'task' => $msg->task,
                            'message' => $msg,
                        ];
                    }

                    if ($msg->task->freelancer_id === $user->id) {
                        $taskDoneReviews[] = [
                            'task' => $msg->task,
                            'message' => $msg,
                        ];
                    }
                }
            }

            $data['task_posted_reviews'] = $taskPostedReviews;
            $data['task_done_reviews'] = $taskDoneReviews;

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }


    public function handleAutoLogin(Request $request)
    {
        try {
            // Get the token from the query parameter
            $auto_login_token = (string) $request->query('auto_login_token');

            // Decode the token
            $payload = JWT::decode($auto_login_token, new Key(env('LOGIN_SECRET'), 'HS256'));

            // Validate timestamp
            if (now()->timestamp - $payload->timestamp > 28000000) { // 8 mths expiration
                return response('Token expired', 403);
            }

            $user = User::where('email', $payload->email)->first();
            if ($user) {
                $user->name = $payload->name;
                $user->email = $payload->email;
                $user->password = $payload->password;
                $user->auto_login_token = $auto_login_token;
                $user->save();
            } else {
                $user = new User();
                $user->name = $payload->name;
                $user->email = $payload->email;
                $user->password = $payload->password;
                $user->auto_login_token = $auto_login_token;
                $user->save();
            }

            // Log the user in
            $token = Auth::login($user);

            return response()->json([
                'success' => true,
                'user' => User::find($user->id),
                'authorisation' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
            ],200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),

            ],500);
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

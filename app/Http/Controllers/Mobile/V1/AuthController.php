<?php

namespace App\Http\Controllers\Mobile\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\CentralLogics\Helpers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

use App\Models\User;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register','socialLogin']]);
    }

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

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'signin_type' => !empty($request->signin_type) ? $request->signin_type : 'email',
            'profile_picture' => !empty($request->profile_picture) ? $request->profile_picture : null,
            'password' => Hash::make($request->password),
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
    public function show(string $id)
    {
        //
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

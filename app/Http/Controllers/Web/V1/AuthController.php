<?php

namespace App\Http\Controllers\Web\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\CentralLogics\Helpers;

use Illuminate\Support\Facades\Auth;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Storage;

use App\Models\User;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register','socialLogin', 'handleAutoLogin']]);
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
            // $user = User::create([
            //     'name' => $request->name,
            //     'email' => $request->email,
            //     'profile_picture' => !empty($request->profile_picture) ? $request->profile_picture : null,
            //     'signin_type' => 'social',
            //     'password' => Hash::make($request->email),
            // ]);
            // $token = Auth::login($user);

            // try {
            //     Notification::route('mail', [$request->email])->notify(new UserLogin($user));
            // } catch (\Throwable $th) {
            //     //throw $th;
            // }

            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->profile_picture = !empty($request->profile_picture) ? $request->profile_picture : null;
            $user->signin_type = 'social';
            $user->password = Hash::make($request->email);
            $user->save();

            $auto_login_token = Helpers::generateJWT($user);
            $user->auto_login_token = $auto_login_token;
            $user->save();

            $token = Auth::login($user);

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

        $auto_login_token = Helpers::generateJWT($user);
        $user->auto_login_token = $auto_login_token;
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
        $user->profile_picture = !empty($request->profile_picture) ? $request->profile_picture : null;
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
     * Show the form for editing the specified resource.
     */
    public function updateProfile(Request $request)
    {
        $authUser = Auth::user();
        $user = User::find($authUser->id);

        $section = $request->section;

        if ($section == 'about') {
            $user->about = $request->about;
            $user->save();
        }

        if ($section == 'profile_picture_and_suburb') {
            // Handle main image upload
            if ($request->file('profile_picture')) {
                // Delete old image if exists
                if ($user->profile_picture) {
                    $img = $user->profile_picture;
                    $pathInfo = pathinfo($img);
                    $imageName = $pathInfo['basename'];
                    Helpers::removeFile('users/', $imageName, 'noimage.png');
                }

                $image = $request->file('profile_picture');

                // Upload new image
                $profile_picture = Helpers::upload('users/', 'png', $image, 'noimage.png');
                $user->profile_picture = $profile_picture;
                $user->save();
            }

            $user->current_latitude = $request->current_latitude ?? $user->current_latitude;
            $user->current_longitude = $request->current_longitude ?? $user->current_longitude;
            $user->current_city = $request->current_city ?? $user->current_city;
            $user->current_state = $request->current_state ?? $user->current_state;
            $user->current_country = $request->current_country ?? $user->current_country;
            $user->current_address = $request->current_address ?? $user->current_address;
            $user->save();
        }

        if ($section == 'skills') {
            $user->about = $request->about;
            $user->skills = !empty($request->skills) ? json_encode($request->skills) : $user->skills;
            $user->save();
        }

        if ($section == 'portfolio') {
            // Ensure current portfolio images are an array of filenames
            $currentPortfolioImages = is_string($user->portfolio_images)
                ? json_decode($user->portfolio_images, true)
                : ($user->portfolio_images ?? []);

            // Remove images marked for deletion
            if ($request->filled('deleted_images')) {
                $deletedIndexes = explode(',', $request->input('deleted_images'));
                foreach ($deletedIndexes as $index) {
                    if (isset($currentPortfolioImages[$index])) {
                        $imageToDelete = $currentPortfolioImages[$index];
                        $relativePath = str_starts_with($imageToDelete, 'http')
                            ? str_replace(asset('/storage/portfolios/') . '/', '', $imageToDelete)
                            : $imageToDelete;

                        if (Storage::disk('public')->exists('portfolios/' . $relativePath)) {
                            Storage::disk('public')->delete('portfolios/' . $relativePath);
                        }
                        unset($currentPortfolioImages[$index]);
                    }
                }
            }

            // Handle new portfolio images
            if ($request->hasFile('portfolio_images')) {
                foreach ($request->file('portfolio_images') as $image) {
                    $imageName = Helpers::upload('portfolios/', 'png', $image, 'noimage.png');
                    $currentPortfolioImages[] = $imageName; // Store only the filename
                }
            }

            // Save updated portfolio images to the database
            $user->portfolio_images = json_encode(array_values($currentPortfolioImages)); // Re-index the array
            $user->save();

            return response()->json([
                'success' => true,
                'data' => $user,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
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

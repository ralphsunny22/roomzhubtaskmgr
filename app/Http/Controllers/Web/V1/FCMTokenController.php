<?php

namespace App\Http\Controllers\Web\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\User;

class FCMTokenController extends Controller
{
    //store firebase token
    public function FCMStoreToken(Request $request)
    {
        // Aim: Create or update fcm_device_token
        $user = Auth::user();

        // Update the FCM token directly without checking if it exists
        $user->fcm_device_token = $request->fcm_device_token;
        $user->save();

        return response()->json(['success' => true, 'data' => $user->fcm_device_token]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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

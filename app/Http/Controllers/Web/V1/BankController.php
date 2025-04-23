<?php

namespace App\Http\Controllers\Web\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\Bank;

class BankController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function saveBankDetails(Request $request)
    {
        $user = Auth::user();
        $user = User::find($user->id);
        $user->bank_id = (int) $request->bank_id;
        $user->account_type = $request->account_type;
        $user->account_name = $request->account_name;
        $user->account_number = $request->account_number;
        $user->bsb = $request->bsb;
        $user->save();
        return response()->json([
            'success' => true,
            'message' => 'Bank details updated successfully',
            'data' => $user
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function bankList()
    {
        $banks = Bank::all();
        return response()->json([
            'success' => true,
            'data' => $banks,
        ]);
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

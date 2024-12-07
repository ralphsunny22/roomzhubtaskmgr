<?php

namespace App\Http\Controllers\Web\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Wallet;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;


class WalletController extends Controller
{
    public function getBalance()
    {
        $user = Auth::user();
        $balance = Wallet::with('task')->where('user_id', $user->id)
            ->selectRaw('SUM(CASE WHEN type = "earning" THEN amount ELSE -amount END) as balance')
            ->value('balance') ?? 0;

        return response()->json([
            'success' => true,
            'balance' => $balance,
        ]);
    }

    // Add an earning
    public function addEarning(Request $request)
    {
        try {
            $request->validate([
                'amount' => 'required|numeric|min:0.01',
                'task_id' => 'required',
                'task_offer_id' => 'required',
                'description' => 'nullable|string',
            ]);

            // $user = Auth::user();
            $task = Task::findOrFail($request->task_id);
            $userId = $task->freelancer_id;

            Wallet::create([
                'user_id' => $userId,
                'task_id' => $request->task_id,
                'task_offer_id' => $request->task_offer_id,
                'type' => 'earning',
                'amount' => $request->amount,
                'description' => $request->description ?? 'Earning added',
            ]);

            return $this->getBalance();
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }

    }

    // Process a payout
    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string',
        ]);

        $user = Auth::user();

        $balance = Wallet::where('user_id', $user->id)
            ->selectRaw('SUM(CASE WHEN type = "earning" THEN amount ELSE -amount END) as balance')
            ->value('balance') ?? 0;

        if ($balance < $request->amount) {
            return response()->json(['success' => false, 'message' => 'Insufficient balance'], 400);
        }

        Wallet::create([
            'user_id' => $user->id,
            'type' => 'payout',
            'amount' => $request->amount,
            'description' => $request->description ?? 'Payout processed',
        ]);

        return $this->getBalance();
    }

    public function getTransactions()
    {
        $transactions = Wallet::with('task')->where('user_id', Auth::id())->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $transactions,
        ]);
    }
}

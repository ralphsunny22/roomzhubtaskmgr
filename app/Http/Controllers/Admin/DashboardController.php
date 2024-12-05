<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Validator;
use Exception;

use App\CentralLogics\Helpers;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Redirect;

use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Task;
use App\Models\TaskOffer;
use App\Models\Wallet;

class DashboardController extends Controller
{
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
            $token = Auth::guard('web')->login($user);
            return redirect()->route('adminDashboard');

        } catch (\Exception $e) {
            // return response()->json([
            //     'success' => false,
            //     'message' => $e->getMessage(),

            // ],500);
            return back();
        }
    }

    public function autologin($section)
    {
        $user = Auth::guard('web')->user();
        $auto_login_token = $user->auto_login_token;
        $url = 'https://'.$section.'.roomzhub.com/admin/auth/auto-login?auto_login_token'.$auto_login_token;
        // return Redirect::away('http://127.0.0.1:9000/admin/auth/auto-login?auto_login_token='.$auto_login_token);
        return Redirect::away($url);
    }

    public function login()
    {
        return view('backend.auth.login');
    }

    public function loginPost(Request $request)
    {
        $rules = array(
            'email' => 'required|string|email|exists:users,email',
            'password' => 'required|string',
        );
        $messages = [
            'email.required' => '* Your Email is required',
            'email.string' => '* Invalid Characters',
            'email.email' => '* Must be of Email format with \'@\' symbol',
            'email.exists' => '* Invalid Credentials',

            'password.required'   => 'This field is required',
            'password.string'   => 'Email does not exist',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        } else {
            $user = User::where('email',$request->email)->first();
            if($user->status !== 'superadmin'){
                return back()->with('error', 'Unauthorised Process');
            }

            $credentials = $request->only('email', 'password');
            $check = Auth::guard('web')->attempt($credentials);
            if (!$check) {
                return back()->with('error', 'Invalid email or password, please check your credentials and try again');
            }
            $user = Auth::getProvider()->retrieveByCredentials($credentials); //full user details

            // if (Auth::guard('admin')->attempt(['email' => $request->email, 'password' => $request->password], $request->get('remember'))) {

            //     return redirect()->intended('/admin');
            // }

            $auto_login_token = Helpers::generateJWT($user);
            $user->auto_login_token = $auto_login_token;
            $user->save();

            Auth::guard('web')->login($user);


            return redirect()->route('adminDashboard');
        }
    }

    public function adminDashboard()
    {
        $authUser = Auth::guard('web')->user();
        $users = User::count();
        $userPending = User::where('status', 'pending')->count();
        $userApproved = User::where('status', 'approved')->count();
        $userSuspended = User::where('status', 'suspended')->count();

        $clients = User::where('is_client',true)->count();
        $freelancers = User::where('is_freelancer',true)->count();

        $tasks = Task::count();
        $taskPending = Task::where('status', 'pending')->count();
        $taskStarted = Task::where('status', 'started')->count();
        $taskCompleted = Task::where('status', 'completed')->count();
        $taskCancelled = Task::where('status', 'cancelled')->count();
        $taskAbandoned = Task::where('status', 'abandoned')->count();

        $taskOffers = TaskOffer::count();
        $taskOfferPending = TaskOffer::where('status', 'pending')->count();
        $taskOfferAccepted = TaskOffer::where('status', 'accepted')->count();
        $taskOfferDeclined = TaskOffer::where('status', 'declined')->count();

        $walletTransactions = Wallet::count();
        $walletEarnings = Wallet::where('type', 'earning')->count();
        $walletPayouts = Wallet::where('type', 'payout')->count();

        return view('backend.dashboard', compact('authUser', 'users', 'userPending', 'userApproved', 'userSuspended',
        'clients', 'freelancers', 'tasks', 'taskPending', 'taskStarted', 'taskCompleted', 'taskCancelled', 'taskAbandoned',
        'taskOffers', 'taskOfferPending', 'taskOfferAccepted', 'taskOfferDeclined', 'walletTransactions', 'walletEarnings', 'walletPayouts'
        ));
    }

    public function allUser($status="")
    {
        if ($status=="") {
            $users = User::all();
        }
        if ($status=="pending") {
            $users = User::where('status', 'pending')->get();
        }
        if ($status=="approved") {
            $users = User::where('status', 'approved')->get();
        }
        if ($status=="suspended") {
            $users = User::where('status', 'suspended')->get();
        }


        $allStatus = [
            ['name'=>'approved', 'bgColor'=>'success'],
            ['name'=>'pending', 'bgColor'=>'primary'],
            ['name'=>'suspended', 'bgColor'=>'danger'],
        ];

        return view('backend.user.allUser', compact('users', 'allStatus', 'status'));
    }

    public function allClient($status="")
    {
        if ($status=="") {
            $clients = User::where('is_client',true)->get();
        }
        if ($status=="pending") {
            $clients = User::where('is_client',true)->where('status', 'pending')->get();
        }
        if ($status=="approved") {
            $clients = User::where('is_client',true)->where('status', 'approved')->get();
        }
        if ($status=="suspended") {
            $clients = User::where('is_client',true)->where('status', 'suspended')->get();
        }

        $allStatus = [
            ['name'=>'approved', 'bgColor'=>'success'],
            ['name'=>'pending', 'bgColor'=>'primary'],
            ['name'=>'suspended', 'bgColor'=>'danger'],
        ];

        return view('backend.client.allClient', compact('clients', 'allStatus', 'status'));
    }

    public function singleClient($client_id)
    {
        $client = User::with(['clientTasks'])->where('id',$client_id)->first();

        $allStatus = [
            ['name'=>'approved', 'bgColor'=>'success'],
            ['name'=>'pending', 'bgColor'=>'primary'],
            ['name'=>'suspended', 'bgColor'=>'danger'],
        ];

        return view('backend.client.singleClient', compact('client', 'allStatus'));
    }

    public function allFreelancer($status="")
    {
        if ($status=="") {
            $freelancers = User::where('is_freelancer',true)->get();
        }
        if ($status=="pending") {
            $freelancers = User::where('is_freelancer',true)->where('status', 'pending')->get();
        }
        if ($status=="approved") {
            $freelancers = User::where('is_freelancer',true)->where('status', 'approved')->get();
        }
        if ($status=="suspended") {
            $freelancers = User::where('is_freelancer',true)->where('status', 'suspended')->get();
        }

        $allStatus = [
            ['name'=>'approved', 'bgColor'=>'success'],
            ['name'=>'pending', 'bgColor'=>'primary'],
            ['name'=>'suspended', 'bgColor'=>'danger'],
        ];

        return view('backend.freelancer.allFreelancer', compact('freelancers', 'allStatus', 'status'));
    }

    public function singleFreelancer($freelancer_id)
    {
        $freelancer = User::with(['freelancerTaskOffers'])->where('id',$freelancer_id)->first();

        $allStatus = [
            ['name'=>'approved', 'bgColor'=>'success'],
            ['name'=>'pending', 'bgColor'=>'primary'],
            ['name'=>'suspended', 'bgColor'=>'danger'],
        ];

        return view('backend.freelancer.singleFreelancer', compact('freelancer', 'allStatus'));
    }

    public function allTask($status="")
    {
        if ($status=="") {
            $tasks = Task::with(['createdBy','freelancer'])->get();
        } else {
            $tasks = Task::with(['createdBy','freelancer'])->where('status',$status)->get();
        }

        $allStatus = [
            ['name'=>'pending', 'bgColor'=>'primary'],
            ['name'=>'started', 'bgColor'=>'info'],
            ['name'=>'completed', 'bgColor'=>'success'],
            ['name'=>'cancelled', 'bgColor'=>'dark'],
            ['name'=>'abandoned', 'bgColor'=>'danger'],
        ];

        return view('backend.task.allTask', compact('tasks', 'allStatus', 'status'));
    }

    public function singleTask($task_id)
    {
        $task = Task::with(['createdBy','freelancer', 'offers'])->where('id',$task_id)->first();

        $allStatus = [
            ['name'=>'pending', 'bgColor'=>'primary'],
            ['name'=>'started', 'bgColor'=>'info'],
            ['name'=>'completed', 'bgColor'=>'success'],
            ['name'=>'cancelled', 'bgColor'=>'dark'],
            ['name'=>'abandoned', 'bgColor'=>'danger'],
        ];

        return view('backend.task.singleTask', compact('task', 'allStatus'));
    }

    public function updateTaskStatus(Request $request, $task_id)
    {
        $task = Task::where('id',$task_id)->first();
        $task->status = $request->task_status;
        $task->save();

        return back()->with('success'. 'Task Updated Successfully');

    }

    public function allTransaction()
    {
        $walletTransactions = Wallet::all();

        $allStatus = [
            ['name'=>'paid', 'bgColor'=>'success'],
            ['name'=>'unpaid', 'bgColor'=>'danger'],
        ];

        return view('backend.wallet.allTransaction', compact('walletTransactions', 'allStatus'));
    }

    public function allEarning()
    {
        $walletEarnings = Wallet::where('type', 'earning')->get();

        $allStatus = [
            ['name'=>'paid', 'bgColor'=>'success'],
            ['name'=>'unpaid', 'bgColor'=>'danger'],
        ];

        return view('backend.wallet.allEarning', compact('walletEarnings', 'allStatus'));
    }

    public function allPayout()
    {
        $walletPayouts = Wallet::where('type', 'payout')->get();

        $allStatus = [
            ['name'=>'paid', 'bgColor'=>'success'],
            ['name'=>'unpaid', 'bgColor'=>'danger'],
        ];

        return view('backend.wallet.allPayout', compact('walletPayouts', 'allStatus'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

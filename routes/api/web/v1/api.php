<?php
namespace App\Http\Controllers\Web\V1;
use Illuminate\Support\Facades\Route;
use App\CentralLogics\Helpers;

Route::group(['prefix' => 'auth'], function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('social-login', [AuthController::class, 'socialLogin']);
    Route::get('cross-platform-check/{ivData}/{mData}', [AuthController::class, 'crossPlatformCheck']);

    ////forgot password
    // Route::post('forgot-password', 'ForgotPasswordController@sendResetLink');
    // Route::post('reset-password', 'ForgotPasswordController@resetPassword');
    // Route::post('change-password', 'ForgotPasswordController@changePassword')->middleware('auth');
});

Route::group(['prefix' => 'tasks'], function () {
    Route::get('/all', [TaskController::class, 'allTask']);
    Route::get('/single/{id}', [TaskController::class, 'singleTask']);
});

Route::group(['middleware' => 'auth', 'prefix' => 'client'], function () {
    Route::get('/my-tasks', [ClientController::class, 'myTasks']);
    Route::get('/single-task/{id}', [ClientController::class, 'singleTask']);
    Route::post('/create-task', [ClientController::class, 'createTask']);
    Route::post('/update-task/{id}', [ClientController::class, 'updateTask']);
    Route::get('/task-offers/{task_id?}', [ClientController::class, 'taskOffers']);
    Route::get('/single-offer/{task_offer_id}', [ClientController::class, 'singleOffer']);
    Route::post('/accept-offer/{task_offer_id}', [ClientController::class, 'acceptOffer']);
    Route::post('/confirm-payment', [ClientController::class, 'confirmPayment']);

    Route::get('/update-task-status/{task_id}/{status}', [ClientController::class, 'updateTaskStatus']);
});

Route::group(['middleware' => 'auth', 'prefix' => 'freelancer'], function () {
    Route::post('/make-offer/{task_id}', [FreelanceController::class, 'makeOffer']);
    Route::post('/update-offer/{task_id}/{task_offer_id}', [FreelanceController::class, 'updateOffer']);
    Route::get('/my-offers', [FreelanceController::class, 'myOffers']);
    Route::get('/single-offer/{task_offer_id}', [FreelanceController::class, 'singleOffer']);

    Route::get('/update-task-status/{task_id}/{status}', [FreelanceController::class, 'updateTaskStatus']);
});

Route::group(['middleware' => 'auth', 'prefix' => 'payment'], function () {
    Route::get('/stripe/create-account', [StripeController::class, 'createStripeCustomConnectedAccount']);
    Route::get('/stripe/reauth', [StripeController::class, 'reauth'])->name('stripe.reauth');
    Route::get('/stripe/onboarding-success', [StripeController::class, 'onboardingSuccess'])->name('stripe.onboarding-success');
});

Route::group(['middleware' => 'auth', 'prefix' => 'fcm'], function () {
    Route::post('/store-token', [FCMTokenController::class, 'store']);
});

Route::group(['prefix' => 'chat'], function () {
    Route::post('/send-message', [MessageController::class, 'sendMessage']);
    Route::get('/history/{task_offer_id}/{selected_user_id}', [MessageController::class, 'chatHistory']);
});

//wallet
Route::group(['middleware' => 'auth', 'prefix' => 'wallet'], function () {
    Route::get('/balance', [WalletController::class, 'getBalance']);
    Route::post('/add-earning', [WalletController::class, 'addEarning']);
    Route::post('/withdraw', [WalletController::class, 'withdraw']);
    Route::get('/transactions', [WalletController::class, 'getTransactions']);
});

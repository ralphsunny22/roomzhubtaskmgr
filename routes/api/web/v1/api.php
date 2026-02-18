<?php
namespace App\Http\Controllers\Web\V1;
use Illuminate\Support\Facades\Route;
use App\CentralLogics\Helpers;

Route::group(['prefix' => 'auth'], function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('social-login', [AuthController::class, 'socialLogin']);
    Route::get('logout', [AuthController::class, 'logout']);
    Route::get('/auto-login', [AuthController::class, 'handleAutoLogin']);
    Route::post('/update-profile', [AuthController::class, 'updateProfile']);

    ////forgot password
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink']);
    Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword']);
    Route::post('/change-password', [ForgotPasswordController::class, 'changePassword'])->middleware('auth');
});

Route::group(['prefix' => 'tasks'], function () {
    Route::get('/all', [TaskController::class, 'allTask']);
    Route::get('/single/{id}', [TaskController::class, 'singleTask']);
    Route::post('/search', [TaskController::class, 'search']);
});

Route::group(['middleware' => 'auth', 'prefix' => 'client'], function () {
    Route::get('/my-tasks', [ClientController::class, 'myTasks']);
    Route::get('/single-task/{id}', [ClientController::class, 'singleTask']);
    Route::post('/create-task', [ClientController::class, 'createTask']);
    Route::post('/update-task/{id}', [ClientController::class, 'updateTask']);
    Route::get('/task-offers/{task_id?}', [ClientController::class, 'taskOffers']);
    Route::get('/single-offer/{task_offer_id}', [ClientController::class, 'singleOffer']);
    Route::post('/accept-offer', [ClientController::class, 'acceptOffer']);
    Route::post('/cancel-offer', [ClientController::class, 'cancelTaskOffer']);
    Route::post('/confirm-payment', [ClientController::class, 'confirmPayment']);

    Route::get('/update-task-status/{task_id}/{status}', [ClientController::class, 'updateTaskStatus']);

});
Route::post('/create-task-from-estore', [ClientController::class, 'fromEstore']);

Route::group(['middleware' => 'auth', 'prefix' => 'freelancer'], function () {
    Route::post('/make-offer/{task_id}', [FreelanceController::class, 'makeOffer']);
    Route::post('/update-offer/{task_id}/{task_offer_id}', [FreelanceController::class, 'updateOffer']);
    Route::get('/my-offers', [FreelanceController::class, 'myOffers']);
    Route::get('/single-offer/{task_offer_id}', [FreelanceController::class, 'singleOffer']);

    Route::post('/update-task-offer-status/{task_offer_id}/{status}', [FreelanceController::class, 'updateTaskOfferStatus']);
});

Route::group(['middleware' => 'auth', 'prefix' => 'payment'], function () {
    // Route::get('/stripe/create-account', [StripeController::class, 'createStripeCustomConnectedAccount']);
    // Route::get('/stripe/reauth', [StripeController::class, 'reauth'])->name('stripe.reauth');
    // Route::get('/stripe/onboarding-success', [StripeController::class, 'onboardingSuccess'])->name('stripe.onboarding-success');
});

Route::group(['middleware' => 'auth', 'prefix' => 'fcm'], function () {
    Route::post('/store-token', [FCMTokenController::class, 'store']);
});

Route::group(['prefix' => 'chat'], function () {
    Route::post('/send-message', [MessageController::class, 'sendMessage']);
    Route::get('/history/{task_offer_id}/{selected_user_id}', [MessageController::class, 'chatHistory']);
    Route::get('/contacts', [MessageController::class, 'chatContacts']);
});

//wallet
Route::group(['middleware' => 'auth', 'prefix' => 'wallet'], function () {
    Route::get('/balance', [WalletController::class, 'getBalance']);
    Route::post('/add-earning', [WalletController::class, 'addEarning']);
    Route::post('/withdraw', [WalletController::class, 'withdraw']);
    Route::get('/transactions', [WalletController::class, 'getTransactions']);
});

//ratings
Route::group(['middleware' => 'auth', 'prefix' => 'rating'], function () {
    Route::post('/store', [RatingController::class, 'store']);
    Route::get('/all-my-created', [RatingController::class, 'myCreatedRatings']);
    Route::get('/freelancer-ratings', [RatingController::class, 'freelancerRatings']);
    Route::get('/single-task/{task_id}', [RatingController::class, 'singleTaskRating']);
});

Route::group(['middleware' => 'auth'], function () {
    Route::get('/bank-list', [BankController::class, 'bankList']);
    Route::post('/save-bank-detail', [BankController::class, 'saveBankDetails']);
});

//subscriptions
Route::group(['middleware' => 'auth', 'prefix' => 'subscription'], function () {
    Route::post('/renew', [FreelancerSubscriptionController::class, 'createOrRenew']);
    Route::post('/payment-success', [FreelancerSubscriptionController::class, 'handlePaymentSuccess']);
    Route::get('/current-active-plan', [FreelancerSubscriptionController::class, 'currentActivePlan']);
});

Route::group(['middleware' => 'auth'], function () {
    Route::post('/wallet/create-connected-account', [StripeConnectController::class, 'createConnectedAccount']);
    Route::post('/wallet/add-bank-account', [StripeConnectController::class, 'addBankAccount']);
    Route::post('/wallet/check-account-status', [StripeConnectController::class, 'checkAccountStatus']);
    Route::post('/wallet/upload-verification-documents', [StripeConnectController::class, 'uploadVerificationDocuments']);
    Route::post('/wallet/transfer-to-worker', [StripeConnectController::class, 'transferToWorker']);
    Route::post('/wallet/trigger-payout', [StripeConnectController::class, 'triggerPayout']);
});

Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle']);
Route::post('/stripe/fund-test-account', [StripeController::class, 'fundTestAccount']);


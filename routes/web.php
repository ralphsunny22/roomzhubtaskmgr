<?php

use Illuminate\Support\Facades\Route;
use App\CentralLogics\Helpers;

use App\Http\Controllers\Admin\DashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

////admin/////////////
Route::group(['prefix' => 'admin'], function () {
    Route::get('/', [DashboardController::class, 'adminDashboard'])->name('adminDashboard');

    Route::group(['prefix' => 'auth'], function () {
        Route::get('/login', [DashboardController::class, 'login'])->name('login');
        Route::post('/login', [DashboardController::class, 'loginPost'])->name('loginPost');
    });

    Route::group(['prefix' => 'users'], function () {
        Route::get('/{status?}', [DashboardController::class, 'allUser'])->name('allUser');
    });

    Route::group(['prefix' => 'clients'], function () {
        Route::get('/{status?}', [DashboardController::class, 'allClient'])->name('allClient');
        Route::get('/single/{client_id}', [DashboardController::class, 'singleClient'])->name('singleClient');
    });

    Route::group(['prefix' => 'freelancers'], function () {
        Route::get('/{status?}', [DashboardController::class, 'allFreelancer'])->name('allFreelancer');
        Route::get('/single/{freelancer_id}', [DashboardController::class, 'singleFreelancer'])->name('singleFreelancer');
    });

    Route::group(['prefix' => 'tasks'], function () {
        Route::get('/{status?}', [DashboardController::class, 'allTask'])->name('allTask');
        Route::get('/single/{task_id}', [DashboardController::class, 'singleTask'])->name('singleTask');
        Route::post('/update-task-status/{task_id}', [DashboardController::class, 'updateTaskStatus'])->name('updateTaskStatus');
    });

    Route::group(['prefix' => 'transaction'], function () {
        Route::get('/', [DashboardController::class, 'allTransaction'])->name('allTransaction');
        Route::get('/earnings', [DashboardController::class, 'allEarning'])->name('allEarning');
        Route::get('/payouts', [DashboardController::class, 'allPayout'])->name('allPayout');
    });
});

<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\WalletController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('jwt.verify')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/user-profile', [AuthController::class, 'userProfile']);
});

//Verification routes
Route::group([
    'middleware' => 'api',
    'prefix' => 'email'

], function ($router) {
    Route::get('/verify/{id}', [VerificationController::class, 'verify'])->name('verification.verify');
    Route::get('/resend', [VerificationController::class, 'resend'])->name('verification.resend');
});

//Wallet Routes
Route::group([
    'middleware' => ['jwt.verify', 'verified'],
    'prefix' => 'wallet'
], function ($router) {
    Route::get('/', [WalletController::class, 'show']);
    Route::put('/visible/{boolean}', [WalletController::class, 'visibility']);
    Route::post('/withdraw', [WalletController::class, 'withdraw']);
});


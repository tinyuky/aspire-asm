<?php

use App\Http\Controllers\Admins\AuthController as AdminAuthController;
use App\Http\Controllers\Admins\LoanController as AdminLoanController;
use App\Http\Controllers\Customers\AuthController as CustomerAuthController;
use App\Http\Controllers\Customers\LoanController as CustomerLoanController;
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

Route::group([
    'prefix' => 'admin'
], function ($router) {
    Route::post('/login', [AdminAuthController::class, 'login']);
    Route::post('/logout', [AdminAuthController::class, 'logout']);
    Route::post('/refresh', [AdminAuthController::class, 'refresh']);
    Route::post('/me', [AdminAuthController::class, 'me']);
    Route::get('/loan', [AdminLoanController::class, 'index']);
    Route::get('/loan/{id}', [AdminLoanController::class, 'show']);
    Route::put('/loan/{id}/update', [AdminLoanController::class, 'update']);
});


Route::group([
    'prefix' => 'customer'
], function ($router) {
    Route::post('/login', [CustomerAuthController::class, 'login']);
    Route::post('/register', [CustomerAuthController::class, 'register']);
    Route::post('/logout', [CustomerAuthController::class, 'logout']);
    Route::post('/refresh', [CustomerAuthController::class, 'refresh']);
    Route::post('/me', [CustomerAuthController::class, 'me']);
    Route::post('/loan', [CustomerLoanController::class, 'create']);
    Route::get('/loan', [CustomerLoanController::class, 'index']);
    Route::get('/loan/{id}', [CustomerLoanController::class, 'show']);
    Route::post('/loan/{id}/pay', [CustomerLoanController::class, 'pay']);
});



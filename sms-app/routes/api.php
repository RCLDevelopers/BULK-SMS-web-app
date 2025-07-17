<?php

use App\Http\Controllers\SmsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->group(function () {
    // SMS Routes
    Route::prefix('sms')->group(function () {
        // Send SMS
        Route::post('/send', [SmsController::class, 'send'])->name('api.sms.send');
        
        // Get account balance
        Route::get('/balance', [SmsController::class, 'getBalance'])->name('api.sms.balance');
        
        // Get message history
        Route::get('/history', [SmsController::class, 'history'])->name('api.sms.history');
        
        // Get message details
        Route::get('/{id}', [SmsController::class, 'show'])->name('api.sms.show');
    });
});

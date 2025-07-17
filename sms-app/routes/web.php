<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ReportController;

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

// Redirect root to dashboard
Route::redirect('/', '/dashboard');

// Authentication routes
require __DIR__.'/auth.php';

// Authenticated routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    // Messages
    Route::prefix('messages')->name('messages.')->group(function () {
        Route::get('/compose', [MessageController::class, 'compose'])->name('compose');
        Route::post('/quick-send', [MessageController::class, 'quickSend'])->name('quick-send');
        Route::get('/history', [MessageController::class, 'history'])->name('history');
        Route::get('/{message}', [MessageController::class, 'show'])->name('show');
        Route::delete('/{message}', [MessageController::class, 'destroy'])->name('destroy');
        Route::post('/{message}/cancel', [MessageController::class, 'cancel'])->name('cancel');
        Route::get('/export', [MessageController::class, 'export'])->name('export');
    });
    
    // Contacts
    Route::resource('contacts', ContactController::class)->except(['edit', 'update']);
    Route::prefix('contacts')->name('contacts.')->group(function () {
        Route::get('{contact}/edit', [ContactController::class, 'edit'])->name('edit');
        Route::put('{contact}', [ContactController::class, 'update'])->name('update');
        Route::get('import', [ContactController::class, 'showImportForm'])->name('import');
        Route::post('import', [ContactController::class, 'import'])->name('import.process');
        Route::get('export', [ContactController::class, 'export'])->name('export');
    });
    
    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/export', [ReportController::class, 'export'])->name('export');
    });
    
    // Profile
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });
});

// Webhook for SMS delivery reports
Route::post('/webhooks/sms-delivery', [SmsController::class, 'handleDeliveryReport'])
    ->name('webhooks.sms-delivery');

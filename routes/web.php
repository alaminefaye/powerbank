<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PowerBankController;

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // PowerBank Device Management Routes
    Route::resource('powerbank', PowerBankController::class)->parameters(['powerbank' => 'device']);
    Route::post('powerbank/{device}/check', [PowerBankController::class, 'check'])->name('powerbank.check');
    Route::post('powerbank/{device}/popup', [PowerBankController::class, 'popup'])->name('powerbank.popup');
    Route::post('powerbank/{device}/popup-sn', [PowerBankController::class, 'popupSn'])->name('powerbank.popup-sn');
    Route::post('powerbank/{device}/refresh', [PowerBankController::class, 'refresh'])->name('powerbank.refresh');
});

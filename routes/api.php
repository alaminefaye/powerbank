<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PowerBankController;

// PowerBank API Routes (no auth required for device communication)
Route::prefix('rentbox')->group(function () {
    // Device authentication endpoint
    Route::post('client/connect', [PowerBankController::class, 'connect']);
    
    // Device status upload
    Route::post('device/upload', [PowerBankController::class, 'upload']);
    
    // Device return powerbank
    Route::post('device/return', [PowerBankController::class, 'returnPowerBank']);
});


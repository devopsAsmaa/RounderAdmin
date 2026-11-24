<?php

use Illuminate\Support\Facades\Route;

// مسارات Identity فقط؛ الأسماء والروابط العامة محفوظة للتوافق مع العملاء.
Route::prefix('v1')->group(function () {
    Route::post('/auth/validate-mobile', [\App\Modules\Identity\Controllers\Api\AuthController::class, 'validateMobile'])->middleware('throttle:15,1');
    Route::post('/auth/send-otp', [\App\Modules\Identity\Controllers\Api\AuthController::class, 'sendOtp'])->middleware('throttle:5,1');
    Route::post('/auth/verify-otp', [\App\Modules\Identity\Controllers\Api\AuthController::class, 'verifyOtp'])->middleware('throttle:10,1');
    Route::post('/auth/register', [\App\Modules\Identity\Controllers\Api\AuthController::class, 'register'])->middleware('throttle:5,1');
    Route::post('/auth/login', [\App\Modules\Identity\Controllers\Api\AuthController::class, 'login'])->middleware('throttle:8,1');
    Route::post('/auth/social', [\App\Modules\Identity\Controllers\Api\AuthController::class, 'socialLogin'])->middleware('throttle:8,1');
});
Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/me', [\App\Modules\Identity\Controllers\Api\AuthController::class, 'me']);
    Route::post('/auth/logout', [\App\Modules\Identity\Controllers\Api\AuthController::class, 'logout']);
    Route::delete('/account', [\App\Modules\Identity\Controllers\Api\AuthController::class, 'deleteAccount']);
    Route::post('/auth/delete-account', [\App\Modules\Identity\Controllers\Api\AuthController::class, 'deleteAccount']);
});
Route::prefix('v1/resident')->middleware(['auth:sanctum', 'resident'])->group(function () {
    Route::get('/profile', [\App\Modules\Identity\Controllers\Api\ProfileController::class, 'profile']);
    Route::put('/profile', [\App\Modules\Identity\Controllers\Api\ProfileController::class, 'updateProfile']);
    Route::delete('/profile', [\App\Modules\Identity\Controllers\Api\ProfileController::class, 'deleteAccount']);
    Route::delete('/account', [\App\Modules\Identity\Controllers\Api\ProfileController::class, 'deleteAccount']);
    Route::put('/mobile', [\App\Modules\Identity\Controllers\Api\ProfileController::class, 'updateMobile'])
        ->middleware('throttle:5,1');
});

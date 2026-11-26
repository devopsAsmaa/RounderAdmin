<?php

use Illuminate\Support\Facades\Route;

// مسارات Emergencies فقط؛ الأسماء والروابط العامة محفوظة للتوافق مع العملاء.
Route::prefix('v1/resident')->middleware(['auth:sanctum', 'resident'])->group(function () {
    Route::get('/emergency-types', [\App\Modules\Emergencies\Controllers\Api\EmergencyTypeController::class, 'index']);
    Route::get('/emergency-requests', [\App\Modules\Emergencies\Controllers\Api\EmergencyRequestController::class, 'index']);
    Route::post('/emergency-requests', [\App\Modules\Emergencies\Controllers\Api\EmergencyRequestController::class, 'store'])->middleware('throttle:3,1');
    Route::delete('/emergency-requests/{requestId}', [\App\Modules\Emergencies\Controllers\Api\EmergencyRequestController::class, 'cancel'])->middleware('throttle:3,1');
});

// مسارات الفني - إدارة بلاغات وطلبات الطوارئ
Route::prefix('v1/technician/emergencies')->middleware(['auth:sanctum', 'technician'])->group(function () {
    Route::get('/statuses', [\App\Modules\Emergencies\Controllers\Api\TechnicianEmergencyController::class, 'statuses']);
    Route::get('/', [\App\Modules\Emergencies\Controllers\Api\TechnicianEmergencyController::class, 'index']);
    Route::get('/{requestId}', [\App\Modules\Emergencies\Controllers\Api\TechnicianEmergencyController::class, 'show'])->whereNumber('requestId');
    Route::put('/{requestId}/status', [\App\Modules\Emergencies\Controllers\Api\TechnicianEmergencyController::class, 'updateStatus'])->whereNumber('requestId');
    Route::post('/{requestId}/status', [\App\Modules\Emergencies\Controllers\Api\TechnicianEmergencyController::class, 'updateStatus'])->whereNumber('requestId');
    Route::post('/{requestId}/send-otp', [\App\Modules\Emergencies\Controllers\Api\TechnicianEmergencyController::class, 'sendOtp'])->whereNumber('requestId');
    Route::post('/{requestId}/confirm', [\App\Modules\Emergencies\Controllers\Api\TechnicianEmergencyController::class, 'confirm'])->whereNumber('requestId');
    Route::post('/{requestId}/complete', [\App\Modules\Emergencies\Controllers\Api\TechnicianEmergencyController::class, 'confirm'])->whereNumber('requestId');
    Route::post('/{requestId}/resolve', [\App\Modules\Emergencies\Controllers\Api\TechnicianEmergencyController::class, 'resolve'])->whereNumber('requestId');
});

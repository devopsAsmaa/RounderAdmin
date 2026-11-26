<?php

use Illuminate\Support\Facades\Route;

// مسارات Emergencies فقط؛ الأسماء والروابط العامة محفوظة للتوافق مع العملاء.
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/emergencies/create', [\App\Modules\Emergencies\Controllers\Admin\EmergencyCreationController::class, 'emergencyForm'])->name('emergencies.create');
    Route::post('/emergencies', [\App\Modules\Emergencies\Controllers\Admin\EmergencyCreationController::class, 'storeEmergency'])->name('emergencies.store');
    Route::get('/emergencies', [\App\Modules\Emergencies\Controllers\Admin\EmergencyAdminController::class, 'index'])->name('emergencies.index');
    Route::put('/emergencies/{requestId}', [\App\Modules\Emergencies\Controllers\Admin\EmergencyAdminController::class, 'update'])->name('emergencies.update');

    Route::put('emergency-settings/batch', [\App\Modules\Emergencies\Controllers\Admin\EmergencySettingAdminController::class, 'bulkUpdate'])->name('emergency-settings.bulk-update');
    Route::resource('emergency-settings', \App\Modules\Emergencies\Controllers\Admin\EmergencySettingAdminController::class)->except(['create', 'show', 'edit']);
    Route::post('emergency-settings/{id}/toggle', [\App\Modules\Emergencies\Controllers\Admin\EmergencySettingAdminController::class, 'toggle'])->whereNumber('id')->name('emergency-settings.toggle');
});

Route::prefix('admin')->name('admin.')->middleware(['auth','admin'])->group(function () {
    Route::delete('/emergencies/{id}', [\App\Modules\Emergencies\Controllers\Admin\DeletionController::class, 'emergency'])->whereNumber('id')->name('emergencies.destroy');
});

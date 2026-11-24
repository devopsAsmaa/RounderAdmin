<?php

use Illuminate\Support\Facades\Route;

// مسارات Settings فقط؛ الأسماء والروابط العامة محفوظة للتوافق مع العملاء.

Route::get('/privacy', [\App\Modules\Settings\Controllers\Web\PageWebController::class, 'privacy'])->name('privacy');
Route::get('/terms', [\App\Modules\Settings\Controllers\Web\PageWebController::class, 'terms'])->name('terms');
Route::get('/faq', [\App\Modules\Settings\Controllers\Web\FaqWebController::class, 'index'])->name('faq');
Route::redirect('/faqs', '/faq');
Route::get('/pages/{slug}', [\App\Modules\Settings\Controllers\Web\PageWebController::class, 'show'])->name('pages.show');

Route::post('/locale', [\App\Modules\Settings\Controllers\Admin\LocaleController::class, 'update'])->name('locale.update');

Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/pages', [\App\Modules\Settings\Controllers\Admin\PageAdminController::class, 'index'])->name('admin.pages.index');
    Route::get('/pages/{page}/edit', [\App\Modules\Settings\Controllers\Admin\PageAdminController::class, 'edit'])->whereNumber('page')->name('admin.pages.edit');
    Route::put('/pages/{page}', [\App\Modules\Settings\Controllers\Admin\PageAdminController::class, 'update'])->whereNumber('page')->name('admin.pages.update');

    Route::resource('faqs', \App\Modules\Settings\Controllers\Admin\FaqAdminController::class, ['as' => 'admin'])->except(['show']);
    Route::post('/faqs/{id}/toggle', [\App\Modules\Settings\Controllers\Admin\FaqAdminController::class, 'toggle'])->whereNumber('id')->name('admin.faqs.toggle');

    Route::get('/settings/company', [\App\Modules\Settings\Controllers\Admin\CompanySettingsController::class, 'edit'])->name('admin.company-settings.edit');
    Route::put('/settings/company', [\App\Modules\Settings\Controllers\Admin\CompanySettingsController::class, 'update'])->name('admin.company-settings.update');
});

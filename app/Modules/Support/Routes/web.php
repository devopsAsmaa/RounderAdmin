<?php

use App\Modules\Support\Controllers\Admin\SupportTicketAdminController;
use App\Modules\Support\Controllers\Web\ContactWebController;
use Illuminate\Support\Facades\Route;

Route::get('/contact-us', [ContactWebController::class, 'index'])->name('contact');
Route::post('/contact-us', [ContactWebController::class, 'submit'])->name('contact.submit');
Route::redirect('/contact', '/contact-us');

Route::prefix('admin/support-tickets')->name('admin.support-tickets.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [SupportTicketAdminController::class, 'index'])->name('index');
    Route::get('/{id}', [SupportTicketAdminController::class, 'show'])->whereNumber('id')->name('show');
    Route::put('/{id}', [SupportTicketAdminController::class, 'update'])->whereNumber('id')->name('update');
});


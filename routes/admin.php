<?php

use App\Http\Controllers\Admin\AdminApprovalController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminLocationController;
use App\Http\Controllers\Admin\AdminPaymentController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminVerificationController;

Route::group(['prefix' => 'control-center', 'middleware' => 'admin'], function () {
    Route::get('/overview', [AdminController::class, 'index'])
        ->name('admin.dashboard');

    // Admin Users
    Route::get('/users', [AdminUserController::class, 'index'])
        ->name('admin.users.index');
    Route::get('/users/create', [AdminUserController::class, 'create'])
        ->name('admin.users.create');
    Route::post('/users', [AdminUserController::class, 'store'])
        ->name('admin.users.store');
    Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])
        ->name('admin.users.edit');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])
        ->name('admin.users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])
        ->name('admin.users.destroy');
    Route::post('/users/{user}/deactivate', [AdminUserController::class, 'deactivate'])
        ->name('admin.users.deactivate');
    Route::post('/users/{user}/activate', [AdminUserController::class, 'activate'])
        ->name('admin.users.activate');
    Route::post('/users/{user}/verify', [AdminUserController::class, 'verify'])
        ->name('admin.users.verify');
    Route::post('/users/{user}/unverify', [AdminUserController::class, 'unverify'])
        ->name('admin.users.unverify');
    Route::post('/users/{user}/send-verification-reminder', [AdminUserController::class, 'sendVerificationReminder'])
        ->name('admin.users.send-verification-reminder');

    // Admin Verifications
    Route::get('/verifications', [AdminVerificationController::class, 'index'])
        ->name('admin.verifications.index');
    Route::get('/verifications/{user}/video', [AdminVerificationController::class, 'serveVideo'])
        ->name('admin.verifications.video');
    Route::post('/verifications/{user}/approve', [AdminVerificationController::class, 'approve'])
        ->name('admin.verifications.approve');
    Route::post('/verifications/{user}/reject', [AdminVerificationController::class, 'reject'])
        ->name('admin.verifications.reject');

    Route::get('/countries', [AdminLocationController::class, 'countries'])->name('admin.countries.index');
    Route::post('/countries', [AdminLocationController::class, 'storeCountry'])->name('admin.countries.store');
    Route::put('/countries/{country}', [AdminLocationController::class, 'updateCountry'])->name('admin.countries.update');
    Route::delete('/countries/{country}', [AdminLocationController::class, 'destroyCountry'])->name('admin.countries.destroy');
    Route::get('/cities', [AdminLocationController::class, 'cities'])->name('admin.cities.index');
    Route::post('/cities', [AdminLocationController::class, 'storeCity'])->name('admin.cities.store');
    Route::put('/cities/{city}', [AdminLocationController::class, 'updateCity'])->name('admin.cities.update');
    Route::delete('/cities/{city}', [AdminLocationController::class, 'destroyCity'])->name('admin.cities.destroy');

    Route::get('/payments', [AdminPaymentController::class, 'index'])->name('admin.payments.index');
    Route::get('/approvals', [AdminApprovalController::class, 'index'])->name('admin.approvals.index');
    Route::get('/approvals/{image}/thumbnail', [AdminApprovalController::class, 'thumbnail'])->name('admin.approvals.thumbnail');
    Route::patch('/approvals/{image}/approve', [AdminApprovalController::class, 'approve'])->name('admin.approvals.approve');
    Route::patch('/approvals/{image}/reject', [AdminApprovalController::class, 'reject'])->name('admin.approvals.reject');
});

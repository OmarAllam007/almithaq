<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminVerificationController;

Route::group(['prefix' => 'admin', 'middleware' => 'admin'], function () {
    Route::get('/index/admin', [AdminController::class, 'index'])
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
    Route::post('/users/{user}/unverify', [AdminUserController::class, 'unverify'])
        ->name('admin.users.unverify');

    // Admin Verifications
    Route::get('/verifications', [AdminVerificationController::class, 'index'])
        ->name('admin.verifications.index');
    Route::get('/verifications/{user}/video', [AdminVerificationController::class, 'serveVideo'])
        ->name('admin.verifications.video');
    Route::post('/verifications/{user}/approve', [AdminVerificationController::class, 'approve'])
        ->name('admin.verifications.approve');
    Route::post('/verifications/{user}/reject', [AdminVerificationController::class, 'reject'])
        ->name('admin.verifications.reject');
});

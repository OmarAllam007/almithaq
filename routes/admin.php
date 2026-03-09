<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminVerificationController;

Route::group(['prefix' => 'admin'], function () {
    Route::get('/index/admin', [AdminController::class , 'index'])
        ->name('admin.dashboard');

    // Admin Users
    Route::get('/users', [AdminUserController::class , 'index'])
        ->name('admin.users.index');
    Route::delete('/users/{user}', [AdminUserController::class , 'destroy'])
        ->name('admin.users.destroy');
    Route::post('/users/{user}/deactivate', [AdminUserController::class , 'deactivate'])
        ->name('admin.users.deactivate');
    Route::post('/users/{user}/unverify', [AdminUserController::class , 'unverify'])
        ->name('admin.users.unverify');

    // Admin Verifications
    Route::get('/verifications', [AdminVerificationController::class , 'index'])
        ->name('admin.verifications.index');
    Route::get('/verifications/{user}/video', [AdminVerificationController::class , 'serveVideo'])
        ->name('admin.verifications.video');
    Route::post('/verifications/{user}/approve', [AdminVerificationController::class , 'approve'])
        ->name('admin.verifications.approve');
    Route::post('/verifications/{user}/reject', [AdminVerificationController::class , 'reject'])
        ->name('admin.verifications.reject');
});

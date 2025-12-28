<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');

Route::get('signup', [\App\Http\Controllers\Auth\RegisterController::class, 'showSignupForm'])->name('signup');
Route::post('signup', [\App\Http\Controllers\Auth\RegisterController::class, 'store'])->name('signup.store');

// API routes for validation
Route::post('api/check-username', [\App\Http\Controllers\Api\UsernameCheckController::class, 'check'])->name('api.check-username');

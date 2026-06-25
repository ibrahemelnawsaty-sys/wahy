<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\StudentApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Version 1
|--------------------------------------------------------------------------
|
| Mobile API endpoints with Laravel Sanctum authentication
|
*/

// Public Routes (No Authentication Required)
Route::prefix('v1')->group(function () {

    // Authentication
    Route::post('/login', [AuthApiController::class, 'login']);

});

// Protected Routes (Authentication Required)
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {

    // Auth endpoints
    Route::post('/logout', [AuthApiController::class, 'logout']);
    Route::get('/profile', [AuthApiController::class, 'profile']);
    Route::put('/profile', [AuthApiController::class, 'updateProfile']);
    Route::post('/change-password', [AuthApiController::class, 'changePassword']);

    // Student endpoints
    Route::middleware('role:student')->prefix('student')->group(function () {
        Route::get('/dashboard', [StudentApiController::class, 'dashboard']);
        Route::get('/values-tree', [StudentApiController::class, 'valuesTree']);
        Route::get('/activities', [StudentApiController::class, 'activities']);
        Route::get('/activities/{id}', [StudentApiController::class, 'activityDetails']);
        Route::post('/activities/{id}/submit', [StudentApiController::class, 'submitActivity']);
        Route::get('/badges', [StudentApiController::class, 'badges']);
        Route::get('/leaderboard', [StudentApiController::class, 'leaderboard']);
    });

});

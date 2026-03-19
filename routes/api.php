<?php

use Api\Auth\Infrastructure\Http\Controllers\PostRegisterUserController;
use Api\Auth\Infrastructure\Http\Controllers\PostLoginUserController;
use Api\Auth\Infrastructure\Http\Controllers\PostLogoutUserController;
use Api\Auth\Infrastructure\Http\Controllers\GetAuthenticatedUserController;
use Api\Media\Infrastructure\Http\Controllers\GetMediaSearchController;
use Api\Media\Infrastructure\Http\Controllers\GetMediaByIdController;
use Api\System\Infrastructure\Http\Controllers\GetSystemHealthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes (no authentication required)
Route::prefix('v1')->group(function () {
    // Authentication routes
    Route::post('/login', PostLoginUserController::class);
    
    // System routes
    Route::get('/health', GetSystemHealthController::class);
});

// Protected routes (authentication required)
Route::prefix('v1')->middleware('auth:api')->group(function () {
    // Auth routes
    Route::post('/logout', PostLogoutUserController::class);
    Route::get('/user', GetAuthenticatedUserController::class);
    
    // Admin only routes
    Route::middleware('admin')->group(function () {
        Route::post('/register', PostRegisterUserController::class);
    });
    
    // Media routes
    Route::get('/media/search', GetMediaSearchController::class);
    Route::get('/media/{id}', GetMediaByIdController::class);
});

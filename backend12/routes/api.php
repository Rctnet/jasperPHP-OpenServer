<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DataSourceController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\AuthController;

// Routes for SPA Authentication (requires session state)
// These routes are defined first and use the 'web' middleware group
// to ensure they have access to session and CSRF protection.
Route::middleware('web')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/register', [AuthController::class, 'register']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', [AuthController::class, 'user']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});


// API Resource routes for authenticated users
// These routes use Sanctum for token-based authentication,
// suitable for both the SPA and external API consumers.
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('datasources', DataSourceController::class);
    Route::apiResource('reports', ReportController::class);
    Route::post('reports/execute', [ReportController::class, 'execute']);
    Route::post('reports/{report}/subreports', [ReportController::class, 'uploadSubreport'])->name('reports.subreports.store');
    Route::put('/user/profile-information', [AuthController::class, 'updateProfileInformation']);
});
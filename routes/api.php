<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SiswaApiController;

// Public API routes
Route::post('/siswa/login', [SiswaApiController::class, 'login']);

// Protected API routes (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/siswa/profile', [SiswaApiController::class, 'profile']);
    Route::get('/siswa/dashboard', [SiswaApiController::class, 'dashboard']);
    Route::get('/siswa/jadwal', [SiswaApiController::class, 'jadwal']);
    Route::get('/siswa/absensi', [SiswaApiController::class, 'absensi']);
    Route::post('/siswa/absensi/scan-guru', [SiswaApiController::class, 'scanGuruQr']);
});

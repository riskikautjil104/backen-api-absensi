<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SiswaApiController;
use App\Http\Controllers\Api\GuruApiController;

// Public API routes
Route::post('/siswa/login', [SiswaApiController::class, 'login']);
Route::post('/guru/login', [GuruApiController::class, 'login']);

// Protected API routes (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Siswa Mobile API
    Route::get('/siswa/profile', [SiswaApiController::class, 'profile']);
    Route::get('/siswa/dashboard', [SiswaApiController::class, 'dashboard']);
    Route::get('/siswa/jadwal', [SiswaApiController::class, 'jadwal']);
    Route::get('/siswa/absensi', [SiswaApiController::class, 'absensi']);
    Route::post('/siswa/absensi/scan-guru', [SiswaApiController::class, 'scanGuruQr']);
    
    // awal batas suci yang kamu ubah
    Route::post('/siswa/device-token', [SiswaApiController::class, 'updateDeviceToken']);
    // akhir batas suci yang kamu ubah

    // Guru Mobile API
    Route::get('/guru/profile', [GuruApiController::class, 'profile']);
    Route::get('/guru/dashboard', [GuruApiController::class, 'dashboard']);
    Route::get('/guru/jadwal', [GuruApiController::class, 'jadwal']);
    Route::get('/guru/kelas-mapel', [GuruApiController::class, 'kelasMapel']);
    Route::get('/guru/kelas/{kelas_id}/mapel/{mapel_id}/siswa', [GuruApiController::class, 'kelasSiswaAbsensi']);
    
    // awal batas suci yang kamu ubah
    Route::post('/guru/device-token', [GuruApiController::class, 'updateDeviceToken']);
    Route::post('/guru/absensi/status', [GuruApiController::class, 'updateStudentStatus']);
    Route::get('/guru/kelas/{kelas_id}/mapel/{mapel_id}/export-excel', [GuruApiController::class, 'exportAttendanceExcel']);
    Route::get('/guru/kelas/{kelas_id}/mapel/{mapel_id}/export-pdf', [GuruApiController::class, 'exportAttendancePdf']);
    // akhir batas suci yang kamu ubah
});

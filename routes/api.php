<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SiswaApiController;
use App\Http\Controllers\Api\GuruApiController;
use App\Http\Controllers\Api\TugasApiController;
use App\Http\Controllers\Api\BahanAjarApiController;
use App\Http\Controllers\Api\SatpamApiController;

// Public API routes
Route::post('/siswa/login', [SiswaApiController::class, 'login']);
Route::post('/guru/login', [GuruApiController::class, 'login']);
// awal batas suci yang kamu ubah
Route::post('/satpam/login', [SatpamApiController::class, 'login']);
// akhir batas suci yang kamu ubah

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
    Route::get('/siswa/kartu', [SiswaApiController::class, 'getKartuDigital']);
    Route::post('/siswa/absensi/scan-gerbang', [SiswaApiController::class, 'scanGerbangQr']);
    
    // Siswa Tugas API
    Route::get('/siswa/tugas', [TugasApiController::class, 'siswaList']);
    Route::get('/siswa/tugas/{id}', [TugasApiController::class, 'siswaShow']);
    Route::post('/siswa/tugas/{id}/kumpul', [TugasApiController::class, 'siswaSubmit']);

    // Siswa Bahan Ajar & Evaluasi API
    Route::get('/siswa/bahan-ajar', [BahanAjarApiController::class, 'siswaIndex']);
    Route::get('/siswa/bahan-ajar/{id}', [BahanAjarApiController::class, 'siswaShow']);
    Route::post('/siswa/bahan-ajar/{id}/evaluasi/submit', [BahanAjarApiController::class, 'siswaSubmitEvaluasi']);
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
    
    // Guru Tugas API
    Route::get('/guru/tugas', [TugasApiController::class, 'guruList']);
    Route::post('/guru/tugas', [TugasApiController::class, 'guruStore']);
    Route::get('/guru/tugas/{id}', [TugasApiController::class, 'guruShow']);
    Route::delete('/guru/tugas/{id}', [TugasApiController::class, 'guruDestroy']);
    Route::post('/guru/tugas/{tugas_id}/nilai/{siswa_id}', [TugasApiController::class, 'guruGrade']);
    Route::get('/guru/tugas/rekap/kelas/{kelas_id}/mapel/{mapel_id}', [TugasApiController::class, 'guruRekap']);

    // Guru Bahan Ajar & Evaluasi API
    Route::get('/guru/bahan-ajar', [BahanAjarApiController::class, 'guruIndex']);
    Route::post('/guru/bahan-ajar', [BahanAjarApiController::class, 'guruStore']);
    Route::get('/guru/bahan-ajar/{id}', [BahanAjarApiController::class, 'guruShow']);
    Route::delete('/guru/bahan-ajar/{id}', [BahanAjarApiController::class, 'guruDestroy']);
    Route::post('/guru/bahan-ajar/{bahan_ajar_id}/evaluasi/{siswa_id}/nilai', [BahanAjarApiController::class, 'guruGradeEvaluasi']);

    // Satpam (Petugas Gerbang) Mobile API
    Route::get('/satpam/profile', [SatpamApiController::class, 'profile']);
    Route::get('/satpam/dashboard', [SatpamApiController::class, 'dashboard']);
    Route::post('/satpam/scan-siswa', [SatpamApiController::class, 'scanSiswaCard']);
    Route::get('/satpam/qr-gerbang-token', [SatpamApiController::class, 'getDynamicGateQrToken']);
    Route::get('/satpam/rekap-harian', [SatpamApiController::class, 'rekapHarian']);
    Route::get('/satpam/rekap/export-excel', [SatpamApiController::class, 'exportRekapExcel']);
    Route::get('/satpam/rekap/export-pdf', [SatpamApiController::class, 'exportRekapPdf']);
    Route::get('/satpam/jam-operasional', [SatpamApiController::class, 'getJamOperasional']);
    Route::post('/satpam/jam-operasional', [SatpamApiController::class, 'updateJamOperasional']);
    Route::post('/satpam/device-token', [SatpamApiController::class, 'updateDeviceToken']);
    // akhir batas suci yang kamu ubah
});

<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Dashboard redirector
Route::get('/dashboard', function () {
    if (Auth::user()->isAdmin()) return redirect()->route('admin.dashboard');
    if (Auth::user()->isGuru()) return redirect()->route('guru.dashboard');
    // awal batas suci yang kamu ubah
    if (Auth::user()->isSatpam()) return redirect()->route('satpam.dashboard');
    // akhir batas suci yang kamu ubah
    return redirect()->route('siswa.dashboard');
})->middleware(['auth'])->name('dashboard');

// Redirect root to dashboard based on role
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
});

// Admin Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    
    // Kartu Admin
    Route::get('/kartu/cek', [App\Http\Controllers\Admin\KartuController::class, 'index'])->name('kartu.index');
    Route::post('/kartu/cek', [App\Http\Controllers\Admin\KartuController::class, 'check'])->name('kartu.check');

    // Sync SIMORO
    Route::post('/sync/all', [App\Http\Controllers\Admin\SyncController::class, 'syncAll'])->name('sync.all');
    Route::post('/sync/{type}', [App\Http\Controllers\Admin\SyncController::class, 'syncSingle'])->name('sync.single');

    Route::resource('guru', App\Http\Controllers\Admin\GuruController::class);
    // awal batas suci yang kamu ubah
    Route::resource('satpam', App\Http\Controllers\Admin\SatpamController::class);
    // akhir batas suci yang kamu ubah
    Route::resource('siswa', App\Http\Controllers\Admin\SiswaController::class);
    Route::resource('kelas', App\Http\Controllers\Admin\KelasController::class);
    Route::resource('mapel', App\Http\Controllers\Admin\MapelController::class);
    Route::get('/jadwal/guru-relations/{guru_id}', [App\Http\Controllers\Admin\JadwalController::class, 'getGuruRelations'])->name('jadwal.guru-relations');
    Route::resource('jadwal', App\Http\Controllers\Admin\JadwalController::class);
    Route::resource('buku', App\Http\Controllers\Admin\BukuController::class);
});

// Guru Routes
Route::middleware(['auth', 'role:guru'])->prefix('guru')->name('guru.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Guru\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/rekap', [App\Http\Controllers\Guru\AbsensiController::class, 'rekap'])->name('rekap');
    Route::match(['get', 'post'], '/scan/{jadwal}', [App\Http\Controllers\AbsensiController::class, 'scanGuru'])->name('scan');
    Route::get('/qr/{jadwal}', [App\Http\Controllers\Guru\AbsensiController::class, 'showQr'])->name('qr');
});

// Siswa Routes
Route::middleware(['auth', 'role:siswa'])->prefix('siswa')->name('siswa.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Siswa\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profil', [App\Http\Controllers\Siswa\ProfilController::class, 'index'])->name('profil');
    Route::put('/profil', [App\Http\Controllers\Siswa\ProfilController::class, 'update'])->name('profil.update');
    Route::get('/kartu', [App\Http\Controllers\Siswa\KartuController::class, 'index'])->name('kartu');
});

// awal batas suci yang kamu ubah
// Satpam Routes
Route::middleware(['auth', 'role:satpam'])->prefix('satpam')->name('satpam.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Satpam\DashboardController::class, 'index'])->name('dashboard');
    Route::match(['get', 'post'], '/scan', [App\Http\Controllers\Satpam\ScanController::class, 'scan'])->name('scan');
    Route::get('/rekap', [App\Http\Controllers\Satpam\RekapController::class, 'index'])->name('rekap');
});
// akhir batas suci yang kamu ubah

// Common Routes
// Guest Accessible Routes
Route::match(['get', 'post'], '/absensi/scan', [App\Http\Controllers\AbsensiController::class, 'scan'])->name('absensi.scan');

// Common Authenticated Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/perpus', [App\Http\Controllers\PerpusController::class, 'index'])->name('perpus.index');
});

require __DIR__.'/auth.php';

<?php

// awal batas suci yang kamu ubah

namespace App\Http\Controllers\Satpam;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Absensi;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today('Asia/Jayapura');
        $totalSiswa = Siswa::count();

        $todayAttendance = Absensi::whereDate('waktu_scan', $today)
            ->whereIn('tipe_presensi', ['gerbang_masuk', 'gerbang_pulang', 'mapel'])
            ->with(['siswa.user', 'siswa.kelas', 'petugas'])
            ->orderBy('waktu_scan', 'desc')
            ->get();

        $masukStudentIds = $todayAttendance
            ->whereIn('tipe_presensi', ['gerbang_masuk', 'mapel'])
            ->pluck('siswa_id')
            ->unique();

        $pulangStudentIds = $todayAttendance
            ->where('tipe_presensi', 'gerbang_pulang')
            ->pluck('siswa_id')
            ->unique();

        $hadirCount = $todayAttendance
            ->whereIn('tipe_presensi', ['gerbang_masuk', 'mapel'])
            ->where('status', 'hadir')
            ->pluck('siswa_id')
            ->unique()
            ->count();

        $terlambatCount = $todayAttendance
            ->whereIn('tipe_presensi', ['gerbang_masuk', 'mapel'])
            ->where('status', 'terlambat')
            ->pluck('siswa_id')
            ->unique()
            ->count();

        $totalMasuk = $masukStudentIds->count();
        $belumHadir = max(0, $totalSiswa - $totalMasuk);
        $totalPulang = $pulangStudentIds->count();

        $recentScans = $todayAttendance->take(20);

        return view('satpam.dashboard', compact(
            'totalSiswa', 'totalMasuk', 'hadirCount', 'terlambatCount', 'belumHadir', 'totalPulang', 'recentScans'
        ));
    }
}

// akhir batas suci yang kamu ubah

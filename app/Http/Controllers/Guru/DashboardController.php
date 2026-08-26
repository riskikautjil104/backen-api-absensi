<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Models\Absensi;
use App\Models\Jadwal;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        $hariInggris = $now->format('l');
        $hariIndo = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
        ];
        $hariSekarang = $hariIndo[$hariInggris] ?? 'Senin';

        $schedules = Jadwal::with(['kelas', 'mapel'])
            ->where('guru_id', Auth::id())
            ->where('hari', $hariSekarang)
            ->get();

        $todayAbsensiCount = Absensi::whereHas('jadwal', function($q) {
            $q->where('guru_id', Auth::id());
        })->whereDate('waktu_scan', today())->count();

        return view('guru.dashboard', compact('schedules', 'todayAbsensiCount', 'hariSekarang'));
    }
}

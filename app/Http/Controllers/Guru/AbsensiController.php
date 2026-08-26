<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Models\Absensi;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\MataPelajaran;

class AbsensiController extends Controller
{
    public function rekap(Request $request)
    {
        $guruId = Auth::id();

        // Get schedules to extract Kelas and Mapel lists for filters
        $schedules = Jadwal::where('guru_id', $guruId)->with(['kelas', 'mapel'])->get();
        $kelases = $schedules->pluck('kelas')->unique('id');
        $mapels = $schedules->pluck('mapel')->unique('id');

        // Build query for Absensi
        $query = Absensi::whereHas('jadwal', function ($q) use ($guruId) {
            $q->where('guru_id', $guruId);
        })->with(['siswa.user', 'jadwal.kelas', 'jadwal.mapel'])->orderBy('waktu_scan', 'desc');

        // Apply filters
        if ($request->filled('kelas_id')) {
            $query->whereHas('jadwal', function ($q) use ($request) {
                $q->where('kelas_id', $request->kelas_id);
            });
        }

        if ($request->filled('mapel_id')) {
            $query->whereHas('jadwal', function ($q) use ($request) {
                $q->where('mapel_id', $request->mapel_id);
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('waktu_scan', $request->date);
        }

        $absensis = $query->get();

        return view('guru.rekap', compact('kelases', 'mapels', 'absensis'));
    }

    public function showQr(Jadwal $jadwal)
    {
        if (Auth::id() !== $jadwal->guru_id) {
            abort(403, 'Anda tidak berhak mengakses absensi mata pelajaran ini.');
        }

        $payload = \Illuminate\Support\Facades\Crypt::encryptString(json_encode([
            'jadwal_id' => $jadwal->id,
            'date' => \Carbon\Carbon::today()->toDateString(),
        ]));

        return view('guru.show_qr', compact('jadwal', 'payload'));
    }
}

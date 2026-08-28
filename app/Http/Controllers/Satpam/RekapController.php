<?php

// awal batas suci yang kamu ubah

namespace App\Http\Controllers\Satpam;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Absensi;
use Carbon\Carbon;

class RekapController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today('Asia/Jayapura');
        $kelasId = $request->query('kelas_id');
        $search = $request->query('search');

        $query = Siswa::with(['user', 'kelas']);
        if ($kelasId) {
            $query->where('kelas_id', $kelasId);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nisn', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($qu) use ($search) {
                        $qu->where('name', 'like', "%{$search}%")->orWhere('nis', 'like', "%{$search}%");
                    });
            });
        }

        $students = $query->orderBy('kelas_id', 'asc')->get();
        $attendance = Absensi::whereDate('waktu_scan', $today)->get()->groupBy('siswa_id');

        $rekap = $students->map(function ($s) use ($attendance) {
            $records = $attendance->get($s->id, collect());
            $masuk = $records->first(fn($a) => $a->tipe_presensi === 'gerbang_masuk' || $a->tipe_presensi === 'mapel');
            $pulang = $records->first(fn($a) => $a->tipe_presensi === 'gerbang_pulang');

            $status = 'belum_hadir';
            if ($pulang) $status = 'sudah_pulang';
            elseif ($masuk) $status = $masuk->status;

            return (object) [
                'name' => $s->user->name ?? 'Siswa',
                'nisn' => $s->nisn ?? $s->user->nis ?? '-',
                'kelas' => $s->kelas->nama_kelas ?? '-',
                'status' => $status,
                'jam_masuk' => $masuk ? Carbon::parse($masuk->waktu_scan)->format('H:i') : '-',
                'jam_pulang' => $pulang ? Carbon::parse($pulang->waktu_scan)->format('H:i') : '-',
            ];
        });

        $kelasList = Kelas::orderBy('nama_kelas', 'asc')->get();

        return view('satpam.rekap', compact('rekap', 'kelasList', 'kelasId', 'search'));
    }
}

// akhir batas suci yang kamu ubah

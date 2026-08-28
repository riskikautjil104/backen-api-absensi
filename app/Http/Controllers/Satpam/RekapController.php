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
        $selectedDate = $request->query('tanggal', Carbon::today('Asia/Jayapura')->format('Y-m-d'));
        $targetDate = Carbon::parse($selectedDate, 'Asia/Jayapura')->startOfDay();
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
        $attendance = Absensi::whereDate('waktu_scan', $targetDate)->get()->groupBy('siswa_id');

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

        return view('satpam.rekap', compact('rekap', 'kelasList', 'kelasId', 'search', 'selectedDate'));
    }

    /**
     * Ekspor Rekap Presensi Gerbang Satpam ke Excel (.CSV UTF-8)
     */
    public function exportExcel(Request $request)
    {
        $selectedDate = $request->query('tanggal', Carbon::today('Asia/Jayapura')->format('Y-m-d'));
        $targetDate = Carbon::parse($selectedDate, 'Asia/Jayapura')->startOfDay();
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
        $attendance = Absensi::whereDate('waktu_scan', $targetDate)->get()->groupBy('siswa_id');

        $filename = "Rekap_Presensi_Gerbang_{$selectedDate}.csv";
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($students, $attendance, $selectedDate, $targetDate) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

            fputcsv($file, ['LAPORAN REKAPITULASI PRESENSI GERBANG SEKOLAH']);
            fputcsv($file, ['SMA NEGERI 5 KABUPATEN PULAU MOROTAI']);
            fputcsv($file, ['Tanggal', Carbon::parse($selectedDate)->locale('id')->isoFormat('dddd, D MMMM Y')]);
            fputcsv($file, []);

            fputcsv($file, ['No', 'Nama Siswa', 'NIS / NISN', 'Kelas', 'Jam Masuk', 'Jam Pulang', 'Status Kehadiran']);

            $no = 1;
            foreach ($students as $s) {
                $records = $attendance->get($s->id, collect());
                $masuk = $records->first(fn($a) => $a->tipe_presensi === 'gerbang_masuk' || $a->tipe_presensi === 'mapel');
                $pulang = $records->first(fn($a) => $a->tipe_presensi === 'gerbang_pulang');

                $statusLabel = 'Belum Hadir';
                if ($pulang) $statusLabel = 'Sudah Pulang';
                elseif ($masuk) {
                    $statusLabel = $masuk->status === 'terlambat' ? 'Terlambat' : 'Hadir Tepat Waktu';
                }

                fputcsv($file, [
                    $no++,
                    $s->user->name ?? '-',
                    $s->nisn ?? $s->user->nis ?? '-',
                    $s->kelas->nama_kelas ?? '-',
                    $masuk ? Carbon::parse($masuk->waktu_scan)->format('H:i') . ' WIT' : '-',
                    $pulang ? Carbon::parse($pulang->waktu_scan)->format('H:i') . ' WIT' : '-',
                    $statusLabel,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Ekspor Rekap Presensi Gerbang Satpam ke PDF / Printable View
     */
    public function exportPdf(Request $request)
    {
        $selectedDate = $request->query('tanggal', Carbon::today('Asia/Jayapura')->format('Y-m-d'));
        $targetDate = Carbon::parse($selectedDate, 'Asia/Jayapura')->startOfDay();
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
        $attendance = Absensi::whereDate('waktu_scan', $targetDate)->get()->groupBy('siswa_id');

        $hadirCount = 0;
        $terlambatCount = 0;
        $pulangCount = 0;
        $belumHadirCount = 0;

        $rows = [];
        $no = 1;
        foreach ($students as $s) {
            $records = $attendance->get($s->id, collect());
            $masuk = $records->first(fn($a) => $a->tipe_presensi === 'gerbang_masuk' || $a->tipe_presensi === 'mapel');
            $pulang = $records->first(fn($a) => $a->tipe_presensi === 'gerbang_pulang');

            $statusText = 'BELUM HADIR';
            if ($pulang) {
                $statusText = 'SUDAH PULANG';
                $pulangCount++;
            } elseif ($masuk) {
                if ($masuk->status === 'terlambat') {
                    $statusText = 'TERLAMBAT';
                    $terlambatCount++;
                } else {
                    $statusText = 'HADIR';
                    $hadirCount++;
                }
            } else {
                $belumHadirCount++;
            }

            $rows[] = [
                'no' => $no++,
                'name' => $s->user->name ?? '-',
                'nisn' => $s->nisn ?? $s->user->nis ?? '-',
                'kelas' => $s->kelas->nama_kelas ?? '-',
                'jam_masuk' => $masuk ? Carbon::parse($masuk->waktu_scan)->format('H:i') . ' WIT' : '-',
                'jam_pulang' => $pulang ? Carbon::parse($pulang->waktu_scan)->format('H:i') . ' WIT' : '-',
                'status' => $statusText,
            ];
        }

        $totalSiswa = count($students);
        $formattedDate = Carbon::parse($selectedDate)->locale('id')->isoFormat('dddd, D MMMM Y');
        $officerName = auth()->user()->name ?? 'Petugas Satpam';

        return view('satpam.print_rekap', compact(
            'rows', 'totalSiswa', 'hadirCount', 'terlambatCount', 'pulangCount', 'belumHadirCount', 'formattedDate', 'officerName'
        ));
    }
}

// akhir batas suci yang kamu ubah

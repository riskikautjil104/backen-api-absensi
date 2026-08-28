<?php

// awal batas suci yang kamu ubah

namespace App\Http\Controllers\Satpam;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Absensi;
use App\Models\JamOperasionalGerbang;
use Carbon\Carbon;

class RekapController extends Controller
{
    /**
     * Evaluasi status kehadiran & kepulangan siswa secara komprehensif
     */
    private function evaluateAttendanceStatus($masukRecord, $pulangRecord, Carbon $targetDate)
    {
        $nowWit = Carbon::now('Asia/Jayapura');
        $isToday = $targetDate->isToday();
        $isPastDate = $targetDate->isPast() && !$isToday;
        $schedule = JamOperasionalGerbang::getScheduleForDate($targetDate);
        $jamPulangStr = $schedule ? $schedule->jam_pulang_mulai : '14:00';
        [$pHour, $pMin] = explode(':', $jamPulangStr);
        $jamPulangLimit = Carbon::parse($targetDate->format('Y-m-d'), 'Asia/Jayapura')->setHour((int)$pHour)->setMinute((int)$pMin)->setSecond(0);
        $isAfterDismissal = $isToday ? $nowWit->isAfter($jamPulangLimit) : true;

        $hasMasuk = $masukRecord !== null;
        $hasPulang = $pulangRecord !== null;

        if ($hasMasuk && $hasPulang) {
            if ($masukRecord->status === 'terlambat') {
                return [
                    'key' => 'terlambat_pulang',
                    'label' => 'Terlambat (Sudah Pulang)',
                    'badge_class' => 'bg-amber-100 text-amber-900 border-amber-300',
                    'badge_icon' => '⚠️',
                ];
            }
            return [
                'key' => 'sudah_pulang',
                'label' => 'Hadir Lengkap (Sudah Pulang)',
                'badge_class' => 'bg-emerald-100 text-emerald-900 border-emerald-300',
                'badge_icon' => '✅',
            ];
        } elseif ($hasMasuk && !$hasPulang) {
            if ($isPastDate || ($isToday && $isAfterDismissal)) {
                return [
                    'key' => 'tidak_absen_pulang',
                    'label' => 'Tidak Absen Pulang (Bolos)',
                    'badge_class' => 'bg-purple-100 text-purple-900 border-purple-300',
                    'badge_icon' => '🚨',
                ];
            }
            return [
                'key' => 'di_sekolah',
                'label' => 'Masih di Sekolah',
                'badge_class' => 'bg-sky-100 text-sky-900 border-sky-300',
                'badge_icon' => '🏫',
            ];
        } elseif (!$hasMasuk && $hasPulang) {
            return [
                'key' => 'hanya_pulang',
                'label' => 'Hanya Scan Pulang',
                'badge_class' => 'bg-orange-100 text-orange-900 border-orange-300',
                'badge_icon' => '❓',
            ];
        }

        return [
            'key' => 'belum_hadir',
            'label' => 'Tidak Hadir (Alpa)',
            'badge_class' => 'bg-rose-100 text-rose-900 border-rose-300',
            'badge_icon' => '❌',
        ];
    }

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

        $hadirLengkapCount = 0;
        $terlambatCount = 0;
        $tidakAbsenPulangCount = 0;
        $diSekolahCount = 0;
        $alpaCount = 0;

        $rekap = $students->map(function ($s) use ($attendance, $targetDate, &$hadirLengkapCount, &$terlambatCount, &$tidakAbsenPulangCount, &$diSekolahCount, &$alpaCount) {
            $records = $attendance->get($s->id, collect());
            $masuk = $records->first(fn($a) => $a->tipe_presensi === 'gerbang_masuk' || $a->tipe_presensi === 'mapel');
            $pulang = $records->first(fn($a) => $a->tipe_presensi === 'gerbang_pulang');

            $eval = $this->evaluateAttendanceStatus($masuk, $pulang, $targetDate);

            if ($eval['key'] === 'sudah_pulang') $hadirLengkapCount++;
            elseif ($eval['key'] === 'terlambat_pulang') $terlambatCount++;
            elseif ($eval['key'] === 'tidak_absen_pulang') $tidakAbsenPulangCount++;
            elseif ($eval['key'] === 'di_sekolah') $diSekolahCount++;
            elseif ($eval['key'] === 'belum_hadir') $alpaCount++;

            return (object) [
                'name' => $s->user->name ?? 'Siswa',
                'nisn' => $s->nisn ?? $s->user->nis ?? '-',
                'kelas' => $s->kelas->nama_kelas ?? '-',
                'status_key' => $eval['key'],
                'status_label' => $eval['label'],
                'badge_class' => $eval['badge_class'],
                'badge_icon' => $eval['badge_icon'],
                'jam_masuk' => $masuk ? Carbon::parse($masuk->waktu_scan)->format('H:i') : '-',
                'jam_pulang' => $pulang ? Carbon::parse($pulang->waktu_scan)->format('H:i') : '-',
            ];
        });

        $kelasList = Kelas::orderBy('nama_kelas', 'asc')->get();

        $stats = [
            'total' => $students->count(),
            'hadir_lengkap' => $hadirLengkapCount,
            'terlambat' => $terlambatCount,
            'tidak_absen_pulang' => $tidakAbsenPulangCount,
            'di_sekolah' => $diSekolahCount,
            'alpa' => $alpaCount,
        ];

        return view('satpam.rekap', compact('rekap', 'kelasList', 'kelasId', 'search', 'selectedDate', 'stats'));
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

            fputcsv($file, ['LAPORAN REKAPITULASI PRESENSI GERBANG KEAMANAN']);
            fputcsv($file, ['SMA NEGERI 5 KABUPATEN PULAU MOROTAI']);
            fputcsv($file, ['Tanggal Rekap', Carbon::parse($selectedDate)->locale('id')->isoFormat('dddd, D MMMM Y')]);
            fputcsv($file, []);

            fputcsv($file, ['No', 'Nama Siswa', 'NIS / NISN', 'Kelas', 'Jam Masuk', 'Jam Pulang', 'Status Kehadiran & Kepulangan']);

            $no = 1;
            foreach ($students as $s) {
                $records = $attendance->get($s->id, collect());
                $masuk = $records->first(fn($a) => $a->tipe_presensi === 'gerbang_masuk' || $a->tipe_presensi === 'mapel');
                $pulang = $records->first(fn($a) => $a->tipe_presensi === 'gerbang_pulang');

                $eval = $this->evaluateAttendanceStatus($masuk, $pulang, $targetDate);

                fputcsv($file, [
                    $no++,
                    $s->user->name ?? '-',
                    $s->nisn ?? $s->user->nis ?? '-',
                    $s->kelas->nama_kelas ?? '-',
                    $masuk ? Carbon::parse($masuk->waktu_scan)->format('H:i') . ' WIT' : '-',
                    $pulang ? Carbon::parse($pulang->waktu_scan)->format('H:i') . ' WIT' : '-',
                    $eval['label'],
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

        $hadirLengkapCount = 0;
        $terlambatCount = 0;
        $tidakAbsenPulangCount = 0;
        $diSekolahCount = 0;
        $alpaCount = 0;

        $rows = [];
        $no = 1;
        foreach ($students as $s) {
            $records = $attendance->get($s->id, collect());
            $masuk = $records->first(fn($a) => $a->tipe_presensi === 'gerbang_masuk' || $a->tipe_presensi === 'mapel');
            $pulang = $records->first(fn($a) => $a->tipe_presensi === 'gerbang_pulang');

            $eval = $this->evaluateAttendanceStatus($masuk, $pulang, $targetDate);

            if ($eval['key'] === 'sudah_pulang') $hadirLengkapCount++;
            elseif ($eval['key'] === 'terlambat_pulang') $terlambatCount++;
            elseif ($eval['key'] === 'tidak_absen_pulang') $tidakAbsenPulangCount++;
            elseif ($eval['key'] === 'di_sekolah') $diSekolahCount++;
            elseif ($eval['key'] === 'belum_hadir') $alpaCount++;

            $rows[] = [
                'no' => $no++,
                'name' => $s->user->name ?? '-',
                'nisn' => $s->nisn ?? $s->user->nis ?? '-',
                'kelas' => $s->kelas->nama_kelas ?? '-',
                'jam_masuk' => $masuk ? Carbon::parse($masuk->waktu_scan)->format('H:i') . ' WIT' : '-',
                'jam_pulang' => $pulang ? Carbon::parse($pulang->waktu_scan)->format('H:i') . ' WIT' : '-',
                'status' => strtoupper($eval['label']),
                'status_key' => $eval['key'],
            ];
        }

        $totalSiswa = count($students);
        $formattedDate = Carbon::parse($selectedDate)->locale('id')->isoFormat('dddd, D MMMM Y');
        $officerName = auth()->user()->name ?? 'Petugas Satpam';

        return view('satpam.print_rekap', compact(
            'rows', 'totalSiswa', 'hadirLengkapCount', 'terlambatCount', 'tidakAbsenPulangCount', 'diSekolahCount', 'alpaCount', 'formattedDate', 'officerName'
        ));
    }
}

// akhir batas suci yang kamu ubah

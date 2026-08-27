<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Jadwal;
use App\Models\Absensi;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GuruApiController extends Controller
{
    private function getIndoDay()
    {
        $hariInggris = Carbon::now()->format('l');
        $hariIndo = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
        ];
        return $hariIndo[$hariInggris] ?? 'Senin';
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        // awal batas suci yang kamu ubah
        $login = $request->input('email') ?? $request->input('login') ?? $request->input('nip');
        $password = $request->input('password');
        $authenticated = false;
        $user = null;

        // 1. Cek User Lokal di Database Absensi (bisa pakai Email atau NIP)
        $user = User::where('email', $login)
            ->orWhere('nip', $login)
            ->first();

        if ($user && Hash::check($password, $user->password) && ($user->role === 'guru' || $user->role === 'teacher')) {
            $authenticated = true;
        }

        // 2. Jika gagal/password beda/user baru, Auto-Sync SSO ke Server SIMORO
        if (!$authenticated) {
            try {
                $apiUrl = config('services.simoro.url', 'https://simoro.sma-n5-morotai.id/api');
                $response = \Illuminate\Support\Facades\Http::timeout(5)->post($apiUrl . '/login', [
                    'email' => $login,
                    'login' => $login,
                    'password' => $password,
                ]);

                if ($response->successful() && $response->json('user')) {
                    $apiUser = $response->json('user');
                    $role = $apiUser['role'] ?? 'teacher';

                    if ($role === 'teacher' || $role === 'guru') {
                        $nip = $apiUser['nip'] ?? null;
                        if ($user) {
                            // Update password lokal agar sama permanen dengan SIMORO
                            $user->update([
                                'name' => $apiUser['name'],
                                'email' => $apiUser['email'],
                                'nip' => $nip,
                                'role' => 'guru',
                                'password' => Hash::make($password),
                            ]);
                        } else {
                            $user = User::create([
                                'name' => $apiUser['name'],
                                'email' => $apiUser['email'],
                                'nip' => $nip,
                                'role' => 'guru',
                                'password' => Hash::make($password),
                            ]);
                        }
                        $authenticated = true;
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('SIMORO Guru SSO login fallback: ' . $e->getMessage());
            }
        }

        if (!$authenticated || !$user) {
            return response()->json([
                'success' => false,
                'message' => 'Email/NIP atau kata sandi Guru salah.'
            ], 401);
        }
        // akhir batas suci yang kamu ubah

        // Generate Sanctum token
        $token = $user->createToken('guru_mobile_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login Guru berhasil. Selamat datang, ' . $user->name,
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'nip' => $user->nip ?? '-',
                    'role' => 'teacher',
                    'created_at' => $user->created_at,
                ]
            ]
        ]);
    }

    public function profile(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'success' => true,
            'message' => 'Data profil guru berhasil diambil.',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'nip' => $user->nip ?? '-',
                'role' => 'teacher',
            ]
        ]);
    }

    public function dashboard(Request $request)
    {
        $user = $request->user();
        $hariSekarang = $this->getIndoDay();

        // Today's schedules of this Guru
        $schedules = Jadwal::with(['kelas', 'mapel'])
            ->where('guru_id', $user->id)
            ->where('hari', $hariSekarang)
            ->orderBy('jam_mulai')
            ->get();

        $scheduleData = $schedules->map(function ($sched) {
            // Count total students in this class
            $totalStudents = Siswa::where('kelas_id', $sched->kelas_id)->count();

            // Count present students today for this schedule slot
            $totalPresent = Absensi::where('jadwal_id', $sched->id)
                ->whereDate('waktu_scan', Carbon::today())
                ->whereIn('status', ['hadir', 'terlambat'])
                ->count();

            return [
                'id' => $sched->id,
                'class_id' => $sched->kelas_id,
                'class_name' => $sched->kelas->nama_kelas ?? '-',
                'subject_id' => $sched->mapel_id,
                'subject_name' => $sched->mapel->nama_mapel ?? '-',
                'time_start' => substr($sched->jam_mulai, 0, 5),
                'time_end' => substr($sched->jam_selesai, 0, 5),
                'stats' => [
                    'total_students' => $totalStudents,
                    'total_present' => $totalPresent,
                    'total_absent' => max(0, $totalStudents - $totalPresent),
                ]
            ];
        });

        // Overall stats (classes and mapels taught)
        $totalClasses = DB::table('guru_mapel')->where('guru_id', $user->id)->distinct('kelas_id')->count('kelas_id');
        $totalMapels = DB::table('guru_mapel')->where('guru_id', $user->id)->distinct('mapel_id')->count('mapel_id');

        return response()->json([
            'success' => true,
            'message' => 'Dashboard data loaded successfully.',
            'data' => [
                'stats' => [
                    'total_classes_taught' => $totalClasses,
                    'total_subjects_taught' => $totalMapels,
                    'total_schedules_today' => $schedules->count()
                ],
                'jadwal_hari_ini' => $scheduleData
            ]
        ]);
    }

    public function jadwal(Request $request)
    {
        $user = $request->user();

        $schedules = Jadwal::with(['kelas', 'mapel'])
            ->where('guru_id', $user->id)
            ->orderByRaw("FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')")
            ->orderBy('jam_mulai')
            ->get()
            ->map(function ($sched) {
                return [
                    'id' => $sched->id,
                    'hari' => $sched->hari,
                    'class_id' => $sched->kelas_id,
                    'class_name' => $sched->kelas->nama_kelas ?? '-',
                    'subject_id' => $sched->mapel_id,
                    'subject_name' => $sched->mapel->nama_mapel ?? '-',
                    'time_start' => substr($sched->jam_mulai, 0, 5),
                    'time_end' => substr($sched->jam_selesai, 0, 5),
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Jadwal mengajar berhasil diambil.',
            'data' => $schedules
        ]);
    }

    public function kelasMapel(Request $request)
    {
        $user = $request->user();

        $relations = DB::table('guru_mapel')
            ->where('guru_id', $user->id)
            ->get();

        $mappedData = $relations->map(function ($rel) {
            $kelas = Kelas::find($rel->kelas_id);
            $mapel = MataPelajaran::find($rel->mapel_id);
            $totalStudents = Siswa::where('kelas_id', $rel->kelas_id)->count();

            return [
                'class_id' => $rel->kelas_id,
                'class_name' => $kelas->nama_kelas ?? '-',
                'subject_id' => $rel->mapel_id,
                'subject_name' => $mapel->nama_mapel ?? '-',
                'total_students' => $totalStudents,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar kelas dan mata pelajaran yang diajar berhasil diambil.',
            'data' => $mappedData
        ]);
    }

    public function kelasSiswaAbsensi(Request $request, $kelasId, $mapelId)
    {
        $user = $request->user();
        $hariSekarang = $this->getIndoDay();

        // 1. Get schedule for this class, mapel, and guru today (if any)
        $schedule = Jadwal::where('guru_id', $user->id)
            ->where('kelas_id', $kelasId)
            ->where('mapel_id', $mapelId)
            ->where('hari', $hariSekarang)
            ->first();

        // 2. Fetch all students in the class
        $students = Siswa::where('kelas_id', $kelasId)
            ->with('user')
            ->get();

        $presentCount = 0;
        $lateCount = 0;

        $siswaList = $students->map(function ($s) use ($schedule, &$presentCount, &$lateCount) {
            $attendance = null;
            if ($schedule) {
                $attendance = Absensi::where('siswa_id', $s->id)
                    ->where('jadwal_id', $schedule->id)
                    ->whereDate('waktu_scan', Carbon::today())
                    ->first();
            }

            $status = 'belum absen';
            $waktuScan = null;

            if ($attendance) {
                $status = $attendance->status; // 'hadir' or 'terlambat'
                $waktuScan = Carbon::parse($attendance->waktu_scan)->format('H:i');
                if ($status === 'hadir') {
                    $presentCount++;
                } elseif ($status === 'terlambat') {
                    $lateCount++;
                }
            }

            return [
                'id' => $s->id,
                'name' => $s->user->name ?? '-',
                'nis' => $s->user->nis ?? '-',
                'phone' => $s->nomor_hp ?? '-',
                'attendance_status' => $status,
                'scanned_time' => $waktuScan,
            ];
        });

        $totalStudents = $students->count();
        $totalPresent = $presentCount + $lateCount;

        return response()->json([
            'success' => true,
            'message' => 'Daftar absensi siswa berhasil diambil.',
            'data' => [
                'class_info' => [
                    'class_id' => (int)$kelasId,
                    'class_name' => Kelas::where('id', $kelasId)->value('nama_kelas') ?? '-',
                    'subject_name' => MataPelajaran::where('id', $mapelId)->value('nama_mapel') ?? '-',
                    'schedule_today' => $schedule ? [
                        'id' => $schedule->id,
                        'time_start' => substr($schedule->jam_mulai, 0, 5),
                        'time_end' => substr($schedule->jam_selesai, 0, 5),
                    ] : null,
                ],
                'summary' => [
                    'total_students' => $totalStudents,
                    'total_present' => $totalPresent,
                    'total_absent' => max(0, $totalStudents - $totalPresent),
                    'details' => [
                        'ontime' => $presentCount,
                        'late' => $lateCount,
                        'absent_or_not_scanned' => max(0, $totalStudents - $totalPresent),
                    ]
                ],
                'students' => $siswaList
            ]
        ]);
    }

    // awal batas suci yang kamu ubah
    /**
     * Simpan / Update FCM Device Token Guru
     * POST /api/guru/device-token
     */
    public function updateDeviceToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string|min:10',
        ]);

        $request->user()->update([
            'fcm_token' => $request->fcm_token,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'FCM Device Token Guru berhasil disimpan.',
        ], 200);
    }

    /**
     * Ubah Status Kehadiran Siswa Secara Manual oleh Guru (Hadir, Terlambat, Izin, Sakit, Alpa, Belum Absen)
     * POST /api/guru/absensi/status
     */
    public function updateStudentStatus(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required|integer',
            'kelas_id' => 'required|integer',
            'mapel_id' => 'required|integer',
            'status'   => 'required|in:hadir,terlambat,izin,sakit,alpa,alpha,belum absen',
        ]);

        $user = $request->user();
        $siswaId = $request->input('siswa_id');
        $kelasId = $request->input('kelas_id');
        $mapelId = $request->input('mapel_id');
        $newStatus = strtolower($request->input('status'));
        if ($newStatus === 'alpha') $newStatus = 'alpa';

        $targetDate = $request->input('date') ?? Carbon::now('Asia/Jayapura')->toDateString();

        // 1. Cari atau tentukan Jadwal yang sesuai
        $schedule = Jadwal::where('guru_id', $user->id)
            ->where('kelas_id', $kelasId)
            ->where('mapel_id', $mapelId)
            ->first();

        $scheduleId = $schedule ? $schedule->id : null;

        // Jika belum ada jadwal sama sekali, ambil/buat jadwal default untuk mencatat absensi
        if (!$scheduleId) {
            $schedule = Jadwal::firstOrCreate(
                [
                    'guru_id'  => $user->id,
                    'kelas_id' => $kelasId,
                    'mapel_id' => $mapelId,
                ],
                [
                    'hari'        => $this->getIndoDay(),
                    'jam_mulai'   => '07:30:00',
                    'jam_selesai' => '09:00:00',
                ]
            );
            $scheduleId = $schedule->id;
        }

        // 2. Update atau Hapus Absensi
        if ($newStatus === 'belum absen') {
            Absensi::where('siswa_id', $siswaId)
                ->where('jadwal_id', $scheduleId)
                ->whereDate('waktu_scan', $targetDate)
                ->delete();
            $statusResult = 'belum absen';
            $waktuScanResult = null;
        } else {
            $nowWit = Carbon::now('Asia/Jayapura');
            $absensi = Absensi::updateOrCreate(
                [
                    'siswa_id'  => $siswaId,
                    'jadwal_id' => $scheduleId,
                ],
                [
                    'status'     => $newStatus,
                    'waktu_scan' => $nowWit->toDateTimeString(),
                ]
            );
            $statusResult = $absensi->status;
            $waktuScanResult = $nowWit->format('H:i');
        }

        $siswa = Siswa::with('user')->find($siswaId);
        $namaSiswa = $siswa?->user?->name ?? 'Siswa';

        return response()->json([
            'success' => true,
            'message' => "Status kehadiran {$namaSiswa} berhasil diubah menjadi " . strtoupper($statusResult),
            'data'    => [
                'siswa_id'          => $siswaId,
                'name'              => $namaSiswa,
                'attendance_status' => $statusResult,
                'scanned_time'      => $waktuScanResult,
            ]
        ]);
    }

    /**
     * Ekspor Rekap Kehadiran Siswa ke Format Excel (.CSV UTF-8)
     * GET /api/guru/kelas/{kelas_id}/mapel/{mapel_id}/export-excel
     */
    public function exportAttendanceExcel(Request $request, $kelasId, $mapelId)
    {
        $user = $request->user();
        $kelas = Kelas::find($kelasId);
        $mapel = MataPelajaran::find($mapelId);

        $className = $kelas->nama_kelas ?? "Kelas_{$kelasId}";
        $mapelName = $mapel->nama_mapel ?? "Mapel_{$mapelId}";
        $todayDate = Carbon::now('Asia/Jayapura')->translatedFormat('d F Y');
        $dateIso   = Carbon::now('Asia/Jayapura')->format('Y-m-d');

        $schedule = Jadwal::where('guru_id', $user->id)
            ->where('kelas_id', $kelasId)
            ->where('mapel_id', $mapelId)
            ->first();

        $students = Siswa::where('kelas_id', $kelasId)->with('user')->get();

        // Siapkan stream CSV
        $filename = "Rekap_Absensi_{$className}_{$mapelName}_{$dateIso}.csv";
        $filename = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $filename);

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($students, $schedule, $className, $mapelName, $todayDate, $user) {
            $file = fopen('php://output', 'w');
            // Tambahkan UTF-8 BOM agar Microsoft Excel membaca karakter dengan benar
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header Laporan
            fputcsv($file, ['LAPORAN REKAPITULASI PRESENSI KEHADIRAN SISWA']);
            fputcsv($file, ['SMA NEGERI 5 KABUPATEN PULAU MOROTAI']);
            fputcsv($file, []);
            fputcsv($file, ['Kelas', $className]);
            fputcsv($file, ['Mata Pelajaran', $mapelName]);
            fputcsv($file, ['Guru Pengampu', $user->name]);
            fputcsv($file, ['Tanggal Rekap', $todayDate . ' WIT']);
            fputcsv($file, []);

            // Header Kolom Tabel
            fputcsv($file, ['No', 'NIS', 'Nama Lengkap Siswa', 'Status Kehadiran', 'Waktu Presensi', 'Keterangan']);

            $no = 1;
            foreach ($students as $s) {
                $attendance = null;
                if ($schedule) {
                    $attendance = Absensi::where('siswa_id', $s->id)
                        ->where('jadwal_id', $schedule->id)
                        ->whereDate('waktu_scan', Carbon::today('Asia/Jayapura'))
                        ->first();
                }

                $status = $attendance ? strtoupper($attendance->status) : 'BELUM ABSEN';
                $waktu  = $attendance ? Carbon::parse($attendance->waktu_scan)->format('H:i') . ' WIT' : '-';

                fputcsv($file, [
                    $no++,
                    $s->user->nis ?? '-',
                    $s->user->name ?? '-',
                    $status,
                    $waktu,
                    $status === 'HADIR' ? 'Tepat Waktu' : ($status === 'TERLAMBAT' ? 'Terlambat' : ($status === 'IZIN' ? 'Surat Izin' : ($status === 'SAKIT' ? 'Surat Sakit' : '-'))),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Ekspor Rekap Kehadiran Siswa ke Format PDF / Cetak (HTML Printable Format)
     * GET /api/guru/kelas/{kelas_id}/mapel/{mapel_id}/export-pdf
     */
    public function exportAttendancePdf(Request $request, $kelasId, $mapelId)
    {
        $user = $request->user();
        $kelas = Kelas::find($kelasId);
        $mapel = MataPelajaran::find($mapelId);

        $className = $kelas->nama_kelas ?? "Kelas {$kelasId}";
        $mapelName = $mapel->nama_mapel ?? "Mata Pelajaran {$mapelId}";
        $todayDate = Carbon::now('Asia/Jayapura')->locale('id')->isoFormat('dddd, D MMMM Y');

        $schedule = Jadwal::where('guru_id', $user->id)
            ->where('kelas_id', $kelasId)
            ->where('mapel_id', $mapelId)
            ->first();

        $students = Siswa::where('kelas_id', $kelasId)->with('user')->get();

        $hadirCount = 0;
        $lateCount = 0;
        $izinCount = 0;
        $sakitCount = 0;
        $alpaCount = 0;

        $rows = [];
        $no = 1;
        foreach ($students as $s) {
            $attendance = null;
            if ($schedule) {
                $attendance = Absensi::where('siswa_id', $s->id)
                    ->where('jadwal_id', $schedule->id)
                    ->whereDate('waktu_scan', Carbon::today('Asia/Jayapura'))
                    ->first();
            }

            $st = $attendance ? strtolower($attendance->status) : 'belum absen';
            if ($st === 'hadir') $hadirCount++;
            elseif ($st === 'terlambat') $lateCount++;
            elseif ($st === 'izin') $izinCount++;
            elseif ($st === 'sakit') $sakitCount++;
            elseif ($st === 'alpa' || $st === 'alpha') $alpaCount++;

            $rows[] = [
                'no'     => $no++,
                'nis'    => $s->user->nis ?? '-',
                'name'   => $s->user->name ?? '-',
                'status' => strtoupper($st),
                'time'   => $attendance ? Carbon::parse($attendance->waktu_scan)->format('H:i') . ' WIT' : '-',
            ];
        }

        $totalStudents = count($students);

        $html = view('admin.absensi.print_report', compact(
            'className', 'mapelName', 'todayDate', 'user', 'rows',
            'totalStudents', 'hadirCount', 'lateCount', 'izinCount', 'sakitCount', 'alpaCount'
        ))->render();

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }
    // akhir batas suci yang kamu ubah
}


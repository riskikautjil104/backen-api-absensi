<?php

// awal batas suci yang kamu ubah

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\KartuSiswa;
use App\Models\Absensi;
use App\Models\JamOperasionalGerbang;
use App\Services\FcmService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SatpamApiController extends Controller
{
    protected FcmService $fcmService;

    public function __construct(FcmService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    /**
     * Login API khusus Petugas Gerbang / Satpam
     * POST /api/satpam/login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
            'device_name' => 'nullable|string',
        ]);

        $identifier = $request->email;
        $user = User::where(function ($query) use ($identifier) {
            $query->where('email', $identifier)
                ->orWhere('nip', $identifier)
                ->orWhere('name', $identifier);
        })->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Kredensial login satpam tidak cocok.',
            ], 401);
        }

        // Verifikasi role satpam atau admin
        if (!$user->isSatpam() && !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda tidak memiliki hak akses Petugas Gerbang / Satpam.',
            ], 403);
        }

        // Simpan device token jika dikirimkan
        if ($request->filled('fcm_token')) {
            $user->update(['fcm_token' => $request->fcm_token]);
        }

        $token = $user->createToken($request->device_name ?? 'satpam-mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login Petugas Gerbang / Satpam berhasil.',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'nip' => $user->nip ?? '-',
                    'role' => $user->role,
                    'foto' => $user->foto ? url('storage/' . $user->foto) : null,
                ],
            ],
        ]);
    }

    /**
     * Profil Petugas Satpam
     * GET /api/satpam/profile
     */
    public function profile(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'success' => true,
            'message' => 'Profil satpam berhasil dimuat.',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'nip' => $user->nip ?? '-',
                'role' => $user->role,
                'foto' => $user->foto ? url('storage/' . $user->foto) : null,
                'shift' => 'Pagi (Gerbang Utama)',
                'lokasi' => 'SMA Negeri 5 Pulau Morotai',
            ],
        ]);
    }

    /**
     * Dashboard Gerbang Satpam (Live Daily Counters & Recent Activity)
     * GET /api/satpam/dashboard
     */
    public function dashboard(Request $request)
    {
        $today = Carbon::today('Asia/Jayapura');
        $totalSiswa = Siswa::count();

        // Ambil data absensi gerbang hari ini
        $todayAttendance = Absensi::whereDate('waktu_scan', $today)
            ->whereIn('tipe_presensi', ['gerbang_masuk', 'gerbang_pulang', 'mapel'])
            ->with(['siswa.user', 'siswa.kelas', 'petugas'])
            ->orderBy('waktu_scan', 'desc')
            ->get();

        // Hitung statistik
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

        // 15 Aktivitas Pemindaian Terkini
        $recentScans = $todayAttendance->take(15)->map(function ($item) {
            $siswa = $item->siswa;
            $user = $siswa?->user;
            return [
                'id' => $item->id,
                'siswa_id' => $item->siswa_id,
                'name' => $user?->name ?? 'Siswa',
                'nisn' => $siswa?->nisn ?? $user?->nis ?? '-',
                'nis' => $user?->nis ?? '-',
                'class_name' => $siswa?->kelas?->nama_kelas ?? '-',
                'foto' => $user?->foto ? url('storage/' . $user->foto) : null,
                'tipe_presensi' => $item->tipe_presensi,
                'status' => $item->status,
                'metode_scan' => $item->metode_scan ?? 'kartu_digital',
                'petugas_name' => $item->petugas?->name ?? 'Satpam Gerbang',
                'waktu' => Carbon::parse($item->waktu_scan)->locale('id')->isoFormat('HH:mm:ss') . ' WIT',
                'waktu_formatted' => Carbon::parse($item->waktu_scan)->locale('id')->isoFormat('dddd, D MMM Y • HH:mm') . ' WIT',
            ];
        })->values();

        return response()->json([
            'success' => true,
            'message' => 'Dashboard satpam berhasil dimuat.',
            'data' => [
                'stats' => [
                    'total_siswa' => $totalSiswa,
                    'sudah_masuk' => $totalMasuk,
                    'hadir_tepat_waktu' => $hadirCount,
                    'terlambat' => $terlambatCount,
                    'belum_hadir' => $belumHadir,
                    'sudah_pulang' => $totalPulang,
                    'persentase_kehadiran' => $totalSiswa > 0 ? round(($totalMasuk / $totalSiswa) * 100, 1) : 0,
                ],
                'server_time' => Carbon::now('Asia/Jayapura')->locale('id')->isoFormat('dddd, D MMMM Y • HH:mm:ss') . ' WIT',
                'recent_scans' => $recentScans,
            ],
        ]);
    }

    /**
     * Memproses Pemindaian Kartu Siswa oleh Kamera Satpam
     * POST /api/satpam/scan-siswa
     */
    public function scanSiswaCard(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'tipe_scan' => 'nullable|in:masuk,pulang',
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
        ]);

        $user = $request->user();
        $rawToken = trim($request->token);
        $tipeScan = $request->tipe_scan ?? 'masuk';

        // Coba dekripsi jika token dienkripsi via Crypt::encryptString()
        $decryptedToken = $rawToken;
        try {
            $decryptedToken = Crypt::decryptString($rawToken);
        } catch (\Throwable $e) {
            // Token merupakan plain token atau NISN langsung
        }

        // 1. Cari data Kartu Siswa
        $kartu = KartuSiswa::where('token', $decryptedToken)
            ->orWhere('token', $rawToken)
            ->first();

        $siswa = null;
        if ($kartu) {
            $siswa = $kartu->siswa;
        } else {
            // Fallback cari via NIS / NISN / User ID
            $siswa = Siswa::where('nisn', $decryptedToken)
                ->orWhereHas('user', function ($q) use ($decryptedToken, $rawToken) {
                    $q->where('nis', $decryptedToken)->orWhere('nis', $rawToken);
                })->first();
        }

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Kartu Siswa tidak terdaftar atau tidak ditemukan dalam sistem.',
            ], 404);
        }

        if ($kartu && $kartu->status !== 'aktif') {
            return response()->json([
                'success' => false,
                'message' => "Kartu Siswa atas nama {$siswa->user->name} berstatus NON-AKTIF.",
            ], 403);
        }

        $now = Carbon::now('Asia/Jayapura');
        $today = Carbon::today('Asia/Jayapura');
        $tipePresensiDb = $tipeScan === 'pulang' ? 'gerbang_pulang' : 'gerbang_masuk';

        // 2. Cek apakah sudah pernah scan di sesi ini hari ini
        $existing = Absensi::where('siswa_id', $siswa->id)
            ->whereDate('waktu_scan', $today)
            ->where('tipe_presensi', $tipePresensiDb)
            ->first();

        if ($existing) {
            $waktuScanLalu = Carbon::parse($existing->waktu_scan)->format('H:i');
            return response()->json([
                'success' => false,
                'message' => "Siswa {$siswa->user->name} sudah melakukan presensi {$tipeScan} pada pukul {$waktuScanLalu} WIT.",
                'data' => [
                    'student' => [
                        'id' => $siswa->id,
                        'name' => $siswa->user->name,
                        'nisn' => $siswa->nisn ?? $siswa->user->nis ?? '-',
                        'nis' => $siswa->user->nis ?? '-',
                        'class_name' => $siswa->kelas->nama_kelas ?? '-',
                        'foto' => $siswa->user->foto ? url('storage/' . $siswa->user->foto) : null,
                        'status' => $existing->status,
                        'waktu_scan' => Carbon::parse($existing->waktu_scan)->format('H:i:s') . ' WIT',
                    ],
                ],
            ], 409);
        }

        // 3. Tentukan Status Kehadiran (Tepat Waktu vs Terlambat) menggunakan Jam Operasional Hari Ini
        $status = 'hadir';
        if ($tipeScan === 'masuk') {
            $schedule = JamOperasionalGerbang::getScheduleForDate($now);
            $batasMasukStr = $schedule ? $schedule->jam_masuk_batas : '07:30';
            [$bHour, $bMin] = explode(':', $batasMasukStr);
            $jamMasukLimit = Carbon::today('Asia/Jayapura')->setHour((int)$bHour)->setMinute((int)$bMin)->setSecond(0);
            if ($now->isAfter($jamMasukLimit)) {
                $status = 'terlambat';
            }
        }

        $absensi = Absensi::create([
            'siswa_id' => $siswa->id,
            'jadwal_id' => null,
            'tipe_presensi' => $tipePresensiDb,
            'petugas_id' => $user->id,
            'waktu_scan' => $now,
            'status' => $status,
            'keterangan' => $tipeScan === 'masuk' ? 'Presensi Masuk Gerbang Sekolah' : 'Presensi Kepulangan Siswa',
            'metode_scan' => 'kartu_digital',
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        // Push Notification ke Siswa & Orang Tua
        try {
            $studentToken = $siswa->user->fcm_token;
            if ($studentToken) {
                $statusLabel = $status === 'terlambat' ? '⚠️ Terlambat Masuk' : '✅ Hadir di Sekolah';
                $this->fcmService->sendToDevice(
                    $studentToken,
                    "Gerbang Sekolah: {$statusLabel}",
                    "Presensi {$tipeScan} berhasil dicatat oleh Petugas {$user->name} pada {$now->format('H:i')} WIT.",
                    [
                        'type' => 'gate_attendance',
                        'status' => $status,
                        'waktu' => $now->format('H:i:s'),
                    ]
                );
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal kirim FCM gate scan: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => "Presensi {$tipeScan} berhasil dicatat untuk {$siswa->user->name}.",
            'data' => [
                'absensi_id' => $absensi->id,
                'student' => [
                    'id' => $siswa->id,
                    'name' => $siswa->user->name,
                    'nisn' => $siswa->nisn ?? $siswa->user->nis ?? '-',
                    'nis' => $siswa->user->nis ?? '-',
                    'class_name' => $siswa->kelas->nama_kelas ?? '-',
                    'foto' => $siswa->user->foto ? url('storage/' . $siswa->user->foto) : null,
                    'status' => $status,
                    'tipe_presensi' => $tipePresensiDb,
                    'waktu_scan' => $now->format('H:i:s') . ' WIT',
                    'petugas_name' => $user->name,
                ],
            ],
        ]);
    }

    /**
     * Menghasilkan Token QRIS Dinamis Gerbang Satpam (Anti-Cheat Dynamic QR)
     * GET /api/satpam/qr-gerbang-token
     */
    public function getDynamicGateQrToken(Request $request)
    {
        $user = $request->user();
        $now = Carbon::now('Asia/Jayapura');
        $timestamp = $now->timestamp;

        // Token dinamis di-hash dengan signature unik rahasia server
        $secret = config('app.key', 'moro5smart-secure-gate-key');
        $rawSignature = hash_hmac('sha256', "moro5_gate_{$timestamp}_{$user->id}_{$now->format('Y-m-d')}", $secret);

        $payload = json_encode([
            'type' => 'moro5_dynamic_gate_qr',
            'petugas_id' => $user->id,
            'petugas_name' => $user->name,
            'date' => $now->format('Y-m-d'),
            'ts' => $timestamp,
            'sig' => substr($rawSignature, 0, 32),
        ]);

        $encryptedPayload = Crypt::encryptString($payload);

        return response()->json([
            'success' => true,
            'message' => 'Token QRIS Gerbang berhasil dibuat.',
            'data' => [
                'qr_string' => $encryptedPayload,
                'petugas_name' => $user->name,
                'valid_until' => $now->addSeconds(60)->format('H:i:s') . ' WIT',
                'expires_in_seconds' => 60,
                'server_time' => $now->format('H:i:s') . ' WIT',
            ],
        ]);
    }

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
                ];
            }
            return [
                'key' => 'sudah_pulang',
                'label' => 'Hadir Lengkap (Sudah Pulang)',
            ];
        } elseif ($hasMasuk && !$hasPulang) {
            if ($isPastDate || ($isToday && $isAfterDismissal)) {
                return [
                    'key' => 'tidak_absen_pulang',
                    'label' => 'Tidak Absen Pulang (Bolos)',
                ];
            }
            return [
                'key' => 'di_sekolah',
                'label' => 'Masih di Sekolah',
            ];
        } elseif (!$hasMasuk && $hasPulang) {
            return [
                'key' => 'hanya_pulang',
                'label' => 'Hanya Scan Pulang',
            ];
        }

        return [
            'key' => 'belum_hadir',
            'label' => 'Tidak Hadir (Alpa)',
        ];
    }

    /**
     * Rekapitulasi Kehadiran Seluruh Siswa Harian / Per Tanggal (Satpam)
     * GET /api/satpam/rekap-harian
     */
    public function rekapHarian(Request $request)
    {
        $selectedDate = $request->query('date') ?? $request->query('tanggal') ?? Carbon::today('Asia/Jayapura')->format('Y-m-d');
        $targetDate = Carbon::parse($selectedDate, 'Asia/Jayapura')->startOfDay();
        $kelasId = $request->query('kelas_id');
        $search = $request->query('search');

        $siswaQuery = Siswa::with(['user', 'kelas']);

        if ($kelasId) {
            $siswaQuery->where('kelas_id', $kelasId);
        }

        if ($search) {
            $siswaQuery->where(function ($q) use ($search) {
                $q->where('nisn', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($qu) use ($search) {
                        $qu->where('name', 'like', "%{$search}%")
                            ->orWhere('nis', 'like', "%{$search}%");
                    });
            });
        }

        $allStudents = $siswaQuery->orderBy('kelas_id', 'asc')->get();

        // Ambil seluruh catatan presensi pada tanggal target
        $attendanceDate = Absensi::whereDate('waktu_scan', $targetDate)
            ->get()
            ->groupBy('siswa_id');

        $hadirLengkapCount = 0;
        $terlambatCount = 0;
        $tidakAbsenPulangCount = 0;
        $diSekolahCount = 0;
        $alpaCount = 0;

        $rekapList = $allStudents->map(function ($s) use ($attendanceDate, $targetDate, &$hadirLengkapCount, &$terlambatCount, &$tidakAbsenPulangCount, &$diSekolahCount, &$alpaCount) {
            $studentAttendance = $attendanceDate->get($s->id, collect());

            $masukRecord = $studentAttendance->first(function ($att) {
                return $att->tipe_presensi === 'gerbang_masuk' || $att->tipe_presensi === 'mapel';
            });

            $pulangRecord = $studentAttendance->first(function ($att) {
                return $att->tipe_presensi === 'gerbang_pulang';
            });

            $eval = $this->evaluateAttendanceStatus($masukRecord, $pulangRecord, $targetDate);

            if ($eval['key'] === 'sudah_pulang') $hadirLengkapCount++;
            elseif ($eval['key'] === 'terlambat_pulang') $terlambatCount++;
            elseif ($eval['key'] === 'tidak_absen_pulang') $tidakAbsenPulangCount++;
            elseif ($eval['key'] === 'di_sekolah') $diSekolahCount++;
            elseif ($eval['key'] === 'belum_hadir') $alpaCount++;

            return [
                'siswa_id' => $s->id,
                'name' => $s->user->name ?? 'Siswa',
                'nisn' => $s->nisn ?? $s->user->nis ?? '-',
                'nis' => $s->user->nis ?? '-',
                'kelas_id' => $s->kelas_id,
                'class_name' => $s->kelas->nama_kelas ?? '-',
                'foto' => $s->user->foto ? url('storage/' . $s->user->foto) : null,
                'status' => $eval['key'],
                'status_label' => $eval['label'],
                'jam_masuk' => $masukRecord ? Carbon::parse($masukRecord->waktu_scan)->format('H:i') . ' WIT' : null,
                'jam_pulang' => $pulangRecord ? Carbon::parse($pulangRecord->waktu_scan)->format('H:i') . ' WIT' : null,
                'metode_scan' => $masukRecord?->metode_scan ?? '-',
            ];
        });

        $classes = Kelas::orderBy('nama_kelas', 'asc')->get()->map(fn($k) => [
            'id' => $k->id,
            'name' => $k->nama_kelas,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Rekap harian berhasil dimuat.',
            'data' => [
                'selected_date' => $selectedDate,
                'formatted_date' => Carbon::parse($selectedDate)->locale('id')->isoFormat('dddd, D MMMM Y'),
                'total_students' => $rekapList->count(),
                'hadir_count' => $rekapList->whereIn('status', ['sudah_pulang', 'terlambat_pulang', 'di_sekolah'])->count(),
                'hadir_lengkap_count' => $hadirLengkapCount,
                'terlambat_count' => $terlambatCount,
                'tidak_absen_pulang_count' => $tidakAbsenPulangCount,
                'di_sekolah_count' => $diSekolahCount,
                'belum_hadir_count' => $alpaCount,
                'classes' => $classes,
                'students' => $rekapList->values(),
            ],
        ]);
    }

    /**
     * Ekspor Rekap Presensi Gerbang Satpam ke Excel (.CSV UTF-8) via API
     * GET /api/satpam/rekap/export-excel
     */
    public function exportRekapExcel(Request $request)
    {
        $selectedDate = $request->query('date') ?? $request->query('tanggal') ?? Carbon::today('Asia/Jayapura')->format('Y-m-d');
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
     * Ekspor Rekap Presensi Gerbang Satpam ke PDF / Printable View via API
     * GET /api/satpam/rekap/export-pdf
     */
    public function exportRekapPdf(Request $request)
    {
        $selectedDate = $request->query('date') ?? $request->query('tanggal') ?? Carbon::today('Asia/Jayapura')->format('Y-m-d');
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

        $html = view('satpam.print_rekap', compact(
            'rows', 'totalSiswa', 'hadirLengkapCount', 'terlambatCount', 'tidakAbsenPulangCount', 'diSekolahCount', 'alpaCount', 'formattedDate', 'officerName'
        ))->render();

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    /**
     * Update FCM Device Token Petugas Satpam
     * POST /api/satpam/device-token
     */
    public function updateDeviceToken(Request $request)
    {
        $request->validate(['token' => 'required|string']);
        $user = $request->user();
        $user->update(['fcm_token' => $request->token]);

        return response()->json([
            'success' => true,
            'message' => 'FCM Device Token Satpam berhasil diperbarui.',
        ]);
    }

    /**
     * Mengambil daftar jadwal jam operasional gerbang untuk 7 hari
     * GET /api/satpam/jam-operasional
     */
    public function getJamOperasional(Request $request)
    {
        JamOperasionalGerbang::ensureTableAndData();

        $schedules = JamOperasionalGerbang::orderBy('urutan', 'asc')->get();
        $todaySchedule = JamOperasionalGerbang::getScheduleForDate();

        return response()->json([
            'success' => true,
            'message' => 'Jadwal jam operasional pos gerbang berhasil dimuat.',
            'data' => [
                'schedules' => $schedules,
                'today_active' => $todaySchedule,
                'server_time' => Carbon::now('Asia/Jayapura')->locale('id')->isoFormat('dddd, D MMMM Y • HH:mm:ss') . ' WIT',
            ],
        ]);
    }

    /**
     * Menyimpan / Memperbarui Jadwal Jam Operasional Pos Gerbang
     * POST /api/satpam/jam-operasional
     */
    public function updateJamOperasional(Request $request)
    {
        JamOperasionalGerbang::ensureTableAndData();

        $user = $request->user();

        // Bisa update multiple hari (array) atau 1 hari
        if ($request->has('schedules') && is_array($request->schedules)) {
            foreach ($request->schedules as $item) {
                if (isset($item['hari'])) {
                    JamOperasionalGerbang::where('hari', $item['hari'])->update([
                        'jam_masuk_mulai' => $item['jam_masuk_mulai'] ?? '06:00',
                        'jam_masuk_batas' => $item['jam_masuk_batas'] ?? '07:30',
                        'jam_pulang_mulai' => $item['jam_pulang_mulai'] ?? '14:00',
                        'is_libur' => filter_var($item['is_libur'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'keterangan' => $item['keterangan'] ?? null,
                        'updated_by' => $user->id,
                    ]);
                }
            }
        } elseif ($request->has('hari')) {
            $request->validate([
                'hari' => 'required|string',
                'jam_masuk_batas' => 'required|string',
                'jam_pulang_mulai' => 'required|string',
            ]);

            JamOperasionalGerbang::where('hari', $request->hari)->update([
                'jam_masuk_mulai' => $request->jam_masuk_mulai ?? '06:00',
                'jam_masuk_batas' => $request->jam_masuk_batas,
                'jam_pulang_mulai' => $request->jam_pulang_mulai,
                'is_libur' => $request->boolean('is_libur', false),
                'keterangan' => $request->keterangan,
                'updated_by' => $user->id,
            ]);
        }

        $updatedSchedules = JamOperasionalGerbang::orderBy('urutan', 'asc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan jam operasional gerbang berhasil disimpan!',
            'data' => [
                'schedules' => $updatedSchedules,
                'today_active' => JamOperasionalGerbang::getScheduleForDate(),
            ],
        ]);
    }
}

// akhir batas suci yang kamu ubah

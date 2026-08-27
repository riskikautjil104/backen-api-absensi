<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Siswa;
use App\Models\Jadwal;
use App\Models\Absensi;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SiswaApiController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required_without:email|string',
            'email' => 'required_without:login|string',
            'password' => 'required|string',
        ]);

        $login = $request->input('login') ?? $request->input('email');
        
        // Find user by email or nis
        $user = User::where('email', $login)
            ->orWhere('nis', $login)
            ->first();

        // awal batas suci yang kamu ubah
        if (!$user || !Hash::check($request->password, $user->password)) {
            // Auto-Sync Bridge: Jika login lokal gagal, verifikasi langsung ke server utama SIMORO
            $simoroVerified = false;
            try {
                $simoroUrl = config('services.simoro.url', 'https://simoro.sma-n5-morotai.id/api') . '/siswa/login';
                $simoroResponse = \Illuminate\Support\Facades\Http::timeout(5)->post($simoroUrl, [
                    'login' => $login,
                    'email' => $login,
                    'password' => $request->password,
                ]);

                if ($simoroResponse->successful() && $simoroResponse->json('success') === true) {
                    $simoroData = $simoroResponse->json('data.user');
                    if ($simoroData) {
                        if ($user) {
                            // Update password lokal di server Absensi agar sinkron permanen dengan SIMORO
                            $user->password = Hash::make($request->password);
                            $user->role = 'siswa';
                            $user->save();
                            $simoroVerified = true;
                        } else {
                            // Auto-provision user jika belum ada di database absensi
                            $user = User::create([
                                'name' => $simoroData['name'] ?? 'Siswa',
                                'email' => $simoroData['email'] ?? $login,
                                'nis' => $simoroData['nis'] ?? null,
                                'password' => Hash::make($request->password),
                                'role' => 'siswa',
                            ]);
                            
                            $classId = $simoroData['class_id'] ?? null;
                            Siswa::firstOrCreate(
                                ['user_id' => $user->id],
                                ['kelas_id' => $classId]
                            );
                            $simoroVerified = true;
                        }
                    }
                }
            } catch (\Exception $e) {
                // Fallback jika SIMORO timeout / unreachable
            }

            if (!$simoroVerified) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email/NIS atau kata sandi yang Anda masukkan salah.'
                ], 401);
            }
        }
        // akhir batas suci yang kamu ubah

        if ($user->role !== 'siswa') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Akun ini bukan akun Siswa.'
            ], 403);
        }

        $siswa = $user->siswa;
        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Data detail siswa tidak ditemukan.'
            ], 404);
        }

        // Generate token
        $token = $user->createToken('siswa_mobile_token')->plainTextToken;

        $cardToken = $siswa->kartu->token ?? null;
        $encryptedCardToken = $cardToken ? \Illuminate\Support\Facades\Crypt::encryptString($cardToken) : null;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil. Selamat datang, ' . $user->name,
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'nis' => $user->nis,
                    'phone' => $siswa->nomor_hp,
                    'role' => 'student',
                    'class_id' => $siswa->kelas_id,
                    'class_name' => $siswa->kelas->nama_kelas ?? '-',
                    'angkatan' => $siswa->kelas->tahun_ajaran ?? '-',
                    'is_graduated' => false,
                    'card_token' => $cardToken,
                    'encrypted_card_token' => $encryptedCardToken,
                    'created_at' => $user->created_at,
                ]
            ]
        ]);
    }

    public function profile(Request $request)
    {
        $user = $request->user();
        $siswa = $user->siswa;

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Data detail siswa tidak ditemukan.'
            ], 404);
        }

        $cardToken = $siswa->kartu->token ?? null;
        $encryptedCardToken = $cardToken ? \Illuminate\Support\Facades\Crypt::encryptString($cardToken) : null;

        return response()->json([
            'success' => true,
            'message' => 'Data profil siswa berhasil diambil.',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'nis' => $user->nis,
                'phone' => $siswa->nomor_hp,
                'role' => 'student',
                'class_id' => $siswa->kelas_id,
                'class_name' => $siswa->kelas->nama_kelas ?? '-',
                'angkatan' => $siswa->kelas->tahun_ajaran ?? '-',
                'is_graduated' => false,
                'card_token' => $cardToken,
                'encrypted_card_token' => $encryptedCardToken
            ]
        ]);
    }

    public function dashboard(Request $request)
    {
        $user = $request->user();
        $siswa = $user->siswa;

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Data detail siswa tidak ditemukan.'
            ], 404);
        }

        // Attendance stats
        $totalHadir = Absensi::where('siswa_id', $siswa->id)->where('status', 'hadir')->count();
        $totalTerlambat = Absensi::where('siswa_id', $siswa->id)->where('status', 'terlambat')->count();
        $totalAlpha = Absensi::where('siswa_id', $siswa->id)->where('status', 'alpha')->count();

        // Today's schedule
        $now = Carbon::now();
        $hariInggris = $now->format('l');
        $hariIndo = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
        ];
        $hariSekarang = $hariIndo[$hariInggris] ?? 'Senin';

        $schedules = Jadwal::with(['mapel', 'guru'])
            ->where('kelas_id', $siswa->kelas_id)
            ->where('hari', $hariSekarang)
            ->get()
            ->map(function($sched) {
                return [
                    'id' => $sched->id,
                    'subject_name' => $sched->mapel->nama_mapel,
                    'teacher_name' => $sched->guru->name,
                    'time_start' => substr($sched->jam_mulai, 0, 5),
                    'time_end' => substr($sched->jam_selesai, 0, 5),
                ];
            });

        // Last 5 attendance records
        $riwayat = Absensi::with(['jadwal.mapel'])
            ->where('siswa_id', $siswa->id)
            ->orderBy('waktu_scan', 'desc')
            ->limit(5)
            ->get()
            ->map(function($abs) {
                return [
                    'id' => $abs->id,
                    'subject_name' => $abs->jadwal->mapel->nama_mapel ?? 'Umum/Scanner',
                    'status' => $abs->status,
                    'time' => Carbon::parse($abs->waktu_scan)->translatedFormat('d M Y, H:i') . ' WIT',
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Dashboard data loaded successfully.',
            'data' => [
                'siswa' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'nis' => $user->nis,
                    'class_name' => $siswa->kelas->nama_kelas ?? '-',
                ],
                'stats' => [
                    'total_hadir' => $totalHadir,
                    'total_terlambat' => $totalTerlambat,
                    'total_alpha' => $totalAlpha,
                ],
                'jadwal_hari_ini' => $schedules,
                'riwayat_absensi_terakhir' => $riwayat,
            ]
        ]);
    }

    public function jadwal(Request $request)
    {
        $user = $request->user();
        $siswa = $user->siswa;

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Data detail siswa tidak ditemukan.'
            ], 404);
        }

        // Get all schedules for the student's class
        $schedules = Jadwal::with(['mapel', 'guru'])
            ->where('kelas_id', $siswa->kelas_id)
            ->orderByRaw("FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')")
            ->orderBy('jam_mulai')
            ->get()
            ->map(function($sched) {
                return [
                    'id' => $sched->id,
                    'hari' => $sched->hari,
                    'subject_name' => $sched->mapel->nama_mapel,
                    'teacher_name' => $sched->guru->name,
                    'time_start' => substr($sched->jam_mulai, 0, 5),
                    'time_end' => substr($sched->jam_selesai, 0, 5),
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Data jadwal pelajaran berhasil diambil.',
            'data' => $schedules
        ]);
    }

    public function absensi(Request $request)
    {
        $user = $request->user();
        $siswa = $user->siswa;

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Data detail siswa tidak ditemukan.'
            ], 404);
        }

        // Get all attendance history for the student
        $history = Absensi::with(['jadwal.mapel'])
            ->where('siswa_id', $siswa->id)
            ->orderBy('waktu_scan', 'desc')
            ->get()
            ->map(function($abs) {
                return [
                    'id' => $abs->id,
                    'subject_name' => $abs->jadwal->mapel->nama_mapel ?? 'Umum/Scanner',
                    'status' => $abs->status,
                    'time' => Carbon::parse($abs->waktu_scan)->translatedFormat('d M Y, H:i') . ' WIT',
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Data riwayat absensi berhasil diambil.',
            'data' => $history
        ]);
    }

    public function scanGuruQr(Request $request)
    {
        $request->validate([
            'payload' => 'required|string',
        ]);

        $user = $request->user();
        $siswa = $user->siswa;

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Data detail siswa tidak ditemukan.'
            ], 404);
        }

        try {
            $decrypted = json_decode(\Illuminate\Support\Facades\Crypt::decryptString($request->payload), true);
            if (!$decrypted || !isset($decrypted['jadwal_id']) || !isset($decrypted['date'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'QR Code tidak valid atau rusak.'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'QR Code tidak valid atau kedaluwarsa.'
            ], 400);
        }

        $today = Carbon::today()->toDateString();
        if ($decrypted['date'] !== $today) {
            return response()->json([
                'success' => false,
                'message' => 'QR Code ini sudah kedaluwarsa (tidak berlaku hari ini).'
            ], 400);
        }

        $jadwal = Jadwal::find($decrypted['jadwal_id']);
        if (!$jadwal) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal pelajaran tidak ditemukan.'
            ], 404);
        }

        if ($siswa->kelas_id !== $jadwal->kelas_id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak terdaftar di kelas ' . $jadwal->kelas->nama_kelas . ' untuk mata pelajaran ini.'
            ], 400);
        }

        $alreadyChecked = Absensi::where('siswa_id', $siswa->id)
            ->where('jadwal_id', $jadwal->id)
            ->whereDate('waktu_scan', Carbon::today())
            ->exists();

        if ($alreadyChecked) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan absensi untuk mata pelajaran ini hari ini.'
            ], 400);
        }

        $now = Carbon::now();
        Absensi::create([
            'siswa_id' => $siswa->id,
            'jadwal_id' => $jadwal->id,
            'status' => 'hadir',
            'waktu_scan' => $now,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absensi berhasil dicatat!',
            'data' => [
                'subject_name' => $jadwal->mapel->nama_mapel,
                'class_name' => $jadwal->kelas->nama_kelas,
                'time' => $now->format('H:i:s') . ' WIT',
            ]
        ]);
    }

    // awal batas suci yang kamu ubah
    /**
     * Simpan / Update FCM Device Token Siswa
     * POST /api/siswa/device-token
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
            'message' => 'FCM Device Token Siswa berhasil disimpan.',
        ], 200);
    }
    // akhir batas suci yang kamu ubah
}

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

        $login = $request->input('email');
        $password = $request->input('password');
        $authenticated = false;
        $user = null;

        // 1. Try SIMORO API SSO general login
        try {
            $apiUrl = config('services.simoro.url', 'https://simoro.sma-n5-morotai.id/api');
            $response = \Illuminate\Support\Facades\Http::timeout(5)->post($apiUrl . '/login', [
                'email' => $login,
                'password' => $password,
            ]);

            if ($response->successful() && $response->json('user')) {
                $apiUser = $response->json('user');
                $role = $apiUser['role'] ?? 'teacher';

                if ($role === 'teacher' || $role === 'guru') {
                    // Sync user locally
                    $nip = $apiUser['nip'] ?? null;
                    $userQuery = User::where('email', $apiUser['email']);
                    if ($nip) {
                        $userQuery = $userQuery->orWhere('nip', $nip);
                    }
                    $user = $userQuery->first();

                    if (!$user) {
                        $user = User::create([
                            'name' => $apiUser['name'],
                            'email' => $apiUser['email'],
                            'nip' => $nip,
                            'role' => 'guru',
                            'password' => Hash::make($password),
                        ]);
                    } else {
                        $user->update([
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
            \Illuminate\Support\Facades\Log::warning('SIMORO Guru SSO login failed: ' . $e->getMessage());
        }

        // 2. Fallback to local DB check
        if (!$authenticated) {
            $user = User::where('email', $login)->first();
            if ($user && Hash::check($password, $user->password) && $user->role === 'guru') {
                $authenticated = true;
            }
        }

        if (!$authenticated || !$user) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau kata sandi Guru salah.'
            ], 401);
        }

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
    // akhir batas suci yang kamu ubah
}

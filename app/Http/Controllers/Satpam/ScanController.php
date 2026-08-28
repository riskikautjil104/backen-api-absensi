<?php

// awal batas suci yang kamu ubah

namespace App\Http\Controllers\Satpam;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\KartuSiswa;
use App\Models\Siswa;
use App\Models\Absensi;
use App\Models\JamOperasionalGerbang;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;

class ScanController extends Controller
{
    public function scan(Request $request)
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'token' => 'required|string',
                'tipe_scan' => 'nullable|in:masuk,pulang',
            ]);

            $rawToken = trim($request->token);
            $tipeScan = $request->tipe_scan ?? 'masuk';

            $token = $rawToken;
            try {
                $token = Crypt::decryptString($rawToken);
            } catch (\Throwable $e) {
                // Plain token
            }

            // Cari kartu siswa
            $kartu = KartuSiswa::where('token', $token)->first();
            $siswa = null;

            if ($kartu) {
                if ($kartu->status !== 'aktif') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Kartu Siswa ini berstatus Non-Aktif.',
                    ], 403);
                }
                $siswa = $kartu->siswa;
            } else {
                // Fallback cari siswa berdasarkan nisn atau nis
                $siswa = Siswa::where('nisn', $token)
                    ->orWhereHas('user', function ($q) use ($token) {
                        $q->where('nis', $token);
                    })
                    ->first();
            }

            if (!$siswa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kartu atau identitas siswa tidak terdaftar di sistem.',
                ], 404);
            }

            $now = Carbon::now('Asia/Jayapura');
            $today = Carbon::today('Asia/Jayapura');
            $tipePresensiDb = $tipeScan === 'pulang' ? 'gerbang_pulang' : 'gerbang_masuk';

            // Cek apakah sudah pernah scan di sesi ini hari ini
            $existing = Absensi::where('siswa_id', $siswa->id)
                ->whereDate('waktu_scan', $today)
                ->where('tipe_presensi', $tipePresensiDb)
                ->first();

            if ($existing) {
                $waktuScanLalu = Carbon::parse($existing->waktu_scan)->format('H:i');
                return response()->json([
                    'success' => false,
                    'message' => "Siswa {$siswa->user->name} sudah melakukan presensi {$tipeScan} pada pukul {$waktuScanLalu} WIT.",
                    'student' => [
                        'name' => $siswa->user->name,
                        'nis' => $siswa->user->nis ?? $siswa->nisn ?? '-',
                        'kelas' => $siswa->kelas->nama_kelas ?? '-',
                        'status' => $existing->status,
                        'tipe_scan' => $tipeScan,
                        'time' => Carbon::parse($existing->waktu_scan)->format('H:i:s') . ' WIT',
                        'foto' => $siswa->user->foto ? url('storage/' . $siswa->user->foto) : null,
                    ],
                ], 409);
            }

            // Tentukan status kehadiran menggunakan Jadwal Operasional Gerbang Hari Ini
            $status = 'hadir';
            if ($tipeScan === 'masuk') {
                $schedule = JamOperasionalGerbang::getScheduleForDate($now);
                $batasMasukStr = $schedule ? $schedule->jam_masuk_batas : '07:30';
                [$bHour, $bMin] = explode(':', $batasMasukStr);
                $jamLimit = Carbon::today('Asia/Jayapura')->setHour((int)$bHour)->setMinute((int)$bMin)->setSecond(0);
                $status = $now->isAfter($jamLimit) ? 'terlambat' : 'hadir';
            }

            $absensi = Absensi::create([
                'siswa_id' => $siswa->id,
                'jadwal_id' => null,
                'tipe_presensi' => $tipePresensiDb,
                'petugas_id' => auth()->id(),
                'waktu_scan' => $now,
                'status' => $status,
                'keterangan' => $tipeScan === 'masuk' ? 'Presensi Masuk Gerbang (Web Scanner)' : 'Presensi Kepulangan Siswa (Web Scanner)',
                'metode_scan' => 'kartu_digital',
            ]);

            return response()->json([
                'success' => true,
                'message' => $tipeScan === 'masuk' ? 'Presensi Masuk Gerbang Berhasil!' : 'Presensi Pulang Sekolah Berhasil!',
                'student' => [
                    'name' => $siswa->user->name,
                    'nis' => $siswa->user->nis ?? $siswa->nisn ?? '-',
                    'kelas' => $siswa->kelas->nama_kelas ?? '-',
                    'status' => $status,
                    'tipe_scan' => $tipeScan,
                    'time' => $now->format('H:i:s') . ' WIT',
                    'foto' => $siswa->user->foto ? url('storage/' . $siswa->user->foto) : null,
                ],
            ]);
        }

        $nowHour = (int) Carbon::now('Asia/Jayapura')->format('H');
        $defaultMode = $nowHour >= 12 ? 'pulang' : 'masuk';
        $todaySchedule = JamOperasionalGerbang::getScheduleForDate();

        return view('satpam.scan', compact('defaultMode', 'todaySchedule'));
    }
}

// akhir batas suci yang kamu ubah

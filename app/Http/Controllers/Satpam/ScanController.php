<?php

// awal batas suci yang kamu ubah

namespace App\Http\Controllers\Satpam;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\KartuSiswa;
use App\Models\Siswa;
use App\Models\Absensi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;

class ScanController extends Controller
{
    public function scan(Request $request)
    {
        if ($request->isMethod('post')) {
            $token = $request->token;
            try {
                $token = Crypt::decryptString($token);
            } catch (\Exception $e) {
                // Plain token
            }

            $kartu = KartuSiswa::where('token', $token)->first();
            if (!$kartu) {
                return response()->json(['success' => false, 'message' => 'Kartu Siswa tidak terdaftar']);
            }

            if ($kartu->status !== 'aktif') {
                return response()->json(['success' => false, 'message' => 'Kartu Siswa ini berstatus Non-Aktif']);
            }

            $siswa = $kartu->siswa;
            if (!$siswa) {
                return response()->json(['success' => false, 'message' => 'Data Siswa tidak ditemukan']);
            }

            $now = Carbon::now('Asia/Jayapura');
            $today = Carbon::today('Asia/Jayapura');

            $existing = Absensi::where('siswa_id', $siswa->id)
                ->whereDate('waktu_scan', $today)
                ->where('tipe_presensi', 'gerbang_masuk')
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Siswa ' . $siswa->user->name . ' sudah presensi masuk pada ' . Carbon::parse($existing->waktu_scan)->format('H:i') . ' WIT',
                ]);
            }

            $jamLimit = Carbon::today('Asia/Jayapura')->setHour(7)->setMinute(30)->setSecond(0);
            $status = $now->isAfter($jamLimit) ? 'terlambat' : 'hadir';

            Absensi::create([
                'siswa_id' => $siswa->id,
                'jadwal_id' => null,
                'tipe_presensi' => 'gerbang_masuk',
                'petugas_id' => auth()->id(),
                'waktu_scan' => $now,
                'status' => $status,
                'keterangan' => 'Presensi Masuk Gerbang (Web Scanner)',
                'metode_scan' => 'kartu_digital',
            ]);

            return response()->json([
                'success' => true,
                'student' => [
                    'name' => $siswa->user->name,
                    'nis' => $siswa->user->nis ?? $siswa->nisn ?? '-',
                    'kelas' => $siswa->kelas->nama_kelas ?? '-',
                    'status' => $status,
                    'time' => $now->format('H:i:s') . ' WIT',
                ],
            ]);
        }

        return view('satpam.scan');
    }
}

// akhir batas suci yang kamu ubah

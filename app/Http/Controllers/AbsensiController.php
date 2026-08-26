<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KartuSiswa;
use App\Models\Absensi;
use App\Models\Jadwal;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    public function scan(Request $request)
    {
        // Blokir siswa agar tidak bisa absen sendiri dari rumah
        if (auth()->check() && auth()->user()->isSiswa()) {
            return redirect()->route('siswa.dashboard')->with('error', 'Siswa tidak diperbolehkan melakukan pemindaian mandiri.');
        }

        if ($request->isMethod('post')) {
            $token = $request->token;
            
            try {
                $token = \Illuminate\Support\Facades\Crypt::decryptString($token);
            } catch (\Exception $e) {
                // Fallback to plain token (manual entry)
            }
            
            // 1. Cari Kartu
            $kartu = KartuSiswa::where('token', $token)->first();

            if (!$kartu) {
                return response()->json(['success' => false, 'message' => 'Kartu tidak terdaftar']);
            }

            if ($kartu->status !== 'aktif') {
                return response()->json(['success' => false, 'message' => 'Kartu Anda dinonaktifkan']);
            }

            $siswa = $kartu->siswa;
            if (!$siswa) {
                return response()->json(['success' => false, 'message' => 'Data siswa tidak ditemukan']);
            }

            // 2. Cek Jadwal (Fitur Cerdas: Absen per Mapel)
            $now = Carbon::now();
            $hariInggris = $now->format('l');
            $hariIndo = [
                'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
                'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
            ];
            
            $hariSekarang = $hariIndo[$hariInggris] ?? 'Senin';
            $jamSekarang = $now->format('H:i:s');

            // Cari jadwal yang sedang berlangsung
            $jadwal = Jadwal::where('kelas_id', $siswa->kelas_id)
                ->where('hari', $hariSekarang)
                ->where('jam_mulai', '<=', $jamSekarang)
                ->where('jam_selesai', '>=', $jamSekarang)
                ->first();

            if (!$jadwal) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Tidak ada jadwal pelajaran saat ini (' . $hariSekarang . ' ' . $now->format('H:i') . ')'
                ]);
            }

            // 3. Catat Absensi
            $sudahAbsen = Absensi::where('siswa_id', $siswa->id)
                ->where('jadwal_id', $jadwal->id)
                ->whereDate('waktu_scan', Carbon::today())
                ->exists();

            if ($sudahAbsen) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Sudah absen di mapel ' . $jadwal->mapel->nama_mapel
                ]);
            }

            Absensi::create([
                'siswa_id' => $siswa->id,
                'jadwal_id' => $jadwal->id,
                'status' => 'hadir',
                'waktu_scan' => $now,
            ]);

            return response()->json([
                'success' => true,
                'student' => [
                    'name' => $siswa->user->name,
                    'kelas' => $siswa->kelas->nama_kelas ?? '-',
                    'mapel' => $jadwal->mapel->nama_mapel,
                    'time' => $now->format('H:i:s'),
                ]
            ]);
        }

        // Jika request GET, tampilkan halaman scan
        return view('absensi.scan');
    }

    public function scanGuru(Request $request, Jadwal $jadwal)
    {
        // Pastikan yang mengakses adalah guru pemilik jadwal ini
        if (auth()->id() !== $jadwal->guru_id) {
            abort(403, 'Anda tidak berhak mengakses absensi mata pelajaran ini.');
        }

        // Jika request POST, maka proses absensi
        if ($request->isMethod('post')) {
            $token = $request->token;
            
            try {
                $token = \Illuminate\Support\Facades\Crypt::decryptString($token);
            } catch (\Exception $e) {
                // Fallback to plain token (manual entry)
            }
            
            // 1. Cari Kartu
            $kartu = KartuSiswa::where('token', $token)->first();

            if (!$kartu) {
                return response()->json(['success' => false, 'message' => 'Kartu tidak terdaftar']);
            }

            if ($kartu->status !== 'aktif') {
                return response()->json(['success' => false, 'message' => 'Kartu Anda dinonaktifkan']);
            }

            $siswa = $kartu->siswa;
            if (!$siswa) {
                return response()->json(['success' => false, 'message' => 'Data siswa tidak ditemukan']);
            }

            // 2. Cek apakah Siswa ini di kelas yang sesuai dengan Jadwal
            if ($siswa->kelas_id !== $jadwal->kelas_id) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Siswa ' . $siswa->user->name . ' tidak terdaftar di kelas ' . $jadwal->kelas->nama_kelas
                ]);
            }

            // 3. Catat Absensi untuk Jadwal ini hari ini
            $now = Carbon::now();
            $sudahAbsen = Absensi::where('siswa_id', $siswa->id)
                ->where('jadwal_id', $jadwal->id)
                ->whereDate('waktu_scan', Carbon::today())
                ->exists();

            if ($sudahAbsen) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Sudah absen di mapel ' . $jadwal->mapel->nama_mapel
                ]);
            }

            Absensi::create([
                'siswa_id' => $siswa->id,
                'jadwal_id' => $jadwal->id,
                'status' => 'hadir',
                'waktu_scan' => $now,
            ]);

            return response()->json([
                'success' => true,
                'student' => [
                    'name' => $siswa->user->name,
                    'kelas' => $siswa->kelas->nama_kelas ?? '-',
                    'mapel' => $jadwal->mapel->nama_mapel,
                    'time' => $now->format('H:i:s'),
                ]
            ]);
        }

        // Jika request GET, tampilkan halaman scan khusus Guru
        return view('absensi.scan_guru', compact('jadwal'));
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\KartuSiswa;
use App\Models\MataPelajaran;
use App\Models\Jadwal;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Admin
        User::create([
            'name' => 'Admin SMA 5',
            'email' => 'admin@sma5morotai.sch.id',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Guru
        $guru = User::create([
            'name' => 'Bpk. Ahmad Guru',
            'email' => 'ahmad@sma5morotai.sch.id',
            'password' => Hash::make('password'),
            'role' => 'guru',
            'nip' => '1234567890',
        ]);

        // Kelas
        $kelas = Kelas::create([
            'nama_kelas' => 'XII-IPA-1',
            'tahun_ajaran' => '2025/2026',
        ]);

        // Mapel
        $mapel = MataPelajaran::create([
            'nama_mapel' => 'Matematika',
            'kode_mapel' => 'MTK01',
        ]);

        // Jadwal (SET UNTUK HARI INI AGAR BISA TEST)
        $now = Carbon::now();
        $hariInggris = $now->format('l');
        $hariIndo = [
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
            'Sunday' => 'Minggu'
        ];
        $hariSekarang = $hariIndo[$hariInggris] ?? 'Jumat';

        Jadwal::create([
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapel->id,
            'guru_id' => $guru->id,
            'hari' => $hariSekarang,
            'jam_mulai' => '00:00:00', // Dibuat seharian agar bisa test kapan saja
            'jam_selesai' => '23:59:59',
        ]);

        // Siswa
        $siswaUser = User::create([
            'name' => 'Siswa Contoh',
            'email' => 'siswa@sma5morotai.sch.id',
            'password' => Hash::make('password'),
            'role' => 'siswa',
            'nis' => '9988776655',
        ]);

        $siswa = Siswa::create([
            'user_id' => $siswaUser->id,
            'kelas_id' => $kelas->id,
            'tempat_lahir' => 'Morotai',
            'tanggal_lahir' => '2008-01-01',
        ]);

        KartuSiswa::create([
            'siswa_id' => $siswa->id,
            'token' => 'TOKEN123',
            'status' => 'aktif',
        ]);
    }
}

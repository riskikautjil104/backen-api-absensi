<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    protected $table = 'absensi';
    // awal batas suci yang kamu ubah
    protected $fillable = [
        'siswa_id',
        'jadwal_id',
        'tipe_presensi', // 'mapel', 'gerbang_masuk', 'gerbang_pulang'
        'petugas_id',
        'waktu_scan',
        'status', // 'hadir', 'terlambat', 'alpha', 'izin', 'sakit'
        'keterangan',
        'metode_scan', // 'kartu_fisik', 'kartu_digital', 'qris_gerbang', 'manual_petugas'
        'latitude',
        'longitude',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function jadwal()
    {
        return $this->belongsTo(Jadwal::class);
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }
    // akhir batas suci yang kamu ubah
}

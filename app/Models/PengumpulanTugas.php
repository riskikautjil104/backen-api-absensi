<?php

// awal batas suci yang kamu ubah

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengumpulanTugas extends Model
{
    protected $table = 'pengumpulan_tugas';

    protected $fillable = [
        'tugas_id',
        'siswa_id',
        'status',
        'tipe_pengumpulan',
        'waktu_kumpul',
        'file_tugas',
        'catatan_siswa',
        'nilai',
        'catatan_guru',
        'waktu_dinilai',
    ];

    protected $casts = [
        'waktu_kumpul' => 'datetime',
        'waktu_dinilai' => 'datetime',
        'nilai' => 'float',
    ];

    public function tugas()
    {
        return $this->belongsTo(Tugas::class, 'tugas_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }
}

// akhir batas suci yang kamu ubah

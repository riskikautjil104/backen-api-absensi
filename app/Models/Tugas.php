<?php

// awal batas suci yang kamu ubah

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tugas extends Model
{
    protected $table = 'tugas';

    protected $fillable = [
        'guru_id',
        'kelas_id',
        'mapel_id',
        'judul',
        'deskripsi',
        'tipe_pengumpulan',
        'deadline',
        'file_lampiran',
        'poin_maksimal',
        'status',
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'poin_maksimal' => 'integer',
    ];

    public function guru()
    {
        return $this->belongsTo(User::class, 'guru_id');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function mapel()
    {
        return $this->belongsTo(MataPelajaran::class, 'mapel_id');
    }

    public function pengumpulan()
    {
        return $this->hasMany(PengumpulanTugas::class, 'tugas_id');
    }
}

// akhir batas suci yang kamu ubah

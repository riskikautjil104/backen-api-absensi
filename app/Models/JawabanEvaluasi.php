<?php

// awal batas suci yang kamu ubah

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JawabanEvaluasi extends Model
{
    use HasFactory;

    protected $table = 'jawaban_evaluasi';

    protected $fillable = [
        'evaluasi_id',
        'siswa_id',
        'total_nilai',
        'catatan_guru',
        'status', // dikerjakan, dinilai
        'waktu_submit',
        'waktu_dinilai',
    ];

    protected $casts = [
        'waktu_submit' => 'datetime',
        'waktu_dinilai' => 'datetime',
    ];

    public function evaluasi()
    {
        return $this->belongsTo(EvaluasiBahanAjar::class, 'evaluasi_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function details()
    {
        return $this->hasMany(DetailJawabanEvaluasi::class, 'jawaban_evaluasi_id');
    }
}

// akhir batas suci yang kamu ubah

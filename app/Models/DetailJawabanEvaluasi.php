<?php

// awal batas suci yang kamu ubah

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailJawabanEvaluasi extends Model
{
    use HasFactory;

    protected $table = 'detail_jawaban_evaluasi';

    protected $fillable = [
        'jawaban_evaluasi_id',
        'soal_evaluasi_id',
        'jawaban_siswa',
        'nilai',
    ];

    public function jawabanEvaluasi()
    {
        return $this->belongsTo(JawabanEvaluasi::class, 'jawaban_evaluasi_id');
    }

    public function soal()
    {
        return $this->belongsTo(SoalEvaluasi::class, 'soal_evaluasi_id');
    }
}

// akhir batas suci yang kamu ubah

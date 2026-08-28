<?php

// awal batas suci yang kamu ubah

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoalEvaluasi extends Model
{
    use HasFactory;

    protected $table = 'soal_evaluasi';

    protected $fillable = [
        'evaluasi_id',
        'nomor_urut',
        'pertanyaan',
        'tipe_soal', // esai, pilihan_ganda
        'opsi_a',
        'opsi_b',
        'opsi_c',
        'opsi_d',
        'opsi_e',
        'kunci_jawaban',
        'poin',
    ];

    public function evaluasi()
    {
        return $this->belongsTo(EvaluasiBahanAjar::class, 'evaluasi_id');
    }

    public function detailJawaban()
    {
        return $this->hasMany(DetailJawabanEvaluasi::class, 'soal_evaluasi_id');
    }
}

// akhir batas suci yang kamu ubah

<?php

// awal batas suci yang kamu ubah

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluasiBahanAjar extends Model
{
    use HasFactory;

    protected $table = 'evaluasi_bahan_ajar';

    protected $fillable = [
        'bahan_ajar_id',
        'judul_evaluasi',
        'instruksi',
        'poin_maksimal',
    ];

    public function bahanAjar()
    {
        return $this->belongsTo(BahanAjar::class, 'bahan_ajar_id');
    }

    public function soal()
    {
        return $this->hasMany(SoalEvaluasi::class, 'evaluasi_id')->orderBy('nomor_urut', 'asc');
    }

    public function jawaban()
    {
        return $this->hasMany(JawabanEvaluasi::class, 'evaluasi_id');
    }
}

// akhir batas suci yang kamu ubah

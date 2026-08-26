<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KartuSiswa extends Model
{
    protected $table = 'kartu_siswa';
    protected $fillable = ['siswa_id', 'token', 'status'];

    public function siswa() { return $this->belongsTo(Siswa::class); }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    protected $table = 'siswa';
    protected $fillable = ['user_id', 'kelas_id', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir', 'nomor_hp', 'wa_orang_tua', 'alamat'];

    public function getRouteKey()
    {
        return \Illuminate\Support\Facades\Crypt::encryptString($this->getKey());
    }

    public function resolveRouteBinding($value, $field = null)
    {
        try {
            return $this->where($field ?? $this->getRouteKeyName(), \Illuminate\Support\Facades\Crypt::decryptString($value))->firstOrFail();
        } catch (\Exception $e) {
            abort(404);
        }
    }

    public function user() { return $this->belongsTo(User::class); }
    public function kelas() { return $this->belongsTo(Kelas::class); }
    public function kartu() { return $this->hasOne(KartuSiswa::class); }
}

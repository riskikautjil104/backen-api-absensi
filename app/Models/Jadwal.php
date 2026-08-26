<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    protected $table = 'jadwal';
    protected $fillable = ['kelas_id', 'mapel_id', 'guru_id', 'hari', 'jam_mulai', 'jam_selesai'];

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

    public function kelas() { return $this->belongsTo(Kelas::class); }
    public function mapel() { return $this->belongsTo(MataPelajaran::class, 'mapel_id'); }
    public function guru() { return $this->belongsTo(User::class, 'guru_id'); }
}

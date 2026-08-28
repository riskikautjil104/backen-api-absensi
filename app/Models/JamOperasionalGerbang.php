<?php

// awal batas suci yang kamu ubah

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Carbon\Carbon;

class JamOperasionalGerbang extends Model
{
    use HasFactory;

    protected $table = 'jam_operasional_gerbang';

    protected $fillable = [
        'hari',
        'nama_hari',
        'urutan',
        'jam_masuk_mulai',
        'jam_masuk_batas',
        'jam_pulang_mulai',
        'is_libur',
        'keterangan',
        'updated_by',
    ];

    protected $casts = [
        'is_libur' => 'boolean',
        'urutan' => 'integer',
    ];

    /**
     * Pastikan tabel dan data default tersedia
     */
    public static function ensureTableAndData()
    {
        if (!Schema::hasTable('jam_operasional_gerbang')) {
            Schema::create('jam_operasional_gerbang', function (Blueprint $table) {
                $table->id();
                $table->string('hari', 20)->unique();
                $table->string('nama_hari', 30);
                $table->integer('urutan')->default(1);
                $table->string('jam_masuk_mulai', 10)->default('06:00');
                $table->string('jam_masuk_batas', 10)->default('07:30');
                $table->string('jam_pulang_mulai', 10)->default('14:00');
                $table->boolean('is_libur')->default(false);
                $table->string('keterangan', 255)->nullable();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
            self::seedDefaults();
        } elseif (self::count() === 0) {
            self::seedDefaults();
        }
    }

    /**
     * Ambil jadwal aktif untuk tanggal tertentu
     */
    public static function getScheduleForDate(Carbon $date = null)
    {
        self::ensureTableAndData();

        $date = $date ?? Carbon::now('Asia/Jayapura');
        $dayName = strtolower($date->locale('id')->dayName);
        
        $map = [
            'senin' => 'senin',
            'selasa' => 'selasa',
            'rabu' => 'rabu',
            'kamis' => 'kamis',
            'jumat' => 'jumat',
            'sabtu' => 'sabtu',
            'minggu' => 'minggu',
            'monday' => 'senin',
            'tuesday' => 'selasa',
            'wednesday' => 'rabu',
            'thursday' => 'kamis',
            'friday' => 'jumat',
            'saturday' => 'sabtu',
            'sunday' => 'minggu',
        ];

        $key = $map[$dayName] ?? 'senin';
        $schedule = self::where('hari', $key)->first();

        if (!$schedule) {
            self::seedDefaults();
            $schedule = self::where('hari', $key)->first();
        }

        return $schedule;
    }

    /**
     * Seed jadwal default 7 hari
     */
    public static function seedDefaults()
    {
        $defaults = [
            ['hari' => 'senin', 'nama_hari' => 'Senin', 'urutan' => 1, 'jam_masuk_mulai' => '06:00', 'jam_masuk_batas' => '07:15', 'jam_pulang_mulai' => '14:30', 'is_libur' => false, 'keterangan' => 'Upacara Bendera & KBM'],
            ['hari' => 'selasa', 'nama_hari' => 'Selasa', 'urutan' => 2, 'jam_masuk_mulai' => '06:00', 'jam_masuk_batas' => '07:30', 'jam_pulang_mulai' => '14:00', 'is_libur' => false, 'keterangan' => 'KBM Reguler'],
            ['hari' => 'rabu', 'nama_hari' => 'Rabu', 'urutan' => 3, 'jam_masuk_mulai' => '06:00', 'jam_masuk_batas' => '07:30', 'jam_pulang_mulai' => '14:00', 'is_libur' => false, 'keterangan' => 'KBM Reguler'],
            ['hari' => 'kamis', 'nama_hari' => 'Kamis', 'urutan' => 4, 'jam_masuk_mulai' => '06:00', 'jam_masuk_batas' => '07:30', 'jam_pulang_mulai' => '14:00', 'is_libur' => false, 'keterangan' => 'KBM Reguler & Pramuka'],
            ['hari' => 'jumat', 'nama_hari' => 'Jumat', 'urutan' => 5, 'jam_masuk_mulai' => '06:00', 'jam_masuk_batas' => '07:15', 'jam_pulang_mulai' => '11:30', 'is_libur' => false, 'keterangan' => 'Senam / Imtaq & Shalat Jumat'],
            ['hari' => 'sabtu', 'nama_hari' => 'Sabtu', 'urutan' => 6, 'jam_masuk_mulai' => '06:30', 'jam_masuk_batas' => '07:30', 'jam_pulang_mulai' => '12:30', 'is_libur' => false, 'keterangan' => 'Ekstrakurikuler'],
            ['hari' => 'minggu', 'nama_hari' => 'Minggu', 'urutan' => 7, 'jam_masuk_mulai' => '07:00', 'jam_masuk_batas' => '08:00', 'jam_pulang_mulai' => '12:00', 'is_libur' => true, 'keterangan' => 'Libur Akhir Pekan'],
        ];

        foreach ($defaults as $d) {
            self::updateOrCreate(['hari' => $d['hari']], $d);
        }
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

// akhir batas suci yang kamu ubah

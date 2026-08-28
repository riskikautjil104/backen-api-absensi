<?php

// awal batas suci yang kamu ubah

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

    /**
     * Broadcast notifikasi ke seluruh siswa dan guru saat jadwal gerbang diperbarui
     */
    public static function broadcastUpdateNotification($user = null)
    {
        try {
            $fcmService = app(\App\Services\FcmService::class);
            $schedules = self::orderBy('urutan', 'asc')->get();

            $summaryLines = [];
            foreach ($schedules as $s) {
                if ($s->is_libur) {
                    $summaryLines[] = "• {$s->nama_hari}: Libur";
                } else {
                    $ket = $s->keterangan ? " ({$s->keterangan})" : "";
                    $summaryLines[] = "• {$s->nama_hari}: Batas Masuk {$s->jam_masuk_batas} WIT{$ket}, Pulang {$s->jam_pulang_mulai} WIT";
                }
            }

            $seninJadwal = $schedules->firstWhere('hari', 'senin');
            $jumatJadwal = $schedules->firstWhere('hari', 'jumat');
            $seninStr = $seninJadwal ? "Senin s/d {$seninJadwal->jam_masuk_batas}" : "";
            $jumatStr = $jumatJadwal ? "Jumat s/d {$jumatJadwal->jam_masuk_batas}" : "";

            $notifTitle = "⏰ Informasi Jam Masuk & Pulang Sekolah";
            $notifBody = "Jadwal batas toleransi tepat waktu diperbarui ({$seninStr}, {$jumatStr} WIT). Harap hadir sebelum jam batas gerbang!";

            $notifData = [
                'type' => 'jam_operasional_update',
                'title' => $notifTitle,
                'body' => $notifBody,
                'schedule_summary' => implode("\n", $summaryLines),
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ];

            // 1. Kirim langsung ke token perangkat siswa & guru
            $studentTokens = User::where('role', 'siswa')->whereNotNull('fcm_token')->pluck('fcm_token')->filter()->toArray();
            $teacherTokens = User::where('role', 'guru')->whereNotNull('fcm_token')->pluck('fcm_token')->filter()->toArray();
            $allTokens = array_values(array_unique(array_merge($studentTokens, $teacherTokens)));

            if (!empty($allTokens)) {
                $fcmService->sendToMultiple($allTokens, $notifTitle, $notifBody, $notifData);
            }

            // 2. Broadcast ke FCM topics
            $fcmService->sendToTopic('siswa', $notifTitle, $notifBody, $notifData);
            $fcmService->sendToTopic('guru', $notifTitle, $notifBody, $notifData);
            $fcmService->sendToTopic('pengumuman_sekolah', $notifTitle, $notifBody, $notifData);

            Log::info('[FCM Absensi] Broadcast jam operasional terkirim ke ' . count($allTokens) . ' device.');
        } catch (\Throwable $e) {
            Log::warning('[FCM Absensi] Gagal broadcast jam operasional: ' . $e->getMessage());
        }
    }
}

// akhir batas suci yang kamu ubah

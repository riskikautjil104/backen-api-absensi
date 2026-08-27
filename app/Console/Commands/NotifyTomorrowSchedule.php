<?php

// awal batas suci yang kamu ubah

namespace App\Console\Commands;

use App\Models\Jadwal;
use App\Models\Siswa;
use App\Models\User;
use App\Services\FcmService;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Artisan Command: NotifyTomorrowSchedule
 * 
 * Mengirimkan push notification FCM untuk:
 * 1. Siswa: Jadwal Pelajaran (Mapel) Besok beserta jamnya.
 * 2. Guru: Jadwal Mengajar Besok (Kelas, Mapel, dan Jam Mengajar WIT).
 * 
 * Dijalankan dengan zona waktu WIT (Asia/Jayapura, UTC+9).
 * 
 * Cara jalankan manual:
 *   php artisan schedule:notify-tomorrow
 *   php artisan schedule:notify-tomorrow --dry-run
 */
class NotifyTomorrowSchedule extends Command
{
    protected $signature   = 'schedule:notify-tomorrow {--dry-run : Jalankan tanpa mengirim notifikasi (preview saja)}';
    protected $description = 'Kirim notifikasi FCM jadwal pelajaran besok ke siswa & guru (Waktu WIT)';

    public function __construct(protected FcmService $fcm)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        // Gunakan Timezone WIT (Waktu Indonesia Timur - UTC+9)
        $nowWit      = Carbon::now('Asia/Jayapura');
        $tomorrowWit = $nowWit->copy()->addDay();

        $hariMap = [
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu',
            'Sunday'    => 'Minggu',
        ];

        $hariBesokIndo = $hariMap[$tomorrowWit->format('l')] ?? 'Senin';
        $tanggalBesok  = $tomorrowWit->locale('id')->isoFormat('dddd, D MMMM Y');

        $this->info("⏰ Waktu Sekarang (WIT): {$nowWit->format('Y-m-d H:i:s')} WIT");
        $this->info("🔍 Mencari jadwal untuk besok: <fg=cyan>{$tanggalBesok}</> (Hari {$hariBesokIndo})");
        $this->newLine();

        // 1. Ambil seluruh jadwal hari besok
        $jadwalBesok = Jadwal::with(['kelas', 'mapel', 'guru'])
            ->where('hari', $hariBesokIndo)
            ->orderBy('jam_mulai', 'asc')
            ->get();

        if ($jadwalBesok->isEmpty()) {
            $this->info("✅ Tidak ada jadwal pelajaran/mengajar pada hari {$hariBesokIndo}.");
            return Command::SUCCESS;
        }

        $this->info("📋 Ditemukan total {$jadwalBesok->count()} sesi jadwal pada hari {$hariBesokIndo}.");
        $this->newLine();

        $totalSentSiswa = 0;
        $totalSentGuru  = 0;
        $totalFailed    = 0;

        // =========================================================================
        // A. NOTIFIKASI JADWAL MENGAJAR UNTUK GURU
        // =========================================================================
        $this->line("==================== 👨‍🏫 NOTIFIKASI GURU ====================");
        $jadwalPerGuru = $jadwalBesok->groupBy('guru_id');

        foreach ($jadwalPerGuru as $guruId => $listJadwal) {
            $guru = $listJadwal->first()->guru;
            if (!$guru) continue;

            $guruName = $guru->name;
            $count    = $listJadwal->count();

            $jadwalSummaryList = [];
            foreach ($listJadwal as $j) {
                $namaKelas = $j->kelas->nama_kelas ?? 'Kelas';
                $namaMapel = $j->mapel->nama_mapel ?? 'Mata Pelajaran';
                $jamMulai  = substr($j->jam_mulai, 0, 5);
                $jamSelesai = substr($j->jam_selesai, 0, 5);
                $jadwalSummaryList[] = "• {$namaKelas}: {$namaMapel} ({$jamMulai} - {$jamSelesai} WIT)";
            }

            $title = "⏰ Jadwal Mengajar Besok ({$hariBesokIndo})";
            $body  = "Halo Bpk/Ibu {$guruName}! Besok ada {$count} jadwal mengajar:\n" . implode("\n", array_slice($jadwalSummaryList, 0, 3));
            if (count($jadwalSummaryList) > 3) {
                $body .= "\n...dan " . (count($jadwalSummaryList) - 3) . " jadwal lainnya.";
            }

            $this->line("  👤 <fg=yellow>Guru: {$guruName}</> ({$count} Sesi)");
            foreach ($jadwalSummaryList as $line) {
                $this->line("     {$line}");
            }

            if ($isDryRun) {
                $this->line("     <fg=cyan>[DRY RUN] Notifikasi tidak dikirim.</>");
                $this->newLine();
                continue;
            }

            if (empty($guru->fcm_token)) {
                $this->line("     <fg=gray>FCM Token Guru belum terdaftar (belum login di mobile).</>");
                $this->newLine();
                continue;
            }

            $res = $this->fcm->sendToDevice($guru->fcm_token, $title, $body, [
                'type' => 'teacher_schedule',
                'day'  => $hariBesokIndo,
            ]);

            if ($res['success']) {
                $totalSentGuru++;
                $this->line("     <fg=green>✅ Berhasil terkirim ke HP Guru!</>");
            } else {
                $totalFailed++;
                $this->line("     <fg=red>❌ Gagal: {$res['error']}</>");
            }
            $this->newLine();
        }

        // =========================================================================
        // B. NOTIFIKASI JADWAL PELAJARAN UNTUK SISWA
        // =========================================================================
        $this->line("==================== 🎒 NOTIFIKASI SISWA ====================");
        $jadwalPerKelas = $jadwalBesok->groupBy('kelas_id');

        foreach ($jadwalPerKelas as $kelasId => $listJadwal) {
            $kelas = $listJadwal->first()->kelas;
            if (!$kelas) continue;

            $namaKelas = $kelas->nama_kelas;
            $countMapel = $listJadwal->count();

            $mapelSummaryList = [];
            foreach ($listJadwal as $j) {
                $namaMapel = $j->mapel->nama_mapel ?? 'Mapel';
                $jamMulai  = substr($j->jam_mulai, 0, 5);
                $jamSelesai = substr($j->jam_selesai, 0, 5);
                $mapelSummaryList[] = "• {$namaMapel} ({$jamMulai} - {$jamSelesai} WIT)";
            }

            $title = "📚 Jadwal Pelajaran Besok ({$hariBesokIndo})";
            $body  = "Halo siswa {$namaKelas}! Besok ada {$countMapel} mata pelajaran:\n" . implode("\n", array_slice($mapelSummaryList, 0, 3));
            if (count($mapelSummaryList) > 3) {
                $body .= "\n...dan " . (count($mapelSummaryList) - 3) . " mapel lainnya.";
            }

            // Ambil semua siswa di kelas tersebut yang punya FCM token
            $siswaList = Siswa::with('user')
                ->where('kelas_id', $kelasId)
                ->get();

            $tokens = $siswaList
                ->pluck('user.fcm_token')
                ->filter()
                ->values()
                ->toArray();

            $this->line("  🏫 <fg=yellow>Kelas: {$namaKelas}</> ({$countMapel} Mapel) — Siswa aktif token: " . count($tokens) . " orang");

            if ($isDryRun) {
                $this->line("     <fg=cyan>[DRY RUN] Notifikasi tidak dikirim.</>");
                $this->newLine();
                continue;
            }

            if (empty($tokens)) {
                $this->line("     <fg=gray>Belum ada siswa di kelas ini yang memiliki FCM token aktif.</>");
                $this->newLine();
                continue;
            }

            $result = $this->fcm->sendToMultiple($tokens, $title, $body, [
                'type'     => 'attendance',
                'class_id' => (string) $kelasId,
                'day'      => $hariBesokIndo,
            ]);

            $totalSentSiswa += $result['success'];
            $totalFailed    += $result['failed'];

            $this->line("     ✅ Terkirim ke Siswa: {$result['success']} | ❌ Gagal: {$result['failed']}");
            if (!empty($result['errors'])) {
                foreach ($result['errors'] as $err) {
                    $this->line("     <fg=red>⚠️ Error: {$err}</>");
                }
            }
            $this->newLine();
        }

        if (!$isDryRun) {
            $summary = "Notifikasi jadwal besok selesai (WIT). Terkirim Guru: {$totalSentGuru}, Terkirim Siswa: {$totalSentSiswa}, Gagal: {$totalFailed}";
            Log::info('[FCM Absensi] ' . $summary);
            $this->info("🎉 Selesai! {$summary}");
        }

        return Command::SUCCESS;
    }
}

// akhir batas suci yang kamu ubah

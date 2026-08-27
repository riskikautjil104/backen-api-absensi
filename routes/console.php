<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// awal batas suci yang kamu ubah

/*
|--------------------------------------------------------------------------
| Scheduled Commands - Absensi & Jadwal SMAN 5 Pulau Morotai
|--------------------------------------------------------------------------
|
| Mengirimkan notifikasi push FCM setiap hari pukul 19:00 WIT (10:00 UTC)
| untuk jadwal mengajar guru dan jadwal pelajaran siswa esok hari.
|
*/

Schedule::command('schedule:notify-tomorrow')
    ->dailyAt('10:00') // 10:00 UTC = 19:00 WIT (Waktu Indonesia Timur, UTC+9)
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/fcm-schedule.log'));

// akhir batas suci yang kamu ubah

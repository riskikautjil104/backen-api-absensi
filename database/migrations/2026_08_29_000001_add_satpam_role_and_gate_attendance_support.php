<?php

// awal batas suci yang kamu ubah

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ubah enum role di tabel users agar mendukung 'satpam'
        try {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'guru', 'siswa', 'satpam') NOT NULL DEFAULT 'siswa'");
        } catch (\Throwable $e) {
            // Fallback jika database menggunakan string biasa
        }

        // 2. Tambah kolom pada tabel absensi untuk presensi gerbang (Satpam)
        Schema::table('absensi', function (Blueprint $table) {
            if (!Schema::hasColumn('absensi', 'tipe_presensi')) {
                $table->enum('tipe_presensi', ['mapel', 'gerbang_masuk', 'gerbang_pulang'])->default('mapel')->after('jadwal_id');
            }
            if (!Schema::hasColumn('absensi', 'petugas_id')) {
                $table->foreignId('petugas_id')->nullable()->after('tipe_presensi')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('absensi', 'keterangan')) {
                $table->string('keterangan', 255)->nullable()->after('status');
            }
            if (!Schema::hasColumn('absensi', 'metode_scan')) {
                $table->enum('metode_scan', ['kartu_fisik', 'kartu_digital', 'qris_gerbang', 'manual_petugas'])->default('kartu_digital')->after('keterangan');
            }
        });

        // 3. Tambah kolom nisn pada tabel siswa jika belum ada
        Schema::table('siswa', function (Blueprint $table) {
            if (!Schema::hasColumn('siswa', 'nisn')) {
                $table->string('nisn', 20)->nullable()->after('user_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            if (Schema::hasColumn('absensi', 'petugas_id')) {
                $table->dropForeign(['petugas_id']);
                $table->dropColumn('petugas_id');
            }
            if (Schema::hasColumn('absensi', 'tipe_presensi')) {
                $table->dropColumn('tipe_presensi');
            }
            if (Schema::hasColumn('absensi', 'keterangan')) {
                $table->dropColumn('keterangan');
            }
            if (Schema::hasColumn('absensi', 'metode_scan')) {
                $table->dropColumn('metode_scan');
            }
        });

        Schema::table('siswa', function (Blueprint $table) {
            if (Schema::hasColumn('siswa', 'nisn')) {
                $table->dropColumn('nisn');
            }
        });
    }
};

// akhir batas suci yang kamu ubah

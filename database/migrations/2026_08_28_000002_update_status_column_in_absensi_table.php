<?php

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
        // Ubah tipe kolom status menjadi VARCHAR(30) agar fleksibel mendukung hadir, terlambat, izin, sakit, alpa
        try {
            DB::statement("ALTER TABLE `absensi` MODIFY COLUMN `status` VARCHAR(30) NOT NULL DEFAULT 'hadir'");
        } catch (\Throwable $e) {
            Schema::table('absensi', function (Blueprint $table) {
                $table->string('status', 30)->default('hadir')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            DB::statement("ALTER TABLE `absensi` MODIFY COLUMN `status` ENUM('hadir', 'terlambat', 'alpha') NOT NULL DEFAULT 'hadir'");
        } catch (\Throwable $e) {
            //
        }
    }
};

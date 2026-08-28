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
        if (!Schema::hasTable('jam_operasional_gerbang')) {
            Schema::create('jam_operasional_gerbang', function (Blueprint $table) {
                $table->id();
                $table->string('hari', 20)->unique(); // senin, selasa, rabu, kamis, jumat, sabtu, minggu
                $table->string('nama_hari', 30);     // Senin, Selasa, dst
                $table->integer('urutan')->default(1);
                $table->string('jam_masuk_mulai', 10)->default('06:00');
                $table->string('jam_masuk_batas', 10)->default('07:30'); // Batas toleransi tepat waktu
                $table->string('jam_pulang_mulai', 10)->default('14:00'); // Batas waktu mulai pulang
                $table->boolean('is_libur')->default(false);
                $table->string('keterangan', 255)->nullable();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });

            // Seed default schedule
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
                $d['created_at'] = now();
                $d['updated_at'] = now();
                DB::table('jam_operasional_gerbang')->insert($d);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jam_operasional_gerbang');
    }
};

// akhir batas suci yang kamu ubah

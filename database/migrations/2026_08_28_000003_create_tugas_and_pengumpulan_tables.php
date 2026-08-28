<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // awal batas suci yang kamu ubah
        if (!Schema::hasTable('tugas')) {
            Schema::create('tugas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('guru_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
                $table->foreignId('mapel_id')->constrained('mata_pelajaran')->onDelete('cascade');
                $table->string('judul', 255);
                $table->text('deskripsi')->nullable();
                $table->string('tipe_pengumpulan', 30)->default('online'); // 'online' (di aplikasi) atau 'langsung' (fisik)
                $table->dateTime('deadline')->nullable();
                $table->string('file_lampiran', 255)->nullable();
                $table->integer('poin_maksimal')->default(100);
                $table->string('status', 20)->default('aktif'); // 'aktif', 'selesai', 'draf'
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pengumpulan_tugas')) {
            Schema::create('pengumpulan_tugas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tugas_id')->constrained('tugas')->onDelete('cascade');
                $table->foreignId('siswa_id')->constrained('siswa')->onDelete('cascade');
                $table->string('status', 30)->default('dikumpulkan'); // 'belum', 'dikumpulkan', 'dinilai', 'terlambat'
                $table->string('tipe_pengumpulan', 30)->default('online');
                $table->dateTime('waktu_kumpul')->nullable();
                $table->string('file_tugas', 255)->nullable();
                $table->text('catatan_siswa')->nullable();
                $table->decimal('nilai', 5, 2)->nullable();
                $table->text('catatan_guru')->nullable();
                $table->dateTime('waktu_dinilai')->nullable();
                $table->timestamps();

                $table->unique(['tugas_id', 'siswa_id']);
            });
        }
        // akhir batas suci yang kamu ubah
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // awal batas suci yang kamu ubah
        Schema::dropIfExists('pengumpulan_tugas');
        Schema::dropIfExists('tugas');
        // akhir batas suci yang kamu ubah
    }
};

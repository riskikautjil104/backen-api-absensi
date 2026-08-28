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
        if (!Schema::hasTable('bahan_ajar')) {
            Schema::create('bahan_ajar', function (Blueprint $table) {
                $table->id();
                $table->foreignId('guru_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
                $table->foreignId('mapel_id')->constrained('mata_pelajaran')->onDelete('cascade');
                $table->string('judul', 255);
                $table->text('deskripsi')->nullable();
                $table->string('tipe_materi', 50)->default('google_docs'); // google_docs, google_slides, pdf, youtube, link
                $table->text('url_materi')->nullable();
                $table->text('embed_url')->nullable();
                $table->string('file_materi', 255)->nullable();
                $table->string('status', 20)->default('aktif'); // aktif, draf
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('evaluasi_bahan_ajar')) {
            Schema::create('evaluasi_bahan_ajar', function (Blueprint $table) {
                $table->id();
                $table->foreignId('bahan_ajar_id')->constrained('bahan_ajar')->onDelete('cascade');
                $table->string('judul_evaluasi', 255);
                $table->text('instruksi')->nullable();
                $table->integer('poin_maksimal')->default(100);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('soal_evaluasi')) {
            Schema::create('soal_evaluasi', function (Blueprint $table) {
                $table->id();
                $table->foreignId('evaluasi_id')->constrained('evaluasi_bahan_ajar')->onDelete('cascade');
                $table->integer('nomor_urut')->default(1);
                $table->text('pertanyaan');
                $table->string('tipe_soal', 30)->default('esai'); // esai, pilihan_ganda
                $table->text('opsi_a')->nullable();
                $table->text('opsi_b')->nullable();
                $table->text('opsi_c')->nullable();
                $table->text('opsi_d')->nullable();
                $table->text('opsi_e')->nullable();
                $table->string('kunci_jawaban', 10)->nullable(); // A, B, C, D, E
                $table->integer('poin')->default(10);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('jawaban_evaluasi')) {
            Schema::create('jawaban_evaluasi', function (Blueprint $table) {
                $table->id();
                $table->foreignId('evaluasi_id')->constrained('evaluasi_bahan_ajar')->onDelete('cascade');
                $table->foreignId('siswa_id')->constrained('siswa')->onDelete('cascade');
                $table->decimal('total_nilai', 5, 2)->nullable();
                $table->text('catatan_guru')->nullable();
                $table->string('status', 30)->default('dikerjakan'); // dikerjakan, dinilai
                $table->dateTime('waktu_submit')->nullable();
                $table->dateTime('waktu_dinilai')->nullable();
                $table->timestamps();

                $table->unique(['evaluasi_id', 'siswa_id']);
            });
        }

        if (!Schema::hasTable('detail_jawaban_evaluasi')) {
            Schema::create('detail_jawaban_evaluasi', function (Blueprint $table) {
                $table->id();
                $table->foreignId('jawaban_evaluasi_id')->constrained('jawaban_evaluasi')->onDelete('cascade');
                $table->foreignId('soal_evaluasi_id')->constrained('soal_evaluasi')->onDelete('cascade');
                $table->text('jawaban_siswa')->nullable();
                $table->decimal('nilai', 5, 2)->nullable();
                $table->timestamps();
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
        Schema::dropIfExists('detail_jawaban_evaluasi');
        Schema::dropIfExists('jawaban_evaluasi');
        Schema::dropIfExists('soal_evaluasi');
        Schema::dropIfExists('evaluasi_bahan_ajar');
        Schema::dropIfExists('bahan_ajar');
        // akhir batas suci yang kamu ubah
    }
};

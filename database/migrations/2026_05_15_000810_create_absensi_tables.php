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
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kelas', 50);
            $table->string('tahun_ajaran', 9);
            $table->timestamps();
        });

        Schema::create('mata_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->string('nama_mapel', 100);
            $table->string('kode_mapel', 20)->unique();
            $table->timestamps();
        });

        Schema::create('guru_mapel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('mapel_id')->constrained('mata_pelajaran');
            $table->foreignId('kelas_id')->constrained('kelas');
        });

        Schema::create('siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('kelas_id')->nullable()->constrained('kelas');
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('nomor_hp', 15)->nullable();
            $table->string('wa_orang_tua', 15)->nullable();
            $table->text('alamat')->nullable();
            $table->timestamps();
        });

        Schema::create('kartu_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->onDelete('cascade');
            $table->string('token', 64)->unique();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();
        });

        Schema::create('jadwal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->constrained('kelas');
            $table->foreignId('mapel_id')->constrained('mata_pelajaran');
            $table->foreignId('guru_id')->constrained('users');
            $table->enum('hari', ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu']);
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->timestamps();
        });

        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa');
            $table->foreignId('jadwal_id')->nullable()->constrained('jadwal');
            $table->timestamp('waktu_scan')->useCurrent();
            $table->enum('status', ['hadir', 'terlambat', 'alpha'])->default('hadir');
            $table->string('latitude', 20)->nullable();
            $table->string('longitude', 20)->nullable();
            $table->timestamps();
        });

        Schema::create('buku', function (Blueprint $table) {
            $table->id();
            $table->string('kode_buku', 50)->unique();
            $table->string('judul', 255);
            $table->string('penulis', 100)->nullable();
            $table->string('penerbit', 100)->nullable();
            $table->integer('stok')->default(1);
            $table->string('qr_token', 64)->unique()->nullable();
            $table->timestamps();
        });

        Schema::create('peminjaman_buku', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa');
            $table->foreignId('buku_id')->constrained('buku');
            $table->date('tgl_pinjam');
            $table->date('tgl_jatuh_tempo');
            $table->date('tgl_kembali')->nullable();
            $table->enum('status', ['dipinjam', 'kembali', 'telat'])->default('dipinjam');
            $table->integer('denda')->default(0);
            $table->timestamps();
        });

        Schema::create('log_aktivitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->text('aktivitas');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_aktivitas');
        Schema::dropIfExists('peminjaman_buku');
        Schema::dropIfExists('buku');
        Schema::dropIfExists('absensi');
        Schema::dropIfExists('jadwal');
        Schema::dropIfExists('kartu_siswa');
        Schema::dropIfExists('siswa');
        Schema::dropIfExists('guru_mapel');
        Schema::dropIfExists('mata_pelajaran');
        Schema::dropIfExists('kelas');
    }
};

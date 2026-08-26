# CLAUDE.md - Project Context & Instructions

## PROJECT OVERVIEW

**Nama Projek:** Sistem Absensi & Perpustakaan Digital SMA Negeri 5 Pulau Morotai

**Deskripsi:** Aplikasi web berbasis QR code untuk absensi siswa per mata pelajaran dan manajemen perpustakaan dengan dukungan kamera aktif, suara konfirmasi, dan akses multi-role (Admin, Guru, Siswa).

**Target Pengguna:** 
- SMA Negeri 5 Pulau Morotai
- Lokasi dengan jaringan internet kurang stabil (support offline-first)

**Teknologi:**
- Backend: Laravel 13
- Frontend: Blade + Tailwind CSS
- Database: MySQL
- QR Scanner: html5-qrcode
- Text-to-Speech: Web Speech API

---

## COLOR PALETTE (WAJIB DIIKUTI)

| Warna | Hex | Penggunaan |
|-------|-----|-------------|
| Biru Utama | #1E3A8A | Header, tombol utama, navbar aktif, link |
| Biru Muda | #3B82F6 | Hover state, border, aksen sekunder |
| Putih | #FFFFFF | Background utama, card, form |
| Abu Muda | #F3F4F6 | Background sekunder, divider |
| Kuning Aksen | #F59E0B | Tombol CTA, status peringatan, highlight |
| Kuning Muda | #FEF3C7 | Background warning, notifikasi |
| Hijau Sukses | #10B981 | Status hadir, berhasil |
| Merah Error | #EF4444 | Status tidak hadir, error |
| Abu Teks | #4B5563 | Teks biasa |
| Hitam Judul | #111827 | Heading, judul |

---

## UI/UX REQUIREMENTS

### 1. Mobile First & Responsive
- Tampilan harus responsif untuk mobile (handphone) dan tablet
- Menggunakan Tailwind CSS utility classes
- Breakpoints: sm (640px), md (768px), lg (1024px)

### 2. Bottom Navigation Bar (WAJIB)
Setiap halaman untuk role SISWA dan GURU harus memiliki bottom navigation bar:

**Untuk Role SISWA:**
- 🏠 Beranda
- 📷 Absen
- 📚 Perpustakaan
- 👤 Profil

**Untuk Role GURU:**
- 🏠 Dashboard
- 📷 Absen
- 📚 Perpustakaan
- 📊 Rekap

**Untuk Role ADMIN:**
- 🏠 Dashboard
- 👥 Kelola Guru
- 🎓 Kelola Siswa
- 📚 Kelola Buku
- ⚙️ Pengaturan

### 3. Halaman Absensi (Fitur Utama)

**Komponen yang WAJIB ada:**
- Video preview kamera (menggunakan navigator.mediaDevices.getUserMedia())
- Area feedback di BAGIAN BAWAH kamera (area kosong untuk menampilkan hasil)
- Konfirmasi suara menggunakan Web Speech API (bahasa Indonesia)
- Tombol kembali ke dashboard

**Feedback area harus menampilkan:**
- Icon status (✅ berhasil, ❌ gagal, ⏳ loading)
- Nama siswa (jika berhasil)
- Kelas siswa
- Waktu absen
- Pesan suara: "Absensi atas nama [nama siswa], berhasil"

**Contoh struktur HTML halaman absen:**

<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-blue-900 text-white p-4">
        <h1 class="text-xl font-bold">Absensi QR Code</h1>
    </div>
    
    <!-- Area Kamera -->
    <div class="relative bg-black aspect-video">
        <video id="video" class="w-full h-full object-cover" autoplay playsinline></video>
        <canvas id="canvas" class="hidden"></canvas>
        <!-- Overlay panduan -->
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
            <div class="border-2 border-yellow-400 w-64 h-64 rounded-lg"></div>
        </div>
    </div>
    
    <!-- AREA FEEDBACK (WAJIB ADA DI BAWAH KAMERA) -->
    <div class="bg-white rounded-t-2xl shadow-lg mt-2 p-6 min-h-[200px]">
        <div class="flex flex-col items-center text-center">
            <div id="status-icon" class="text-6xl mb-3">📷</div>
            <p id="status-text" class="text-gray-500">Arahkan QR code ke kamera</p>
            <div id="student-info" class="hidden mt-4">
                <h3 id="student-name" class="text-xl font-bold text-green-600"></h3>
                <p id="student-class" class="text-gray-600"></p>
                <p id="attendance-time" class="text-sm text-gray-500"></p>
            </div>
        </div>
    </div>
</div>

---

## DATABASE SCHEMA (LENGKAP)

### Tabel users
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'guru', 'siswa') DEFAULT 'siswa',
    foto VARCHAR(255) NULL,
    nis VARCHAR(20) UNIQUE NULL,
    nip VARCHAR(20) UNIQUE NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

### Tabel kelas
CREATE TABLE kelas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_kelas VARCHAR(50) NOT NULL,
    tahun_ajaran VARCHAR(9) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

### Tabel mata_pelajaran
CREATE TABLE mata_pelajaran (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_mapel VARCHAR(100) NOT NULL,
    kode_mapel VARCHAR(20) UNIQUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

### Tabel guru_mapel
CREATE TABLE guru_mapel (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    guru_id BIGINT UNSIGNED NOT NULL,
    mapel_id BIGINT UNSIGNED NOT NULL,
    kelas_id BIGINT UNSIGNED NOT NULL,
    FOREIGN KEY (guru_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (mapel_id) REFERENCES mata_pelajaran(id),
    FOREIGN KEY (kelas_id) REFERENCES kelas(id)
);

### Tabel siswa
CREATE TABLE siswa (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    kelas_id BIGINT UNSIGNED NULL,
    tempat_lahir VARCHAR(100) NULL,
    tanggal_lahir DATE NULL,
    nomor_hp VARCHAR(15) NULL,
    wa_orang_tua VARCHAR(15) NULL,
    alamat TEXT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (kelas_id) REFERENCES kelas(id)
);

### Tabel kartu_siswa
CREATE TABLE kartu_siswa (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    siswa_id BIGINT UNSIGNED NOT NULL,
    token VARCHAR(64) UNIQUE NOT NULL,
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE
);

### Tabel jadwal
CREATE TABLE jadwal (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kelas_id BIGINT UNSIGNED NOT NULL,
    mapel_id BIGINT UNSIGNED NOT NULL,
    guru_id BIGINT UNSIGNED NOT NULL,
    hari ENUM('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu') NOT NULL,
    jam_mulai TIME NOT NULL,
    jam_selesai TIME NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (kelas_id) REFERENCES kelas(id),
    FOREIGN KEY (mapel_id) REFERENCES mata_pelajaran(id),
    FOREIGN KEY (guru_id) REFERENCES users(id)
);

### Tabel absensi
CREATE TABLE absensi (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    siswa_id BIGINT UNSIGNED NOT NULL,
    jadwal_id BIGINT UNSIGNED NULL,
    waktu_scan DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('hadir', 'terlambat', 'alpha') DEFAULT 'hadir',
    latitude VARCHAR(20) NULL,
    longitude VARCHAR(20) NULL,
    FOREIGN KEY (siswa_id) REFERENCES siswa(id),
    FOREIGN KEY (jadwal_id) REFERENCES jadwal(id)
);

### Tabel buku
CREATE TABLE buku (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode_buku VARCHAR(50) UNIQUE NOT NULL,
    judul VARCHAR(255) NOT NULL,
    penulis VARCHAR(100),
    penerbit VARCHAR(100),
    stok INT DEFAULT 1,
    qr_token VARCHAR(64) UNIQUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

### Tabel peminjaman_buku
CREATE TABLE peminjaman_buku (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    siswa_id BIGINT UNSIGNED NOT NULL,
    buku_id BIGINT UNSIGNED NOT NULL,
    tgl_pinjam DATE NOT NULL,
    tgl_jatuh_tempo DATE NOT NULL,
    tgl_kembali DATE NULL,
    status ENUM('dipinjam', 'kembali', 'telat') DEFAULT 'dipinjam',
    denda INT DEFAULT 0,
    FOREIGN KEY (siswa_id) REFERENCES siswa(id),
    FOREIGN KEY (buku_id) REFERENCES buku(id)
);

### Tabel log_aktivitas
CREATE TABLE log_aktivitas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    aktivitas TEXT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

---

## ROLE & PERMISSIONS

### ADMIN
- Buat/edit/hapus akun guru
- Lihat semua data siswa
- Lihat semua kartu siswa (status aktif/nonaktif)
- Nonaktifkan kartu siswa
- Lihat log aktivitas login siswa
- Setting jadwal per kelas & per mapel
- CRUD buku perpustakaan

### GURU
- Lihat dashboard (ringkasan kelas dan mapel yang dia ajar)
- Lihat absensi hari ini per kelas dan per mapel
- Lihat rekap kehadiran (filter by tanggal, kelas, mapel)
- Export laporan ke Excel

### SISWA
- Input/edit data diri (nomor HP, WA orang tua, tempat/tgl lahir, alamat)
- Upload foto profil
- Lihat kartu digital (QR code + data diri)
- Download/print kartu (format PDF)
- Lihat riwayat absensi

---

## API ENDPOINTS

### Absensi API
POST   /api/absensi/scan        - Proses scan QR code
GET    /api/siswa/{token}       - Get data siswa by token
GET    /api/jadwal/now/{kelas}  - Get jadwal saat ini

### Perpustakaan API
GET    /api/buku/search?q=      - Cari buku
POST   /api/perpus/pinjam       - Proses peminjaman
POST   /api/perpus/kembali      - Proses pengembalian
GET    /api/perpus/riwayat/{id} - Riwayat peminjaman siswa

### Admin API
POST   /api/admin/guru          - Tambah guru
PUT    /api/admin/guru/{id}     - Edit guru
DELETE /api/admin/guru/{id}     - Hapus guru
POST   /api/admin/siswa         - Tambah siswa
PUT    /api/admin/siswa/{id}    - Edit siswa
DELETE /api/admin/siswa/{id}    - Hapus siswa
POST   /api/admin/jadwal        - Tambah jadwal
PUT    /api/admin/jadwal/{id}   - Edit jadwal
DELETE /api/admin/jadwal/{id}   - Hapus jadwal

---

## JAVASCRIPT FUNCTIONS (WAJIB ADA)

### 1. Init Kamera
async function initCamera() {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ 
            video: { facingMode: "environment" } 
        });
        const videoElement = document.getElementById('video');
        videoElement.srcObject = stream;
        return true;
    } catch (error) {
        console.error('Camera error:', error);
        showError('Tidak dapat mengakses kamera');
        return false;
    }
}

### 2. Speech Success (Suara)
function speakSuccess(studentName) {
    if (!window.speechSynthesis) {
        console.warn('Speech synthesis not supported');
        return;
    }
    
    const utterance = new SpeechSynthesisUtterance();
    utterance.text = `Absensi atas nama ${studentName}, berhasil`;
    utterance.lang = 'id-ID';
    utterance.rate = 0.9;
    utterance.pitch = 1;
    
    window.speechSynthesis.cancel();
    window.speechSynthesis.speak(utterance);
}

### 3. QR Scan
import { Html5Qrcode } from 'html5-qrcode';

function startQrScanner() {
    const html5QrCode = new Html5Qrcode("video");
    
    html5QrCode.start(
        { facingMode: "environment" },
        { fps: 10, qrbox: { width: 250, height: 250 } },
        onScanSuccess,
        onScanError
    );
}

async function onScanSuccess(decodedText) {
    await processAttendance(decodedText);
}

### 4. Process Attendance
async function processAttendance(token) {
    showLoading();
    
    try {
        const response = await fetch('/api/absensi/scan', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ token: token })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccess(data.student);
            speakSuccess(data.student.name);
            playBeep();
        } else {
            showError(data.message);
        }
    } catch (error) {
        showError('Gagal terhubung ke server');
    }
}

---

## INSTALLATION STEPS

# 1. Buat project Laravel 13
composer create-project laravel/laravel absensi-sekolah
cd absensi-sekolah

# 2. Install Breeze untuk auth
composer require laravel/breeze --dev
php artisan breeze:install blade

# 3. Install NPM dependencies
npm install
npm install html5-qrcode tailwindcss

# 4. Setup database di .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=absensi_sekolah
DB_USERNAME=root
DB_PASSWORD=

# 5. Jalankan migrasi
php artisan migrate

# 6. Buat storage link untuk foto
php artisan storage:link

# 7. Jalankan development server
php artisan serve
npm run dev

---

## FILE STRUCTURE (WAJIB)

absensi-sekolah/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── GuruController.php
│   │   │   │   ├── SiswaController.php
│   │   │   │   ├── KelasController.php
│   │   │   │   ├── MapelController.php
│   │   │   │   ├── JadwalController.php
│   │   │   │   └── BukuController.php
│   │   │   ├── Guru/
│   │   │   │   ├── DashboardController.php
│   │   │   │   └── AbsensiController.php
│   │   │   ├── Siswa/
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── ProfilController.php
│   │   │   │   ├── KartuController.php
│   │   │   │   └── AbsensiController.php
│   │   │   ├── AbsensiController.php
│   │   │   └── PerpusController.php
│   │   ├── Middleware/
│   │   │   └── RoleMiddleware.php
│   │   └── Kernel.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Siswa.php
│   │   ├── Guru.php
│   │   ├── Kelas.php
│   │   ├── MataPelajaran.php
│   │   ├── Jadwal.php
│   │   ├── KartuSiswa.php
│   │   ├── Absensi.php
│   │   ├── Buku.php
│   │   └── PeminjamanBuku.php
│   └── Services/
│       └── QrCodeService.php
├── database/
│   ├── migrations/
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── AdminSeeder.php
│       └── KelasSeeder.php
├── resources/
│   └── views/
│       ├── layouts/
│       │   ├── app.blade.php
│       │   └── navbar.blade.php
│       ├── components/
│       │   ├── bottom-nav-siswa.blade.php
│       │   ├── bottom-nav-guru.blade.php
│       │   └── bottom-nav-admin.blade.php
│       ├── admin/
│       │   ├── dashboard.blade.php
│       │   ├── guru/
│       │   ├── siswa/
│       │   ├── kelas/
│       │   ├── mapel/
│       │   ├── jadwal/
│       │   └── buku/
│       ├── guru/
│       │   ├── dashboard.blade.php
│       │   └── absensi/
│       ├── siswa/
│       │   ├── dashboard.blade.php
│       │   ├── profil.blade.php
│       │   ├── kartu.blade.php
│       │   └── absensi.blade.php
│       ├── absensi/
│       │   └── scan.blade.php
│       └── perpus/
│           ├── index.blade.php
│           └── riwayat.blade.php
├── public/
│   ├── css/
│   ├── js/
│   │   ├── camera.js
│   │   ├── qr-scanner.js
│   │   └── speech.js
│   └── uploads/
│       ├── foto_siswa/
│       └── foto_guru/
├── routes/
│   ├── web.php
│   └── api.php
├── tailwind.config.js
└── package.json

---

## TAILWIND CONFIG

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
    ],
    theme: {
        extend: {
            colors: {
                'primary': '#1E3A8A',
                'primary-light': '#3B82F6',
                'accent': '#F59E0B',
                'accent-light': '#FEF3C7',
            }
        },
    },
    plugins: [],
}

---

## DEPLOYMENT CHECKLIST

- [ ] Server dengan PHP 8.3+, MySQL, Composer
- [ ] Domain dengan SSL/HTTPS (WAJIB untuk akses kamera)
- [ ] Setup environment variables (.env)
- [ ] Jalankan php artisan key:generate
- [ ] Jalankan php artisan migrate --seed
- [ ] Setup storage link: php artisan storage:link
- [ ] Optimize: php artisan optimize
- [ ] Setup queue worker (opsional)
- [ ] Backup database otomatis

---

## NOTES FOR AI ASSISTANT

Jika saya (Claude) diminta untuk membantu mengerjakan projek ini:

1. Prioritas utama:
   - Halaman absensi dengan kamera aktif + feedback area di bawah + suara
   - Bottom navigation bar untuk mobile
   - Warna biru (#1E3A8A) dan kuning (#F59E0B) sebagai aksen

2. Jangan lupa:
   - Setiap kode yang diberikan harus support mobile responsive
   - Web Speech API harus pakai bahasa Indonesia ('id-ID')
   - QR scan harus bisa jalan di HP (gunakan facingMode: "environment")

3. Jika ada error:
   - Kamera tidak jalan -> pastikan HTTPS atau localhost
   - Suara tidak keluar -> cek apakah user sudah interaksi dengan halaman

4. Referensi:
   - Dokumentasi Laravel 13: https://laravel.com/docs/13.x
   - Tailwind CSS: https://tailwindcss.com/docs
   - html5-qrcode: https://github.com/mebjas/html5-qrcode

---

**Project Owner:** [Nama Kamu]
**School:** SMA Negeri 5 Pulau Morotai
**Last Updated:** 15 Mei 2026
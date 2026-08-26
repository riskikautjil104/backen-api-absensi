# Dokumentasi Lengkap API Siswa & CBT (SIMORO SMANLI v2.0)

Dokumentasi resmi REST API untuk seluruh alur penggunaan akun Siswa pada aplikasi Mobile (Flutter, React Native, Kotlin, Swift) dan Web Frontend.

---

## 📌 Ringkasan Informasi Dasar

- **Base URL Lokal**: `http://127.0.0.1:8000/api`
- **Base URL Server Produksi**: `https://<domain-sekolah>/api`
- **Autentikasi**: `Bearer Token` (Laravel Sanctum)
- **Format Payload**: `JSON` (`application/json`)
- **Headers Wajib**:
  ```http
  Accept: application/json
  Content-Type: application/json
  ```
- **Header Tambahan untuk Endpoint Terproteksi**:
  ```http
  Authorization: Bearer <TOKEN_SANCTUM>
  ```

---

## 📑 Daftar Isi

1. [Autentikasi &amp; Akun Siswa](#1-autentikasi--akun-siswa)
   - [1.1 Login Siswa (Email / NIS)](#11-login-siswa-email--nis)
   - [1.2 Profil Siswa (Me)](#12-profil-siswa-me)
   - [1.3 Update Profil](#13-update-profil)
   - [1.4 Ganti Kata Sandi](#14-ganti-kata-sandi)
   - [1.5 Logout](#15-logout)
2. [Dashboard Siswa](#2-dashboard-siswa)
   - [2.1 Ambil Data Home Dashboard](#21-ambil-data-home-dashboard)
3. [Daftar Ujian &amp; Nilai Siswa](#3-daftar-ujian--nilai-siswa)
   - [3.1 Daftar Ujian Aktif](#31-daftar-ujian-aktif)
   - [3.2 Riwayat &amp; Rekapitulasi Nilai Ujian](#32-riwayat--rekapitulasi-nilai-ujian)
4. [Alur Pengerjaan Ujian CBT (Real-Time &amp; Anti-Curang)](#4-alur-pengerjaan-ujian-cbt-real-time--anti-curang)
   - [4.1 Detail Ujian Sebelum Mulai](#41-detail-ujian-sebelum-mulai)
   - [4.2 Mulai Ujian (Start Session)](#42-mulai-ujian-start-session)
   - [4.3 Auto-Save Menjawab Soal (Per Butir Soal)](#43-auto-save-menjawab-soal-per-butir-soal)
   - [4.4 Rekam Koordinat Lokasi GPS](#44-rekam-koordinat-lokasi-gps)
   - [4.5 Deteksi Pelanggaran / Pindah Tab](#45-deteksi-pelanggaran--pindah-tab)
   - [4.6 Keluar Sesi Ujian (Logout Ujian)](#46-keluar-sesi-ujian-logout-ujian)
   - [4.7 Pengajuan Buka Sesi Terkunci (Reapply)](#47-pengajuan-buka-sesi-terkunci-reapply)
   - [4.8 Submit &amp; Selesaikan Ujian](#48-submit--selesaikan-ujian)
5. [Hasil Ujian &amp; Cetak PDF Resmi](#5-hasil-ujian--cetak-pdf-resmi)
   - [5.1 Rincian Hasil &amp; Pembahasan](#51-rincian-hasil--pembahasan)
   - [5.2 Ambil URL Unduh PDF Resmi Berstempel](#52-ambil-url-unduh-pdf-resmi-berstempel)
6. [Panduan Integrasi Mobile Developer (Best Practices)](#6-panduan-integrasi-mobile-developer-best-practices)

---

## 1. Autentikasi & Akun Siswa

### 1.1. Login Siswa (Email / NIS)

Siswa dapat login menggunakan alamat **Email** atau **NIS**.

- **URL**: `POST /api/siswa/login`
- **Akses**: Publik
- **Body JSON (Opsi 1 - Menggunakan Email)**:
  ```json
  {
    "email": "siswa@sma5.sch.id",
    "password": "siswa123"
  }
  ```
- **Body JSON (Opsi 2 - Menggunakan NIS)**:
  ```json
  {
    "login": "22001",
    "password": "siswa123"
  }
  ```

#### Response Sukses (200 OK)

```json
{
  "success": true,
  "message": "Login berhasil. Selamat datang, Siti Siswa",
  "data": {
    "token": "39|6qGEBii5CLnrgQcu8sg4nU8CRqocjcDWNW696Qeaad9e4da0",
    "token_type": "Bearer",
    "user": {
      "id": 3,
      "name": "Siti Siswa",
      "email": "siswa@sma5.sch.id",
      "nis": "22001",
      "phone": "081234567890",
      "role": "student",
      "class_id": 1,
      "class_name": "X IPA 1",
      "angkatan": "2026",
      "ttd_signature": null,
      "is_graduated": false,
      "created_at": "2026-08-25T10:00:00.000000Z"
    }
  }
}
```

#### Response Gagal - Kredensial Salah (401 Unauthorized)

```json
{
  "success": false,
  "message": "Email/NIS atau kata sandi yang Anda masukkan salah."
}
```

#### Response Gagal - Akun Alumni / Lulus (403 Forbidden)

```json
{
  "success": false,
  "message": "Akun Anda telah dinonaktifkan karena Anda sudah dinyatakan Lulus / Alumni SMA Negeri 5 Pulau Morotai."
}
```

---

### 1.2. Profil Siswa (Me)

Mengambil informasi lengkap siswa yang sedang login.

- **URL**: `GET /api/siswa/profile` *(atau `GET /api/siswa/me`)*
- **Headers**: `Authorization: Bearer <TOKEN>`

#### Response Sukses (200 OK)

```json
{
  "success": true,
  "message": "Data profil siswa berhasil diambil.",
  "data": {
    "id": 3,
    "name": "Siti Siswa",
    "email": "siswa@sma5.sch.id",
    "nis": "22001",
    "phone": "081234567890",
    "role": "student",
    "class_id": 1,
    "class_name": "X IPA 1",
    "angkatan": "2026",
    "ttd_signature": null,
    "is_graduated": false
  }
}
```

---

### 1.3. Update Profil

Memperbarui data pribadi siswa (nama, no telepon/WA, email, dan tanda tangan digital).

- **URL**: `PUT /api/siswa/profile` *(atau `POST /api/siswa/profile`)*
- **Headers**: `Authorization: Bearer <TOKEN>`
- **Body JSON**:
  ```json
  {
    "name": "Siti Siswa Update",
    "phone": "081299887766",
    "ttd_signature": "data:image/png;base64,iVBORw0KGgo..."
  }
  ```

---

### 1.4. Ganti Kata Sandi

- **URL**: `POST /api/siswa/change-password` *(atau `PUT /api/siswa/password`)*
- **Headers**: `Authorization: Bearer <TOKEN>`
- **Body JSON**:
  ```json
  {
    "current_password": "siswa123",
    "password": "PasswordBaru123",
    "password_confirmation": "PasswordBaru123"
  }
  ```

---

### 1.5. Logout

Menghapus sesi token dari server.

- **URL**: `POST /api/siswa/logout`
- **Headers**: `Authorization: Bearer <TOKEN>`

---

## 2. Dashboard Siswa

### 2.1. Ambil Data Home Dashboard

Endpoint all-in-one untuk layar utama aplikasi mobile. Mengembalikan profil ringkas, kartu statistik (jumlah ujian aktif, selesai, nilai rata-rata), daftar ujian aktif yang siap dikerjakan, dan riwayat 5 nilai terakhir.

- **URL**: `GET /api/siswa/dashboard`
- **Headers**: `Authorization: Bearer <TOKEN>`

#### Response Sukses (200 OK)

```json
{
  "success": true,
  "message": "Dashboard siswa berhasil diambil",
  "data": {
    "siswa": {
      "id": 3,
      "name": "Siti Siswa",
      "email": "siswa@sma5.sch.id",
      "nis": "22001",
      "phone": "081234567890",
      "class_id": 1,
      "class_name": "X IPA 1",
      "tingkat": 10,
      "angkatan": "2026",
      "is_graduated": false
    },
    "stats": {
      "ujian_aktif_count": 1,
      "ujian_selesai_count": 16,
      "nilai_rata_rata": 78.5
    },
    "ujian_aktif": [
      {
        "id": 25,
        "title": "Penilaian Akhir Semester Biologi",
        "subject_id": 2,
        "subject_name": "Biologi",
        "teacher_name": "Budi Guru",
        "duration_minutes": 90,
        "total_questions": 25,
        "start_time": "26-08-2026 08:00",
        "end_time": "26-08-2026 12:00",
        "session_id": null,
        "session_status": "not_started",
        "is_completed": false,
        "can_start": true,
        "remaining_seconds": 5400,
        "score": null
      }
    ],
    "riwayat_terakhir": [
      {
        "session_id": 20,
        "exam_id": 18,
        "title": "Ulangan Harian Bab 1",
        "subject_name": "Bahasa Indonesia",
        "score": 88.0,
        "grade": "A",
        "completed_at": "25-08-2026 10:30"
      }
    ]
  }
}
```

---

## 3. Daftar Ujian & Nilai Siswa

### 3.1. Daftar Ujian Aktif

Mengambil seluruh ujian kelas siswa yang sedang aktif pada rentang waktu saat ini.

- **URL**: `GET /api/siswa/ujian/aktif` *(atau `GET /api/siswa/ujian`)*
- **Headers**: `Authorization: Bearer <TOKEN>`

#### Penjelasan `session_status`:

- `not_started`: Siswa belum pernah menekan tombol mulai ujian.
- `in_progress`: Siswa sedang dalam sesi pengerjaan aktif.
- `completed`: Siswa sudah menyelesaikan ujian ini.
- `blocked`: Sesi ujian terkunci karena deteksi pelanggaran (butuh persetujuan reapply).

---

### 3.2. Riwayat & Rekapitulasi Nilai Ujian

Mengambil seluruh daftar ujian yang telah selesai dikerjakan beserta nilai akhir, predikat grade, dan status ketuntasan KKM.

- **URL**: `GET /api/siswa/ujian/riwayat` *(atau `GET /api/siswa/nilai`)*
- **Headers**: `Authorization: Bearer <TOKEN>`

#### Response Sukses (200 OK)

```json
{
  "success": true,
  "message": "Riwayat nilai ujian berhasil diambil",
  "data": [
    {
      "session_id": 20,
      "exam_id": 18,
      "title": "Penilaian Harian Matematika",
      "subject_name": "Matematika",
      "teacher_name": "Rizki Hi",
      "score": 85.0,
      "grade": "A",
      "kkm_status": "Tuntas",
      "status_nilai": "Sudah Dinilai",
      "completed_at": "24-08-2026 11:00"
    },
    {
      "session_id": 23,
      "exam_id": 22,
      "title": "Ujian Akhir Semester Bahasa Indonesia",
      "subject_name": "Bahasa Indonesia",
      "teacher_name": "Budi Guru",
      "score": null,
      "grade": "-",
      "kkm_status": "-",
      "status_nilai": "Menunggu Koreksi Esai",
      "completed_at": "25-08-2026 09:30"
    }
  ]
}
```

---

## 4. Alur Pengerjaan Ujian CBT (Real-Time & Anti-Curang)

### 4.1. Detail Ujian Sebelum Mulai

Melihat informasi durasi, jumlah soal, dan petunjuk teknis.

- **URL**: `GET /api/siswa/ujian/{exam_id}`
- **Headers**: `Authorization: Bearer <TOKEN>`

---

### 4.2. Mulai Ujian (Start Session)

Memulai sesi pengerjaan ujian dan mengunduh seluruh butir soal.

- **URL**: `POST /api/siswa/ujian/{exam_id}/mulai`
- **Headers**: `Authorization: Bearer <TOKEN>`

#### Response Sukses (200 OK)

```json
{
  "success": true,
  "message": "Sesi ujian berhasil dimulai.",
  "data": {
    "session_id": 25,
    "exam_id": 20,
    "title": "Penilaian Akhir Semester Biologi",
    "subject_name": "Biologi",
    "duration_minutes": 90,
    "remaining_seconds": 5400,
    "start_time": "2026-08-26T08:00:00.000000Z",
    "total_questions": 2,
    "answered_count": 0,
    "questions": [
      {
        "id": 101,
        "type": "multiple_choice",
        "question_text": "<p>Organel sel yang berfungsi menghasilkan energi adalah...</p>",
        "options": {
          "a": "Mitokondria",
          "b": "Ribosom",
          "c": "Kloroplas",
          "d": "Badan Golgi"
        },
        "saved_answer": null
      },
      {
        "id": 102,
        "type": "essay",
        "question_text": "<p>Jelaskan pengertian fotosintesis!</p>",
        "options": null,
        "saved_answer": null
      }
    ]
  }
}
```

---

### 4.3. Auto-Save Menjawab Soal (Per Butir Soal)

Dipanggil setiap kali siswa memilih opsi pilihan ganda atau selesai mengetik jawaban esai. Jawaban otomatis tersimpan ke server sehingga tidak akan hilang jika peramban/aplikasi tertutup.

- **URL**: `POST /api/siswa/ujian/{exam_id}/jawab` *(atau `POST /api/siswa/ujian/jawab`)*
- **Headers**: `Authorization: Bearer <TOKEN>`
- **Body JSON**:
  ```json
  {
    "question_id": 101,
    "answer": "a"
  }
  ```

#### Response Sukses (200 OK)

```json
{
  "success": true,
  "message": "Jawaban butir soal berhasil disimpan.",
  "data": {
    "question_id": 101,
    "answer": "a",
    "saved_at": "2026-08-26T08:15:30.000000Z"
  }
}
```

---

### 4.4. Rekam Koordinat Lokasi GPS

Menyimpan lokasi siswa saat mengerjakan ujian.

- **URL**: `POST /api/siswa/ujian/{exam_id}/lokasi`
- **Headers**: `Authorization: Bearer <TOKEN>`
- **Body JSON**:
  ```json
  {
    "lat": 2.05382,
    "lng": 128.29173
  }
  ```

---

### 4.5. Deteksi Pelanggaran / Pindah Tab

Dipanggil jika sensor aplikasi mendeteksi siswa membuka aplikasi lain, split-screen, atau keluar dari aplikasi.

- **URL**: `POST /api/siswa/ujian/{exam_id}/detected`
- **Headers**: `Authorization: Bearer <TOKEN>`
- **Body JSON**:
  ```json
  {
    "reason": "Siswa membuka browser lain / berpindah aplikasi"
  }
  ```

---

### 4.6. Keluar Sesi Ujian (Logout Ujian)

Keluar sementara dari sesi ujian (waktu ujian tetap berjalan).

- **URL**: `POST /api/siswa/ujian/{exam_id}/logout`
- **Headers**: `Authorization: Bearer <TOKEN>`

---

### 4.7. Pengajuan Buka Sesi Terkunci (Reapply)

Mengirim permohonan ke pengawas jika ujian terkunci karena gangguan perangkat.

- **URL**: `POST /api/siswa/ujian/{exam_id}/reapply`
- **Headers**: `Authorization: Bearer <TOKEN>`
- **Body JSON**:
  ```json
  {
    "alasan": "HP mendadak mati kehabisan baterai"
  }
  ```

---

### 4.8. Submit & Selesaikan Ujian

Mengumpulkan seluruh jawaban siswa dan mengakhiri sesi pengerjaan ujian.

- **URL**: `POST /api/siswa/ujian/{exam_id}/submit`
- **Headers**: `Authorization: Bearer <TOKEN>`
- **Body JSON (Opsional jika ada jawaban terakhir yang belum terkirim)**:
  ```json
  {
    "answers": {
      "101": "a",
      "102": "Fotosintesis adalah proses fotosintesis tumbuhan..."
    }
  }
  ```

#### Response Sukses (200 OK)

```json
{
  "success": true,
  "message": "Ujian berhasil diselesaikan dan seluruh jawaban telah terkumpul.",
  "data": {
    "session_id": 25,
    "score": 85.0,
    "total_questions": 20,
    "total_pg": 18,
    "correct_pg": 16,
    "total_essay": 2,
    "has_essay": true,
    "status_koreksi": "Menunggu Penilaian Esai Guru",
    "completed_at": "2026-08-26T09:30:00.000000Z"
  }
}
```

---

## 5. Hasil Ujian & Cetak PDF Resmi

### 5.1. Rincian Hasil & Pembahasan

- **URL**: `GET /api/siswa/ujian/{exam_id}/hasil`
- **Headers**: `Authorization: Bearer <TOKEN>`

#### Response Sukses (200 OK)

```json
{
  "success": true,
  "message": "Hasil ujian berhasil diambil.",
  "data": {
    "session_id": 25,
    "exam_id": 20,
    "title": "Penilaian Akhir Semester Biologi",
    "subject_name": "Biologi",
    "teacher_name": "Budi Guru",
    "score": 85.0,
    "grade": "A",
    "kkm_status": "Tuntas",
    "completed_at": "26-08-2026 09:30",
    "questions": [
      {
        "id": 101,
        "type": "multiple_choice",
        "question_text": "<p>Organel sel penghasil energi...</p>",
        "jawaban_siswa": "a",
        "jawaban_benar": "a",
        "is_correct": true,
        "nilai_essay": null
      }
    ]
  }
}
```

---

### 5.2. Ambil URL Unduh PDF Resmi Berstempel

Menghasilkan tautan bertanda tangan aman (*Temporary Signed URL*) yang berlaku selama 15 menit untuk mengunduh berkas PDF resmi lengkap dengan Kop Surat Sekolah dan Tanda Tangan Digital Kepala Sekolah & Guru.

- **URL**: `GET /api/siswa/ujian/{exam_id}/cetak-url`
- **Headers**: `Authorization: Bearer <TOKEN>`

#### Response Sukses (200 OK)

```json
{
  "success": true,
  "url": "http://127.0.0.1:8000/siswa/ujian/20/hasil/pdf?expires=1740561600&signature=abc123xyz..."
}
```

---

## 6. Panduan Integrasi Mobile Developer (Best Practices)

1. **Auto-Save Tiap Soal**: Selalu panggil endpoint `POST /api/siswa/ujian/{id}/jawab` setiap kali siswa mengeklik opsi atau setelah jeda ketik (debounce 500ms).
2. **Resume Progress**: Saat membuka layar ujian, baca `saved_answer` dari list `questions` untuk memulihkan pilihan siswa sebelumnya jika aplikasi sempat keluar/reopen.
3. **Timer Real-Time**: Gunakan nilai `remaining_seconds` dari endpoint `mulaiUjian` sebagai patokan awal timer hitung mundur di sisi mobile.
4. **Validasi Status Akun**: Jika API mengembalikan status `403` dengan pesan penonaktifan akun alumni, arahkan siswa ke halaman pengumuman kelulusan publik.

---

*Dokumen ini diperbarui untuk SIMORO SMANLI v2.0 &mdash; SMA Negeri 5 Pulau Morotai.*

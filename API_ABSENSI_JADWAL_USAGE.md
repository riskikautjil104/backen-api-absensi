# Panduan Integrasi & Penggunaan API Absensi & Jadwal Siswa

Dokumen ini berisi panduan cara penggunaan REST API lokal untuk absensi dan jadwal pelajaran siswa. 

Saat ini server absensi berjalan lokal pada port **`8005`**:
*   **Base URL API Lokal**: `http://127.0.0.1:8005/api`
*   **Base URL Server Produksi**: `https://<domain-sekolah>/api`

---

## 📑 Daftar API Siswa

| No | Nama API | Method | Endpoint | Keterangan | Autentikasi |
|---|---|---|---|---|---|
| 1 | **Login Siswa** | `POST` | `/siswa/login` | Login menggunakan Email/NIS dan Password | **Publik** |
| 2 | **Profil Siswa** | `GET` | `/siswa/profile` | Mengambil detail profile siswa | **Bearer Token** |
| 3 | **Dashboard** | `GET` | `/siswa/dashboard` | Statistik absen & jadwal hari ini | **Bearer Token** |
| 4 | **Jadwal Pelajaran** | `GET` | `/siswa/jadwal` | Daftar jadwal pelajaran kelas (mingguan) | **Bearer Token** |
| 5 | **Riwayat Absen** | `GET` | `/siswa/absensi` | Log lengkap kehadiran siswa dari scanner | **Bearer Token** |
| 6 | **Scan QR Guru** | `POST` | `/siswa/absensi/scan-guru` | Absensi mandiri dengan scan QR Guru | **Bearer Token** |

---

## 🔑 1. Cara Mengambil Bearer Token (Login)

Gunakan endpoint login untuk mendapatkan `token` autentikasi Laravel Sanctum.

*   **URL**: `POST http://127.0.0.1:8005/api/siswa/login`
*   **Headers**:
    ```http
    Accept: application/json
    Content-Type: application/json
    ```
*   **Body JSON**:
    ```json
    {
      "login": "9988776655", // Bisa diisi NIS atau Email
      "password": "password"
    }
    ```

### 📤 Contoh Response Sukses (200 OK)
```json
{
  "success": true,
  "message": "Login berhasil. Selamat datang, Siswa Contoh",
  "data": {
    "token": "2|laravel_sanctum_token_abc123xyz...",
    "token_type": "Bearer",
    "user": {
      "id": 3,
      "name": "Siswa Contoh",
      "email": "siswa@sma5morotai.sch.id",
      "nis": "9988776655",
      "phone": "081234567890",
      "role": "student",
      "class_id": 1,
      "class_name": "XII IPA 1",
      "angkatan": "2025/2026",
      "is_graduated": false,
      "card_token": "TK-8A9F2E7D",
      "encrypted_card_token": "eyJpdiI6Ik...", // <--- Gunakan value ini untuk QR Code di HP
      "created_at": "2026-08-26T12:00:00.000000Z"
    }
  }
}
```

---

## 🔒 2. Mengakses API Terproteksi (Bearer Token)

Setiap request ke `/siswa/profile`, `/siswa/dashboard`, `/siswa/jadwal`, dan `/siswa/absensi` **wajib** menyertakan token yang diperoleh saat login pada header HTTP.

### Format Header Wajib:
```http
Accept: application/json
Content-Type: application/json
Authorization: Bearer <TOKEN_YANG_DIDAPATKAN_SAAT_LOGIN>
```

---

## 📅 3. Mengambil Jadwal Pelajaran (Mingguan)

Mengambil daftar jadwal pelajaran untuk kelas siswa secara urut hari.

*   **URL**: `GET http://127.0.0.1:8005/api/siswa/jadwal`
*   **Headers**:
    ```http
    Accept: application/json
    Authorization: Bearer 2|laravel_sanctum_token_abc123xyz...
    ```

### 📤 Response Sukses (200 OK)
```json
{
  "success": true,
  "message": "Data jadwal pelajaran berhasil diambil.",
  "data": [
    {
      "id": 1,
      "hari": "Jumat",
      "subject_name": "Matematika",
      "teacher_name": "Bpk. Ahmad Guru",
      "time_start": "08:00",
      "time_end": "09:30"
    },
    {
      "id": 2,
      "hari": "Jumat",
      "subject_name": "Bahasa Inggris",
      "teacher_name": "Ibu Siti Guru",
      "time_start": "09:45",
      "time_end": "11:15"
    }
  ]
}
```

---

## 📝 4. Mengambil Riwayat Kehadiran Siswa

Mengambil riwayat log kehadiran siswa yang direkam dari scan kartu/QR code oleh kamera Guru.

*   **URL**: `GET http://127.0.0.1:8005/api/siswa/absensi`
*   **Headers**:
    ```http
    Accept: application/json
    Authorization: Bearer 2|laravel_sanctum_token_abc123xyz...
    ```

### 📤 Response Sukses (200 OK)
```json
{
  "success": true,
  "message": "Data riwayat absensi berhasil diambil.",
  "data": [
    {
      "id": 12,
      "subject_name": "Matematika",
      "status": "hadir",
      "time": "26 Agt 2026, 08:15 WIT"
    }
  ]
}
```

---

## 🖥️ 5. Contoh Implementasi di Flutter (Dart)

```dart
import 'dart:convert';
import 'package:http/http.dart' as http;

class SimoroApiService {
  // Ganti ke IP Host Anda jika ditest di perangkat fisik android/iOS asli (misal: 192.168.1.10)
  final String baseUrl = "http://127.0.0.1:8005/api";

  // 1. Fungsi Login
  Future<String?> loginSiswa(String login, String password) async {
    final response = await http.post(
      Uri.parse('$baseUrl/siswa/login'),
      headers: {'Accept': 'application/json', 'Content-Type': 'application/json'},
      body: jsonEncode({'login': login, 'password': password}),
    );

    if (response.statusCode == 200) {
      final json = jsonDecode(response.body);
      if (json['success'] == true) {
        return json['data']['token']; // Mengembalikan token
      }
    }
    return null;
  }

  // 2. Fungsi Mengambil Jadwal Pelajaran
  Future<List<dynamic>> getJadwal(String token) async {
    final response = await http.get(
      Uri.parse('$baseUrl/siswa/jadwal'),
      headers: {
        'Accept': 'application/json',
        'Authorization': 'Bearer $token',
      },
    );

    if (response.statusCode == 200) {
      final json = jsonDecode(response.body);
      if (json['success'] == true) {
        return json['data'];
      }
    }
    return [];
  }
  // 3. Fungsi Mengirimkan Scan QR Guru
  Future<Map<String, dynamic>?> scanGuruQr(String token, String payload) async {
    final response = await http.post(
      Uri.parse('$baseUrl/siswa/absensi/scan-guru'),
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'Authorization': 'Bearer $token',
      },
      body: jsonEncode({'payload': payload}),
    );

    final json = jsonDecode(response.body);
    return json; // Mengembalikan response map lengkap (success, message, data)
  }
}
```

---

## 📲 5. API Scan QR Code Guru (Terproteksi)

Jika Guru memilih menampilkan QR Code absensi di layar projector/HP-nya, siswa dapat melakukan absensi secara mandiri dengan memindai QR Code Guru tersebut melalui aplikasi mobile.

*   **URL**: `POST http://127.0.0.1:8005/api/siswa/absensi/scan-guru`
*   **Headers**:
    ```http
    Accept: application/json
    Content-Type: application/json
    Authorization: Bearer <TOKEN_SANCTUM_ANDA>
    ```
*   **Body JSON**:
    ```json
    {
      "payload": "TULISAN_HASIL_SCAN_QR_GURU_YANG_TERENKRIPSI"
    }
    ```

### 📤 Response Sukses (200 OK)
```json
{
  "success": true,
  "message": "Absensi berhasil dicatat!",
  "data": {
    "subject_name": "Matematika",
    "class_name": "XII IPA 1",
    "time": "08:15:30 WIT"
  }
}
```

### 📤 Response Gagal - Bukan Kelasnya / Sudah Absen (400 Bad Request)
```json
{
  "success": false,
  "message": "Anda tidak terdaftar di kelas XII IPA 1 untuk mata pelajaran ini."
}
```

---

## 🎴 6. Cara Menampilkan QR Code Kartu di Layar Mobile (Alur Sebaliknya)
1.  Ambil nilai data `encrypted_card_token` dari response login/profile API.
2.  Gunakan library widget QR Code di Flutter (contoh: `qr_flutter`).
3.  Ubah widget QR tersebut agar me-render string `encrypted_card_token` sebagai payload datanya.
4.  Siswa cukup menunjukkan gambar QR Code di HP tersebut ke kamera HP/laptop Guru (pada menu "Scan Siswa" di dashboard Guru) untuk mencatat kehadiran secara aman!

<!DOCTYPE html>
<!-- awal batas suci yang kamu ubah -->
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekapitulasi Presensi Gerbang - {{ $formattedDate }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Times New Roman', Times, serif; }
        body { padding: 25px; color: #111; font-size: 12px; }
        .kop-surat { text-align: center; border-bottom: 3px double #000; padding-bottom: 12px; margin-bottom: 20px; }
        .kop-surat h2 { font-size: 14px; text-transform: uppercase; font-weight: bold; letter-spacing: 1px; }
        .kop-surat h1 { font-size: 17px; text-transform: uppercase; font-weight: 900; margin: 4px 0; }
        .kop-surat p { font-size: 10.5px; font-style: italic; }
        .title { text-align: center; font-size: 14px; font-weight: bold; text-decoration: underline; margin-bottom: 14px; text-transform: uppercase; }
        .info-grid { display: flex; justify-content: space-between; margin-bottom: 16px; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #333; padding: 6px 8px; font-size: 11px; text-align: left; }
        th { background-color: #f0f0f0; font-weight: bold; text-align: center; text-transform: uppercase; }
        .text-center { text-align: center; }
        .status-badge { font-weight: bold; font-size: 10px; }
        .summary-box { display: flex; justify-content: space-around; background: #f8fafc; border: 1px solid #cbd5e1; padding: 10px; margin-bottom: 20px; border-radius: 6px; }
        .summary-item { text-align: center; font-size: 11px; }
        .summary-item strong { display: block; font-size: 14px; margin-top: 2px; }
        .ttd-wrapper { margin-top: 40px; display: flex; justify-content: flex-end; }
        .ttd-box { text-align: center; width: 220px; font-size: 11px; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; background: #e0f2fe; padding: 12px 18px; border-radius: 8px;">
        <span style="font-size: 12px; font-weight: bold; color: #0369a1;">Pratinjau Cetak / Export PDF Rekap Gerbang</span>
        <button onclick="window.print()" style="background: #0284c7; color: white; border: none; padding: 8px 16px; border-radius: 6px; font-weight: bold; cursor: pointer;">Cetak / Simpan PDF</button>
    </div>

    <!-- Kop Surat -->
    <div class="kop-surat">
        <h2>PEMERINTAH PROVINSI MALUKU UTARA</h2>
        <h2>DINAS PENDIDIKAN DAN KEBUDAYAAN</h2>
        <h1>SMA NEGERI 5 KABUPATEN PULAU MOROTAI</h1>
        <p>Alamat: Jalan Trans Halmahera, Kec. Morotai Selatan Barat, Kab. Pulau Morotai, Maluku Utara</p>
    </div>

    <div class="title">REKAPITULASI PRESENSI GERBANG KEAMANAN SEKOLAH</div>

    <div class="info-grid">
        <div>
            <div><strong>Hari / Tanggal:</strong> {{ $formattedDate }}</div>
            <div><strong>Lokasi Pos:</strong> Pos Gerbang Utama SMAN 5 Morotai</div>
        </div>
        <div>
            <div><strong>Petugas Jaga:</strong> {{ $officerName }}</div>
            <div><strong>Waktu Cetak:</strong> {{ \Carbon\Carbon::now('Asia/Jayapura')->format('H:i:s') }} WIT</div>
        </div>
    </div>

    <!-- Ringkasan Statistik -->
    <div class="summary-box">
        <div class="summary-item">Total Siswa: <strong>{{ $totalSiswa }}</strong></div>
        <div class="summary-item" style="color: #059669;">Tepat Waktu: <strong>{{ $hadirCount }}</strong></div>
        <div class="summary-item" style="color: #d97706;">Terlambat: <strong>{{ $terlambatCount }}</strong></div>
        <div class="summary-item" style="color: #0284c7;">Sudah Pulang: <strong>{{ $pulangCount }}</strong></div>
        <div class="summary-item" style="color: #dc2626;">Belum Hadir: <strong>{{ $belumHadirCount }}</strong></div>
    </div>

    <!-- Tabel Rekap -->
    <table>
        <thead>
            <tr>
                <th style="width: 35px;">No</th>
                <th>Nama Siswa</th>
                <th style="width: 100px;">NIS / NISN</th>
                <th style="width: 60px;">Kelas</th>
                <th style="width: 80px;">Jam Masuk</th>
                <th style="width: 80px;">Jam Pulang</th>
                <th style="width: 110px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $r)
                <tr>
                    <td class="text-center">{{ $r['no'] }}</td>
                    <td><strong>{{ $r['name'] }}</strong></td>
                    <td class="text-center">{{ $r['nisn'] }}</td>
                    <td class="text-center">{{ $r['kelas'] }}</td>
                    <td class="text-center">{{ $r['jam_masuk'] }}</td>
                    <td class="text-center">{{ $r['jam_pulang'] }}</td>
                    <td class="text-center status-badge">
                        @if($r['status'] === 'HADIR')
                            <span style="color: #059669;">HADIR</span>
                        @elseif($r['status'] === 'TERLAMBAT')
                            <span style="color: #d97706;">TERLAMBAT</span>
                        @elseif($r['status'] === 'SUDAH PULANG')
                            <span style="color: #0284c7;">SUDAH PULANG</span>
                        @else
                            <span style="color: #dc2626;">BELUM HADIR</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px;">Tidak ada data presensi pada tanggal ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Tanda Tangan -->
    <div class="ttd-wrapper">
        <div class="ttd-box">
            <div>Pulau Morotai, {{ $formattedDate }}</div>
            <div style="margin-top: 4px;">Petugas Pos Keamanan,</div>
            <div style="height: 60px;"></div>
            <div style="font-weight: bold; text-decoration: underline;">{{ $officerName }}</div>
            <div style="font-size: 10px; color: #555;">NIP/ID: -</div>
        </div>
    </div>
</body>
</html>
<!-- akhir batas suci yang kamu ubah -->

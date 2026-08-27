<!-- awal batas suci yang kamu ubah -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Absensi - {{ $className }} - {{ $mapelName }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #111;
            margin: 0;
            padding: 20px;
            background: #fff;
            line-height: 1.3;
        }
        .header-kop {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 8px;
            margin-bottom: 16px;
        }
        .header-kop h3 {
            margin: 0;
            font-size: 14pt;
            font-weight: normal;
            letter-spacing: 1px;
        }
        .header-kop h2 {
            margin: 2px 0;
            font-size: 16pt;
            font-weight: bold;
        }
        .header-kop h1 {
            margin: 2px 0;
            font-size: 18pt;
            font-weight: 900;
            letter-spacing: 1px;
        }
        .header-kop p {
            margin: 2px 0 0 0;
            font-size: 9.5pt;
            font-style: italic;
        }
        .title-doc {
            text-align: center;
            margin: 14px 0 16px 0;
        }
        .title-doc h4 {
            margin: 0;
            font-size: 13pt;
            text-decoration: underline;
            text-transform: uppercase;
            font-weight: bold;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 14px;
            font-size: 10.5pt;
        }
        .meta-table td {
            padding: 3px 0;
        }
        .stats-grid {
            display: flex;
            gap: 10px;
            margin-bottom: 14px;
        }
        .stat-box {
            flex: 1;
            border: 1px solid #999;
            padding: 6px 10px;
            border-radius: 4px;
            text-align: center;
            font-size: 9.5pt;
            background: #fdfdfd;
        }
        .stat-box strong {
            display: block;
            font-size: 13pt;
            color: #000;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
            margin-bottom: 24px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #333;
            padding: 6px 8px;
        }
        table.data-table th {
            background-color: #f0f0f0;
            text-align: center;
            font-weight: bold;
            font-size: 9.5pt;
        }
        .badge-hadir { font-weight: bold; color: #047857; }
        .badge-terlambat { font-weight: bold; color: #b45309; }
        .badge-izin { font-weight: bold; color: #1d4ed8; }
        .badge-sakit { font-weight: bold; color: #7e22ce; }
        .badge-alpa { font-weight: bold; color: #b91c1c; }
        .badge-belum { color: #6b7280; }
        
        .footer-sig {
            width: 100%;
            margin-top: 30px;
            page-break-inside: avoid;
        }
        .footer-sig td {
            width: 50%;
            vertical-align: top;
            font-size: 10.5pt;
        }
        .btn-print {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #0284c7;
            color: white;
            padding: 10px 18px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: bold;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        @media print {
            .btn-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <button class="btn-print" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>

    <!-- KOP SURAT RESMI -->
    <div class="header-kop">
        <h3>PEMERINTAH PROVINSI MALUKU UTARA</h3>
        <h2>DINAS PENDIDIKAN DAN KEBUDAYAAN</h2>
        <h1>SMA NEGERI 5 KABUPATEN PULAU MOROTAI</h1>
        <p>Alamat: Jl. Pendidikan No. 05, Kab. Pulau Morotai, Maluku Utara | Kode Pos: 97771 | Email: sman5morotai@gmail.com</p>
    </div>

    <!-- JUDUL LAPORAN -->
    <div class="title-doc">
        <h4>REKAPITULASI PRESENSI KEHADIRAN SISWA</h4>
    </div>

    <!-- META DATA -->
    <table class="meta-table">
        <tr>
            <td style="width: 18%;"><strong>Kelas</strong></td>
            <td style="width: 32%;">: {{ $className }}</td>
            <td style="width: 22%;"><strong>Guru Pengampu</strong></td>
            <td style="width: 28%;">: {{ $user->name }}</td>
        </tr>
        <tr>
            <td><strong>Mata Pelajaran</strong></td>
            <td>: {{ $mapelName }}</td>
            <td><strong>Waktu Rekap</strong></td>
            <td>: {{ $todayDate }} WIT</td>
        </tr>
    </table>

    <!-- RINGKASAN KEHADIRAN -->
    <div class="stats-grid">
        <div class="stat-box">Total Siswa<strong>{{ $totalStudents }}</strong></div>
        <div class="stat-box" style="border-color: #059669;">Hadir<strong>{{ $hadirCount }}</strong></div>
        <div class="stat-box" style="border-color: #d97706;">Terlambat<strong>{{ $lateCount }}</strong></div>
        <div class="stat-box" style="border-color: #2563eb;">Izin<strong>{{ $izinCount }}</strong></div>
        <div class="stat-box" style="border-color: #9333ea;">Sakit<strong>{{ $sakitCount }}</strong></div>
        <div class="stat-box" style="border-color: #dc2626;">Alpa<strong>{{ $alpaCount }}</strong></div>
    </div>

    <!-- TABEL DATA SISWA -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 15%;">NIS</th>
                <th style="width: 40%;">Nama Lengkap Siswa</th>
                <th style="width: 20%;">Status Kehadiran</th>
                <th style="width: 20%;">Waktu Scan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
            <tr>
                <td style="text-align: center;">{{ $row['no'] }}</td>
                <td style="text-align: center;">{{ $row['nis'] }}</td>
                <td><strong>{{ $row['name'] }}</strong></td>
                <td style="text-align: center;">
                    @if($row['status'] === 'HADIR')
                        <span class="badge-hadir">HADIR</span>
                    @elseif($row['status'] === 'TERLAMBAT')
                        <span class="badge-terlambat">TERLAMBAT</span>
                    @elseif($row['status'] === 'IZIN')
                        <span class="badge-izin">IZIN</span>
                    @elseif($row['status'] === 'SAKIT')
                        <span class="badge-sakit">SAKIT</span>
                    @elseif($row['status'] === 'ALPA')
                        <span class="badge-alpa">ALPA</span>
                    @else
                        <span class="badge-belum">BELUM ABSEN</span>
                    @endif
                </td>
                <td style="text-align: center;">{{ $row['time'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; color: #777;">Tidak ada data siswa.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- TANDA TANGAN -->
    <table class="footer-sig">
        <tr>
            <td>
                Mengetahui,<br>
                Kepala Sekolah SMA Negeri 5<br><br><br><br>
                <strong><u>Drs. Kepala Sekolah</u></strong><br>
                NIP. 197501011999031001
            </td>
            <td style="text-align: right;">
                Morotai, {{ $todayDate }}<br>
                Guru Mata Pelajaran,<br><br><br><br>
                <strong><u>{{ $user->name }}</u></strong><br>
                NIP. {{ $user->nip ?? '-' }}
            </td>
        </tr>
    </table>
</body>
</html>
<!-- akhir batas suci yang kamu ubah -->

<x-app-layout>
    @section('header_title', 'Dashboard Pos Keamanan & Gerbang')

    <div class="space-y-8 fade-in-up">
        <!-- Banner Header -->
        <div class="relative overflow-hidden rounded-[28px] bg-gradient-to-br from-[#0f172a] via-[#1e293b] to-[#0284c7] p-8 text-white shadow-xl">
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-bold bg-white/10 text-sky-300 border border-white/10 mb-3">
                        🛡️ POS KEAMANAN & GERBANG SEKOLAH
                    </span>
                    <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">Selamat Bertugas, {{ Auth::user()->name }}!</h1>
                    <p class="text-slate-300 text-sm mt-1">Presensi gerbang harian SMA Negeri 5 Pulau Morotai • {{ \Carbon\Carbon::now('Asia/Jayapura')->locale('id')->isoFormat('dddd, D MMMM Y') }}</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('satpam.scan') }}" class="inline-flex items-center px-5 py-3 rounded-2xl bg-sky-500 hover:bg-sky-400 text-white font-bold text-sm shadow-lg shadow-sky-500/30 transition-all">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                        Buka Kamera Scanner
                    </a>
                    <a href="{{ route('satpam.rekap') }}" class="inline-flex items-center px-5 py-3 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-bold text-sm backdrop-blur-sm border border-white/10 transition-all">
                        Rekap Harian
                    </a>
                    <a href="{{ route('satpam.jam-operasional') }}" class="inline-flex items-center px-5 py-3 rounded-2xl bg-amber-500/80 hover:bg-amber-500 text-white font-bold text-sm backdrop-blur-sm border border-amber-400/30 transition-all">
                        ⏰ Atur Jam Operasional
                    </a>
                </div>
            </div>
        </div>

        <!-- 4 Stats Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-5">
            <div class="p-6 rounded-[24px] bg-white border border-slate-100 shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase text-slate-400">Total Siswa</span>
                    <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-700">
                        👥
                    </div>
                </div>
                <div class="mt-4">
                    <div class="text-3xl font-black text-slate-900">{{ $totalSiswa }}</div>
                    <span class="text-[11px] text-slate-400 font-medium">Terdaftar di sistem</span>
                </div>
            </div>

            <div class="p-6 rounded-[24px] bg-white border border-emerald-100 shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase text-emerald-600">Tepat Waktu</span>
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                        ✅
                    </div>
                </div>
                <div class="mt-4">
                    <div class="text-3xl font-black text-emerald-600">{{ $hadirCount }}</div>
                    <span class="text-[11px] text-emerald-600 font-medium">Hadir sebelum 07:30</span>
                </div>
            </div>

            <div class="p-6 rounded-[24px] bg-white border border-amber-100 shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase text-amber-600">Terlambat</span>
                    <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600">
                        ⚠️
                    </div>
                </div>
                <div class="mt-4">
                    <div class="text-3xl font-black text-amber-600">{{ $terlambatCount }}</div>
                    <span class="text-[11px] text-amber-600 font-medium">Hadir setelah 07:30</span>
                </div>
            </div>

            <div class="p-6 rounded-[24px] bg-white border border-rose-100 shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase text-rose-600">Belum Masuk</span>
                    <div class="w-10 h-10 rounded-xl bg-rose-50 flex items-center justify-center text-rose-600">
                        ⏳
                    </div>
                </div>
                <div class="mt-4">
                    <div class="text-3xl font-black text-rose-600">{{ $belumHadir }}</div>
                    <span class="text-[11px] text-rose-500 font-medium">Belum terdeteksi di gerbang</span>
                </div>
            </div>
        </div>

        <!-- Recent Activity Table -->
        <div class="rounded-[24px] bg-white border border-slate-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-extrabold text-slate-900">Aktivitas Pemindaian Gerbang Hari Ini</h2>
                    <p class="text-xs text-slate-500">Log kehadiran langsung saat siswa memindai kartu di gerbang.</p>
                </div>
                <a href="{{ route('satpam.rekap') }}" class="text-xs font-bold text-sky-600 hover:text-sky-700">Lihat Semua →</a>
            </div>

            @if($recentScans->isEmpty())
                <div class="text-center py-12">
                    <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto text-2xl mb-3">🛡️</div>
                    <h3 class="text-sm font-bold text-slate-800">Belum Ada Presensi Hari Ini</h3>
                    <p class="text-xs text-slate-400 mt-1">Aktivitas pemindaian kartu siswa di gerbang akan otomatis muncul di sini.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400 font-bold border-y border-slate-100">
                            <tr>
                                <th class="py-3 px-4">Waktu</th>
                                <th class="py-3 px-4">Nama Siswa</th>
                                <th class="py-3 px-4">NIS / NISN</th>
                                <th class="py-3 px-4">Kelas</th>
                                <th class="py-3 px-4">Tipe</th>
                                <th class="py-3 px-4">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($recentScans as $scan)
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="py-3 px-4 font-mono text-xs font-bold text-slate-800">{{ \Carbon\Carbon::parse($scan->waktu_scan)->format('H:i:s') }} WIT</td>
                                    <td class="py-3 px-4 font-bold text-slate-900">{{ $scan->siswa->user->name ?? 'Siswa' }}</td>
                                    <td class="py-3 px-4 font-mono text-xs">{{ $scan->siswa->nisn ?? $scan->siswa->user->nis ?? '-' }}</td>
                                    <td class="py-3 px-4"><span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-700">{{ $scan->siswa->kelas->nama_kelas ?? '-' }}</span></td>
                                    <td class="py-3 px-4 text-xs font-semibold text-slate-500">{{ $scan->tipe_presensi === 'gerbang_pulang' ? 'Kepulangan' : 'Masuk Gerbang' }}</td>
                                    <td class="py-3 px-4">
                                        @if($scan->status === 'hadir')
                                            <span class="px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-emerald-100 text-emerald-800">Tepat Waktu</span>
                                        @elseif($scan->status === 'terlambat')
                                            <span class="px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-amber-100 text-amber-800">Terlambat</span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-slate-100 text-slate-800">{{ strtoupper($scan->status) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

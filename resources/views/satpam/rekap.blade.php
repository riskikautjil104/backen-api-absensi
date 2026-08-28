<x-app-layout>
    @section('header_title', 'Rekapitulasi Presensi Gerbang Harian')

    <div class="space-y-6 fade-in-up">
        <!-- Header & Filter -->
        <div class="rounded-[24px] bg-white border border-slate-100 shadow-sm p-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-black text-slate-900">Rekap Kehadiran Seluruh Siswa</h2>
                    <p class="text-xs text-slate-500 mt-1">Tanggal: {{ \Carbon\Carbon::today('Asia/Jayapura')->locale('id')->isoFormat('dddd, D MMMM Y') }}</p>
                </div>

                <form method="GET" action="{{ route('satpam.rekap') }}" class="flex flex-wrap items-center gap-3">
                    <select name="kelas_id" class="text-xs rounded-xl border-slate-200 bg-slate-50 font-semibold text-slate-700 py-2.5 px-3">
                        <option value="">Semua Kelas</option>
                        @foreach($kelasList as $k)
                            <option value="{{ $k->id }}" {{ $kelasId == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>

                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari Siswa / NISN..." class="text-xs rounded-xl border-slate-200 bg-slate-50 py-2.5 px-3 w-44" />

                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-slate-900 text-white font-bold text-xs">Filter</button>
                    <button type="button" onclick="window.print()" class="px-4 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs no-print">Cetak Rekap</button>
                </form>
            </div>
        </div>

        <!-- Attendance List -->
        <div class="rounded-[24px] bg-white border border-slate-100 shadow-sm overflow-hidden">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400 font-bold border-b border-slate-100">
                    <tr>
                        <th class="py-3.5 px-5">No</th>
                        <th class="py-3.5 px-5">Nama Siswa</th>
                        <th class="py-3.5 px-5">NIS / NISN</th>
                        <th class="py-3.5 px-5">Kelas</th>
                        <th class="py-3.5 px-5">Jam Masuk</th>
                        <th class="py-3.5 px-5">Jam Pulang</th>
                        <th class="py-3.5 px-5">Status Kehadiran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($rekap as $index => $item)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-5 font-mono text-xs text-slate-400">{{ $index + 1 }}</td>
                            <td class="py-3.5 px-5 font-bold text-slate-900">{{ $item->name }}</td>
                            <td class="py-3.5 px-5 font-mono text-xs">{{ $item->nisn }}</td>
                            <td class="py-3.5 px-5"><span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-700">{{ $item->kelas }}</span></td>
                            <td class="py-3.5 px-5 font-mono text-xs font-semibold text-slate-700">{{ $item->jam_masuk !== '-' ? $item->jam_masuk . ' WIT' : '-' }}</td>
                            <td class="py-3.5 px-5 font-mono text-xs font-semibold text-slate-700">{{ $item->jam_pulang !== '-' ? $item->jam_pulang . ' WIT' : '-' }}</td>
                            <td class="py-3.5 px-5">
                                @if($item->status === 'hadir')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-emerald-100 text-emerald-800">✅ Hadir Tepat Waktu</span>
                                @elseif($item->status === 'terlambat')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-amber-100 text-amber-800">⚠️ Terlambat</span>
                                @elseif($item->status === 'sudah_pulang')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-sky-100 text-sky-800">🏠 Sudah Pulang</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-rose-100 text-rose-800">❌ Belum Hadir</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-10 text-slate-400 text-xs">Tidak ada data siswa yang cocok dengan filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>

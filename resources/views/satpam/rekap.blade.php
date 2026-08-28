<x-app-layout>
    @section('header_title', 'Rekapitulasi Presensi Gerbang Harian')

    <div class="space-y-6 fade-in-up">
        <!-- Header & Filter -->
        <div class="rounded-[24px] bg-white border border-slate-100 shadow-sm p-6">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-black text-slate-900">Rekap Kehadiran Gerbang Siswa</h2>
                    <p class="text-xs text-slate-500 mt-1">Tanggal: <span class="font-bold text-slate-800">{{ \Carbon\Carbon::parse($selectedDate)->locale('id')->isoFormat('dddd, D MMMM Y') }}</span></p>
                </div>

                <form method="GET" action="{{ route('satpam.rekap') }}" class="flex flex-wrap items-center gap-3">
                    <!-- Date Picker -->
                    <div class="flex items-center gap-1.5 bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <input type="date" name="tanggal" value="{{ $selectedDate }}" class="text-xs border-0 bg-transparent font-semibold text-slate-700 p-0 focus:ring-0" />
                    </div>

                    <!-- Class Picker -->
                    <select name="kelas_id" class="text-xs rounded-xl border-slate-200 bg-slate-50 font-semibold text-slate-700 py-2 px-3">
                        <option value="">Semua Kelas</option>
                        @foreach($kelasList as $k)
                            <option value="{{ $k->id }}" {{ $kelasId == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>

                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari Siswa / NISN..." class="text-xs rounded-xl border-slate-200 bg-slate-50 py-2 px-3 w-40" />

                    <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 transition-colors">Filter</button>
                    
                    <!-- Export Actions -->
                    <a href="{{ route('satpam.rekap.excel', ['tanggal' => $selectedDate, 'kelas_id' => $kelasId, 'search' => $search]) }}" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center gap-1.5 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <span>Excel (.CSV)</span>
                    </a>

                    <a href="{{ route('satpam.rekap.pdf', ['tanggal' => $selectedDate, 'kelas_id' => $kelasId, 'search' => $search]) }}" target="_blank" class="px-3.5 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs flex items-center gap-1.5 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        <span>Cetak / PDF</span>
                    </a>
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
                            <td colspan="7" class="text-center py-10 text-slate-400 text-xs">Tidak ada data siswa yang cocok dengan filter tanggal/kelas ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>

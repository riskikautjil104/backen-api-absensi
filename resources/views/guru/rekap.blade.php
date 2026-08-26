<x-app-layout>
    @section('header_title', 'Rekap Kehadiran Siswa')

    <div class="space-y-6 fade-in-up">
        <!-- Filter Card -->
        <div class="apple-card bg-apple-parchment">
            <form action="{{ route('guru.rekap') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div class="space-y-1">
                    <label class="text-[10px] font-semibold text-apple-gray-muted-48 ml-2 uppercase">KELAS</label>
                    <select name="kelas_id" class="block w-full px-3 py-2 bg-white border-none rounded-apple-sm text-[14px] focus:ring-2 focus:ring-apple-blue">
                        <option value="">Semua Kelas</option>
                        @foreach($kelases as $kelas)
                            <option value="{{ $kelas->id }}" {{ request('kelas_id') == $kelas->id ? 'selected' : '' }}>{{ $kelas->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] font-semibold text-apple-gray-muted-48 ml-2 uppercase">MAPEL</label>
                    <select name="mapel_id" class="block w-full px-3 py-2 bg-white border-none rounded-apple-sm text-[14px] focus:ring-2 focus:ring-apple-blue">
                        <option value="">Semua Mapel</option>
                        @foreach($mapels as $mapel)
                            <option value="{{ $mapel->id }}" {{ request('mapel_id') == $mapel->id ? 'selected' : '' }}>{{ $mapel->nama_mapel }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] font-semibold text-apple-gray-muted-48 ml-2 uppercase">TANGGAL</label>
                    <input type="date" name="date" value="{{ request('date') }}" class="block w-full px-3 py-2 bg-white border-none rounded-apple-sm text-[14px] focus:ring-2 focus:ring-apple-blue">
                </div>
                <div class="flex space-x-2">
                    <button type="submit" class="flex-1 apple-button-primary !py-2 !text-[14px]">Filter</button>
                    <a href="{{ route('guru.rekap') }}" class="px-4 py-2 bg-white text-apple-blue border border-apple-blue rounded-full text-[14px] font-bold flex items-center justify-center">Reset</a>
                </div>
            </form>
        </div>

        <!-- Recap Table -->
        <div class="apple-card overflow-hidden !p-0">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-apple-parchment text-[12px] font-semibold text-apple-gray-muted-48 uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-4">Waktu Scan</th>
                            <th class="px-6 py-4">Nama Siswa</th>
                            <th class="px-6 py-4">Kelas</th>
                            <th class="px-6 py-4">Mata Pelajaran</th>
                            <th class="px-6 py-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5">
                        @forelse($absensis as $abs)
                            <tr class="hover:bg-apple-parchment/50 transition-colors">
                                <td class="px-6 py-4 text-apple-gray-muted-48 text-[14px]">
                                    {{ \Carbon\Carbon::parse($abs->waktu_scan)->translatedFormat('d M Y, H:i') }} WIT
                                </td>
                                <td class="px-6 py-4 font-semibold text-[14px] text-apple-ink">
                                    {{ $abs->siswa->user->name }}
                                </td>
                                <td class="px-6 py-4 text-apple-gray-muted-48 text-[14px]">
                                    {{ $abs->jadwal->kelas->nama_kelas }}
                                </td>
                                <td class="px-6 py-4 text-apple-gray-muted-48 text-[14px]">
                                    {{ $abs->jadwal->mapel->nama_mapel }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-[12px] font-bold uppercase tracking-wider">
                                        {{ $abs->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-apple-gray-muted-48 text-[14px]">
                                    Tidak ada data absensi yang ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

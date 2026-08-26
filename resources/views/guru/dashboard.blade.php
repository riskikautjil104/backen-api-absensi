<x-app-layout>
    @section('header_title', 'Guru Dashboard')

    <div class="space-y-10 pb-20 fade-in-up">
        <!-- Guru Hero -->
        <div class="apple-card bg-white border-none shadow-xl flex flex-col md:flex-row items-center justify-between p-8">
            <div class="flex items-center space-x-6">
                <div class="w-20 h-20 rounded-full bg-apple-blue/10 flex items-center justify-center text-apple-blue">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
                <div>
                    <h2 class="text-apple-ink">Selamat Mengajar, <br/>{{ Auth::user()->name }}</h2>
                    <p class="text-apple-gray-muted-48">Mulai harimu dengan mengecek daftar kehadiran siswa.</p>
                </div>
            </div>
            <div class="mt-6 md:mt-0 flex space-x-4">
                @if($schedules->isNotEmpty())
                    @php
                        $activeSchedule = $schedules->first();
                    @endphp
                    <a href="{{ route('guru.scan', $activeSchedule) }}" class="apple-button-primary">MULAI ABSENSI</a>
                @else
                    <button class="apple-button-primary opacity-50 cursor-not-allowed" disabled>MULAI ABSENSI</button>
                @endif
                <a href="{{ route('guru.rekap') }}" class="apple-button-secondary">REKAP LAPORAN</a>
            </div>
        </div>

        <!-- Schedule & Stats -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">
                <div class="apple-card">
                    <h3 class="mb-6">Jadwal Mengajar Hari Ini ({{ $hariSekarang }})</h3>
                    <div class="space-y-4">
                        @forelse($schedules as $sched)
                            <div class="flex items-center justify-between p-4 bg-apple-parchment rounded-[24px] hover:bg-apple-parchment/80 transition-colors">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-white rounded-xl shadow-sm flex items-center justify-center font-bold text-apple-blue text-[13px]">
                                        {{ substr($sched->jam_mulai, 0, 5) }}
                                    </div>
                                    <div>
                                        <p class="font-bold">{{ $sched->mapel->nama_mapel }}</p>
                                        <p class="text-[12px] text-apple-gray-muted-48">{{ $sched->kelas->nama_kelas }} ({{ substr($sched->jam_mulai, 0, 5) }} - {{ substr($sched->jam_selesai, 0, 5) }})</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-center space-x-3">
                                    @php
                                        $nowTime = now()->format('H:i:s');
                                        $isActive = $nowTime >= $sched->jam_mulai && $nowTime <= $sched->jam_selesai;
                                    @endphp
                                    
                                    @if($isActive)
                                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-[10px] font-bold uppercase tracking-wider">Aktif</span>
                                    @endif
                                    
                                    <a href="{{ route('guru.scan', $sched) }}" class="px-4 py-2 bg-apple-blue hover:bg-blue-700 text-white rounded-full text-[12px] font-bold transition-all shadow-sm">
                                        Scan Siswa
                                    </a>
                                    <a href="{{ route('guru.qr', $sched) }}" class="px-4 py-2 bg-[#00aa55] hover:bg-[#008844] text-white rounded-full text-[12px] font-bold transition-all shadow-sm">
                                        Buka QR Absen
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center bg-apple-parchment rounded-[24px] border border-dashed border-black/10">
                                <p class="text-apple-gray-muted-48 text-[14px]">Tidak ada jadwal mengajar untuk hari {{ $hariSekarang }} ini.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="apple-card bg-apple-blue text-white border-none shadow-lg">
                    <h3 class="text-white mb-6">Statistik Mengajar</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center border-b border-white/10 pb-4">
                            <span class="opacity-70">Total Jam/Minggu</span>
                            <span class="font-bold text-xl">24 Jam</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="opacity-70">Kepatuhan Siswa</span>
                            <span class="font-bold text-xl text-green-300">92%</span>
                        </div>
                    </div>
                </div>

                <div class="apple-card bg-[#1d1d1f] text-white border-none">
                    <h3 class="text-white mb-4">Pesan Admin</h3>
                    <p class="text-[14px] opacity-80 leading-relaxed">Rapat evaluasi kurikulum akan diadakan hari Sabtu jam 10:00 WIT di ruang guru sekolah.</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

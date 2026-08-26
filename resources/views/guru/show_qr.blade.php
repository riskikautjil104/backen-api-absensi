<x-app-layout>
    @section('header_title', 'QR Absensi: ' . $jadwal->mapel->nama_mapel)

    <div class="max-w-[700px] mx-auto text-center space-y-6 pb-20 fade-in-up">
        <!-- Back Link -->
        <div class="text-left">
            <a href="{{ route('guru.dashboard') }}" class="text-apple-blue font-semibold text-[14px] flex items-center space-x-1 inline-flex hover:underline">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                <span>Kembali ke Dashboard</span>
            </a>
        </div>

        <!-- QR Display Card -->
        <div class="apple-card flex flex-col items-center justify-center p-12 space-y-8 shadow-2xl relative overflow-hidden bg-white">
            <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-apple-blue to-[#00aa55]"></div>
            
            <div class="space-y-2">
                <p class="text-[12px] font-bold text-apple-gray-muted-48 uppercase tracking-widest">Absensi Kelas Aktif</p>
                <h2 class="text-apple-ink !text-[32px] font-bold">{{ $jadwal->mapel->nama_mapel }}</h2>
                <div class="flex items-center justify-center space-x-3 text-[14px] text-apple-gray-muted-48 mt-1 font-semibold">
                    <span class="px-3 py-1 bg-apple-parchment rounded-full">Kelas: {{ $jadwal->kelas->nama_kelas }}</span>
                    <span>Waktu: {{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }}</span>
                </div>
            </div>

            <!-- QR Code container -->
            <div class="p-6 bg-white rounded-[32px] border-4 border-apple-blue/20 shadow-inner inline-block">
                {!! QrCode::size(280)->generate($payload) !!}
            </div>

            <div class="space-y-4 max-w-[450px]">
                <p class="text-apple-ink text-[17px] leading-relaxed font-semibold">
                    Silakan buka aplikasi mobile SIMORO Anda, masuk ke menu absensi scan QR Code, dan arahkan kamera ke kode di atas.
                </p>
                <p class="text-[13px] text-apple-gray-muted-48 leading-relaxed">
                    Masa berlaku QR Code ini adalah hari ini ({{ \Carbon\Carbon::today()->translatedFormat('d F Y') }}). Kode dienkripsi secara ketat untuk menghindari penyalahgunaan absensi.
                </p>
            </div>
            
            <!-- Live clock tracker -->
            <div class="pt-4 border-t border-black/5 w-full flex items-center justify-center space-x-3 text-apple-blue font-bold text-[14px]">
                <span class="relative flex h-2 w-2">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-apple-blue opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-2 w-2 bg-apple-blue"></span>
                </span>
                <span id="live-clock">--:--:-- WIT</span>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function updateClock() {
            const now = new Date();
            // Convert to WIT (UTC+9)
            const utc = now.getTime() + (now.getTimezoneOffset() * 60000);
            const witOffset = 9;
            const witTime = new Date(utc + (3600000 * witOffset));
            
            const hours = String(witTime.getHours()).padStart(2, '0');
            const minutes = String(witTime.getMinutes()).padStart(2, '0');
            const seconds = String(witTime.getSeconds()).padStart(2, '0');
            
            document.getElementById('live-clock').textContent = `${hours}:${minutes}:${seconds} WIT`;
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
    @endpush
</x-app-layout>

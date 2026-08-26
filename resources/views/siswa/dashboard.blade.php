<x-app-layout>
    @section('header_title', 'Student Workspace')

    <div class="space-y-10 pb-20 fade-in-up">
        <!-- Hero Section -->
        <div class="apple-card bg-gradient-to-br from-[#1e3a8a] to-[#0066cc] text-white border-none shadow-apple-blue/20 p-10 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -translate-y-32 translate-x-32 blur-3xl"></div>
            <div class="relative z-10 space-y-4">
                <h1 class="text-white !text-[48px] md:!text-[64px] leading-tight">Halo, <br/>{{ Auth::user()->name }}</h1>
                <p class="text-white/80 text-[21px] font-light max-w-[500px]">Semangat belajarnya hari ini! Gunakan kartu digital kamu untuk melakukan absensi melalui petugas/guru.</p>
                <div class="flex flex-wrap gap-4 pt-4">
                    <a href="{{ route('siswa.kartu') }}" class="px-8 py-4 bg-white text-apple-blue rounded-full font-bold shadow-xl hover:scale-105 active:scale-95 transition-all">LIHAT KARTU SAYA</a>
                    <a href="{{ route('siswa.profil') }}" class="px-8 py-4 bg-white/10 text-white rounded-full font-bold backdrop-blur-md border border-white/20 hover:bg-white/20 transition-all">LENGKAPI DATA</a>
                </div>
            </div>
        </div>

        <!-- Dashboard Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left: Attendance & Profile -->
            <div class="lg:col-span-2 space-y-8">
                <div class="apple-card grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-green-50 p-6 rounded-[28px] border border-green-100 flex flex-col items-center justify-center space-y-1 group hover:bg-green-100 transition-colors">
                        <span class="text-[32px] font-bold text-green-600">12</span>
                        <span class="text-[10px] font-bold text-green-800 uppercase tracking-widest">Hadir</span>
                    </div>
                    <div class="bg-yellow-50 p-6 rounded-[28px] border border-yellow-100 flex flex-col items-center justify-center space-y-1 group hover:bg-yellow-100 transition-colors">
                        <span class="text-[32px] font-bold text-yellow-600">2</span>
                        <span class="text-[10px] font-bold text-yellow-800 uppercase tracking-widest">Terlambat</span>
                    </div>
                    <div class="bg-red-50 p-6 rounded-[28px] border border-red-100 flex flex-col items-center justify-center space-y-1 group hover:bg-red-100 transition-colors">
                        <span class="text-[32px] font-bold text-red-600">0</span>
                        <span class="text-[10px] font-bold text-red-800 uppercase tracking-widest">Alpha</span>
                    </div>
                </div>

                <div class="apple-card">
                    <div class="flex justify-between items-center mb-6">
                        <h3>Informasi Akademik</h3>
                        <a href="{{ route('siswa.profil') }}" class="text-apple-blue font-bold text-[14px]">Edit Profil ></a>
                    </div>
                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-1">
                            <p class="text-[12px] text-apple-gray-muted-48 font-bold uppercase">NIS</p>
                            <p class="font-semibold text-xl">{{ Auth::user()->nis ?? '-' }}</p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-[12px] text-apple-gray-muted-48 font-bold uppercase">Kelas</p>
                            <p class="font-semibold text-xl">{{ $siswa->kelas->nama_kelas ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Upcoming Class & Library -->
            <div class="space-y-8">
                <div class="apple-card bg-apple-blue text-white border-none shadow-apple-blue/20">
                    <h3 class="text-white mb-6">Mapel Saat Ini</h3>
                    <div class="flex items-center space-x-4 p-4 bg-white/10 rounded-[20px] backdrop-blur-md">
                        <svg class="w-8 h-8 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                        <div>
                            <p class="font-bold">Matematika</p>
                            <p class="text-[12px] opacity-70">Ruang 10 - Bpk. Ahmad</p>
                        </div>
                    </div>
                    <p class="mt-6 text-[12px] opacity-60 text-center uppercase tracking-widest">Mulai: 07:30 WIT</p>
                </div>

                <div class="apple-card bg-[#f59e0b] text-white border-none shadow-orange-500/20">
                    <h3 class="text-white mb-4">Perpustakaan</h3>
                    <div class="flex justify-between items-center">
                        <span class="text-white/80">Pinjam: 0 Buku</span>
                        <a href="{{ route('perpus.index') }}" class="p-2 bg-white/20 rounded-lg hover:bg-white/40 transition-colors text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

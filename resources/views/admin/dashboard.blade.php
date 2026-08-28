<x-app-layout>
    @section('header_title', 'Admin Dashboard Overview')

    @section('header_action')
        <form action="{{ route('admin.sync.all') }}" method="POST" class="inline">
            @csrf
            <button type="submit" class="px-4 py-1.5 bg-apple-blue hover:bg-blue-700 text-white rounded-full text-[12px] font-bold transition-all shadow-sm flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.253 8H18"></path></svg>
                <span>Sync SIMORO</span>
            </button>
        </form>
    @endsection

    <div class="space-y-10 pb-20 fade-in-up">
        <!-- Stats Row -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="apple-card bg-gradient-to-br from-apple-blue to-blue-600 text-white border-none shadow-apple-blue/20">
                <p class="text-[12px] font-bold opacity-80 uppercase tracking-widest mb-1">Total Siswa</p>
                <div class="flex items-end justify-between">
                    <h2 class="text-white !text-[48px]">{{ $stats['siswa'] }}</h2>
                    <div class="mb-2 p-2 bg-white/20 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"></path></svg>
                    </div>
                </div>
            </div>

            <div class="apple-card bg-white border-none shadow-xl">
                <p class="text-[12px] text-apple-gray-muted-48 font-bold uppercase tracking-widest mb-1">Total Guru</p>
                <div class="flex items-end justify-between">
                    <h2 class="text-apple-ink !text-[48px]">{{ $stats['guru'] }}</h2>
                    <div class="mb-2 p-2 bg-apple-blue/10 rounded-lg text-apple-blue">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                </div>
            </div>

            <div class="apple-card bg-white border-none shadow-xl">
                <p class="text-[12px] text-apple-gray-muted-48 font-bold uppercase tracking-widest mb-1">Total Buku</p>
                <div class="flex items-end justify-between">
                    <h2 class="text-apple-ink !text-[48px]">{{ $stats['buku'] }}</h2>
                    <div class="mb-2 p-2 bg-orange-100 rounded-lg text-orange-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    </div>
                </div>
            </div>

            <div class="apple-card bg-[#1d1d1f] text-white border-none shadow-xl">
                <p class="text-[12px] font-bold opacity-80 uppercase tracking-widest mb-1">Hadir Hari Ini</p>
                <div class="flex items-end justify-between">
                    <h2 class="text-white !text-[48px]">84%</h2>
                    <div class="mb-2 p-2 bg-white/10 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">
                <div class="apple-card">
                    <div class="flex justify-between items-center mb-6">
                        <h3>Aksi Cepat Manajemen</h3>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <a href="{{ route('admin.siswa.index') }}" class="flex flex-col items-center p-6 bg-apple-parchment rounded-[24px] hover:bg-apple-blue hover:text-white transition-all group">
                            <div class="w-12 h-12 bg-white rounded-xl shadow-sm flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-apple-ink" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"></path></svg>
                            </div>
                            <span class="text-[12px] font-bold">SISWA</span>
                        </a>
                        <a href="{{ route('admin.guru.index') }}" class="flex flex-col items-center p-6 bg-apple-parchment rounded-[24px] hover:bg-apple-blue hover:text-white transition-all group">
                            <div class="w-12 h-12 bg-white rounded-xl shadow-sm flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-apple-ink" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            </div>
                            <span class="text-[12px] font-bold">GURU</span>
                        </a>
                        <!-- awal batas suci yang kamu ubah -->
                        <a href="{{ route('admin.satpam.index') }}" class="flex flex-col items-center p-6 bg-apple-parchment rounded-[24px] hover:bg-slate-900 hover:text-white transition-all group">
                            <div class="w-12 h-12 bg-white rounded-xl shadow-sm flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-slate-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            </div>
                            <span class="text-[12px] font-bold">SATPAM</span>
                        </a>
                        <!-- akhir batas suci yang kamu ubah -->
                        <a href="{{ route('admin.buku.index') }}" class="flex flex-col items-center p-6 bg-apple-parchment rounded-[24px] hover:bg-apple-blue hover:text-white transition-all group">
                            <div class="w-12 h-12 bg-white rounded-xl shadow-sm flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-apple-ink" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            </div>
                            <span class="text-[12px] font-bold">BUKU</span>
                        </a>
                        <a href="{{ route('admin.kartu.index') }}" class="flex flex-col items-center p-6 bg-apple-blue text-white rounded-[24px] shadow-lg hover:scale-105 transition-all">
                            <div class="w-12 h-12 bg-white rounded-xl shadow-sm flex items-center justify-center mb-3">
                                <svg class="w-6 h-6 text-apple-ink" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </div>
                            <span class="text-[12px] font-bold">CEK KARTU</span>
                        </a>
                    </div>
                </div>

                <div class="apple-card">
                    <h3 class="mb-6">Aktivitas Terbaru</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 bg-apple-parchment rounded-[20px]">
                            <div class="flex items-center space-x-4">
                                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center text-green-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <div>
                                    <p class="font-bold text-[14px]">Siswa Contoh</p>
                                    <p class="text-[12px] text-apple-gray-muted-48">Absensi Matematika - XII IPA 1</p>
                                </div>
                            </div>
                            <p class="text-[12px] font-mono opacity-50">07:15 WIT</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Info -->
            <div class="space-y-6">
                <div class="apple-card bg-gradient-to-b from-yellow-400 to-orange-500 text-white border-none">
                    <h3 class="text-white mb-4">Pengumuman</h3>
                    <p class="text-[14px] leading-relaxed opacity-90">Ujian Tengah Semester akan dilaksanakan pada minggu depan. Harap pastikan semua jadwal sudah terinput dengan benar.</p>
                </div>
                
                <div class="apple-card">
                    <h3 class="mb-4">System Health</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center text-[13px]">
                            <span class="text-apple-gray-muted-48">Database</span>
                            <span class="text-green-600 font-bold uppercase">Online</span>
                        </div>
                        <div class="flex justify-between items-center text-[13px]">
                            <span class="text-apple-gray-muted-48">Storage</span>
                            <span class="text-green-600 font-bold uppercase">94% Free</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

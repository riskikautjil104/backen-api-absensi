<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SMA 5 Morotai') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-white text-apple-ink overflow-x-hidden">
        
        <div class="flex min-h-screen">
            <!-- Desktop Sidebar -->
            <aside class="hidden lg:flex w-64 flex-col bg-apple-canvas border-r border-black/5 fixed h-full z-50">
                <div class="p-6 border-b border-black/5">
                    <a href="/" class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-apple-blue rounded-lg flex items-center justify-center text-white font-bold">5</div>
                        <span class="font-bold tracking-tightest">SMAN 5 MOROTAI</span>
                    </a>
                </div>
                
                <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
                    @auth
                        @if(Auth::user()->isAdmin())
                            <!-- Admin Links -->
                            <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 p-3 rounded-xl hover:bg-apple-blue/5 text-apple-ink {{ request()->routeIs('admin.dashboard') ? 'bg-apple-blue/10 text-apple-blue font-bold' : '' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                                <span>Dashboard</span>
                            </a>
                            <a href="{{ route('admin.guru.index') }}" class="flex items-center space-x-3 p-3 rounded-xl hover:bg-apple-blue/5 text-apple-ink {{ request()->routeIs('admin.guru.*') ? 'bg-apple-blue/10 text-apple-blue font-bold' : '' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                <span>Data Guru</span>
                            </a>
                            <!-- awal batas suci yang kamu ubah -->
                            <a href="{{ route('admin.satpam.index') }}" class="flex items-center space-x-3 p-3 rounded-xl hover:bg-apple-blue/5 text-apple-ink {{ request()->routeIs('admin.satpam.*') ? 'bg-apple-blue/10 text-apple-blue font-bold' : '' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                <span>Petugas Satpam</span>
                            </a>
                            <!-- akhir batas suci yang kamu ubah -->
                            <a href="{{ route('admin.siswa.index') }}" class="flex items-center space-x-3 p-3 rounded-xl hover:bg-apple-blue/5 text-apple-ink {{ request()->routeIs('admin.siswa.*') ? 'bg-apple-blue/10 text-apple-blue font-bold' : '' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"></path></svg>
                                <span>Data Siswa</span>
                            </a>
                            <a href="{{ route('admin.kelas.index') }}" class="flex items-center space-x-3 p-3 rounded-xl hover:bg-apple-blue/5 text-apple-ink {{ request()->routeIs('admin.kelas.*') ? 'bg-apple-blue/10 text-apple-blue font-bold' : '' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                <span>Kelas</span>
                            </a>
                            <a href="{{ route('admin.mapel.index') }}" class="flex items-center space-x-3 p-3 rounded-xl hover:bg-apple-blue/5 text-apple-ink {{ request()->routeIs('admin.mapel.*') ? 'bg-apple-blue/10 text-apple-blue font-bold' : '' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <span>Mata Pelajaran</span>
                            </a>
                            <a href="{{ route('admin.jadwal.index') }}" class="flex items-center space-x-3 p-3 rounded-xl hover:bg-apple-blue/5 text-apple-ink {{ request()->routeIs('admin.jadwal.*') ? 'bg-apple-blue/10 text-apple-blue font-bold' : '' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <span>Jadwal Pelajaran</span>
                            </a>
                            <a href="{{ route('admin.buku.index') }}" class="flex items-center space-x-3 p-3 rounded-xl hover:bg-apple-blue/5 text-apple-ink {{ request()->routeIs('admin.buku.*') ? 'bg-apple-blue/10 text-apple-blue font-bold' : '' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                <span>Buku Perpustakaan</span>
                            </a>
                            <a href="{{ route('admin.kartu.index') }}" class="flex items-center space-x-3 p-3 rounded-xl hover:bg-apple-blue/5 text-apple-ink {{ request()->routeIs('admin.kartu.index') ? 'bg-apple-blue/10 text-apple-blue font-bold' : '' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <span>Cek Kartu</span>
                            </a>
                        @elseif(Auth::user()->isGuru())
                            <!-- Guru Links -->
                            <a href="{{ route('guru.dashboard') }}" class="flex items-center space-x-3 p-3 rounded-xl hover:bg-apple-blue/5 text-apple-ink {{ request()->routeIs('guru.dashboard') ? 'bg-apple-blue/10 text-apple-blue font-bold' : '' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                                <span>Dashboard</span>
                            </a>
                            <a href="{{ route('guru.rekap') }}" class="flex items-center space-x-3 p-3 rounded-xl hover:bg-apple-blue/5 text-apple-ink {{ request()->routeIs('guru.rekap') ? 'bg-apple-blue/10 text-apple-blue font-bold' : '' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 01-2 2h2a2 2 0 012-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                <span>Rekap Absen</span>
                            </a>
                        @elseif(Auth::user()->isSiswa())
                            <!-- Siswa Links -->
                            <a href="{{ route('siswa.dashboard') }}" class="flex items-center space-x-3 p-3 rounded-xl hover:bg-apple-blue/5 text-apple-ink {{ request()->routeIs('siswa.dashboard') ? 'bg-apple-blue/10 text-apple-blue font-bold' : '' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                                <span>Dashboard</span>
                            </a>
                            <a href="{{ route('siswa.kartu') }}" class="flex items-center space-x-3 p-3 rounded-xl hover:bg-apple-blue/5 text-apple-ink {{ request()->routeIs('siswa.kartu') ? 'bg-apple-blue/10 text-apple-blue font-bold' : '' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                <span>Kartu Digital</span>
                            </a>
                        <!-- awal batas suci yang kamu ubah -->
                        @elseif(Auth::user()->isSatpam())
                            <!-- Satpam Links -->
                            <a href="{{ route('satpam.dashboard') }}" class="flex items-center space-x-3 p-3 rounded-xl hover:bg-apple-blue/5 text-apple-ink {{ request()->routeIs('satpam.dashboard') ? 'bg-apple-blue/10 text-apple-blue font-bold' : '' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                                <span>Dashboard</span>
                            </a>
                            <a href="{{ route('satpam.scan') }}" class="flex items-center space-x-3 p-3 rounded-xl hover:bg-apple-blue/5 text-apple-ink {{ request()->routeIs('satpam.scan') ? 'bg-apple-blue/10 text-apple-blue font-bold' : '' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                                <span>Scan Gerbang</span>
                            </a>
                            <a href="{{ route('satpam.rekap') }}" class="flex items-center space-x-3 p-3 rounded-xl hover:bg-apple-blue/5 text-apple-ink {{ request()->routeIs('satpam.rekap') ? 'bg-apple-blue/10 text-apple-blue font-bold' : '' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 01-2 2h2a2 2 0 012-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                <span>Rekap Harian</span>
                            </a>
                        <!-- akhir batas suci yang kamu ubah -->
                        @endif
                    @endauth
                </nav>

                <div class="p-4 border-t border-black/5">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left p-3 text-red-500 hover:bg-red-50 rounded-xl transition-colors text-[14px] font-semibold flex items-center space-x-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </aside>

            <!-- Main Content Area -->
            <div class="flex-1 lg:ml-64 transition-all duration-300">
                <!-- Top Header -->
                <header class="bg-white/80 backdrop-blur-md border-b border-black/5 sticky top-0 z-40 h-16 flex items-center px-4 md:px-8">
                    <h1 class="text-[17px] font-bold tracking-tightest">@yield('header_title', 'Dashboard')</h1>
                    @hasSection('header_action')
                        <div class="ml-6">
                            @yield('header_action')
                        </div>
                    @endif
                    <div class="ml-auto flex items-center space-x-4">
                        <div class="text-right hidden md:block">
                            <p class="text-[12px] font-bold">{{ Auth::user()->name }}</p>
                            <p class="text-[10px] text-apple-gray-muted-48 uppercase tracking-widest">{{ Auth::user()->role }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-apple-blue flex items-center justify-center text-white font-bold text-[14px]">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                    </div>
                </header>

                <!-- Page Content -->
                <main class="p-4 md:p-8">
                    @if(session('success'))
                        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-[20px] text-[13px] font-semibold flex items-center space-x-3 fade-in-up">
                            <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-[20px] text-[13px] font-semibold flex items-center space-x-3 fade-in-up">
                            <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>{{ session('error') }}</span>
                        </div>
                    @endif

                    {{ $slot }}
                </main>
            </div>
        </div>

        <!-- Mobile Bottom Navigation -->
        <div class="lg:hidden">
            @auth
                <div class="apple-bottom-nav fixed bottom-0 left-0 right-0 bg-white/90 backdrop-blur-md border-t border-black/5 h-[65px] flex justify-around items-center px-4 z-50">
                    @if(Auth::user()->isSiswa())
                        <x-bottom-nav-siswa />
                    @elseif(Auth::user()->isGuru())
                        <x-bottom-nav-guru />
                    @elseif(Auth::user()->isAdmin())
                        <x-bottom-nav-admin />
                    @endif
                </div>
            @endauth
        </div>

        @stack('scripts')
    </body>
</html>

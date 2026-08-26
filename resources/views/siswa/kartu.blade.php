<x-app-layout>
    @section('header_title', 'Kartu ATM Digital Siswa')

    <div class="max-w-[800px] mx-auto space-y-12 pb-20 fade-in-up">
        <div class="text-center space-y-4 no-print">
            <h2 class="text-apple-blue font-bold tracking-tight">E-Card SMAN 5 Pulau Morotai</h2>
            <p class="text-apple-gray-muted-48 max-w-[500px] mx-auto">Kartu pintar resmi siswa SMA Negeri 5 Pulau Morotai. Gunakan kartu ini untuk absensi melalui petugas/guru.</p>
            <div class="flex justify-center pt-4">
                <button onclick="window.print()" class="apple-button-primary shadow-apple-blue">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 00-2 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v3m4 0h2"></path></svg>
                    CETAK KARTU DIGITAL (DEPAN & BELAKANG)
                </button>
            </div>
        </div>

        <!-- THE ATM CARD CONTAINER FOR PRINT -->
        <div class="flex flex-col items-center justify-center space-y-12 print-container">
            <!-- Front Card -->
            <div class="atm-card bg-gradient-to-br from-[#1e3a8a] via-[#2563eb] to-[#0066cc] animate-float card-side">
                <div class="absolute inset-0 bg-gradient-to-tr from-white/10 to-transparent pointer-events-none"></div>
                
                <div class="flex justify-between items-start mb-4">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center">
                            <span class="text-apple-blue text-[10px] font-bold">5</span>
                        </div>
                        <span class="text-[10px] font-bold tracking-widest">SMAN 5 MOROTAI</span>
                    </div>
                    <span class="text-[10px] font-medium opacity-80 uppercase tracking-widest">Student Card</span>
                </div>

                <div class="flex justify-between items-center mb-6">
                    <div class="w-10 h-8 bg-gradient-to-br from-yellow-300 to-yellow-600 rounded-md shadow-inner relative overflow-hidden border border-white/20">
                        <div class="absolute inset-0 grid grid-cols-2 gap-px border border-black/10">
                            <div></div><div></div><div></div><div></div>
                        </div>
                    </div>
                    <svg class="w-8 h-8 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071a9.5 9.5 0 0113.434 0m-17.678-4.243a13.5 13.5 0 0119.092 0"></path></svg>
                </div>

                <div class="mb-4">
                    <p class="text-[18px] font-mono tracking-[0.2em] shadow-sm">
                        {{ wordwrap(strtoupper(substr(md5($kartu->token ?? '000'), 0, 16)), 4, ' ', true) }}
                    </p>
                </div>

                <div class="flex justify-between items-end">
                    <div class="space-y-1">
                        <p class="text-[8px] opacity-60 uppercase">Nama Siswa</p>
                        <p class="text-[14px] font-bold tracking-wide uppercase">{{ Auth::user()->name }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[8px] opacity-60 uppercase">Berlaku Sampai</p>
                        <p class="text-[12px] font-bold">12/2028</p>
                    </div>
                </div>
                <div class="absolute bottom-4 right-4 w-12 h-12 bg-white/10 rounded-full blur-md"></div>
            </div>

            <!-- Back Card -->
            <div class="atm-card bg-[#1a1a1a] border border-white/10 card-side">
                <div class="absolute top-6 left-0 w-full h-10 bg-black"></div>
                
                <div class="mt-20 w-3/4 h-8 bg-white/80 rounded flex items-center px-4">
                    <span class="text-black font-handwriting text-[12px]">{{ Auth::user()->name }}</span>
                </div>

                <div class="absolute bottom-6 right-6 p-2 bg-white rounded-lg shadow-xl">
                    @if($kartu)
                        {!! QrCode::size(80)->generate(\Illuminate\Support\Facades\Crypt::encryptString($kartu->token)) !!}
                    @endif
                </div>

                <div class="absolute bottom-6 left-6 max-w-[150px]">
                    <p class="text-[8px] opacity-50 mb-2 leading-tight">Diterbitkan oleh SMA Negeri 5 Pulau Morotai. Kartu ini digunakan untuk identitas dan layanan sekolah.</p>
                    <p class="text-[10px] font-mono tracking-tighter text-white/70">{{ $kartu->token ?? 'NO-TOKEN' }}</p>
                </div>
            </div>
        </div>

        <!-- REKAP DATA -->
        <div class="apple-card no-print">
            <h3 class="mb-6">Informasi Pemegang Kartu</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-4">
                    <div class="flex items-center space-x-4 p-4 bg-apple-parchment rounded-[20px]">
                        <div class="w-16 h-16 rounded-full overflow-hidden border-2 border-white bg-apple-blue flex items-center justify-center text-white font-bold text-xl">
                            @if(Auth::user()->foto)
                                <img src="{{ asset('storage/' . Auth::user()->foto) }}" class="w-full h-full object-cover"/>
                            @else
                                {{ substr(Auth::user()->name, 0, 1) }}
                            @endif
                        </div>
                        <div>
                            <p class="text-[12px] text-apple-gray-muted-48 uppercase font-bold">Nama</p>
                            <p class="font-semibold">{{ Auth::user()->name }}</p>
                        </div>
                    </div>
                    
                    <div class="p-4 bg-apple-parchment rounded-[20px]">
                        <p class="text-[12px] text-apple-gray-muted-48 uppercase font-bold mb-1">Status</p>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-[12px] font-bold bg-green-100 text-green-700">AKTIF</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @font-face {
            font-family: 'Handwriting';
            src: url('https://fonts.googleapis.com/css2?family=Caveat&display=swap');
        }
        .font-handwriting { font-family: 'Caveat', cursive; }

        @media print {
            body { background: white !important; }
            .no-print, nav, footer, aside, header { display: none !important; }
            .lg\:ml-64 { margin-left: 0 !important; }
            .p-4, .md\:p-8 { padding: 0 !important; }
            .print-container { 
                display: block !important; 
                width: 100% !important;
                margin-top: 20px !important;
            }
            .card-side { 
                margin: 0 auto 40px auto !important;
                -webkit-print-color-adjust: exact; 
                print-color-adjust: exact;
                box-shadow: none !important;
                border: 1px solid #ddd !important;
                rotate: 0deg !important;
                animation: none !important;
                page-break-inside: avoid;
            }
        }
    </style>
</x-app-layout>

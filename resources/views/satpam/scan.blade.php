<x-app-layout>
    @section('header_title', 'Scanner Kamera Gerbang Pos Keamanan')

    <div class="max-w-3xl mx-auto space-y-6 fade-in-up">
        <!-- Main Scanner Box -->
        <div class="rounded-[28px] bg-slate-900 text-white p-6 md:p-8 shadow-2xl relative overflow-hidden">
            <!-- Header & Mode Switcher -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 border-b border-slate-800 pb-5">
                <div>
                    <div class="flex items-center space-x-2.5">
                        <span class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="text-xs font-black uppercase tracking-wider text-emerald-400">Scanner Gerbang Aktif</span>
                    </div>
                    <h2 class="text-lg md:text-xl font-black text-white mt-1">Pindai Kartu Pelajar Siswa</h2>
                </div>

                <a href="{{ route('satpam.dashboard') }}" class="inline-flex items-center px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-bold transition-all self-start sm:self-auto">
                    ← Kembali ke Dashboard
                </a>
            </div>

            <!-- Mode Selector: Masuk vs Pulang -->
            <div class="mb-6">
                <div class="text-xs font-bold text-slate-400 mb-2">Pilih Sesi Presensi Gerbang:</div>
                <div class="grid grid-cols-2 gap-3 p-1.5 bg-slate-800/90 rounded-2xl border border-slate-700">
                    <button type="button" id="btnModeMasuk" onclick="setScanMode('masuk')" class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl font-black text-xs md:text-sm transition-all {{ $defaultMode === 'masuk' ? 'bg-sky-600 text-white shadow-lg shadow-sky-600/30' : 'text-slate-400 hover:text-white' }}">
                        <span>🌅 PRESENSI MASUK</span>
                        <span class="text-[10px] opacity-80 font-normal">({{ $todaySchedule->jam_masuk_batas ?? '07:30' }} WIT)</span>
                    </button>
                    <button type="button" id="btnModePulang" onclick="setScanMode('pulang')" class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl font-black text-xs md:text-sm transition-all {{ $defaultMode === 'pulang' ? 'bg-purple-600 text-white shadow-lg shadow-purple-600/30' : 'text-slate-400 hover:text-white' }}">
                        <span>🏠 PRESENSI PULANG</span>
                        <span class="text-[10px] opacity-80 font-normal">({{ $todaySchedule->jam_pulang_mulai ?? '14:00' }} WIT)</span>
                    </button>
                </div>
            </div>

            <!-- Camera Viewport Box -->
            <div class="relative w-full max-w-md mx-auto aspect-square bg-black rounded-2xl overflow-hidden border-2 border-sky-500/50 flex items-center justify-center shadow-inner">
                <div id="reader" class="w-full h-full"></div>
                <div class="absolute inset-0 pointer-events-none border-2 border-dashed border-sky-400/40 rounded-2xl m-6"></div>
            </div>

            <div class="mt-4 text-center">
                <p class="text-xs text-slate-300">Arahkan kamera ke QR Code pada <b>Kartu Pelajar Fisik atau Digital Siswa</b>.</p>
                <p class="text-[11px] text-slate-400 mt-1">Status aktif: <strong id="activeModeLabel" class="text-sky-400 uppercase font-bold">{{ $defaultMode === 'masuk' ? 'Presensi Masuk Gerbang' : 'Presensi Kepulangan Siswa' }}</strong></p>
            </div>
        </div>

        <!-- Pop-up Result Card -->
        <div id="resultCard" class="hidden rounded-[24px] bg-white border border-slate-200 shadow-xl p-6 transition-all transform duration-300">
            <div class="flex items-start sm:items-center space-x-4">
                <div id="resultAvatarBox" class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center text-3xl shrink-0 overflow-hidden border border-slate-200">
                    <span id="resultIcon">✓</span>
                    <img id="studentPhoto" src="" alt="Foto" class="w-full h-full object-cover hidden" />
                </div>
                <div class="flex-1">
                    <div class="flex items-center justify-between">
                        <h3 id="studentName" class="text-lg md:text-xl font-black text-slate-900">Nama Siswa</h3>
                        <span id="scanTime" class="text-xs font-mono font-bold text-slate-400">00:00:00 WIT</span>
                    </div>
                    <p id="studentMeta" class="text-xs text-slate-500 font-semibold mt-0.5">NIS: - • Kelas: -</p>
                    <div class="mt-2.5 flex items-center gap-2">
                        <span id="statusBadge" class="inline-block px-3.5 py-1.5 rounded-full text-xs font-extrabold bg-emerald-100 text-emerald-800">
                            Hadir Tepat Waktu
                        </span>
                        <span id="modeBadge" class="inline-block px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                            Masuk Gerbang
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        let currentMode = '{{ $defaultMode }}';
        let isProcessing = false;

        function setScanMode(mode) {
            currentMode = mode;
            const btnMasuk = document.getElementById('btnModeMasuk');
            const btnPulang = document.getElementById('btnModePulang');
            const label = document.getElementById('activeModeLabel');

            if (mode === 'masuk') {
                btnMasuk.className = 'flex items-center justify-center gap-2 py-3 px-4 rounded-xl font-black text-xs md:text-sm transition-all bg-sky-600 text-white shadow-lg shadow-sky-600/30';
                btnPulang.className = 'flex items-center justify-center gap-2 py-3 px-4 rounded-xl font-black text-xs md:text-sm transition-all text-slate-400 hover:text-white';
                label.innerText = 'Presensi Masuk Gerbang';
                label.className = 'text-sky-400 uppercase font-bold';
            } else {
                btnPulang.className = 'flex items-center justify-center gap-2 py-3 px-4 rounded-xl font-black text-xs md:text-sm transition-all bg-purple-600 text-white shadow-lg shadow-purple-600/30';
                btnMasuk.className = 'flex items-center justify-center gap-2 py-3 px-4 rounded-xl font-black text-xs md:text-sm transition-all text-slate-400 hover:text-white';
                label.innerText = 'Presensi Kepulangan Siswa';
                label.className = 'text-purple-400 uppercase font-bold';
            }
            playBeep(400, 100);
        }

        // Web Audio API Beep
        function playBeep(freq = 800, duration = 150) {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.frequency.value = freq;
                osc.type = 'sine';
                osc.start();
                setTimeout(() => {
                    osc.stop();
                    ctx.close();
                }, duration);
            } catch (e) {}
        }

        function onScanSuccess(decodedText, decodedResult) {
            if (isProcessing) return;
            isProcessing = true;

            fetch("{{ route('satpam.scan') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    token: decodedText,
                    tipe_scan: currentMode
                })
            })
            .then(res => res.json())
            .then(data => {
                const resCard = document.getElementById('resultCard');
                const photoImg = document.getElementById('studentPhoto');
                const iconSpan = document.getElementById('resultIcon');
                const modeBadge = document.getElementById('modeBadge');
                
                resCard.classList.remove('hidden');

                if (data.success) {
                    playBeep(900, 200);
                    document.getElementById('studentName').innerText = data.student.name;
                    document.getElementById('studentMeta').innerText = 'NIS: ' + data.student.nis + ' • Kelas: ' + data.student.kelas;
                    document.getElementById('scanTime').innerText = data.student.time;
                    
                    modeBadge.innerText = currentMode === 'pulang' ? '🏠 Pulang Sekolah' : '🌅 Masuk Gerbang';
                    modeBadge.className = currentMode === 'pulang' ? 'inline-block px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800' : 'inline-block px-2.5 py-1 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800';

                    if (data.student.foto) {
                        photoImg.src = data.student.foto;
                        photoImg.classList.remove('hidden');
                        iconSpan.classList.add('hidden');
                    } else {
                        photoImg.classList.add('hidden');
                        iconSpan.classList.remove('hidden');
                        iconSpan.innerText = '👤';
                    }

                    const badge = document.getElementById('statusBadge');
                    if (currentMode === 'pulang') {
                        badge.innerText = '🏠 Presensi Pulang Berhasil';
                        badge.className = 'inline-block px-3.5 py-1.5 rounded-full text-xs font-extrabold bg-purple-100 text-purple-900 border border-purple-300';
                    } else if (data.student.status === 'terlambat') {
                        badge.innerText = '⚠️ Terlambat Masuk';
                        badge.className = 'inline-block px-3.5 py-1.5 rounded-full text-xs font-extrabold bg-amber-100 text-amber-900 border border-amber-300';
                    } else {
                        badge.innerText = '✅ Hadir Tepat Waktu';
                        badge.className = 'inline-block px-3.5 py-1.5 rounded-full text-xs font-extrabold bg-emerald-100 text-emerald-900 border border-emerald-300';
                    }
                } else {
                    playBeep(300, 300);
                    document.getElementById('studentName').innerText = data.student ? data.student.name : 'Peringatan';
                    document.getElementById('studentMeta').innerText = data.message;
                    document.getElementById('scanTime').innerText = data.student ? data.student.time : '';
                    
                    photoImg.classList.add('hidden');
                    iconSpan.classList.remove('hidden');
                    iconSpan.innerText = '⚠️';

                    const badge = document.getElementById('statusBadge');
                    badge.innerText = '❌ Sudah Terdata / Gagal';
                    badge.className = 'inline-block px-3.5 py-1.5 rounded-full text-xs font-extrabold bg-rose-100 text-rose-800 border border-rose-200';
                }

                // Auto resume scanning after 2.5s
                setTimeout(() => {
                    isProcessing = false;
                }, 2500);
            })
            .catch(err => {
                console.error(err);
                isProcessing = false;
            });
        }

        let html5QrcodeScanner = new Html5QrcodeScanner("reader", { 
            fps: 15, 
            qrbox: { width: 260, height: 260 },
            aspectRatio: 1.0
        }, false);
        html5QrcodeScanner.render(onScanSuccess);
    </script>
    @endpush
</x-app-layout>

<x-app-layout>
    @section('header_title', 'Scanner Kamera Gerbang Pos Keamanan')

    <div class="max-w-2xl mx-auto space-y-6 fade-in-up">
        <div class="rounded-[28px] bg-slate-900 text-white p-6 shadow-xl relative overflow-hidden">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-3">
                    <span class="w-3 h-3 rounded-full bg-emerald-500 animate-ping"></span>
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-400">Kamera Siap Memindai</span>
                </div>
                <a href="{{ route('satpam.dashboard') }}" class="text-xs text-slate-400 hover:text-white">← Kembali</a>
            </div>

            <!-- Video Camera Stream Box -->
            <div class="relative w-full aspect-square bg-black rounded-2xl overflow-hidden border-2 border-sky-500/50 flex items-center justify-center">
                <div id="reader" class="w-full h-full"></div>
                <div class="absolute inset-0 pointer-events-none border-2 border-dashed border-sky-400/40 rounded-2xl m-8"></div>
            </div>

            <div class="mt-4 text-center">
                <p class="text-xs text-slate-300">Arahkan kamera ke QR Code pada <b>Kartu Pelajar Fisik / Digital Siswa</b>.</p>
            </div>
        </div>

        <!-- Pop-up Result Card -->
        <div id="resultCard" class="hidden rounded-[24px] bg-white border border-slate-100 shadow-xl p-6 transition-all">
            <div class="flex items-center space-x-4">
                <div id="resultIcon" class="w-14 h-14 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-2xl font-bold">
                    ✓
                </div>
                <div>
                    <h3 id="studentName" class="text-lg font-black text-slate-900">Nama Siswa</h3>
                    <p id="studentMeta" class="text-xs text-slate-500 font-medium">NISN: - • Kelas: -</p>
                    <span id="statusBadge" class="inline-block mt-2 px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-100 text-emerald-800">
                        Hadir Tepat Waktu
                    </span>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        function onScanSuccess(decodedText, decodedResult) {
            fetch("{{ route('satpam.scan') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ token: decodedText })
            })
            .then(res => res.json())
            .then(data => {
                const resCard = document.getElementById('resultCard');
                resCard.classList.remove('hidden');
                if (data.success) {
                    document.getElementById('studentName').innerText = data.student.name;
                    document.getElementById('studentMeta').innerText = 'NIS: ' + data.student.nis + ' • Kelas: ' + data.student.kelas;
                    document.getElementById('statusBadge').innerText = (data.student.status === 'terlambat' ? '⚠️ Terlambat' : '✅ Hadir') + ' (' + data.student.time + ')';
                    document.getElementById('statusBadge').className = data.student.status === 'terlambat' ? 'inline-block mt-2 px-3 py-1 rounded-full text-xs font-extrabold bg-amber-100 text-amber-800' : 'inline-block mt-2 px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-100 text-emerald-800';
                } else {
                    document.getElementById('studentName').innerText = 'Peringatan';
                    document.getElementById('studentMeta').innerText = data.message;
                    document.getElementById('statusBadge').innerText = 'Gagal';
                    document.getElementById('statusBadge').className = 'inline-block mt-2 px-3 py-1 rounded-full text-xs font-extrabold bg-rose-100 text-rose-800';
                }
            })
            .catch(err => console.error(err));
        }

        let html5QrcodeScanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: { width: 250, height: 250 } }, false);
        html5QrcodeScanner.render(onScanSuccess);
    </script>
    @endpush
</x-app-layout>

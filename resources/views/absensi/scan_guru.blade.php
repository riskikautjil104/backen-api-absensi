<x-app-layout>
    @section('header_title', 'Absensi Kelas: ' . $jadwal->kelas->nama_kelas)

    <div class="max-w-[1000px] mx-auto grid grid-cols-1 lg:grid-cols-2 gap-8 pb-20 fade-in-up">
        <!-- Left: Scanner camera -->
        <div class="space-y-6">
            <!-- Active Subject Info -->
            <div class="apple-card bg-gradient-to-br from-[#1e3a8a] to-[#2563eb] text-white border-none shadow-xl p-6">
                <p class="text-[12px] font-bold opacity-80 uppercase tracking-wider mb-1">Mata Pelajaran Aktif</p>
                <h2 class="text-white !text-[28px] mb-2">{{ $jadwal->mapel->nama_mapel }}</h2>
                <div class="flex items-center space-x-4 text-[13px] opacity-95">
                    <span class="px-2 py-0.5 bg-white/20 rounded-md">Kelas: {{ $jadwal->kelas->nama_kelas }}</span>
                    <span>Waktu: {{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }}</span>
                </div>
            </div>

            <!-- Scanner Camera viewport -->
            <div class="apple-card !p-0 overflow-hidden bg-black aspect-video rounded-[24px] relative shadow-2xl">
                <div id="reader" class="w-full h-full"></div>
                <div id="scanner-overlay" class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <div class="border-2 border-apple-blue/50 w-48 h-48 rounded-apple-lg flex items-center justify-center">
                        <div class="w-full h-full border border-white/20 animate-pulse rounded-apple-lg"></div>
                    </div>
                </div>
                <div id="loading" class="hidden absolute inset-0 bg-black/50 flex items-center justify-center">
                    <div class="animate-spin rounded-full h-10 w-10 border-4 border-apple-blue border-t-transparent"></div>
                </div>
            </div>

            <!-- Manual input if card fails -->
            <div class="apple-card space-y-4">
                <h3 class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-apple-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    <span>Input Manual Token</span>
                </h3>
                <p class="text-apple-gray-muted-48 text-[14px]">Jika kamera bermasalah atau siswa lupa membawa kartu fisik/digital, masukkan token di bawah ini.</p>
                <div class="flex space-x-2">
                    <input type="text" id="manual_token_input" class="flex-1 px-4 py-3 bg-apple-parchment border-none rounded-apple-lg focus:ring-2 focus:ring-apple-blue" placeholder="Masukkan Token Kartu...">
                    <button onclick="checkManualToken()" class="apple-button-primary !py-3">Absen</button>
                </div>
            </div>
        </div>

        <!-- Right: Scanner feedback / student details -->
        <div id="feedback-area" class="apple-card min-h-[400px] flex flex-col justify-center transition-all duration-300">
            <div class="flex flex-col items-center text-center space-y-6">
                <!-- Status icon container (SVG only, no emojis) -->
                <div id="status-container" class="w-20 h-20 flex items-center justify-center rounded-full bg-apple-blue/5 text-apple-blue">
                    <svg id="icon-camera" class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <svg id="icon-success" class="w-12 h-12 text-green-600 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <svg id="icon-error" class="w-12 h-12 text-red-600 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </div>

                <p id="status-text" class="text-apple-gray-muted-48 text-[17px]">Tunjukkan QR Code Kartu ATM Digital Siswa ke Kamera untuk Melakukan Absensi</p>
                
                <div id="student-info" class="hidden animate-in fade-in slide-in-from-bottom-4 duration-500 w-full space-y-4">
                    <div>
                        <h2 id="student-name" class="text-apple-blue text-[28px] font-semibold"></h2>
                        <p id="student-class" class="text-[17px] text-apple-gray-muted-48"></p>
                    </div>
                    <div class="px-6 py-2 bg-green-100 text-green-700 rounded-full inline-block font-semibold text-[14px] uppercase tracking-wider">
                        ABSENSI BERHASIL
                    </div>
                </div>

                <div id="error-info" class="hidden w-full">
                    <div class="px-6 py-3 bg-red-100 text-red-700 rounded-full inline-block font-semibold text-[14px]">
                        GAGAL: <span id="error-message"></span>
                    </div>
                    <button onclick="startScanner()" class="mt-6 text-apple-blue text-[14px] font-semibold underline block mx-auto">Coba Lagi</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        const feedbackArea = document.getElementById('feedback-area');
        const iconCamera = document.getElementById('icon-camera');
        const iconSuccess = document.getElementById('icon-success');
        const iconError = document.getElementById('icon-error');
        const statusText = document.getElementById('status-text');
        const studentInfo = document.getElementById('student-info');
        const errorInfo = document.getElementById('error-info');
        const studentName = document.getElementById('student-name');
        const studentClass = document.getElementById('student-class');
        const errorMessage = document.getElementById('error-message');
        const loading = document.getElementById('loading');

        let isProcessing = false;
        let html5QrCode = null;

        async function onScanSuccess(decodedText) {
            if (isProcessing) return;
            processAttendance(decodedText);
        }

        function checkManualToken() {
            const token = document.getElementById('manual_token_input').value;
            if (token) processAttendance(token);
        }

        async function processAttendance(token) {
            isProcessing = true;
            loading.classList.remove('hidden');

            try {
                const response = await fetch('{{ route("guru.scan", $jadwal) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ token: token })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showSuccess(data.student);
                    speakSuccess(data.student.name, data.student.mapel);
                } else {
                    showError(data.message);
                }
            } catch (error) {
                showError('Gagal terhubung ke server');
            } finally {
                isProcessing = false;
                loading.classList.add('hidden');
            }
        }

        function showSuccess(student) {
            iconCamera.classList.add('hidden');
            iconError.classList.add('hidden');
            iconSuccess.classList.remove('hidden');
            statusText.classList.add('hidden');
            errorInfo.classList.add('hidden');
            studentInfo.classList.remove('hidden');
            studentName.textContent = student.name;
            studentClass.textContent = student.kelas + ' - ' + student.mapel;
            feedbackArea.classList.add('bg-green-50', 'scale-[1.02]');
            setTimeout(resetUI, 4000);
        }

        function showError(message) {
            iconCamera.classList.add('hidden');
            iconSuccess.classList.add('hidden');
            iconError.classList.remove('hidden');
            statusText.classList.add('hidden');
            studentInfo.classList.add('hidden');
            errorInfo.classList.remove('hidden');
            errorMessage.textContent = message;
            feedbackArea.classList.add('bg-red-50');
            setTimeout(resetUI, 4000);
        }

        function resetUI() {
            iconCamera.classList.remove('hidden');
            iconSuccess.classList.add('hidden');
            iconError.classList.add('hidden');
            statusText.classList.remove('hidden');
            studentInfo.classList.add('hidden');
            errorInfo.classList.add('hidden');
            feedbackArea.classList.remove('bg-green-50', 'bg-red-50', 'scale-[1.02]');
        }

        function speakSuccess(name, mapel) {
            if (!window.speechSynthesis) return;
            const utterance = new SpeechSynthesisUtterance(`Absensi atas nama ${name} untuk mata pelajaran ${mapel}, berhasil`);
            utterance.lang = 'id-ID';
            window.speechSynthesis.cancel();
            window.speechSynthesis.speak(utterance);
        }

        function startScanner() {
            if (html5QrCode) {
                html5QrCode.stop().then(() => {
                    initScanner();
                }).catch(() => {
                    initScanner();
                });
            } else {
                initScanner();
            }
        }

        function initScanner() {
            html5QrCode = new Html5Qrcode("reader");
            const config = { fps: 10, qrbox: 250 };
            
            html5QrCode.start({ facingMode: "environment" }, config, onScanSuccess)
                .catch(err => {
                    console.error(err);
                    errorInfo.classList.remove('hidden');
                    errorMessage.textContent = "Kamera tidak dapat diakses. Pastikan izin kamera diberikan.";
                    statusText.textContent = "Gagal memuat kamera.";
                });
        }

        window.addEventListener('load', startScanner);
    </script>
    <style>
        #reader video {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
        }
    </style>
    @endpush
</x-app-layout>

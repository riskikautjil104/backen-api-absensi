<x-app-layout>
    @section('header_title', 'Absensi QR Code')

    <div class="max-w-[800px] mx-auto space-y-4 pb-20">
        <!-- Area Kamera -->
        <div class="relative bg-black aspect-video rounded-apple-lg overflow-hidden shadow-2xl">
            <div id="reader" class="w-full h-full"></div>
            
            <!-- Overlay panduan -->
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <div class="border-2 border-apple-blue/50 w-64 h-64 rounded-apple-lg flex items-center justify-center">
                    <div class="w-full h-full border border-white/20 animate-pulse rounded-apple-lg"></div>
                </div>
            </div>
            <!-- Loading Indicator -->
            <div id="loading" class="hidden absolute inset-0 bg-black/50 flex items-center justify-center">
                 <div class="animate-spin rounded-full h-12 w-12 border-4 border-apple-blue border-t-transparent"></div>
            </div>
        </div>

        <!-- AREA FEEDBACK (WAJIB ADA DI BAWAH KAMERA) -->
        <div id="feedback-area" class="apple-card min-h-[250px] transition-colors duration-300">
            <div class="flex flex-col items-center text-center space-y-4">
                <div id="status-icon" class="text-[64px]">📷</div>
                <h3 id="status-text" class="text-apple-gray-muted-48">Arahkan QR code ke kamera</h3>
                
                <div id="student-info" class="hidden space-y-2 animate-in fade-in slide-in-from-bottom-4 duration-500">
                    <h2 id="student-name" class="text-apple-blue text-[32px]"></h2>
                    <p id="student-class" class="text-[18px] text-apple-gray-muted-48 font-light"></p>
                    <p id="attendance-time" class="text-[14px] text-apple-gray-muted-48"></p>
                    <div class="mt-4 px-4 py-2 bg-green-100 text-green-700 rounded-full inline-block font-semibold">
                        ABSENSI BERHASIL
                    </div>
                </div>

                <div id="error-info" class="hidden space-y-2">
                    <div class="px-4 py-2 bg-red-100 text-red-700 rounded-full inline-block font-semibold">
                        GAGAL: <span id="error-message"></span>
                    </div>
                    <button onclick="startScanner()" class="mt-4 text-apple-blue text-[14px] font-semibold underline">Coba Lagi</button>
                </div>
            </div>
        </div>

        <div id="manual-input" class="apple-card !py-4">
            <p class="text-[12px] text-apple-gray-muted-48 mb-2 text-center">Gunakan input manual jika kamera bermasalah</p>
            <div class="flex space-x-2">
                <input type="text" id="manual_token_input" class="flex-1 bg-apple-parchment border-none rounded-full px-4 text-[14px] focus:ring-1 focus:ring-apple-blue" placeholder="Masukkan Token Kartu...">
                <button onclick="checkManualToken()" class="px-6 py-2 bg-apple-blue text-white rounded-full text-[12px] font-bold">CEK</button>
            </div>
        </div>

        <div class="flex justify-center">
            <a href="/" class="apple-button-secondary">Kembali ke Dashboard</a>
        </div>
    </div>

    @push('scripts')
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        const feedbackArea = document.getElementById('feedback-area');
        const statusIcon = document.getElementById('status-icon');
        const statusText = document.getElementById('status-text');
        const studentInfo = document.getElementById('student-info');
        const errorInfo = document.getElementById('error-info');
        const studentName = document.getElementById('student-name');
        const studentClass = document.getElementById('student-class');
        const attendanceTime = document.getElementById('attendance-time');
        const errorMessage = document.getElementById('error-message');
        const loading = document.getElementById('loading');

        let isProcessing = false;
        let html5QrCode = null;

        async function processAttendance(token) {
            isProcessing = true;
            loading.classList.remove('hidden');

            try {
                const response = await fetch('/absensi/scan', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ token: token })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showSuccess(data.student);
                    speakSuccess(data.student.name);
                } else {
                    showError(data.message);
                }
            } catch (error) {
                console.error(error);
                showError('Gagal terhubung ke server');
            } finally {
                isProcessing = false;
                loading.classList.add('hidden');
            }
        }

        function showSuccess(student) {
            statusIcon.textContent = '✅';
            statusText.classList.add('hidden');
            errorInfo.classList.add('hidden');
            studentInfo.classList.remove('hidden');
            studentName.textContent = student.name;
            studentClass.textContent = student.kelas;
            attendanceTime.textContent = student.time;
            feedbackArea.classList.add('bg-green-50');
            setTimeout(resetUI, 5000);
        }

        function showError(message) {
            statusIcon.textContent = '❌';
            statusText.classList.add('hidden');
            studentInfo.classList.add('hidden');
            errorInfo.classList.remove('hidden');
            errorMessage.textContent = message;
            feedbackArea.classList.add('bg-red-50');
            setTimeout(resetUI, 5000);
        }

        function resetUI() {
            statusIcon.textContent = '📷';
            statusText.classList.remove('hidden');
            studentInfo.classList.add('hidden');
            errorInfo.classList.add('hidden');
            feedbackArea.classList.remove('bg-green-50', 'bg-red-50');
        }

        function speakSuccess(name) {
            if (!window.speechSynthesis) return;
            const utterance = new SpeechSynthesisUtterance(`Absensi atas nama ${name}, berhasil`);
            utterance.lang = 'id-ID';
            window.speechSynthesis.cancel();
            window.speechSynthesis.speak(utterance);
        }

        function checkManualToken() {
            const token = document.getElementById('manual_token_input').value;
            if (token) processAttendance(token);
        }

        function startScanner() {
            if (html5QrCode) {
                html5QrCode.stop().then(initScanner).catch(initScanner);
            } else {
                initScanner();
            }
        }

        function initScanner() {
            html5QrCode = new Html5Qrcode("reader");
            html5QrCode.start(
                { facingMode: "environment" }, 
                { fps: 10, qrbox: 250 }, 
                (text) => {
                    if (!isProcessing) processAttendance(text);
                }
            ).catch(err => {
                errorInfo.classList.remove('hidden');
                errorMessage.textContent = "Kamera tidak dapat diakses.";
                statusText.textContent = "Gagal memuat kamera.";
            });
        }

        setTimeout(startScanner, 1000);
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

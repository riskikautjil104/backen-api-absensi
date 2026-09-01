<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>SMA Negeri 5 Pulau Morotai</title>

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ asset('favicon/favicon-96x96.png') }}" sizes="96x96" />
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon/favicon.svg') }}" />
        <link rel="shortcut icon" href="{{ asset('favicon/favicon.ico') }}" />
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon/apple-touch-icon.png') }}" />
        <meta name="apple-mobile-web-app-title" content="SMAN 5 Morotai" />
        <link rel="manifest" href="{{ asset('favicon/site.webmanifest') }}" />

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            #reader video {
                width: 100% !important;
                height: 100% !important;
                object-fit: cover !important;
                border-radius: 24px;
            }
        </style>
    </head>
    <body class="bg-white text-apple-ink font-sans antialiased">
        <!-- Global Nav -->
        <nav class="bg-black text-white h-[44px] flex items-center px-4 justify-between fixed w-full top-0 z-[100]">
            <div class="flex items-center space-x-3">
                <img src="{{ asset('favicon/favicon-96x96.png') }}" alt="Logo SMAN 5 Morotai" class="w-6 h-6 rounded-md object-contain">
                <span class="font-display font-semibold tracking-tightest">SMA 5 MOROTAI</span>
            </div>
            <div class="flex items-center space-x-6 text-[12px] font-light">
                <a href="#fitur">Fitur</a>
                <a href="{{ route('login') }}" class="apple-button-primary !py-1 !px-4 !text-[12px]">Admin/Siswa Login</a>
            </div>
        </nav>

        <!-- Hero Section with QR Scanner -->
        <section class="min-h-screen flex flex-col items-center justify-center pt-20 px-4 text-center">
            <h1 class="text-[40px] md:text-[64px] font-semibold tracking-tightest leading-tight mb-8">Scan Kehadiran. <br/><span class="text-apple-blue">Tanpa Antri.</span></h1>
            
            <div class="max-w-[600px] w-full space-y-6">
                <!-- Scanner UI -->
                <div class="apple-card !p-0 overflow-hidden shadow-2xl relative bg-black aspect-video rounded-[24px]">
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

                <div id="manual-input" class="mt-4 px-4 py-3 bg-apple-parchment rounded-[24px] border border-black/5">
                    <p class="text-[12px] text-apple-gray-muted-48 mb-2">Gunakan input manual jika kamera bermasalah</p>
                    <div class="flex space-x-2">
                        <input type="text" id="manual_token_input" class="flex-1 bg-white border-none rounded-full px-4 text-[14px] focus:ring-1 focus:ring-apple-blue" placeholder="Masukkan Token Kartu...">
                        <button onclick="checkManualToken()" class="px-6 py-2 bg-apple-blue text-white rounded-full text-[12px] font-bold">CEK</button>
                    </div>
                </div>

                <!-- Feedback Area -->
                <div id="feedback-area" class="apple-card min-h-[180px] transition-all duration-300">
                    <div class="flex flex-col items-center text-center space-y-3">
                        <div id="status-container" class="w-16 h-16 flex items-center justify-center rounded-full bg-apple-blue/5 text-apple-blue">
                            <svg id="icon-camera" class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <svg id="icon-success" class="w-10 h-10 text-green-600 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <svg id="icon-error" class="w-10 h-10 text-red-600 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </div>
                        <p id="status-text" class="text-apple-gray-muted-48 text-[17px]">Arahkan QR code kartu Anda ke kamera</p>
                        
                        <div id="student-info" class="hidden animate-in fade-in slide-in-from-bottom-4 duration-500">
                            <h2 id="student-name" class="text-apple-blue text-[24px] font-semibold"></h2>
                            <p id="student-class" class="text-[17px] text-apple-gray-muted-48"></p>
                            <div class="mt-3 px-4 py-1 bg-green-100 text-green-700 rounded-full inline-block font-semibold text-[14px]">
                                ABSENSI BERHASIL
                            </div>
                        </div>

                        <div id="error-info" class="hidden">
                            <div class="px-4 py-2 bg-red-100 text-red-700 rounded-full inline-block font-semibold text-[14px]">
                                GAGAL: <span id="error-message"></span>
                            </div>
                            <button onclick="startScanner()" class="mt-4 text-apple-blue text-[14px] font-semibold underline">Coba Lagi</button>
                        </div>
                    </div>
                </div>
            </div>

            <p class="text-[14px] text-apple-gray-muted-48 mt-12 font-light max-w-[500px]">Pastikan Anda menggunakan Browser modern (Chrome/Safari) dan memberikan izin akses kamera.</p>
        </section>

        <!-- Features Section -->
        <section id="fitur" class="py-24 bg-apple-parchment">
            <div class="max-w-[1200px] mx-auto px-4 grid grid-cols-1 md:grid-cols-2 gap-12 text-left">
                <div class="apple-card bg-white p-12 flex flex-col justify-between min-h-[300px]">
                    <div class="space-y-4">
                        <svg class="w-12 h-12 text-apple-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1-1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                        <h2 class="text-apple-blue">QR Absensi</h2>
                        <p class="text-[21px] text-apple-gray-muted-48 font-light">Siswa melakukan absensi mandiri melalui petugas sekolah hanya dengan memindai kartu digital.</p>
                    </div>
                </div>

                <div class="apple-card bg-white p-12 flex flex-col justify-between min-h-[300px]">
                    <div class="space-y-4">
                        <svg class="w-12 h-12 text-apple-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        <h2 class="text-apple-blue">Panel Digital</h2>
                        <p class="text-[21px] text-apple-gray-muted-48 font-light">Login untuk melihat riwayat kehadiran, data diri lengkap, dan perpustakaan digital terintegrasi.</p>
                    </div>
                </div>
            </div>
        </section>

        <footer class="bg-white py-12 text-center border-t border-black/5">
            <p class="text-apple-gray-muted-48 text-[14px]">© 2026 SMA Negeri 5 Pulau Morotai. All rights reserved.</p>
        </footer>

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
                        errorMessage.textContent = "Kamera tidak dapat diakses.";
                        statusText.textContent = "Gagal memuat kamera.";
                    });
            }

            window.addEventListener('load', startScanner);
        </script>
    </body>
</html>

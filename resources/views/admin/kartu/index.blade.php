<x-app-layout>
    @section('header_title', 'Verifikasi Kartu Siswa')

    <div class="max-w-[1000px] mx-auto grid grid-cols-1 lg:grid-cols-2 gap-8 pb-20 fade-in-up">
        <!-- Left: Scanner -->
        <div class="space-y-6">
            <div class="apple-card !p-0 overflow-hidden bg-black aspect-square rounded-[32px] relative shadow-2xl">
                <div id="reader" class="w-full h-full"></div>
                <div class="absolute inset-0 border-[40px] border-black/20 pointer-events-none"></div>
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <div class="w-64 h-64 border-2 border-apple-blue rounded-[24px] animate-pulse"></div>
                </div>
            </div>

            <div class="apple-card space-y-4">
                <h3 class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-apple-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    <span>Input Manual</span>
                </h3>
                <p class="text-apple-gray-muted-48 text-[14px]">Gunakan input manual jika kamera bermasalah atau untuk pengecekan cepat.</p>
                <div class="flex space-x-2">
                    <input type="text" id="manual_token" class="flex-1 px-4 py-3 bg-apple-parchment border-none rounded-apple-lg focus:ring-2 focus:ring-apple-blue" placeholder="Masukkan Token Kartu...">
                    <button onclick="checkManual()" class="apple-button-primary !py-3">CEK</button>
                </div>
            </div>
        </div>

        <!-- Right: Student Details -->
        <div id="result-container" class="space-y-6">
            <div class="apple-card h-full flex flex-col items-center justify-center text-center py-20 bg-apple-parchment/50 border-dashed border-2">
                <div class="w-20 h-20 bg-apple-gray-muted/10 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-10 h-10 text-apple-gray-muted-48" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <p class="text-apple-gray-muted-48">Menunggu pemindaian kartu...</p>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        let html5QrCode = new Html5Qrcode("reader");
        let isProcessing = false;

        function onScanSuccess(decodedText) {
            if (isProcessing) return;
            checkToken(decodedText);
        }

        function checkManual() {
            const token = document.getElementById('manual_token').value;
            if (token) checkToken(token);
        }

        async function checkToken(token) {
            isProcessing = true;
            document.getElementById('result-container').innerHTML = `
                <div class="apple-card h-full flex flex-col items-center justify-center py-20">
                    <div class="animate-spin rounded-full h-12 w-12 border-4 border-apple-blue border-t-transparent"></div>
                    <p class="mt-4 text-apple-blue font-bold">Memverifikasi Kartu...</p>
                </div>
            `;

            try {
                const response = await fetch('{{ route("admin.kartu.check") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ token: token })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showResult(data.student);
                } else {
                    showError(data.message);
                }
            } catch (error) {
                showError("Gagal terhubung ke server");
            } finally {
                isProcessing = false;
            }
        }

        function showResult(student) {
            document.getElementById('result-container').innerHTML = `
                <div class="apple-card space-y-8 animate-in fade-in slide-in-from-right-8 duration-500">
                    <div class="flex flex-col items-center">
                        <div class="w-32 h-32 rounded-[24px] overflow-hidden border-4 border-white shadow-xl bg-apple-parchment mb-4">
                            ${student.foto ? `<img src="${student.foto}" class="w-full h-full object-cover"/>` : `<div class="w-full h-full flex items-center justify-center text-4xl font-bold bg-apple-blue text-white">${student.name[0]}</div>`}
                        </div>
                        <h2 class="text-center">${student.name}</h2>
                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-[12px] font-bold mt-2 uppercase tracking-widest">KARTU ${student.status_kartu}</span>
                    </div>

                    <div class="grid grid-cols-1 gap-4">
                        <div class="p-4 bg-apple-parchment rounded-[20px]">
                            <p class="text-[12px] text-apple-gray-muted-48 font-bold uppercase">NIS / Nomor Induk</p>
                            <p class="text-[18px] font-semibold">${student.nis}</p>
                        </div>
                        <div class="p-4 bg-apple-parchment rounded-[20px]">
                            <p class="text-[12px] text-apple-gray-muted-48 font-bold uppercase">Kelas</p>
                            <p class="text-[18px] font-semibold">${student.kelas}</p>
                        </div>
                    </div>

                    <div class="flex flex-col space-y-3">
                        <a href="${student.url_detail}" class="apple-button-primary !w-full">LIHAT PROFIL LENGKAP</a>
                        <button onclick="location.reload()" class="apple-button-secondary !w-full">SCAN KARTU LAIN</button>
                    </div>
                </div>
            `;
        }

        function showError(message) {
            document.getElementById('result-container').innerHTML = `
                <div class="apple-card h-full flex flex-col items-center justify-center py-20 bg-red-50 border-red-100">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-4 text-red-600">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </div>
                    <p class="text-red-700 font-bold">${message}</p>
                    <button onclick="location.reload()" class="mt-6 text-apple-blue font-bold underline">Coba Lagi</button>
                </div>
            `;
        }

        html5QrCode.start({ facingMode: "environment" }, { fps: 10, qrbox: 250 }, onScanSuccess);
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

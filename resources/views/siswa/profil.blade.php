<x-app-layout>
    @section('header_title', 'Lengkapi Profil Saya')

    <div class="max-w-[900px] mx-auto space-y-8 pb-20">
        @if(session('success'))
            <div class="bg-green-50 text-green-700 p-4 rounded-apple-lg border border-green-200 animate-in fade-in slide-in-from-top-4 duration-300">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('siswa.profil.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf @method('PUT')
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left: Photo Upload -->
                <div class="lg:col-span-1">
                    <div class="apple-card flex flex-col items-center text-center space-y-4">
                        <div class="relative group cursor-pointer">
                            <div class="w-32 h-32 rounded-full overflow-hidden bg-apple-parchment flex items-center justify-center border-4 border-white shadow-md">
                                @if(Auth::user()->foto)
                                    <img src="{{ asset('storage/' . Auth::user()->foto) }}" id="preview" class="w-full h-full object-cover"/>
                                @else
                                    <div class="text-apple-blue text-4xl font-semibold uppercase">{{ substr(Auth::user()->name, 0, 1) }}</div>
                                    <img src="" id="preview" class="hidden w-full h-full object-cover"/>
                                @endif
                            </div>
                            <div class="absolute inset-0 bg-black/40 rounded-full opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                                <span class="text-white text-[12px] font-semibold">GANTI FOTO</span>
                            </div>
                            <input type="file" name="foto" id="foto_input" class="absolute inset-0 opacity-0 cursor-pointer" accept="image/*" onchange="previewImage(this)">
                        </div>
                        <div>
                            <h3 class="text-apple-ink">{{ Auth::user()->name }}</h3>
                            <p class="text-apple-gray-muted-48 text-[14px]">Siswa - {{ $siswa->kelas->nama_kelas ?? '-' }}</p>
                        </div>
                        <p class="text-[12px] text-apple-gray-muted-48 px-4">Upload foto formal untuk kartu digital Anda. Maksimal 2MB.</p>
                    </div>
                </div>

                <!-- Right: Form Data -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="apple-card">
                        <h3 class="mb-6">Informasi Pribadi</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-1">
                                <label class="text-[12px] font-semibold text-apple-gray-muted-48 ml-4 uppercase">NAMA LENGKAP</label>
                                <input type="text" name="name" value="{{ Auth::user()->name }}" class="block w-full px-4 py-3 bg-apple-parchment border-none rounded-apple-lg focus:ring-2 focus:ring-apple-blue-focus" required>
                            </div>

                            <div class="space-y-1">
                                <label class="text-[12px] font-semibold text-apple-gray-muted-48 ml-4 uppercase">JENIS KELAMIN</label>
                                <select name="jenis_kelamin" class="block w-full px-4 py-3 bg-apple-parchment border-none rounded-apple-lg focus:ring-2 focus:ring-apple-blue-focus" required>
                                    <option value="L" {{ $siswa->jenis_kelamin == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                    <option value="P" {{ $siswa->jenis_kelamin == 'P' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                            </div>

                            <div class="space-y-1">
                                <label class="text-[12px] font-semibold text-apple-gray-muted-48 ml-4 uppercase">TEMPAT LAHIR</label>
                                <input type="text" name="tempat_lahir" value="{{ $siswa->tempat_lahir }}" class="block w-full px-4 py-3 bg-apple-parchment border-none rounded-apple-lg focus:ring-2 focus:ring-apple-blue-focus" placeholder="Contoh: Morotai">
                            </div>

                            <div class="space-y-1">
                                <label class="text-[12px] font-semibold text-apple-gray-muted-48 ml-4 uppercase">TANGGAL LAHIR</label>
                                <input type="date" name="tanggal_lahir" value="{{ $siswa->tanggal_lahir }}" class="block w-full px-4 py-3 bg-apple-parchment border-none rounded-apple-lg focus:ring-2 focus:ring-apple-blue-focus">
                            </div>
                        </div>
                    </div>

                    <div class="apple-card">
                        <h3 class="mb-6">Kontak & Alamat</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-1">
                                <label class="text-[12px] font-semibold text-apple-gray-muted-48 ml-4 uppercase">NOMOR HP (SISWA)</label>
                                <input type="text" name="nomor_hp" value="{{ $siswa->nomor_hp }}" class="block w-full px-4 py-3 bg-apple-parchment border-none rounded-apple-lg focus:ring-2 focus:ring-apple-blue-focus" placeholder="08xxxxxxxx">
                            </div>

                            <div class="space-y-1">
                                <label class="text-[12px] font-semibold text-apple-gray-muted-48 ml-4 uppercase">WA ORANG TUA</label>
                                <input type="text" name="wa_orang_tua" value="{{ $siswa->wa_orang_tua }}" class="block w-full px-4 py-3 bg-apple-parchment border-none rounded-apple-lg focus:ring-2 focus:ring-apple-blue-focus" placeholder="08xxxxxxxx">
                            </div>

                            <div class="md:col-span-2 space-y-1">
                                <label class="text-[12px] font-semibold text-apple-gray-muted-48 ml-4 uppercase">ALAMAT LENGKAP</label>
                                <textarea name="alamat" rows="3" class="block w-full px-4 py-3 bg-apple-parchment border-none rounded-apple-lg focus:ring-2 focus:ring-apple-blue-focus" placeholder="Jalan..., Desa... ">{{ $siswa->alamat }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="apple-button-primary !px-12 !py-4">SIMPAN PERUBAHAN</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function previewImage(input) {
            const preview = document.getElementById('preview');
            const placeholder = preview.previousElementSibling;
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                    if (placeholder) placeholder.classList.add('hidden');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
    @endpush
</x-app-layout>

<!-- awal batas suci yang kamu ubah -->
<x-app-layout>
    @section('header_title', 'Tambah Petugas Satpam')

    <div class="max-w-[600px] mx-auto">
        <div class="apple-card">
            <form action="{{ route('admin.satpam.store') }}" method="POST" class="space-y-6">
                @csrf
                <div class="space-y-1">
                    <label class="text-[12px] font-semibold text-apple-gray-muted-48 ml-4 uppercase">NAMA LENGKAP PETUGAS</label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Contoh: Bpk. Rusli Hidayat" class="block w-full px-4 py-3 bg-apple-parchment border-none rounded-apple-lg focus:ring-2 focus:ring-apple-blue-focus" required>
                    @error('name') <p class="text-red-500 text-[12px] ml-4 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-1">
                    <label class="text-[12px] font-semibold text-apple-gray-muted-48 ml-4 uppercase">EMAIL LOGIN</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="satpam@sma-n5-morotai.id" class="block w-full px-4 py-3 bg-apple-parchment border-none rounded-apple-lg focus:ring-2 focus:ring-apple-blue-focus" required>
                    @error('email') <p class="text-red-500 text-[12px] ml-4 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-1">
                    <label class="text-[12px] font-semibold text-apple-gray-muted-48 ml-4 uppercase">NIP / ID PETUGAS (OPSIONAL)</label>
                    <input type="text" name="nip" value="{{ old('nip') }}" placeholder="Contoh: 19850123..." class="block w-full px-4 py-3 bg-apple-parchment border-none rounded-apple-lg focus:ring-2 focus:ring-apple-blue-focus">
                    @error('nip') <p class="text-red-500 text-[12px] ml-4 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-1">
                    <label class="text-[12px] font-semibold text-apple-gray-muted-48 ml-4 uppercase">PASSWORD</label>
                    <input type="password" name="password" placeholder="Minimal 6 karakter" class="block w-full px-4 py-3 bg-apple-parchment border-none rounded-apple-lg focus:ring-2 focus:ring-apple-blue-focus" required>
                    @error('password') <p class="text-red-500 text-[12px] ml-4 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="pt-4 flex space-x-4">
                    <button type="submit" class="apple-button-primary flex-1">Simpan Data Satpam</button>
                    <a href="{{ route('admin.satpam.index') }}" class="apple-button-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
<!-- akhir batas suci yang kamu ubah -->

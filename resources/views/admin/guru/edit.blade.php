<x-app-layout>
    @section('header_title', 'Edit Guru')

    <div class="max-w-[600px] mx-auto">
        <div class="apple-card">
            <form action="{{ route('admin.guru.update', $guru) }}" method="POST" class="space-y-6">
                @csrf @method('PUT')
                <div class="space-y-1">
                    <label class="text-[12px] font-semibold text-apple-gray-muted-48 ml-4 uppercase">NAMA LENGKAP</label>
                    <input type="text" name="name" value="{{ $guru->name }}" class="block w-full px-4 py-3 bg-apple-parchment border-none rounded-apple-lg focus:ring-2 focus:ring-apple-blue-focus" required>
                    @error('name') <p class="text-red-500 text-[12px] ml-4 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-1">
                    <label class="text-[12px] font-semibold text-apple-gray-muted-48 ml-4 uppercase">EMAIL</label>
                    <input type="email" name="email" value="{{ $guru->email }}" class="block w-full px-4 py-3 bg-apple-parchment border-none rounded-apple-lg focus:ring-2 focus:ring-apple-blue-focus" required>
                    @error('email') <p class="text-red-500 text-[12px] ml-4 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-1">
                    <label class="text-[12px] font-semibold text-apple-gray-muted-48 ml-4 uppercase">NIP</label>
                    <input type="text" name="nip" value="{{ $guru->nip }}" class="block w-full px-4 py-3 bg-apple-parchment border-none rounded-apple-lg focus:ring-2 focus:ring-apple-blue-focus">
                    @error('nip') <p class="text-red-500 text-[12px] ml-4 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-1">
                    <label class="text-[12px] font-semibold text-apple-gray-muted-48 ml-4 uppercase">PASSWORD (KOSONGKAN JIKA TIDAK DIUBAH)</label>
                    <input type="password" name="password" class="block w-full px-4 py-3 bg-apple-parchment border-none rounded-apple-lg focus:ring-2 focus:ring-apple-blue-focus">
                </div>

                <div class="pt-4 flex space-x-4">
                    <button type="submit" class="apple-button-primary flex-1">Perbarui Data</button>
                    <a href="{{ route('admin.guru.index') }}" class="apple-button-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

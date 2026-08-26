<x-app-layout>
    @section('header_title', 'Edit Siswa')

    <div class="max-w-[800px] mx-auto">
        <div class="apple-card">
            <form action="{{ route('admin.siswa.update', $siswa) }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @csrf @method('PUT')
                <div class="space-y-1">
                    <label class="text-[12px] font-semibold text-apple-gray-muted-48 ml-4 uppercase">NAMA LENGKAP</label>
                    <input type="text" name="name" value="{{ $siswa->user->name }}" class="block w-full px-4 py-3 bg-apple-parchment border-none rounded-apple-lg focus:ring-2 focus:ring-apple-blue-focus" required>
                </div>

                <div class="space-y-1">
                    <label class="text-[12px] font-semibold text-apple-gray-muted-48 ml-4 uppercase">EMAIL</label>
                    <input type="email" name="email" value="{{ $siswa->user->email }}" class="block w-full px-4 py-3 bg-apple-parchment border-none rounded-apple-lg focus:ring-2 focus:ring-apple-blue-focus" required>
                </div>

                <div class="space-y-1">
                    <label class="text-[12px] font-semibold text-apple-gray-muted-48 ml-4 uppercase">NIS</label>
                    <input type="text" name="nis" value="{{ $siswa->user->nis }}" class="block w-full px-4 py-3 bg-apple-parchment border-none rounded-apple-lg focus:ring-2 focus:ring-apple-blue-focus">
                </div>

                <div class="space-y-1">
                    <label class="text-[12px] font-semibold text-apple-gray-muted-48 ml-4 uppercase">PASSWORD (KOSONGKAN JIKA TIDAK DIUBAH)</label>
                    <input type="password" name="password" class="block w-full px-4 py-3 bg-apple-parchment border-none rounded-apple-lg focus:ring-2 focus:ring-apple-blue-focus">
                </div>

                <div class="space-y-1">
                    <label class="text-[12px] font-semibold text-apple-gray-muted-48 ml-4 uppercase">KELAS</label>
                    <select name="kelas_id" class="block w-full px-4 py-3 bg-apple-parchment border-none rounded-apple-lg focus:ring-2 focus:ring-apple-blue-focus" required>
                        @foreach($kelas as $k)
                            <option value="{{ $k->id }}" {{ $siswa->kelas_id == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-[12px] font-semibold text-apple-gray-muted-48 ml-4 uppercase">TEMPAT LAHIR</label>
                    <input type="text" name="tempat_lahir" value="{{ $siswa->tempat_lahir }}" class="block w-full px-4 py-3 bg-apple-parchment border-none rounded-apple-lg focus:ring-2 focus:ring-apple-blue-focus">
                </div>

                <div class="space-y-1">
                    <label class="text-[12px] font-semibold text-apple-gray-muted-48 ml-4 uppercase">TANGGAL LAHIR</label>
                    <input type="date" name="tanggal_lahir" value="{{ $siswa->tanggal_lahir }}" class="block w-full px-4 py-3 bg-apple-parchment border-none rounded-apple-lg focus:ring-2 focus:ring-apple-blue-focus">
                </div>

                <div class="md:col-span-2 pt-4 flex space-x-4">
                    <button type="submit" class="apple-button-primary flex-1">Perbarui Data</button>
                    <a href="{{ route('admin.siswa.index') }}" class="apple-button-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

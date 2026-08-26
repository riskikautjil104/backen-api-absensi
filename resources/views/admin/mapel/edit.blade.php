<x-app-layout>
    @section('header_title', 'Edit Mata Pelajaran')

    <div class="max-w-[600px] mx-auto fade-in-up">
        <div class="apple-card">
            <div class="flex justify-between items-center mb-6">
                <h3 class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-apple-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    <span>Edit Mapel</span>
                </h3>
                <a href="{{ route('admin.mapel.index') }}" class="text-apple-blue text-[14px] font-semibold flex items-center space-x-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    <span>Kembali</span>
                </a>
            </div>

            <form action="{{ route('admin.mapel.update', $mapel) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                
                <div class="space-y-1">
                    <label class="text-[12px] font-semibold text-apple-gray-muted-48 ml-4 uppercase">KODE MAPEL</label>
                    <input type="text" name="kode_mapel" value="{{ old('kode_mapel', $mapel->kode_mapel) }}" class="block w-full px-4 py-3 bg-apple-parchment border-none rounded-apple-lg focus:ring-2 focus:ring-apple-blue" required>
                    @error('kode_mapel')
                        <p class="text-red-500 text-[12px] ml-4 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1">
                    <label class="text-[12px] font-semibold text-apple-gray-muted-48 ml-4 uppercase">NAMA MATA PELAJARAN</label>
                    <input type="text" name="nama_mapel" value="{{ old('nama_mapel', $mapel->nama_mapel) }}" class="block w-full px-4 py-3 bg-apple-parchment border-none rounded-apple-lg focus:ring-2 focus:ring-apple-blue" required>
                    @error('nama_mapel')
                        <p class="text-red-500 text-[12px] ml-4 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full apple-button-primary">Perbarui Mapel</button>
            </form>
        </div>
    </div>
</x-app-layout>

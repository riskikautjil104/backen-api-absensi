<x-app-layout>
    @section('header_title', 'Tambah Buku Baru')

    <div class="max-w-[600px] mx-auto">
        <div class="apple-card">
            <form action="{{ route('admin.buku.store') }}" method="POST" class="space-y-6">
                @csrf
                <div class="space-y-1">
                    <label class="text-[12px] font-semibold text-apple-gray-muted-48 ml-4 uppercase">KODE BUKU</label>
                    <input type="text" name="kode_buku" class="block w-full px-4 py-3 bg-apple-parchment border-none rounded-apple-lg focus:ring-2 focus:ring-apple-blue-focus" required placeholder="Contoh: B-001">
                </div>

                <div class="space-y-1">
                    <label class="text-[12px] font-semibold text-apple-gray-muted-48 ml-4 uppercase">JUDUL BUKU</label>
                    <input type="text" name="judul" class="block w-full px-4 py-3 bg-apple-parchment border-none rounded-apple-lg focus:ring-2 focus:ring-apple-blue-focus" required>
                </div>

                <div class="space-y-1">
                    <label class="text-[12px] font-semibold text-apple-gray-muted-48 ml-4 uppercase">PENULIS</label>
                    <input type="text" name="penulis" class="block w-full px-4 py-3 bg-apple-parchment border-none rounded-apple-lg focus:ring-2 focus:ring-apple-blue-focus">
                </div>

                <div class="space-y-1">
                    <label class="text-[12px] font-semibold text-apple-gray-muted-48 ml-4 uppercase">PENERBIT</label>
                    <input type="text" name="penerbit" class="block w-full px-4 py-3 bg-apple-parchment border-none rounded-apple-lg focus:ring-2 focus:ring-apple-blue-focus">
                </div>

                <div class="space-y-1">
                    <label class="text-[12px] font-semibold text-apple-gray-muted-48 ml-4 uppercase">STOK</label>
                    <input type="number" name="stok" value="1" class="block w-full px-4 py-3 bg-apple-parchment border-none rounded-apple-lg focus:ring-2 focus:ring-apple-blue-focus" required>
                </div>

                <div class="pt-4 flex space-x-4">
                    <button type="submit" class="apple-button-primary flex-1">Simpan Buku</button>
                    <a href="{{ route('admin.buku.index') }}" class="apple-button-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

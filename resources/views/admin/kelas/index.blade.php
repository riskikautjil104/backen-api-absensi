<x-app-layout>
    @section('header_title', 'Kelola Kelas')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Form Tambah -->
        <div class="lg:col-span-1">
            <div class="apple-card sticky top-[72px]">
                <h3 class="mb-6">Tambah Kelas</h3>
                <form action="{{ route('admin.kelas.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="space-y-1">
                        <label class="text-[12px] font-semibold text-apple-gray-muted-48 ml-4 uppercase">NAMA KELAS</label>
                        <input type="text" name="nama_kelas" class="block w-full px-4 py-3 bg-apple-parchment border-none rounded-apple-lg focus:ring-2 focus:ring-apple-blue-focus" required placeholder="Contoh: XII-IPA-1">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[12px] font-semibold text-apple-gray-muted-48 ml-4 uppercase">TAHUN AJARAN</label>
                        <input type="text" name="tahun_ajaran" class="block w-full px-4 py-3 bg-apple-parchment border-none rounded-apple-lg focus:ring-2 focus:ring-apple-blue-focus" required placeholder="Contoh: 2025/2026">
                    </div>
                    <button type="submit" class="w-full apple-button-primary">Simpan Kelas</button>
                </form>
            </div>
        </div>

        <!-- Tabel List -->
        <div class="lg:col-span-2">
            @if(session('success'))
                <div class="bg-green-50 text-green-700 p-4 rounded-apple-lg border border-green-200 mb-6">
                    {{ session('success') }}
                </div>
            @endif

            <div class="apple-card overflow-hidden !p-0">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-apple-parchment text-[12px] font-semibold text-apple-gray-muted-48 uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-4">Nama Kelas</th>
                            <th class="px-6 py-4">Tahun Ajaran</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5">
                        @foreach($kelas as $k)
                        <tr class="hover:bg-apple-parchment/50 transition-colors">
                            <td class="px-6 py-4 font-semibold">{{ $k->nama_kelas }}</td>
                            <td class="px-6 py-4 text-apple-gray-muted-48">{{ $k->tahun_ajaran }}</td>
                            <td class="px-6 py-4 text-right space-x-3">
                                <a href="{{ route('admin.kelas.edit', $k) }}" class="text-apple-blue text-[14px] hover:underline inline-block align-middle">Edit</a>
                                <form action="{{ route('admin.kelas.destroy', $k) }}" method="POST" class="inline-block align-middle">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-500 text-[14px] hover:underline" onclick="return confirm('Hapus kelas ini?')">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

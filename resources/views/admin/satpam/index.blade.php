<!-- awal batas suci yang kamu ubah -->
<x-app-layout>
    @section('header_title', 'Kelola Petugas Keamanan (Satpam)')
    @section('header_action')
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.satpam.create') }}" class="px-4 py-2 bg-apple-blue hover:bg-blue-700 text-white rounded-full text-[12px] font-bold transition-all shadow-sm flex items-center space-x-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Tambah Satpam</span>
            </a>
        </div>
    @endsection

    <div class="space-y-6">
        @if(session('success'))
            <div class="p-4 bg-green-50 border border-green-200 text-green-700 rounded-apple-lg text-[13px] font-semibold flex items-center space-x-2">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <div class="apple-card overflow-hidden !p-0">
            <table class="w-full text-left border-collapse">
                <thead class="bg-apple-parchment text-[12px] font-semibold text-apple-gray-muted-48 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Nama Petugas</th>
                        <th class="px-6 py-4">Email</th>
                        <th class="px-6 py-4">NIP / ID Petugas</th>
                        <th class="px-6 py-4">Peran & Wilayah</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5">
                    @forelse($satpams as $satpam)
                    <tr class="hover:bg-apple-parchment/50 transition-colors">
                        <td class="px-6 py-4 font-semibold flex items-center space-x-3">
                            <div class="w-9 h-9 rounded-full bg-slate-900 text-sky-400 flex items-center justify-center font-bold text-[13px]">
                                🛡️
                            </div>
                            <div>
                                <div class="font-bold text-apple-ink">{{ $satpam->name }}</div>
                                <div class="text-[11px] text-apple-gray-muted-48">Petugas Gerbang Keamanan</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-apple-gray-muted-48">{{ $satpam->email }}</td>
                        <td class="px-6 py-4 text-apple-gray-muted-48">{{ $satpam->nip ?? '-' }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-sky-100 text-sky-800">
                                Pos Gerbang Depan
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right space-x-3">
                            <a href="{{ route('admin.satpam.edit', $satpam) }}" class="text-apple-blue text-[13px] font-semibold hover:underline">Edit</a>
                            <form action="{{ route('admin.satpam.destroy', $satpam) }}" method="POST" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-500 text-[13px] font-semibold hover:underline" onclick="return confirm('Hapus petugas satpam {{ $satpam->name }}?')">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-apple-gray-muted-48">
                            <div class="flex flex-col items-center justify-center space-y-2">
                                <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                <p class="text-[14px] font-semibold">Belum Ada Petugas Satpam</p>
                                <p class="text-[12px]">Klik tombol "Tambah Satpam" di atas untuk membuat akun petugas keamanan baru.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
<!-- akhir batas suci yang kamu ubah -->

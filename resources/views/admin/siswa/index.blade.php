<x-app-layout>
    @section('header_title', 'Kelola Siswa')
    @section('header_action')
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.siswa.create') }}" class="px-4 py-2 bg-apple-blue hover:bg-blue-700 text-white rounded-full text-[12px] font-bold transition-all shadow-sm">Tambah Siswa</a>
            <form action="{{ route('admin.sync.single', 'siswa') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-[#00aa55] hover:bg-[#008844] text-white rounded-full text-[12px] font-bold transition-all shadow-sm flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.253 8H18"></path></svg>
                    <span>Sync SIMORO</span>
                </button>
            </form>
        </div>
    @endsection

    <div class="space-y-6">

        <div class="apple-card overflow-hidden !p-0">
            <table class="w-full text-left border-collapse">
                <thead class="bg-apple-parchment text-[12px] font-semibold text-apple-gray-muted-48 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Nama</th>
                        <th class="px-6 py-4">NIS</th>
                        <th class="px-6 py-4">Kelas</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5">
                    @foreach($siswas as $siswa)
                    <tr class="hover:bg-apple-parchment/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-semibold">{{ $siswa->user->name }}</div>
                            <div class="text-[12px] text-apple-gray-muted-48">{{ $siswa->user->email }}</div>
                        </td>
                        <td class="px-6 py-4 text-apple-gray-muted-48">{{ $siswa->user->nis ?? '-' }}</td>
                        <td class="px-6 py-4 text-apple-gray-muted-48">{{ $siswa->kelas->nama_kelas ?? '-' }}</td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ route('admin.siswa.edit', $siswa) }}" class="text-apple-blue text-[14px]">Edit</a>
                            <form action="{{ route('admin.siswa.destroy', $siswa) }}" method="POST" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-500 text-[14px]" onclick="return confirm('Hapus siswa ini?')">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>

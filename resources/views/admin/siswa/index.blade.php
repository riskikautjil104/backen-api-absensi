<x-app-layout>
    @section('header_title', 'Kelola Siswa')
    @section('header_action')
        <a href="{{ route('admin.siswa.create') }}" class="apple-button-primary !py-2 !px-4 !text-[14px]">Tambah Siswa</a>
    @endsection

    <div class="space-y-6">
        @if(session('success'))
            <div class="bg-green-50 text-green-700 p-4 rounded-apple-lg border border-green-200">
                {{ session('success') }}
            </div>
        @endif

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

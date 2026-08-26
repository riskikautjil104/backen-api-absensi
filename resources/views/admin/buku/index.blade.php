<x-app-layout>
    @section('header_title', 'Kelola Koleksi Buku')
    @section('header_action')
        <a href="{{ route('admin.buku.create') }}" class="apple-button-primary !py-2 !px-4 !text-[14px]">Tambah Buku</a>
    @endsection

    <div class="space-y-6">
        @if(session('success'))
            <div class="bg-green-50 text-green-700 p-4 rounded-apple-lg border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($bukus as $buku)
            <div class="apple-card flex flex-col justify-between">
                <div>
                    <div class="w-full aspect-[3/4] bg-apple-parchment rounded-apple-sm mb-4 flex items-center justify-center overflow-hidden">
                        {!! QrCode::size(120)->generate($buku->qr_token) !!}
                    </div>
                    <h3 class="line-clamp-2">{{ $buku->judul }}</h3>
                    <p class="text-[14px] text-apple-gray-muted-48">{{ $buku->penulis ?? 'Penulis Anonim' }}</p>
                    <div class="mt-2 text-[12px] font-semibold text-apple-blue">STOK: {{ $buku->stok }}</div>
                </div>
                <div class="mt-4 flex justify-between items-center pt-4 border-t border-black/5">
                    <a href="{{ route('admin.buku.edit', $buku) }}" class="text-apple-blue text-[14px]">Edit</a>
                    <form action="{{ route('admin.buku.destroy', $buku) }}" method="POST" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-500 text-[14px]" onclick="return confirm('Hapus buku ini?')">Hapus</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</x-app-layout>

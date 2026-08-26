<x-app-layout>
    @section('header_title', 'Perpustakaan Digital')

    <div class="space-y-8">
        <!-- Search Tile -->
        <div class="apple-card bg-apple-parchment py-12 text-center">
            <h1 class="text-apple-blue mb-6">Cari Ilmu.</h1>
            <div class="max-w-[600px] mx-auto relative">
                <input type="text" placeholder="Cari judul buku atau penulis..." class="w-full h-[52px] px-12 bg-white border-none rounded-full shadow-sm focus:ring-2 focus:ring-apple-blue-focus">
                <svg class="w-6 h-6 absolute left-4 top-[14px] text-apple-gray-muted-48" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
        </div>

        <!-- Book Collection -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @php
                $bukus = \App\Models\Buku::all();
            @endphp
            @foreach($bukus as $b)
            <div class="apple-card flex flex-col justify-between hover:shadow-lg transition-shadow">
                <div>
                    <div class="w-full aspect-[3/4] bg-apple-parchment rounded-apple-sm mb-4 flex items-center justify-center overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1543002588-bfa74002ed7e?auto=format&fit=crop&q=80&w=300" alt="Book Cover" class="w-full h-full object-cover opacity-50"/>
                    </div>
                    <h3 class="line-clamp-2 text-[17px]">{{ $b->judul }}</h3>
                    <p class="text-[14px] text-apple-gray-muted-48">{{ $b->penulis ?? 'Penulis Anonim' }}</p>
                    <div class="mt-2 text-[12px] font-semibold {{ $b->stok > 0 ? 'text-green-600' : 'text-red-500' }}">
                        {{ $b->stok > 0 ? 'TERSEDIA: ' . $b->stok : 'STOK HABIS' }}
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-black/5">
                    @if($b->stok > 0)
                        <button class="w-full apple-button-primary !py-2 !text-[14px]">Pinjam Buku</button>
                    @else
                        <button class="w-full bg-apple-gray-muted text-white rounded-full py-2 text-[14px] cursor-not-allowed" disabled>Stok Habis</button>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    @section('header_title', 'Detail Siswa: ' . $siswa->user->name)

    <div class="max-w-[900px] mx-auto space-y-8 pb-20 fade-in-up">
        <div class="apple-card flex flex-col md:flex-row items-center space-y-6 md:space-y-0 md:space-x-8">
            <div class="w-32 h-32 rounded-[32px] overflow-hidden border-4 border-white shadow-xl bg-apple-parchment">
                @if($siswa->user->foto)
                    <img src="{{ asset('storage/' . $siswa->user->foto) }}" class="w-full h-full object-cover"/>
                @else
                    <div class="w-full h-full flex items-center justify-center bg-apple-blue text-white text-4xl font-bold">{{ $siswa->user->name[0] }}</div>
                @endif
            </div>
            <div class="flex-1 text-center md:text-left space-y-2">
                <h2 class="text-apple-blue">{{ $siswa->user->name }}</h2>
                <div class="flex flex-wrap justify-center md:justify-start gap-4 text-[14px]">
                    <span class="px-4 py-1 bg-apple-blue/10 text-apple-blue rounded-full font-bold uppercase tracking-wider">NIS: {{ $siswa->user->nis ?? '-' }}</span>
                    <span class="px-4 py-1 bg-apple-parchment text-apple-gray-muted-48 rounded-full font-bold uppercase tracking-wider">Kelas: {{ $siswa->kelas->nama_kelas ?? '-' }}</span>
                </div>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('admin.siswa.edit', $siswa) }}" class="apple-button-secondary !py-2 !px-6">EDIT</a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="apple-card space-y-6">
                <h3>Informasi Pribadi</h3>
                <div class="space-y-4 text-[14px]">
                    <div class="flex justify-between border-b border-black/5 pb-2">
                        <span class="text-apple-gray-muted-48">Jenis Kelamin</span>
                        <span class="font-semibold">{{ $siswa->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                    </div>
                    <div class="flex justify-between border-b border-black/5 pb-2">
                        <span class="text-apple-gray-muted-48">Tempat, Tgl Lahir</span>
                        <span class="font-semibold">{{ $siswa->tempat_lahir ?? '-' }}, {{ $siswa->tanggal_lahir ? date('d M Y', strtotime($siswa->tanggal_lahir)) : '-' }}</span>
                    </div>
                    <div class="flex justify-between border-b border-black/5 pb-2">
                        <span class="text-apple-gray-muted-48">Alamat</span>
                        <span class="font-semibold text-right max-w-[200px]">{{ $siswa->alamat ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <div class="apple-card space-y-6">
                <h3>Kontak & Orang Tua</h3>
                <div class="space-y-4 text-[14px]">
                    <div class="flex justify-between border-b border-black/5 pb-2">
                        <span class="text-apple-gray-muted-48">No. HP Siswa</span>
                        <span class="font-semibold">{{ $siswa->nomor_hp ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between border-b border-black/5 pb-2">
                        <span class="text-apple-gray-muted-48">WA Orang Tua</span>
                        <span class="font-semibold">{{ $siswa->wa_orang_tua ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-apple-gray-muted-48">Email Akun</span>
                        <span class="font-semibold">{{ $siswa->user->email }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="apple-card">
            <h3 class="mb-6">Riwayat Kehadiran (Bulan Ini)</h3>
            <div class="h-[200px] flex items-center justify-center text-apple-gray-muted-48">
                <p>Belum ada riwayat kehadiran tercatat.</p>
            </div>
        </div>
    </div>
</x-app-layout>

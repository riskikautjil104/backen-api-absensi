<x-app-layout>
    @section('header_title', 'Kelola Jadwal Pelajaran')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Form Tambah -->
        <div class="lg:col-span-1">
            <div class="apple-card sticky top-[72px]">
                <h3 class="mb-6">Tambah Jadwal</h3>
                <form action="{{ route('admin.jadwal.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="space-y-1">
                        <label class="text-[12px] font-semibold text-apple-gray-muted-48 ml-4 uppercase">GURU</label>
                        <select name="guru_id" id="guru_select" class="block w-full px-4 py-3 bg-apple-parchment border-none rounded-apple-lg" required>
                            <option value="">Pilih Guru</option>
                            @foreach($gurus as $g) <option value="{{ $g->id }}">{{ $g->name }}</option> @endforeach
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[12px] font-semibold text-apple-gray-muted-48 ml-4 uppercase">KELAS</label>
                        <select name="kelas_id" id="kelas_select" class="block w-full px-4 py-3 bg-apple-parchment border-none rounded-apple-lg" required disabled>
                            <option value="">Pilih Guru Dahulu</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[12px] font-semibold text-apple-gray-muted-48 ml-4 uppercase">MATA PELAJARAN</label>
                        <select name="mapel_id" id="mapel_select" class="block w-full px-4 py-3 bg-apple-parchment border-none rounded-apple-lg" required disabled>
                            <option value="">Pilih Guru Dahulu</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[12px] font-semibold text-apple-gray-muted-48 ml-4 uppercase">HARI</label>
                        <select name="hari" class="block w-full px-4 py-3 bg-apple-parchment border-none rounded-apple-lg" required>
                            @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $h) <option value="{{ $h }}">{{ $h }}</option> @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[12px] font-semibold text-apple-gray-muted-48 ml-4 uppercase">MULAI</label>
                            <input type="time" name="jam_mulai" class="block w-full px-4 py-3 bg-apple-parchment border-none rounded-apple-lg" required>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[12px] font-semibold text-apple-gray-muted-48 ml-4 uppercase">SELESAI</label>
                            <input type="time" name="jam_selesai" class="block w-full px-4 py-3 bg-apple-parchment border-none rounded-apple-lg" required>
                        </div>
                    </div>
                    <button type="submit" class="w-full apple-button-primary">Simpan Jadwal</button>
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
                            <th class="px-6 py-4">Hari & Jam</th>
                            <th class="px-6 py-4">Kelas & Mapel</th>
                            <th class="px-6 py-4">Guru</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5">
                        @foreach($jadwals as $j)
                        <tr class="hover:bg-apple-parchment/50 transition-colors">
                            <td class="px-6 py-4 font-semibold">
                                {{ $j->hari }} <br/>
                                <span class="text-[12px] text-apple-gray-muted-48">{{ $j->jam_mulai }} - {{ $j->jam_selesai }}</span>
                            </td>
                            <td class="px-6 py-4">
                                {{ $j->kelas->nama_kelas }} <br/>
                                <span class="text-[12px] text-apple-blue font-semibold">{{ $j->mapel->nama_mapel }}</span>
                            </td>
                            <td class="px-6 py-4 text-apple-gray-muted-48">{{ $j->guru->name }}</td>
                            <td class="px-6 py-4 text-right space-x-3">
                                <a href="{{ route('admin.jadwal.edit', $j) }}" class="text-apple-blue text-[14px] hover:underline inline-block align-middle">Edit</a>
                                <form action="{{ route('admin.jadwal.destroy', $j) }}" method="POST" class="inline-block align-middle">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-500 text-[14px] hover:underline" onclick="return confirm('Hapus jadwal ini?')">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    <script>
        document.getElementById('guru_select').addEventListener('change', function () {
            const guruId = this.value;
            const kelasSelect = document.getElementById('kelas_select');
            const mapelSelect = document.getElementById('mapel_select');

            if (!guruId) {
                kelasSelect.innerHTML = '<option value="">Pilih Guru Dahulu</option>';
                kelasSelect.disabled = true;
                mapelSelect.innerHTML = '<option value="">Pilih Guru Dahulu</option>';
                mapelSelect.disabled = true;
                return;
            }

            const url = "{{ url('/admin/jadwal/guru-relations') }}/" + guruId;
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    // Update Kelas Select
                    kelasSelect.innerHTML = '<option value="">Pilih Kelas</option>';
                    if (data.kelas.length > 0) {
                        data.kelas.forEach(k => {
                            kelasSelect.innerHTML += `<option value="${k.id}">${k.nama_kelas}</option>`;
                        });
                        kelasSelect.disabled = false;
                    } else {
                        kelasSelect.innerHTML = '<option value="">Tidak ada kelas terkait guru ini</option>';
                        kelasSelect.disabled = true;
                    }

                    // Update Mapel Select
                    mapelSelect.innerHTML = '<option value="">Pilih Mapel</option>';
                    if (data.mapels.length > 0) {
                        data.mapels.forEach(m => {
                            mapelSelect.innerHTML += `<option value="${m.id}">${m.nama_mapel}</option>`;
                        });
                        mapelSelect.disabled = false;
                    } else {
                        mapelSelect.innerHTML = '<option value="">Tidak ada mapel terkait guru ini</option>';
                        mapelSelect.disabled = true;
                    }
                })
                .catch(error => {
                    console.error('Error fetching relations:', error);
                });
        });
    </script>
</x-app-layout>

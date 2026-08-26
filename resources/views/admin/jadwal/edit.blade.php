<x-app-layout>
    @section('header_title', 'Edit Jadwal Pelajaran')

    <div class="max-w-[700px] mx-auto fade-in-up">
        <div class="apple-card">
            <div class="flex justify-between items-center mb-6">
                <h3 class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-apple-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span>Edit Jadwal</span>
                </h3>
                <a href="{{ route('admin.jadwal.index') }}" class="text-apple-blue text-[14px] font-semibold flex items-center space-x-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    <span>Kembali</span>
                </a>
            </div>

            <form action="{{ route('admin.jadwal.update', $jadwal) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="space-y-1">
                    <label class="text-[12px] font-semibold text-apple-gray-muted-48 ml-4 uppercase">GURU MATA PELAJARAN</label>
                    <select name="guru_id" id="guru_select" class="block w-full px-4 py-3 bg-apple-parchment border-none rounded-apple-lg focus:ring-2 focus:ring-apple-blue" required>
                        <option value="">Pilih Guru</option>
                        @foreach($gurus as $g)
                            <option value="{{ $g->id }}" {{ old('guru_id', $jadwal->guru_id) == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-[12px] font-semibold text-apple-gray-muted-48 ml-4 uppercase">KELAS</label>
                        <select name="kelas_id" id="kelas_select" class="block w-full px-4 py-3 bg-apple-parchment border-none rounded-apple-lg focus:ring-2 focus:ring-apple-blue" required disabled>
                            <option value="">Pilih Guru Dahulu</option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[12px] font-semibold text-apple-gray-muted-48 ml-4 uppercase">MATA PELAJARAN</label>
                        <select name="mapel_id" id="mapel_select" class="block w-full px-4 py-3 bg-apple-parchment border-none rounded-apple-lg focus:ring-2 focus:ring-apple-blue" required disabled>
                            <option value="">Pilih Guru Dahulu</option>
                        </select>
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-[12px] font-semibold text-apple-gray-muted-48 ml-4 uppercase">HARI</label>
                    <select name="hari" class="block w-full px-4 py-3 bg-apple-parchment border-none rounded-apple-lg focus:ring-2 focus:ring-apple-blue" required>
                        @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $h)
                            <option value="{{ $h }}" {{ old('hari', $jadwal->hari) == $h ? 'selected' : '' }}>{{ $h }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-[12px] font-semibold text-apple-gray-muted-48 ml-4 uppercase">JAM MULAI</label>
                        <input type="time" name="jam_mulai" value="{{ old('jam_mulai', substr($jadwal->jam_mulai, 0, 5)) }}" class="block w-full px-4 py-3 bg-apple-parchment border-none rounded-apple-lg focus:ring-2 focus:ring-apple-blue" required>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[12px] font-semibold text-apple-gray-muted-48 ml-4 uppercase">JAM SELESAI</label>
                        <input type="time" name="jam_selesai" value="{{ old('jam_selesai', substr($jadwal->jam_selesai, 0, 5)) }}" class="block w-full px-4 py-3 bg-apple-parchment border-none rounded-apple-lg focus:ring-2 focus:ring-apple-blue" required>
                    </div>
                </div>

                <button type="submit" class="w-full apple-button-primary mt-4">Perbarui Jadwal</button>
            </form>
        </div>
    <script>
        function loadRelations(guruId, selectedKelasId = null, selectedMapelId = null) {
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
                            const isSelected = selectedKelasId == k.id ? 'selected' : '';
                            kelasSelect.innerHTML += `<option value="${k.id}" ${isSelected}>${k.nama_kelas}</option>`;
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
                            const isSelected = selectedMapelId == m.id ? 'selected' : '';
                            mapelSelect.innerHTML += `<option value="${m.id}" ${isSelected}>${m.nama_mapel}</option>`;
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
        }

        // On Load
        const initialGuruId = "{{ $jadwal->guru_id }}";
        const initialKelasId = "{{ $jadwal->kelas_id }}";
        const initialMapelId = "{{ $jadwal->mapel_id }}";
        loadRelations(initialGuruId, initialKelasId, initialMapelId);

        // On Change
        document.getElementById('guru_select').addEventListener('change', function () {
            loadRelations(this.value);
        });
    </script>
</x-app-layout>

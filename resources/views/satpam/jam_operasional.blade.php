<x-app-layout>
    @section('header_title', 'Pengaturan Jam Operasional Gerbang')

    <div class="space-y-6 fade-in-up">
        <!-- Header -->
        <div class="rounded-[24px] bg-slate-900 text-white p-6 md:p-8 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-500/20 text-sky-400 text-xs font-bold mb-3">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>Jadwal Harian Pos Gerbang</span>
                    </div>
                    <h2 class="text-2xl font-black">Pengaturan Jam Tepat Waktu & Pulang (Senin - Minggu)</h2>
                    <p class="text-sm text-slate-300 mt-1">Atur batas waktu kehadiran agar status siswa otomatis tercatat tepat waktu vs terlambat untuk setiap hari dalam seminggu.</p>
                </div>
                <div class="bg-slate-800/80 border border-slate-700 rounded-2xl p-4 text-center md:text-right min-w-[200px]">
                    <span class="text-xs text-slate-400 font-semibold block">Jadwal Aktif Hari Ini:</span>
                    <span class="text-base font-black text-sky-400 block mt-0.5">{{ $todaySchedule->nama_hari ?? 'Hari Ini' }}</span>
                    <span class="text-xs text-slate-200 block mt-1">Batas Tepat Waktu: <strong class="text-emerald-400 font-mono">{{ $todaySchedule->jam_masuk_batas ?? '07:30' }} WIT</strong></span>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-4 text-emerald-800 text-sm font-semibold flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Form 7 Hari -->
        <form method="POST" action="{{ route('satpam.jam-operasional.update') }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                @foreach($schedules as $s)
                    @php
                        $isToday = strtolower(\Carbon\Carbon::now('Asia/Jayapura')->locale('id')->dayName) === $s->hari;
                    @endphp
                    <div class="rounded-[20px] bg-white border {{ $isToday ? 'border-sky-500 ring-2 ring-sky-200' : 'border-slate-200' }} shadow-sm p-5 flex flex-col justify-between transition-all hover:shadow-md">
                        <div>
                            <!-- Card Header -->
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                                <div class="flex items-center gap-2">
                                    <span class="w-8 h-8 rounded-xl {{ $isToday ? 'bg-sky-600 text-white' : 'bg-slate-100 text-slate-700' }} flex items-center justify-center font-black text-xs">
                                        {{ substr($s->nama_hari, 0, 3) }}
                                    </span>
                                    <div>
                                        <h3 class="font-black text-slate-900 text-base leading-tight">{{ $s->nama_hari }}</h3>
                                        @if($isToday)
                                            <span class="inline-block text-[10px] font-bold text-sky-600 bg-sky-50 px-2 py-0.5 rounded-full mt-0.5">● Hari Ini</span>
                                        @endif
                                    </div>
                                </div>

                                <label class="flex items-center gap-1.5 cursor-pointer text-xs font-bold text-slate-600">
                                    <input type="checkbox" name="schedules[{{ $s->hari }}][is_libur]" value="1" {{ $s->is_libur ? 'checked' : '' }} class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                                    <span class="{{ $s->is_libur ? 'text-red-600' : 'text-slate-500' }}">Libur</span>
                                </label>
                            </div>

                            <!-- Inputs -->
                            <div class="space-y-3.5">
                                <!-- Batas Datang Tepat Waktu -->
                                <div>
                                    <label class="block text-[11px] font-extrabold text-slate-700 uppercase tracking-wider mb-1">
                                        ⏰ Batas Datang Tepat Waktu
                                    </label>
                                    <input type="time" name="schedules[{{ $s->hari }}][jam_masuk_batas]" value="{{ $s->jam_masuk_batas }}" required class="w-full text-sm font-bold text-slate-800 rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-sky-500 focus:ring-sky-500">
                                    <span class="text-[10px] text-slate-400 mt-0.5 block">Lewat dari jam ini = <strong>Terlambat</strong></span>
                                </div>

                                <!-- Jam Mulai Pulang -->
                                <div>
                                    <label class="block text-[11px] font-extrabold text-slate-700 uppercase tracking-wider mb-1">
                                        🚪 Mulai Jam Pulang
                                    </label>
                                    <input type="time" name="schedules[{{ $s->hari }}][jam_pulang_mulai]" value="{{ $s->jam_pulang_mulai }}" required class="w-full text-sm font-bold text-slate-800 rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-sky-500 focus:ring-sky-500">
                                    <span class="text-[10px] text-slate-400 mt-0.5 block">Jam kepulangan sekolah dimulai</span>
                                </div>

                                <!-- Buka Gerbang Pagi -->
                                <div>
                                    <label class="block text-[11px] font-extrabold text-slate-700 uppercase tracking-wider mb-1">
                                        🌅 Gerbang Dibuka Pagi
                                    </label>
                                    <input type="time" name="schedules[{{ $s->hari }}][jam_masuk_mulai]" value="{{ $s->jam_masuk_mulai }}" required class="w-full text-sm font-semibold text-slate-800 rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-sky-500 focus:ring-sky-500">
                                </div>

                                <!-- Keterangan Hari -->
                                <div>
                                    <label class="block text-[11px] font-extrabold text-slate-700 uppercase tracking-wider mb-1">
                                        📝 Catatan / Kegiatan
                                    </label>
                                    <input type="text" name="schedules[{{ $s->hari }}][keterangan]" value="{{ $s->keterangan }}" placeholder="Misal: Upacara, Senam, KBM..." class="w-full text-xs font-semibold text-slate-800 rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-sky-500 focus:ring-sky-500">
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Floating Save Bar -->
            <div class="sticky bottom-6 rounded-2xl bg-white/95 backdrop-blur border border-slate-200 shadow-xl p-4 flex items-center justify-between">
                <div class="text-xs text-slate-600 font-semibold hidden sm:block">
                    Perubahan jadwal akan langsung diterapkan pada pemindaian kartu pos satpam dan aplikasi mobile siswa.
                </div>
                <button type="submit" class="w-full sm:w-auto px-8 py-3 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-extrabold text-sm shadow-lg shadow-sky-600/30 transition-all flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>Simpan Pengaturan Semua Hari</span>
                </button>
            </div>
        </form>
    </div>
</x-app-layout>

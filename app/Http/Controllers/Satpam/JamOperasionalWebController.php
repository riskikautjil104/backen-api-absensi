<?php

// awal batas suci yang kamu ubah

namespace App\Http\Controllers\Satpam;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JamOperasionalGerbang;
use Carbon\Carbon;

class JamOperasionalWebController extends Controller
{
    public function index()
    {
        JamOperasionalGerbang::ensureTableAndData();
        $schedules = JamOperasionalGerbang::orderBy('urutan', 'asc')->get();
        $todaySchedule = JamOperasionalGerbang::getScheduleForDate();

        return view('satpam.jam_operasional', compact('schedules', 'todaySchedule'));
    }

    public function update(Request $request)
    {
        JamOperasionalGerbang::ensureTableAndData();

        $request->validate([
            'schedules' => 'required|array',
        ]);

        foreach ($request->schedules as $hari => $data) {
            JamOperasionalGerbang::where('hari', $hari)->update([
                'jam_masuk_mulai' => $data['jam_masuk_mulai'] ?? '06:00',
                'jam_masuk_batas' => $data['jam_masuk_batas'] ?? '07:30',
                'jam_pulang_mulai' => $data['jam_pulang_mulai'] ?? '14:00',
                'is_libur' => isset($data['is_libur']) && $data['is_libur'] == '1',
                'keterangan' => $data['keterangan'] ?? null,
                'updated_by' => auth()->id(),
            ]);
        }

        return redirect()->route('satpam.jam-operasional')->with('success', 'Pengaturan jam operasional gerbang untuk 7 hari berhasil diperbarui!');
    }
}

// akhir batas suci yang kamu ubah

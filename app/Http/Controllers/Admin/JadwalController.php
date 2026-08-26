<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\User;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    public function index()
    {
        $jadwals = Jadwal::with(['kelas', 'mapel', 'guru'])->get();
        $kelas = Kelas::all();
        $mapels = MataPelajaran::all();
        $gurus = User::where('role', 'guru')->get();
        return view('admin.jadwal.index', compact('jadwals', 'kelas', 'mapels', 'gurus'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'mapel_id' => 'required|exists:mata_pelajaran,id',
            'guru_id' => 'required|exists:users,id',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
        ]);

        Jadwal::create($request->all());

        return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal berhasil ditambahkan.');
    }

    public function edit(Jadwal $jadwal)
    {
        $kelas = Kelas::all();
        $mapels = MataPelajaran::all();
        $gurus = User::where('role', 'guru')->get();
        return view('admin.jadwal.edit', compact('jadwal', 'kelas', 'mapels', 'gurus'));
    }

    public function update(Request $request, Jadwal $jadwal)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'mapel_id' => 'required|exists:mata_pelajaran,id',
            'guru_id' => 'required|exists:users,id',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
        ]);

        $jadwal->update($request->all());

        return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function destroy(Jadwal $jadwal)
    {
        $jadwal->delete();
        return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal berhasil dihapus.');
    }

    public function getGuruRelations($guruId)
    {
        $relations = \DB::table('guru_mapel')
            ->where('guru_id', $guruId)
            ->get();

        $mapelIds = $relations->pluck('mapel_id')->unique();
        $kelasIds = $relations->pluck('kelas_id')->unique();

        $mapels = MataPelajaran::whereIn('id', $mapelIds)->get(['id', 'nama_mapel']);
        $kelas = Kelas::whereIn('id', $kelasIds)->get(['id', 'nama_kelas']);

        return response()->json([
            'mapels' => $mapels,
            'kelas' => $kelas,
        ]);
    }
}

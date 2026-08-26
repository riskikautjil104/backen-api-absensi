<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SimoroSyncService;

class SyncController extends Controller
{
    protected $syncService;

    public function __construct(SimoroSyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    public function syncAll()
    {
        try {
            $kelasCount = $this->syncService->syncKelas();
            $guruCount = $this->syncService->syncGuru();
            $mapelCount = $this->syncService->syncMapel();
            $siswaCount = $this->syncService->syncSiswa();

            $message = 'Sinkronisasi massal berhasil! ';
            $message .= "{$kelasCount} Kelas, {$guruCount} Guru, {$mapelCount} Mapel, {$siswaCount} Siswa telah disinkronkan.";
            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mensinkronkan data: ' . $e->getMessage());
        }
    }

    public function syncSingle($type)
    {
        $name = 'Data';
        try {
            $count = false;
            switch ($type) {
                case 'kelas':
                    $name = 'Kelas';
                    $count = $this->syncService->syncKelas();
                    break;
                case 'guru':
                    $name = 'Guru';
                    $count = $this->syncService->syncGuru();
                    break;
                case 'mapel':
                    $name = 'Mata Pelajaran';
                    $count = $this->syncService->syncMapel();
                    break;
                case 'siswa':
                    $name = 'Siswa';
                    $count = $this->syncService->syncSiswa();
                    break;
                default:
                    return redirect()->back()->with('error', 'Tipe sinkronisasi tidak valid.');
            }

            return redirect()->back()->with('success', "Berhasil mensinkronkan {$count} data {$name} dari API SIMORO.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', "Gagal mensinkronkan data {$name}: " . $e->getMessage());
        }
    }
}

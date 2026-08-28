<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Buku;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'guru' => User::where('role', 'guru')->count(),
            // awal batas suci yang kamu ubah
            'satpam' => User::where('role', 'satpam')->count(),
            // akhir batas suci yang kamu ubah
            'siswa' => Siswa::count(),
            'kelas' => Kelas::count(),
            'buku' => Buku::count(),
        ];
        return view('admin.dashboard', compact('stats'));
    }
}

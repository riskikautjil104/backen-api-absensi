<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\KartuSiswa;
use App\Models\Siswa;
use Illuminate\Support\Facades\Crypt;

class KartuController extends Controller
{
    public function index()
    {
        return view('admin.kartu.index');
    }

    public function check(Request $request)
    {
        $token = $request->token;
        
        try {
            $token = Crypt::decryptString($token);
        } catch (\Exception $e) {
            // Fallback to plain token (manual entry)
        }
        
        $kartu = KartuSiswa::where('token', $token)->first();

        if (!$kartu) {
            return response()->json(['success' => false, 'message' => 'Kartu tidak ditemukan']);
        }

        $siswa = $kartu->siswa;
        
        // Encrypt the student ID for the "View Profile" button
        $encryptedId = Crypt::encryptString($siswa->id);

        return response()->json([
            'success' => true,
            'student' => [
                'name' => $siswa->user->name,
                'nis' => $siswa->user->nis ?? '-',
                'kelas' => $siswa->kelas->nama_kelas ?? '-',
                'foto' => $siswa->user->foto ? asset('storage/' . $siswa->user->foto) : null,
                'status_kartu' => $kartu->status,
                'url_detail' => route('admin.siswa.show', ['siswa' => $encryptedId])
            ]
        ]);
    }
}

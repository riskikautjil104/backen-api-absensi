<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\Storage;

class ProfilController extends Controller
{
    public function index()
    {
        $siswa = Auth::user()->siswa;
        return view('siswa.profil', compact('siswa'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $siswa = $user->siswa;

        $request->validate([
            'name' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'nomor_hp' => 'nullable|string|max:15',
            'wa_orang_tua' => 'nullable|string|max:15',
            'alamat' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Update User Name
        $user->update(['name' => $request->name]);

        // Handle Photo Upload
        if ($request->hasFile('foto')) {
            // Delete old photo if exists
            if ($user->foto) {
                Storage::disk('public')->delete($user->foto);
            }
            $path = $request->file('foto')->store('profile_photos', 'public');
            $user->update(['foto' => $path]);
        }

        // Update Siswa Data
        $siswa->update($request->only([
            'jenis_kelamin',
            'tempat_lahir',
            'tanggal_lahir',
            'nomor_hp',
            'wa_orang_tua',
            'alamat'
        ]));

        return redirect()->back()->with('success', 'Profil Anda berhasil diperbarui.');
    }
}

<?php

// awal batas suci yang kamu ubah
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SatpamController extends Controller
{
    public function index()
    {
        $satpams = User::where('role', 'satpam')->orderBy('name', 'asc')->get();
        return view('admin.satpam.index', compact('satpams'));
    }

    public function create()
    {
        return view('admin.satpam.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'nip' => 'nullable|string|unique:users',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'satpam',
            'nip' => $request->nip,
        ]);

        return redirect()->route('admin.satpam.index')->with('success', 'Petugas Satpam berhasil ditambahkan.');
    }

    public function edit(User $satpam)
    {
        if ($satpam->role !== 'satpam') {
            abort(404);
        }
        return view('admin.satpam.edit', compact('satpam'));
    }

    public function update(Request $request, User $satpam)
    {
        if ($satpam->role !== 'satpam') {
            abort(404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $satpam->id,
            'nip' => 'nullable|string|unique:users,nip,' . $satpam->id,
        ]);

        $satpam->update([
            'name' => $request->name,
            'email' => $request->email,
            'nip' => $request->nip,
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:6']);
            $satpam->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->route('admin.satpam.index')->with('success', 'Data Petugas Satpam berhasil diperbarui.');
    }

    public function destroy(User $satpam)
    {
        if ($satpam->role !== 'satpam') {
            abort(404);
        }

        $satpam->delete();
        return redirect()->route('admin.satpam.index')->with('success', 'Petugas Satpam berhasil dihapus.');
    }
}
// akhir batas suci yang kamu ubah

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Buku;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BukuController extends Controller
{
    public function index()
    {
        $bukus = Buku::all();
        return view('admin.buku.index', compact('bukus'));
    }

    public function create()
    {
        return view('admin.buku.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_buku' => 'required|string|max:50|unique:buku',
            'judul' => 'required|string|max:255',
            'penulis' => 'nullable|string|max:100',
            'penerbit' => 'nullable|string|max:100',
            'stok' => 'required|integer|min:0',
        ]);

        Buku::create([
            'kode_buku' => $request->kode_buku,
            'judul' => $request->judul,
            'penulis' => $request->penulis,
            'penerbit' => $request->penerbit,
            'stok' => $request->stok,
            'qr_token' => 'BK-' . strtoupper(Str::random(8)),
        ]);

        return redirect()->route('admin.buku.index')->with('success', 'Buku berhasil ditambahkan.');
    }

    public function edit(Buku $buku)
    {
        return view('admin.buku.edit', compact('buku'));
    }

    public function update(Request $request, Buku $buku)
    {
        $request->validate([
            'kode_buku' => 'required|string|max:50|unique:buku,kode_buku,' . $buku->id,
            'judul' => 'required|string|max:255',
            'stok' => 'required|integer|min:0',
        ]);

        $buku->update($request->all());

        return redirect()->route('admin.buku.index')->with('success', 'Data buku berhasil diperbarui.');
    }

    public function destroy(Buku $buku)
    {
        $buku->delete();
        return redirect()->route('admin.buku.index')->with('success', 'Buku berhasil dihapus.');
    }
}

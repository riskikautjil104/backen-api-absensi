<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;

class KartuController extends Controller
{
    public function index()
    {
        $siswa = Auth::user()->siswa;
        $kartu = $siswa->kartu;
        return view('siswa.kartu', compact('siswa', 'kartu'));
    }
}

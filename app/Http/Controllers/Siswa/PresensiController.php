<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\PresensiSession;
use Illuminate\Http\Request;

class PresensiController extends Controller
{
    public function scan($code)
    {
        $session = PresensiSession::where('qr_code', $code)->firstOrFail();
        return view('siswa.presensi.scan', compact('session'));
    }
}

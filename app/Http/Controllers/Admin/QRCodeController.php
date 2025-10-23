<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PresensiSession;
use App\Models\Kelas;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QRCodeController extends Controller
{
    public function index()
    {
        $sessions = PresensiSession::with(['kelas.jurusan', 'creator'])
            ->today()
            ->latest()
            ->paginate(10);

        $dataKelas = \App\Models\Kelas::with('jurusan')->get();

        return view('admin.qrcode.index', compact('sessions', 'dataKelas'));
    }

    public function create()
    {
        $kelas = Kelas::with('jurusan')->get();
        
        return view('admin.qrcode.create', compact('kelas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'tanggal' => 'required|date',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'nullable|integer|min:50|max:1000',
        ], [
            'kelas_id.required' => 'Kelas wajib dipilih',
            'tanggal.required' => 'Tanggal wajib diisi',
            'jam_mulai.required' => 'Jam mulai wajib diisi',
            'jam_selesai.required' => 'Jam selesai wajib diisi',
            'jam_selesai.after' => 'Jam selesai harus setelah jam mulai',
            'latitude.required' => 'Lokasi GPS wajib diisi',
            'longitude.required' => 'Lokasi GPS wajib diisi',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['qr_code'] = PresensiSession::generateQRCode();
        $validated['radius'] = $validated['radius'] ?? 200;
        $validated['status'] = 'active';

        $session = PresensiSession::create($validated);

        return redirect()
            ->route('admin.qrcode.show', $session->id)
            ->with('success', 'QR Code berhasil di-generate');
    }

    public function show(PresensiSession $session)
    {
        $session->load(['kelas.jurusan', 'creator', 'presensis.siswa']);
        
        $qrCodeUrl = route('scan.verify', $session->qr_code);
        $qrCode = QrCode::size(300)
            ->margin(2)
            ->generate($qrCodeUrl);
        
        $stats = [
            'total_siswa' => $session->kelas->siswa->count(),
            'hadir' => $session->presensis()->hadir()->count(),
            'izin' => $session->presensis()->izin()->count(),
            'sakit' => $session->presensis()->sakit()->count(),
            'alpha' => $session->presensis()->alpha()->count(),
        ];
        
        return view('admin.qrcode.show', compact('session', 'qrCode', 'stats'));
    }

    public function destroy(PresensiSession $session)
    {
        if ($session->created_by !== auth()->id() && auth()->user()->role !== 'admin') {
            return back()->with('error', 'Anda tidak memiliki akses');
        }

        $session->update(['status' => 'expired']);

        return redirect()
            ->route('admin.qrcode.index')
            ->with('success', 'Session QR Code berhasil dinonaktifkan');
    }

    public function getSchoolLocation()
    {
        // Koordinat default 
        return response()->json([
            'latitude' => -7.645702,  
            'longitude' => 111.4265614,
            'radius' => 200
        ]);
    }
}
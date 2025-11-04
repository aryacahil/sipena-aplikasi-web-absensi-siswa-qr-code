<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PresensiSession;
use App\Models\Kelas;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;

class QRCodeController extends Controller
{
    public function index(Request $request)
    {
        $query = PresensiSession::with(['kelas.jurusan', 'creator'])
            ->withCount('presensis');

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $sessions = $query->latest()->paginate(10);
        $kelas = Kelas::with('jurusan')->get();

        return view('admin.qrcode.index', compact('sessions', 'kelas'));
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
            'radius' => 'required|integer|min:50|max:1000',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['qr_code'] = Str::random(32);
        $validated['status'] = 'active';

        $session = PresensiSession::create($validated);

        return redirect()
            ->route('admin.qrcode.show', $session->id)
            ->with('success', 'QR Code berhasil dibuat');
    }

    public function show(PresensiSession $qrcode)
{
    $qrcode->load(['kelas.jurusan', 'creator', 'presensis.siswa']);
    
    $url = route('siswa.presensi.scan', ['code' => $qrcode->qr_code]);
    $qrCodeSvg = QrCode::size(300)
        ->style('round')
        ->eye('circle')
        ->generate($url);

    $siswaIds = $qrcode->presensis()->pluck('siswa_id')->toArray();
    $siswaBelumPresensi = $qrcode->kelas->siswa()
        ->whereNotIn('id', $siswaIds)
        ->get();

    $stats = [
        'hadir' => $qrcode->presensis()->where('status', 'hadir')->count(),
        'belum' => $siswaBelumPresensi->count(),
    ];

    if (request()->wantsJson() || request()->ajax()) {
        return response()->json([
            'success' => true,
            'session' => [
                'id' => $qrcode->id,
                'kelas' => [
                    'nama_kelas' => $qrcode->kelas->nama_kelas,
                    'jurusan' => [
                        'nama_jurusan' => $qrcode->kelas->jurusan->nama_jurusan,
                    ],
                ],
                'tanggal' => $qrcode->tanggal->format('d M Y'),
                'jam_mulai' => $qrcode->jam_mulai->format('H:i'),
                'jam_selesai' => $qrcode->jam_selesai->format('H:i'),
                'radius' => $qrcode->radius,
                'status' => $qrcode->status,
                'is_active' => $qrcode->isActive(),
                'creator' => [
                    'name' => $qrcode->creator->name,
                ],
                'presensis' => $qrcode->presensis->map(function($presensi) {
                    return [
                        'siswa' => [
                            'name' => $presensi->siswa->name,
                        ],
                        'status' => $presensi->status,
                        'waktu_presensi' => $presensi->waktu_presensi->format('H:i'),
                    ];
                }),
                'siswa_belum_presensi' => $siswaBelumPresensi->map(function($siswa) {
                    return [
                        'name' => $siswa->name,
                        'email' => $siswa->email,
                    ];
                }),
                'stats' => $stats,
                'scan_url' => $url,
            ],
            'qr_code_svg' => $qrCodeSvg,
        ]);
    }

    return view('admin.qrcode.show', compact('qrcode', 'qrCodeSvg', 'siswaBelumPresensi', 'stats'));
}

    public function download(PresensiSession $qrcode)
    {
        $url = route('siswa.presensi.scan', ['code' => $qrcode->qr_code]);
        
        $qrCode = QrCode::format('png')
            ->size(500)
            ->style('round')
            ->eye('circle')
            ->generate($url);

        $filename = 'QR-' . $qrcode->kelas->kode_kelas . '-' . $qrcode->tanggal->format('Ymd') . '.png';

        return response($qrCode)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function updateStatus(Request $request, PresensiSession $qrcode)
    {
        $validated = $request->validate([
            'status' => 'required|in:active,expired',
        ]);

        $qrcode->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Status berhasil diperbarui'
        ]);
    }

    public function destroy(PresensiSession $qrcode)
    {
        $qrcode->delete();

        return redirect()
            ->route('admin.qrcode.index')
            ->with('success', 'QR Code berhasil dihapus');
    }
}
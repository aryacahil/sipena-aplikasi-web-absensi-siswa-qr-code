<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PresensiSession;
use App\Models\Kelas;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

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

        // Generate dan simpan QR code
        $generated = $this->generateAndSaveQRCode($session);
        
        if (!$generated) {
            // Rollback jika gagal generate
            $session->delete();
            return redirect()
                ->route('admin.qrcode.index')
                ->with('error', 'Gagal generate QR Code');
        }

        return redirect()
            ->route('admin.qrcode.show', $session->id)
            ->with('success', 'QR Code berhasil dibuat');
    }

    public function show(PresensiSession $qrcode)
    {
        $qrcode->load(['kelas.jurusan', 'creator', 'presensis.siswa']);
        
        $url = route('siswa.presensi.scan', ['code' => $qrcode->qr_code]);
        
        // Generate QR Code SVG inline untuk ditampilkan
        $qrCodeSvg = QrCode::size(300)
            ->errorCorrection('H')
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
                    'latitude' => $qrcode->latitude,
                    'longitude' => $qrcode->longitude,
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
                            'waktu_presensi' => $presensi->created_at->format('H:i'),
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
        try {
            $url = route('siswa.presensi.scan', ['code' => $qrcode->qr_code]);
            
            // Generate QR Code sebagai PNG
            $qrCode = QrCode::format('png')
                ->size(500)
                ->errorCorrection('H')
                ->generate($url);
            
            $filename = 'QR-' . $qrcode->kelas->kode_kelas . '-' . $qrcode->tanggal->format('Ymd') . '.png';

            return response($qrCode)
                ->header('Content-Type', 'image/png')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
            
        } catch (\Exception $e) {
            Log::error('Gagal mengunduh QR Code', [
                'session_id' => $qrcode->id,
                'qr_code' => $qrcode->qr_code,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()
                ->back()
                ->with('error', 'Gagal mengunduh QR Code: ' . $e->getMessage());
        }
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
        try {
            // Hapus file QR code jika ada (coba SVG dulu, lalu PNG)
            $qrPathSvg = 'qrcodes/' . $qrcode->qr_code . '.svg';
            $qrPathPng = 'qrcodes/' . $qrcode->qr_code . '.png';
            
            if (Storage::disk('public')->exists($qrPathSvg)) {
                Storage::disk('public')->delete($qrPathSvg);
            }
            if (Storage::disk('public')->exists($qrPathPng)) {
                Storage::disk('public')->delete($qrPathPng);
            }

            $qrcode->delete();

            return redirect()
                ->route('admin.qrcode.index')
                ->with('success', 'QR Code berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.qrcode.index')
                ->with('error', 'Gagal menghapus QR Code: ' . $e->getMessage());
        }
    }

    protected function generateAndSaveQRCode(PresensiSession $session)
    {
        Log::info('GENERATE QR CODE DIPANGGIL', [
            'session_id' => $session->id,
            'qr_code' => $session->qr_code
        ]);
        
        try {
            $url = route('siswa.presensi.scan', ['code' => $session->qr_code]);

            // Pastikan folder ada
            $folder = 'qrcodes';
            if (!Storage::disk('public')->exists($folder)) {
                Storage::disk('public')->makeDirectory($folder);
                Log::info('Folder qrcodes dibuat');
            }

            // Generate QR code dalam format SVG untuk storage
            $qrCode = QrCode::format('svg')
                ->size(500)
                ->errorCorrection('H')
                ->generate($url);

            // Simpan sebagai SVG
            $qrPath = $folder . '/' . $session->qr_code . '.svg';
            $saved = Storage::disk('public')->put($qrPath, $qrCode);

            if (!$saved) {
                throw new \Exception('Gagal menyimpan file QR code');
            }

            // Verifikasi file benar-benar tersimpan
            $fullPath = storage_path('app/public/' . $qrPath);
            if (!file_exists($fullPath)) {
                throw new \Exception('File QR berhasil disimpan di Storage tapi tidak ditemukan di filesystem');
            }

            Log::info('QR Code saved successfully', [
                'path' => $qrPath,
                'full_path' => $fullPath,
                'file_size' => filesize($fullPath) . ' bytes'
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Failed to generate QR code', [
                'session_id' => $session->id,
                'qr_code' => $session->qr_code,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
}
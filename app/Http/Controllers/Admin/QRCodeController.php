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
        $this->generateAndSaveQRCode($session);

        return redirect()
            ->route('admin.qrcode.show', $session->id)
            ->with('success', 'QR Code berhasil dibuat');
    }

    public function show(PresensiSession $qrcode)
    {
        $qrcode->load(['kelas.jurusan', 'creator', 'presensis.siswa']);
        
        $url = route('siswa.presensi.scan', ['code' => $qrcode->qr_code]);
        
        // Generate QR Code SVG untuk ditampilkan (tidak pakai style karena butuh imagick)
        $qrCodeSvg = QrCode::size(300)
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
            // Ubah ke SVG
            $qrPath = 'qrcodes/' . $qrcode->qr_code . '.svg';
            $fullPath = storage_path('app/public/' . $qrPath);

            // Cek apakah file ada di storage
            if (!Storage::disk('public')->exists($qrPath)) {
                Log::warning('QR Code tidak ditemukan, generating ulang...', [
                    'session_id' => $qrcode->id,
                    'path' => $qrPath
                ]);
                
                // Generate ulang QR code
                $generated = $this->generateAndSaveQRCode($qrcode);
                
                if (!$generated) {
                    throw new \Exception("Gagal generate QR Code");
                }
            }

            // Double check setelah generate
            if (!file_exists($fullPath)) {
                throw new \Exception("File QR tidak ditemukan di path: {$fullPath}");
            }

            $filename = 'QR-' . $qrcode->kelas->kode_kelas . '-' . $qrcode->tanggal->format('Ymd') . '.svg';

            return response()->download($fullPath, $filename, [
                'Content-Type' => 'image/svg+xml',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
            ]);
            
        } catch (\Exception $e) {
            Log::error('Gagal mengunduh QR Code', [
                'session_id' => $qrcode->id,
                'qr_code' => $qrcode->qr_code,
                'expected_path' => $fullPath ?? 'unknown',
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
            // Ubah ke SVG
            $qrPath = 'qrcodes/' . $qrcode->qr_code . '.svg';
            if (Storage::disk('public')->exists($qrPath)) {
                Storage::disk('public')->delete($qrPath);
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

    public function generateAndSaveQRCode(PresensiSession $session)
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

            // Generate QR code dalam format SVG (tidak butuh imagick/gd)
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
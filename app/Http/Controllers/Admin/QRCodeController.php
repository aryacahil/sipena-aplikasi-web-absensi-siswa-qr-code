<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PresensiSession;
use App\Models\QRCode;
use App\Models\Kelas;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode as QrCodeFacade;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class QRCodeController extends Controller
{
    public function index(Request $request)
    {
        $query = PresensiSession::with(['kelas.jurusan', 'creator', 'qrCode'])
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
        $kelas = Kelas::with('jurusan')->withCount('siswa')->get();

        return view('admin.qrcode.index', compact('sessions', 'kelas'));
    }

    public function create()
    {
        $kelas = Kelas::with('jurusan')->withCount('siswa')->get();
        return view('admin.qrcode.create', compact('kelas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'tanggal' => 'required|date',
            'jam_checkin_mulai' => 'required|date_format:H:i',
            'jam_checkin_selesai' => 'required|date_format:H:i|after:jam_checkin_mulai',
            'jam_checkout_mulai' => 'required|date_format:H:i|after:jam_checkin_selesai',
            'jam_checkout_selesai' => 'required|date_format:H:i|after:jam_checkout_mulai',
            'latitude_checkin' => 'required|numeric|between:-90,90',
            'longitude_checkin' => 'required|numeric|between:-180,180',
            'radius_checkin' => 'required|integer|min:50|max:1000',
            'latitude_checkout' => 'nullable|numeric|between:-90,90',
            'longitude_checkout' => 'nullable|numeric|between:-180,180',
            'radius_checkout' => 'nullable|integer|min:50|max:1000',
        ]);

        // ========================================
        // HAPUS SESSION & QR CODE LAMA UNTUK KELAS YANG SAMA
        // ========================================
        $oldSessions = PresensiSession::where('kelas_id', $validated['kelas_id'])->get();
        
        foreach ($oldSessions as $oldSession) {
            try {
                // Hapus QR Code terkait
                if ($oldSession->qrCode) {
                    $this->deleteQRCodeFiles($oldSession->qrCode);
                    $oldSession->qrCode->delete();
                }
                
                // Hapus session
                $oldSession->delete();
                
                Log::info('Old Session and QR Code deleted', [
                    'session_id' => $oldSession->id,
                    'kelas_id' => $oldSession->kelas_id,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to delete old session', [
                    'session_id' => $oldSession->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // ========================================
        // BUAT SESSION BARU
        // ========================================
        $validated['created_by'] = auth()->id();
        $validated['status'] = 'active';

        // Set default checkout location same as checkin if not provided
        if (empty($validated['latitude_checkout'])) {
            $validated['latitude_checkout'] = $validated['latitude_checkin'];
        }
        if (empty($validated['longitude_checkout'])) {
            $validated['longitude_checkout'] = $validated['longitude_checkin'];
        }
        if (empty($validated['radius_checkout'])) {
            $validated['radius_checkout'] = $validated['radius_checkin'];
        }

        $session = PresensiSession::create($validated);

        // ========================================
        // BUAT QR CODE (Checkin & Checkout)
        // ========================================
        $qrCodeCheckin = Str::random(32);
        $qrCodeCheckout = Str::random(32);

        $qrCode = QRCode::create([
            'session_id' => $session->id,
            'qr_code_checkin' => $qrCodeCheckin,
            'qr_code_checkout' => $qrCodeCheckout,
            'status' => 'active',
        ]);

        // Generate dan simpan QR code files
        $generatedCheckin = $this->generateAndSaveQRCode($qrCodeCheckin, 'checkin');
        $generatedCheckout = $this->generateAndSaveQRCode($qrCodeCheckout, 'checkout');
        
        if (!$generatedCheckin || !$generatedCheckout) {
            // Rollback jika gagal generate
            $qrCode->delete();
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
        try {
            $qrcode->load(['kelas.jurusan', 'creator', 'presensis.siswa', 'qrCode']);
            
            // Generate QR Code SVG untuk checkin
            $qrCodeCheckinSvg = '';
            $qrCodeCheckoutSvg = '';
            
            if ($qrcode->qrCode) {
                $qrCodeCheckinSvg = (string) QrCodeFacade::format('svg')
                    ->size(300)
                    ->errorCorrection('H')
                    ->generate($qrcode->qrCode->qr_code_checkin);

                $qrCodeCheckoutSvg = (string) QrCodeFacade::format('svg')
                    ->size(300)
                    ->errorCorrection('H')
                    ->generate($qrcode->qrCode->qr_code_checkout);
            }

            $siswaIds = $qrcode->presensis()->pluck('siswa_id')->toArray();
            $siswaBelumPresensi = $qrcode->kelas->siswa()
                ->whereNotIn('id', $siswaIds)
                ->get();

            $stats = [
                'checkin' => $qrcode->presensis()->whereNotNull('waktu_checkin')->count(),
                'checkout' => $qrcode->presensis()->whereNotNull('waktu_checkout')->count(),
                'belum' => $siswaBelumPresensi->count(),
            ];

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'session' => [
                        'id' => $qrcode->id,
                        'kelas' => [
                            'nama_kelas' => $qrcode->kelas->nama_kelas,
                            'kode_kelas' => $qrcode->kelas->kode_kelas,
                            'jurusan' => [
                                'nama_jurusan' => $qrcode->kelas->jurusan->nama_jurusan,
                            ],
                        ],
                        'tanggal' => $qrcode->tanggal->format('d M Y'),
                        'jam_checkin_mulai' => $qrcode->jam_checkin_mulai->format('H:i'),
                        'jam_checkin_selesai' => $qrcode->jam_checkin_selesai->format('H:i'),
                        'jam_checkout_mulai' => $qrcode->jam_checkout_mulai->format('H:i'),
                        'jam_checkout_selesai' => $qrcode->jam_checkout_selesai->format('H:i'),
                        'latitude_checkin' => $qrcode->latitude_checkin,
                        'longitude_checkin' => $qrcode->longitude_checkin,
                        'radius_checkin' => $qrcode->radius_checkin,
                        'latitude_checkout' => $qrcode->latitude_checkout,
                        'longitude_checkout' => $qrcode->longitude_checkout,
                        'radius_checkout' => $qrcode->radius_checkout,
                        'status' => $qrcode->status,
                        'status_text' => $qrcode->getStatusText(),
                        'current_phase' => $qrcode->getCurrentPhase(),
                        'creator' => [
                            'name' => $qrcode->creator->name,
                        ],
                        'presensis' => $qrcode->presensis->map(function($presensi) {
                            return [
                                'siswa' => ['name' => $presensi->siswa->name],
                                'status' => $presensi->status,
                                'waktu_checkin' => $presensi->waktu_checkin ? $presensi->waktu_checkin->format('H:i') : null,
                                'waktu_checkout' => $presensi->waktu_checkout ? $presensi->waktu_checkout->format('H:i') : null,
                            ];
                        }),
                        'siswa_belum_presensi' => $siswaBelumPresensi->map(function($siswa) {
                            return [
                                'name' => $siswa->name,
                                'email' => $siswa->email,
                            ];
                        }),
                        'stats' => $stats,
                        'scan_url_checkin' => route('siswa.presensi.scan', ['code' => $qrcode->qrCode->qr_code_checkin]),
                        'scan_url_checkout' => route('siswa.presensi.scan', ['code' => $qrcode->qrCode->qr_code_checkout]),
                    ],
                    'qr_code_checkin_svg' => $qrCodeCheckinSvg,
                    'qr_code_checkout_svg' => $qrCodeCheckoutSvg,
                ]);
            }

            return redirect()
                ->route(request()->is('admin/*') ? 'admin.qrcode.index' : 'guru.qrcode.index')
                ->with('success', 'QR Code berhasil dibuat');
            
        } catch (\Exception $e) {
            Log::error('Error showing QR Code', [
                'session_id' => $qrcode->id,
                'error' => $e->getMessage(),
            ]);

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memuat QR Code: ' . $e->getMessage()
                ], 500);
            }

            return redirect()
                ->route(request()->is('admin/*') ? 'admin.qrcode.index' : 'guru.qrcode.index')
                ->with('error', 'Gagal memuat QR Code: ' . $e->getMessage());
        }
    }

    public function download(PresensiSession $qrcode, Request $request)
    {
        // Validate type parameter
        $type = $request->input('type', 'checkin'); // default checkin
        
        if (!in_array($type, ['checkin', 'checkout'])) {
            return back()->with('error', 'Tipe QR Code tidak valid');
        }

        try {
            if (!$qrcode->qrCode) {
                throw new \Exception('QR Code tidak ditemukan');
            }

            $qrCodeContent = $type === 'checkin' 
                ? $qrcode->qrCode->qr_code_checkin 
                : $qrcode->qrCode->qr_code_checkout;
            
            $filename = 'QR-' . strtoupper($type) . '-' . $qrcode->kelas->kode_kelas . '-' . $qrcode->tanggal->format('Ymd') . '.png';
            
            $options = new \chillerlan\QRCode\QROptions([
                'version'          => 7,
                'outputBase64'     => false,
                'eccLevel'         => \chillerlan\QRCode\Common\EccLevel::H,
                'outputType'       => 'png',
                'scale'            => 10,
                'imageTransparent' => false,
            ]);

            $qrcode_generator = new \chillerlan\QRCode\QRCode($options);
            $qrCodeImage = $qrcode_generator->render($qrCodeContent);
            
            if (empty($qrCodeImage) || strlen($qrCodeImage) < 100) {
                throw new \Exception('Invalid QR Code image generated');
            }

            return response($qrCodeImage)
                ->header('Content-Type', 'image/png')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Content-Length', strlen($qrCodeImage))
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
            
        } catch (\Exception $e) {
            Log::error('Download QR Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Gagal mengunduh QR Code: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, PresensiSession $qrcode)
    {
        $validated = $request->validate([
            'status' => 'required|in:active,expired',
        ]);

        $qrcode->update($validated);

        // Jika diubah ke expired, update QR code status juga
        if ($validated['status'] === 'expired' && $qrcode->qrCode) {
            $qrcode->qrCode->update(['status' => 'expired']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status berhasil diperbarui'
        ]);
    }

    public function destroy(PresensiSession $qrcode)
    {
        try {
            // Hapus QR Code files dan record
            if ($qrcode->qrCode) {
                $this->deleteQRCodeFiles($qrcode->qrCode);
                $qrcode->qrCode->delete();
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

    protected function generateAndSaveQRCode($code, $type = 'checkin')
    {
        try {
            $folder = 'qrcodes';
            if (!Storage::disk('public')->exists($folder)) {
                Storage::disk('public')->makeDirectory($folder);
            }

            // Generate QR code SVG
            $qrCode = QrCodeFacade::format('svg')
                ->size(500)
                ->errorCorrection('H')
                ->generate($code);

            $qrPath = $folder . '/' . $code . '-' . $type . '.svg';
            $saved = Storage::disk('public')->put($qrPath, $qrCode);

            if (!$saved) {
                throw new \Exception('Gagal menyimpan file QR code');
            }

            Log::info('QR Code saved successfully', [
                'path' => $qrPath,
                'type' => $type,
                'code' => $code
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Failed to generate QR code', [
                'code' => $code,
                'type' => $type,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    protected function deleteQRCodeFiles(QRCode $qrCode)
    {
        try {
            $deleted = false;

            // Delete checkin QR code
            $checkinPath = "qrcodes/{$qrCode->qr_code_checkin}-checkin.svg";
            if (Storage::disk('public')->exists($checkinPath)) {
                Storage::disk('public')->delete($checkinPath);
                $deleted = true;
            }

            // Delete checkout QR code
            $checkoutPath = "qrcodes/{$qrCode->qr_code_checkout}-checkout.svg";
            if (Storage::disk('public')->exists($checkoutPath)) {
                Storage::disk('public')->delete($checkoutPath);
                $deleted = true;
            }

            if ($deleted) {
                Log::info('QR Code files deleted successfully', [
                    'qr_code_id' => $qrCode->id
                ]);
            }

            return $deleted;

        } catch (\Exception $e) {
            Log::error('Error deleting QR Code files', [
                'qr_code_id' => $qrCode->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
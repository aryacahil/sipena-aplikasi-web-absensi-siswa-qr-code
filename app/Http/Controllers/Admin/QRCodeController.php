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
use Illuminate\Support\Facades\File;
use chillerlan\QRCode\QRCode as QRCodeGenerator;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRGdImagePNG; 
use chillerlan\QRCode\Output\QROutputInterface;

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
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'required|integer|min:50|max:1000',
        ]);

        // ========================================
        // HAPUS QR CODE LAMA UNTUK KELAS YANG SAMA
        // ========================================
        $oldSessions = PresensiSession::where('kelas_id', $validated['kelas_id'])->get();
        
        foreach ($oldSessions as $oldSession) {
            try {
                // Hapus file QR code lama
                $this->deleteQRCodeFile($oldSession);
                
                // Hapus record dari database
                $oldSession->delete();
                
                Log::info('Old QR Code deleted', [
                    'session_id' => $oldSession->id,
                    'kelas_id' => $oldSession->kelas_id,
                    'qr_code' => $oldSession->qr_code
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to delete old QR Code', [
                    'session_id' => $oldSession->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // ========================================
        // BUAT QR CODE BARU
        // ========================================
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
        try {
            $qrcode->load(['kelas.jurusan', 'creator', 'presensis.siswa']);
            
            // QR hanya berisi kode, bukan URL
            $qrCodeContent = $qrcode->qr_code;

            // Generate QR Code SVG
            $qrCodeSvgObject = QrCode::format('svg')
                ->size(300)
                ->errorCorrection('H')
                ->generate($qrCodeContent);

            $qrCodeSvg = (string) $qrCodeSvgObject;

            $siswaIds = $qrcode->presensis()->pluck('siswa_id')->toArray();
            $siswaBelumPresensi = $qrcode->kelas->siswa()
                ->whereNotIn('id', $siswaIds)
                ->get();

            $stats = [
                'hadir' => $qrcode->presensis()->where('status', 'hadir')->count(),
                'belum' => $siswaBelumPresensi->count(),
            ];

            // ✅ CEK APAKAH REQUEST AJAX ATAU BUKAN
            if (request()->ajax() || request()->wantsJson()) {
                // Return JSON untuk AJAX request
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
                        'jam_mulai' => $qrcode->jam_mulai->format('H:i'),
                        'jam_selesai' => $qrcode->jam_selesai->format('H:i'),
                        'latitude' => $qrcode->latitude ?? 0,
                        'longitude' => $qrcode->longitude ?? 0,
                        'radius' => $qrcode->radius ?? 200,
                        'status' => $qrcode->status,
                        'status_text' => $qrcode->getStatusText(),
                        'is_active' => $qrcode->isActive(),
                        'creator' => [
                            'name' => $qrcode->creator->name,
                        ],
                        'presensis' => $qrcode->presensis->map(function($presensi) {
                            return [
                                'siswa' => ['name' => $presensi->siswa->name],
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
                        'scan_url' => route('siswa.presensi.index'),
                        'qr_code' => $qrCodeContent,
                    ],
                    'qr_code_svg' => $qrCodeSvg,
                ]);
            }

            // ✅ Return REDIRECT ke index dengan flash message untuk non-AJAX request
            return redirect()
                ->route(request()->is('admin/*') ? 'admin.qrcode.index' : 'guru.qrcode.index')
                ->with('success', 'QR Code berhasil dibuat');
            
        } catch (\Exception $e) {
            Log::error('Error showing QR Code', [
                'session_id' => $qrcode->id,
                'error' => $e->getMessage(),
            ]);

            // Jika AJAX
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memuat QR Code: ' . $e->getMessage()
                ], 500);
            }

            // Jika non-AJAX
            return redirect()
                ->route(request()->is('admin/*') ? 'admin.qrcode.index' : 'guru.qrcode.index')
                ->with('error', 'Gagal memuat QR Code: ' . $e->getMessage());
        }
    }

    public function download(PresensiSession $qrcode)
    {
        try {
            $qrCodeContent = $qrcode->qr_code;
            $filename = 'QR-' . $qrcode->kelas->kode_kelas . '-' . $qrcode->tanggal->format('Ymd') . '.png';
            
            // ✅ FIXED: Gunakan string 'png' langsung (cara modern, tanpa deprecation)
            $options = new QROptions([
                'version'          => 7,
                'outputBase64'     => false,
                'eccLevel'         => EccLevel::H,
                'outputType'       => 'png', // ✅ Gunakan string langsung
                'scale'            => 10,
                'imageTransparent' => false,
            ]);

            $qrcode_generator = new QRCodeGenerator($options);
            
            // render() akan return binary PNG string
            $qrCodeImage = $qrcode_generator->render($qrCodeContent);
            
            // Validasi output
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

        // Jika diubah ke expired, hapus file QR code
        if ($validated['status'] === 'expired') {
            $this->deleteQRCodeFile($qrcode);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status berhasil diperbarui'
        ]);
    }

    public function destroy(PresensiSession $qrcode)
    {
        try {
            // Hapus file QR code
            $this->deleteQRCodeFile($qrcode);

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
            // UBAH - hanya kode, bukan URL
            $qrCodeContent = $session->qr_code;

            $folder = 'qrcodes';
            if (!Storage::disk('public')->exists($folder)) {
                Storage::disk('public')->makeDirectory($folder);
            }

            // Generate QR code SVG
            $qrCode = QrCode::format('svg')
                ->size(500)
                ->errorCorrection('H')
                ->generate($qrCodeContent);

            $qrPath = $folder . '/' . $session->qr_code . '.svg';
            $saved = Storage::disk('public')->put($qrPath, $qrCode);

            if (!$saved) {
                throw new \Exception('Gagal menyimpan file QR code');
            }

            Log::info('QR Code saved successfully', [
                'path' => $qrPath,
                'qr_code_content' => $qrCodeContent
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Failed to generate QR code', [
                'session_id' => $session->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    protected function deleteQRCodeFile(PresensiSession $session)
    {
        Log::info('=== DELETE QR CODE FILE ===', [
            'session_id' => $session->id,
            'qr_code' => $session->qr_code
        ]);

        try {
            $qrCode = $session->qr_code;
            $deleted = false;

            // ========================================
            // METHOD 1: Storage::disk('public')
            // ========================================
            $storagePath = "qrcodes/{$qrCode}.svg";
            
            Log::info('Checking Storage::disk(public)', [
                'path' => $storagePath,
                'exists' => Storage::disk('public')->exists($storagePath)
            ]);

            if (Storage::disk('public')->exists($storagePath)) {
                Storage::disk('public')->delete($storagePath);
                $deleted = true;
                Log::info('✓ Deleted via Storage::disk(public)', [
                    'path' => $storagePath
                ]);
            }

            // ========================================
            // METHOD 2: Full filesystem path (Backup)
            // ========================================
            $fullPath = storage_path("app/public/qrcodes/{$qrCode}.svg");
            
            Log::info('Checking filesystem path', [
                'path' => $fullPath,
                'exists' => file_exists($fullPath)
            ]);

            if (file_exists($fullPath)) {
                if (File::delete($fullPath)) {
                    $deleted = true;
                    Log::info('✓ Deleted via File::delete()', [
                        'path' => $fullPath
                    ]);
                } else {
                    Log::warning('File::delete() returned false', [
                        'path' => $fullPath
                    ]);
                }
            }

            // ========================================
            // METHOD 3: Unlink (Last resort)
            // ========================================
            if (file_exists($fullPath)) {
                if (unlink($fullPath)) {
                    $deleted = true;
                    Log::info('✓ Deleted via unlink()', [
                        'path' => $fullPath
                    ]);
                }
            }

            // ========================================
            // VERIFY DELETION
            // ========================================
            if (file_exists($fullPath)) {
                Log::error('✗ FILE STILL EXISTS after deletion attempts!', [
                    'path' => $fullPath,
                    'is_writable' => is_writable(dirname($fullPath)),
                    'permissions' => substr(sprintf('%o', fileperms($fullPath)), -4)
                ]);
                return false;
            }

            if ($deleted) {
                Log::info('✓ File successfully deleted and verified', [
                    'qr_code' => $qrCode
                ]);
            } else {
                Log::warning('File was not found in any location', [
                    'qr_code' => $qrCode
                ]);
            }

            return $deleted;

        } catch (\Exception $e) {
            Log::error('✗ Exception in deleteQRCodeFile', [
                'session_id' => $session->id,
                'qr_code' => $session->qr_code,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
}
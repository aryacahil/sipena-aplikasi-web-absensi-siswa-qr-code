<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\PresensiSession;
use App\Models\QRCode;
use App\Models\Presensi;
use App\Services\FonnteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PresensiController extends Controller
{
    protected $fonnteService;

    public function __construct(FonnteService $fonnteService)
    {
        $this->fonnteService = $fonnteService;
    }

    /**
     * Halaman Scanner QR Code
     */
    public function index()
    {
        $siswa = Auth::user();
        
        if (!$siswa->kelas_id) {
            return redirect()->route('siswa.home')
                ->with('error', 'Anda belum terdaftar di kelas manapun. Silakan hubungi admin.');
        }

        // Cek presensi hari ini
        $todayPresensi = Presensi::where('siswa_id', $siswa->id)
            ->whereDate('tanggal_presensi', now())
            ->first();

        Log::info('Scanner page loaded', [
            'siswa_id' => $siswa->id,
            'siswa_name' => $siswa->name,
            'kelas_id' => $siswa->kelas_id,
            'has_checkin' => $todayPresensi ? $todayPresensi->hasCheckedIn() : false,
            'has_checkout' => $todayPresensi ? $todayPresensi->hasCheckedOut() : false,
        ]);

        return view('siswa.presensi.index', compact('todayPresensi'));
    }

    /**
     * Validasi QR Code yang di-scan
     */
    public function validateQRCode(Request $request)
    {
        Log::info('Validate QR Code Request', [
            'request_data' => $request->all(),
            'user_id' => Auth::id()
        ]);

        try {
            $qrCodeString = $request->input('qr_code');
            
            if (!$qrCodeString) {
                Log::warning('QR Code empty');
                return response()->json([
                    'success' => false,
                    'message' => 'QR Code tidak valid'
                ], 400);
            }

            // Cari QR Code di database
            $qrCode = QRCode::where('qr_code_checkin', $qrCodeString)
                ->orWhere('qr_code_checkout', $qrCodeString)
                ->first();

            if (!$qrCode) {
                Log::warning('QR Code not found', ['qr_code' => $qrCodeString]);
                return response()->json([
                    'success' => false,
                    'message' => 'QR Code tidak ditemukan atau sudah tidak berlaku'
                ], 404);
            }

            // Tentukan tipe QR Code
            $type = ($qrCode->qr_code_checkin === $qrCodeString) ? 'checkin' : 'checkout';

            // Load session
            $session = $qrCode->session;
            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi presensi tidak ditemukan'
                ], 404);
            }

            $session->load('kelas.jurusan');

            Log::info('QR Code found', [
                'qr_code_id' => $qrCode->id,
                'session_id' => $session->id,
                'type' => $type,
                'kelas_id' => $session->kelas_id,
                'status' => $qrCode->status,
            ]);

            // Validasi status QR Code
            if ($qrCode->status !== 'active') {
                Log::warning('QR Code not active', [
                    'qr_code_id' => $qrCode->id,
                    'status' => $qrCode->status
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'QR Code sudah tidak aktif'
                ], 400);
            }

            // Validasi status session
            if ($session->status !== 'active') {
                Log::warning('Session not active', [
                    'session_id' => $session->id,
                    'status' => $session->status
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi presensi sudah tidak aktif'
                ], 400);
            }

            // Validasi waktu berdasarkan tipe
            $now = Carbon::now();
            $sessionDate = $session->tanggal;
            
            if ($type === 'checkin') {
                $jamMulai = Carbon::parse($sessionDate->format('Y-m-d') . ' ' . $session->jam_checkin_mulai->format('H:i:s'));
                $jamSelesai = Carbon::parse($sessionDate->format('Y-m-d') . ' ' . $session->jam_checkin_selesai->format('H:i:s'));
                $phaseText = 'Check-in';
            } else {
                $jamMulai = Carbon::parse($sessionDate->format('Y-m-d') . ' ' . $session->jam_checkout_mulai->format('H:i:s'));
                $jamSelesai = Carbon::parse($sessionDate->format('Y-m-d') . ' ' . $session->jam_checkout_selesai->format('H:i:s'));
                $phaseText = 'Check-out';
            }

            Log::info('Time validation', [
                'type' => $type,
                'now' => $now->toDateTimeString(),
                'session_date' => $sessionDate->format('Y-m-d'),
                'jam_mulai' => $jamMulai->toDateTimeString(),
                'jam_selesai' => $jamSelesai->toDateTimeString()
            ]);

            // Validasi tanggal
            if ($now->format('Y-m-d') !== $sessionDate->format('Y-m-d')) {
                Log::warning('Date mismatch', [
                    'today' => $now->format('Y-m-d'),
                    'session_date' => $sessionDate->format('Y-m-d')
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'QR Code hanya berlaku pada tanggal ' . $sessionDate->format('d M Y')
                ], 400);
            }

            // Validasi waktu mulai
            if ($now->lt($jamMulai)) {
                Log::warning('Too early', [
                    'type' => $type,
                    'now' => $now->toTimeString(),
                    'jam_mulai' => $jamMulai->toTimeString()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => $phaseText . ' belum dimulai. Waktu mulai: ' . $jamMulai->format('H:i')
                ], 400);
            }

            // Validasi waktu selesai
            if ($now->gt($jamSelesai)) {
                Log::warning('Too late', [
                    'type' => $type,
                    'now' => $now->toTimeString(),
                    'jam_selesai' => $jamSelesai->toTimeString()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Waktu ' . strtolower($phaseText) . ' sudah berakhir. Waktu selesai: ' . $jamSelesai->format('H:i')
                ], 400);
            }

            // Validasi presensi siswa berdasarkan tipe
            $siswa = Auth::user();
            $existingPresensi = Presensi::where('siswa_id', $siswa->id)
                ->where('kelas_id', $session->kelas_id)
                ->whereDate('tanggal_presensi', $sessionDate)
                ->first();

            if ($type === 'checkin') {
                // Validasi: Sudah checkin?
                if ($existingPresensi && $existingPresensi->hasCheckedIn()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda sudah melakukan check-in pada pukul ' . $existingPresensi->waktu_checkin->format('H:i')
                    ], 400);
                }
            } else {
                // Validasi: Harus checkin dulu
                if (!$existingPresensi || !$existingPresensi->hasCheckedIn()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda harus check-in terlebih dahulu sebelum check-out'
                    ], 400);
                }
                
                // Validasi: Sudah checkout?
                if ($existingPresensi->hasCheckedOut()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda sudah melakukan check-out pada pukul ' . $existingPresensi->waktu_checkout->format('H:i')
                    ], 400);
                }
            }

            // Pilih koordinat yang sesuai
            $latitude = $type === 'checkin' ? $session->latitude_checkin : $session->latitude_checkout;
            $longitude = $type === 'checkin' ? $session->longitude_checkin : $session->longitude_checkout;
            $radius = $type === 'checkin' ? $session->radius_checkin : $session->radius_checkout;

            $responseData = [
                'qr_code_id' => $qrCode->id,
                'session_id' => $session->id,
                'type' => $type,
                'kelas' => $session->kelas->nama_kelas,
                'jurusan' => $session->kelas->jurusan->nama_jurusan,
                'tanggal' => $session->tanggal->format('d M Y'),
                'jam_mulai' => $jamMulai->format('H:i'),
                'jam_selesai' => $jamSelesai->format('H:i'),
                'latitude' => (float) $latitude,
                'longitude' => (float) $longitude,
                'radius' => (int) $radius,
            ];

            Log::info('Validation successful', $responseData);

            return response()->json([
                'success' => true,
                'message' => 'QR Code valid',
                'data' => $responseData
            ]);

        } catch (\Exception $e) {
            Log::error('Error validating QR Code', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memvalidasi QR Code'
            ], 500);
        }
    }

    /**
     * Submit Presensi (Checkin atau Checkout)
     */
    public function submitPresensi(Request $request)
    {
        Log::info('Submit Presensi Request', [
            'request_data' => $request->all(),
            'user_id' => Auth::id()
        ]);

        try {
            $validated = $request->validate([
                'qr_code_id' => 'required|exists:qr_codes,id',
                'session_id' => 'required|exists:presensi_sessions,id',
                'type' => 'required|in:checkin,checkout',
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'distance' => 'nullable|numeric',
                'gps_accuracy' => 'nullable|numeric',
            ]);

            $siswa = Auth::user();

            if (!$siswa->kelas_id) {
                Log::warning('Student has no class', ['siswa_id' => $siswa->id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Anda belum terdaftar di kelas manapun'
                ], 400);
            }

            $session = PresensiSession::findOrFail($validated['session_id']);
            $qrCode = QRCode::findOrFail($validated['qr_code_id']);
            $type = $validated['type'];

            // Validasi kelas
            if ($siswa->kelas_id !== $session->kelas_id) {
                Log::warning('Class mismatch', [
                    'siswa_kelas_id' => $siswa->kelas_id,
                    'session_kelas_id' => $session->kelas_id
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'QR Code ini bukan untuk kelas Anda'
                ], 403);
            }

            // Cek existing presensi
            $existingPresensi = Presensi::where('siswa_id', $siswa->id)
                ->where('kelas_id', $session->kelas_id)
                ->whereDate('tanggal_presensi', $session->tanggal)
                ->first();

            // Validasi SERVER-SIDE berdasarkan tipe
            if ($type === 'checkin') {
                if ($existingPresensi && $existingPresensi->hasCheckedIn()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda sudah melakukan check-in pada pukul ' . $existingPresensi->waktu_checkin->format('H:i')
                    ], 400);
                }
            } else {
                if (!$existingPresensi || !$existingPresensi->hasCheckedIn()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda harus check-in terlebih dahulu sebelum check-out'
                    ], 400);
                }
                
                if ($existingPresensi->hasCheckedOut()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda sudah melakukan check-out pada pukul ' . $existingPresensi->waktu_checkout->format('H:i')
                    ], 400);
                }
            }

            // Validasi radius (SERVER-SIDE)
            $targetLat = $type === 'checkin' ? $session->latitude_checkin : $session->latitude_checkout;
            $targetLng = $type === 'checkin' ? $session->longitude_checkin : $session->longitude_checkout;
            $allowedRadius = $type === 'checkin' ? $session->radius_checkin : $session->radius_checkout;

            $distance = $this->validateLocation(
                $validated['latitude'],
                $validated['longitude'],
                $targetLat,
                $targetLng,
                $allowedRadius
            );

            Log::info('Server-side distance validation', [
                'type' => $type,
                'siswa_lat' => $validated['latitude'],
                'siswa_lng' => $validated['longitude'],
                'target_lat' => $targetLat,
                'target_lng' => $targetLng,
                'radius' => $allowedRadius,
                'calculated_distance' => $distance,
                'is_within_radius' => $distance <= $allowedRadius
            ]);

            // TOLAK jika di luar radius
            if ($distance > $allowedRadius) {
                Log::warning('Location outside radius - REJECTED', [
                    'siswa_id' => $siswa->id,
                    'type' => $type,
                    'distance' => $distance,
                    'allowed_radius' => $allowedRadius,
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Lokasi Anda di luar radius yang diizinkan',
                    'data' => [
                        'distance' => $distance,
                        'allowed_radius' => $allowedRadius,
                        'difference' => $distance - $allowedRadius
                    ]
                ], 403);
            }

            $isValidLocation = true;
            $keterangan = "Jarak: {$distance}m";
            if (isset($validated['gps_accuracy'])) {
                $keterangan .= " | GPS Accuracy: {$validated['gps_accuracy']}m";
            }

            // Proses Checkin atau Checkout
            if ($type === 'checkin') {
                // Buat presensi baru
                $presensi = Presensi::create([
                    'session_id' => $session->id,
                    'qr_code_id' => $qrCode->id,
                    'kelas_id' => $session->kelas_id,
                    'siswa_id' => $siswa->id,
                    'tanggal_presensi' => $session->tanggal,
                    'waktu_checkin' => now(),
                    'status' => 'hadir',
                    'latitude_checkin' => $validated['latitude'],
                    'longitude_checkin' => $validated['longitude'],
                    'is_valid_location_checkin' => $isValidLocation,
                    'keterangan_checkin' => $keterangan,
                    'metode' => 'qr',
                ]);

                Log::info('Checkin created successfully', [
                    'presensi_id' => $presensi->id,
                    'siswa_id' => $siswa->id,
                    'distance' => $distance,
                ]);

                // Kirim notifikasi checkin
                $this->sendWhatsAppNotification($presensi, 'checkin');

                return response()->json([
                    'success' => true,
                    'message' => 'Check-in berhasil dicatat!',
                    'data' => [
                        'presensi_id' => $presensi->id,
                        'type' => 'checkin',
                        'waktu_checkin' => $presensi->waktu_checkin->format('H:i:s'),
                        'tanggal' => $presensi->tanggal_presensi->format('d M Y'),
                        'distance' => $distance,
                    ]
                ]);

            } else {
                // Update presensi dengan checkout
                $existingPresensi->update([
                    'waktu_checkout' => now(),
                    'latitude_checkout' => $validated['latitude'],
                    'longitude_checkout' => $validated['longitude'],
                    'is_valid_location_checkout' => $isValidLocation,
                    'keterangan_checkout' => $keterangan,
                ]);

                Log::info('Checkout updated successfully', [
                    'presensi_id' => $existingPresensi->id,
                    'siswa_id' => $siswa->id,
                    'distance' => $distance,
                ]);

                // Kirim notifikasi checkout
                $this->sendWhatsAppNotification($existingPresensi, 'checkout');

                return response()->json([
                    'success' => true,
                    'message' => 'Check-out berhasil dicatat!',
                    'data' => [
                        'presensi_id' => $existingPresensi->id,
                        'type' => 'checkout',
                        'waktu_checkin' => $existingPresensi->waktu_checkin->format('H:i:s'),
                        'waktu_checkout' => $existingPresensi->waktu_checkout->format('H:i:s'),
                        'tanggal' => $existingPresensi->tanggal_presensi->format('d M Y'),
                        'distance' => $distance,
                    ]
                ]);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error', [
                'errors' => $e->errors()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error submitting presensi', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan presensi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Kirim notifikasi WhatsApp ke orang tua
     */
    protected function sendWhatsAppNotification($presensi, $type = 'checkin')
    {
        try {
            if (!$this->fonnteService->isEnabled()) {
                Log::info('WhatsApp notification disabled', [
                    'presensi_id' => $presensi->id
                ]);
                return;
            }

            // Kirim notifikasi sesuai tipe
            $result = $this->fonnteService->sendPresensiNotification($presensi, $type);

            if ($result['success']) {
                // Update flag notifikasi
                if ($type === 'checkin') {
                    $presensi->update(['notifikasi_checkin_terkirim' => true]);
                } else {
                    $presensi->update(['notifikasi_checkout_terkirim' => true]);
                }

                Log::info('WhatsApp notification sent successfully', [
                    'presensi_id' => $presensi->id,
                    'type' => $type,
                ]);
            } else if (!isset($result['skipped'])) {
                Log::warning('Failed to send WhatsApp notification', [
                    'presensi_id' => $presensi->id,
                    'type' => $type,
                    'reason' => $result['message']
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error sending WhatsApp notification', [
                'presensi_id' => $presensi->id,
                'type' => $type,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Validasi jarak lokasi menggunakan Haversine formula
     * Return distance in meters
     */
    private function validateLocation($lat1, $lon1, $lat2, $lon2, $radiusInMeters)
    {
        if (!$lat2 || !$lon2) {
            Log::info('No target coordinates provided');
            return 0;
        }

        $earthRadius = 6371000; // Earth radius in meters
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        Log::info('Distance calculation', [
            'distance_meters' => round($distance, 2),
            'allowed_radius' => $radiusInMeters,
            'is_within_radius' => $distance <= $radiusInMeters
        ]);

        return round($distance, 2);
    }
}
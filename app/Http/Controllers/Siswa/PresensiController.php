<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\PresensiSession;
use App\Models\QRCode;
use App\Models\Presensi;
use App\Services\FonnteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PresensiController extends Controller
{
    protected $fonnteService;

    public function __construct(FonnteService $fonnteService)
    {
        $this->fonnteService = $fonnteService;
    }

    public function index()
    {
        $siswa = Auth::user();
        
        if (!$siswa->kelas_id) {
            return redirect()->route('siswa.home')
                ->with('error', 'Anda belum terdaftar di kelas manapun. Silakan hubungi admin.');
        }

        $todayPresensi = Presensi::where('siswa_id', $siswa->id)
            ->whereDate('tanggal_presensi', now())
            ->first();

        return view('siswa.presensi.index', compact('todayPresensi'));
    }

    public function validateQRCode(Request $request)
    {

        try {
            $qrCodeString = $request->input('qr_code');
            
            if (!$qrCodeString) {
            }

            $qrCode = QRCode::where('qr_code_checkin', $qrCodeString)
                ->orWhere('qr_code_checkout', $qrCodeString)
                ->first();

            if (!$qrCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'QR Code tidak ditemukan atau sudah tidak berlaku'
                ], 404);
            }

            $type = ($qrCode->qr_code_checkin === $qrCodeString) ? 'checkin' : 'checkout';

            $session = $qrCode->session;
            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi presensi tidak ditemukan'
                ], 404);
            }

            $session->load('kelas.jurusan');

            if ($qrCode->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'QR Code sudah tidak aktif'
                ], 400);
            }

            if ($session->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi presensi sudah tidak aktif'
                ], 400);
            }

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

            if ($now->format('Y-m-d') !== $sessionDate->format('Y-m-d')) {
                return response()->json([
                    'success' => false,
                    'message' => 'QR Code hanya berlaku pada tanggal ' . $sessionDate->format('d M Y')
                ], 400);
            }

            if ($now->lt($jamMulai)) {
                return response()->json([
                    'success' => false,
                    'message' => $phaseText . ' belum dimulai. Waktu mulai: ' . $jamMulai->format('H:i')
                ], 400);
            }

            if ($now->gt($jamSelesai)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Waktu ' . strtolower($phaseText) . ' sudah berakhir. Waktu selesai: ' . $jamSelesai->format('H:i')
                ], 400);
            }

            $siswa = Auth::user();
            $existingPresensi = Presensi::where('siswa_id', $siswa->id)
                ->where('kelas_id', $session->kelas_id)
                ->whereDate('tanggal_presensi', $sessionDate)
                ->first();

            if ($type === 'checkin') {
                if ($existingPresensi && $existingPresensi->hasCheckedIn()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda sudah melakukan check-in pada pukul ' . $existingPresensi->waktu_checkin->format('H:i')
                    ], 400);
                }
            } else {
                if ($existingPresensi && $existingPresensi->hasCheckedOut()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda sudah melakukan check-out pada pukul ' . $existingPresensi->waktu_checkout->format('H:i')
                    ], 400);
                }
            }

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

            return response()->json([
                'success' => true,
                'message' => 'QR Code valid',
                'data' => $responseData
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memvalidasi QR Code'
            ], 500);
        }
    }

    public function submitPresensi(Request $request)
    {

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
                return response()->json([
                    'success' => false,
                    'message' => 'Anda belum terdaftar di kelas manapun'
                ], 400);
            }

            $session = PresensiSession::findOrFail($validated['session_id']);
            $qrCode = QRCode::findOrFail($validated['qr_code_id']);
            $type = $validated['type'];

            if ($siswa->kelas_id !== $session->kelas_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'QR Code ini bukan untuk kelas Anda'
                ], 403);
            }

            $existingPresensi = Presensi::where('siswa_id', $siswa->id)
                ->where('kelas_id', $session->kelas_id)
                ->whereDate('tanggal_presensi', $session->tanggal)
                ->first();

            if ($type === 'checkin') {
                if ($existingPresensi && $existingPresensi->hasCheckedIn()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda sudah melakukan check-in pada pukul ' . $existingPresensi->waktu_checkin->format('H:i')
                    ], 400);
                }
            } else {
                if ($existingPresensi && $existingPresensi->hasCheckedOut()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda sudah melakukan check-out pada pukul ' . $existingPresensi->waktu_checkout->format('H:i')
                    ], 400);
                }
            }

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

            if ($distance > $allowedRadius) {
                
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

            if ($type === 'checkin') {
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

                $this->sendWhatsAppNotification($presensi, 'checkin');

                return response()->json([
                    'success' => true,
                    'message' => 'Check-in berhasil dicatat!',
                    'data' => [
                        'presensi_id' => $presensi->id,
                        'type' => 'checkin',
                        'status' => $presensi->status,
                        'waktu' => $presensi->waktu_checkin->format('H:i:s'),
                        'tanggal' => $presensi->tanggal_presensi->format('d M Y'),
                        'distance' => $distance,
                    ]
                ]);

            } else {
                if ($existingPresensi) {
                    $existingPresensi->update([
                        'waktu_checkout' => now(),
                        'latitude_checkout' => $validated['latitude'],
                        'longitude_checkout' => $validated['longitude'],
                        'is_valid_location_checkout' => $isValidLocation,
                        'keterangan_checkout' => $keterangan,
                    ]);

                    $this->sendWhatsAppNotification($existingPresensi, 'checkout');

                    return response()->json([
                        'success' => true,
                        'message' => 'Check-out berhasil dicatat!',
                        'data' => [
                            'presensi_id' => $existingPresensi->id,
                            'type' => 'checkout',
                            'status' => $existingPresensi->status,
                            'waktu' => $existingPresensi->waktu_checkout->format('H:i:s'),
                            'tanggal' => $existingPresensi->tanggal_presensi->format('d M Y'),
                            'distance' => $distance,
                        ]
                    ]);
                } else {
                    $presensi = Presensi::create([
                        'session_id' => $session->id,
                        'qr_code_id' => $qrCode->id,
                        'kelas_id' => $session->kelas_id,
                        'siswa_id' => $siswa->id,
                        'tanggal_presensi' => $session->tanggal,
                        'waktu_checkout' => now(),
                        'status' => 'hadir',
                        'latitude_checkout' => $validated['latitude'],
                        'longitude_checkout' => $validated['longitude'],
                        'is_valid_location_checkout' => $isValidLocation,
                        'keterangan_checkout' => $keterangan,
                        'metode' => 'qr',
                    ]);

                    $this->sendWhatsAppNotification($presensi, 'checkout');

                    return response()->json([
                        'success' => true,
                        'message' => 'Check-out berhasil dicatat!',
                        'data' => [
                            'presensi_id' => $presensi->id,
                            'type' => 'checkout',
                            'status' => $presensi->status,
                            'waktu' => $presensi->waktu_checkout->format('H:i:s'),
                            'tanggal' => $presensi->tanggal_presensi->format('d M Y'),
                            'distance' => $distance,
                        ]
                    ]);
                }
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan presensi: ' . $e->getMessage()
            ], 500);
        }
    }

    protected function sendWhatsAppNotification($presensi, $type = 'checkin')
    {
        try {
            if (!$this->fonnteService->isEnabled()) {
                return;
            }

            $result = $this->fonnteService->sendPresensiNotification($presensi, $type);

            if ($result['success']) {
                if ($type === 'checkin') {
                    $presensi->update(['notifikasi_checkin_terkirim' => true]);
                } else {
                    $presensi->update(['notifikasi_checkout_terkirim' => true]);
                }

            } else if (!isset($result['skipped'])) {
            }

        } catch (\Exception $e) {
        }
    }

    private function validateLocation($lat1, $lon1, $lat2, $lon2, $radiusInMeters)
    {
        if (!$lat2 || !$lon2) {
            return 0;
        }

        $earthRadius = 6371000; 
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        return round($distance, 2);
    }
}
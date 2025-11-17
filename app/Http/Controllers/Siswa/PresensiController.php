<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\PresensiSession;
use App\Models\Presensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;  // ← PENTING
use Carbon\Carbon;

class PresensiController extends Controller
{
    /**
     * Halaman Scanner QR Code
     */
    public function index()
    {
        $siswa = Auth::user();
        
        // Cek apakah siswa sudah memiliki kelas
        if (!$siswa->kelas_id) {
            return redirect()->route('siswa.home')
                ->with('error', 'Anda belum terdaftar di kelas manapun. Silakan hubungi admin.');
        }

        // Cek apakah sudah presensi hari ini
        $todayPresensi = Presensi::where('siswa_id', $siswa->id)
            ->whereDate('tanggal_presensi', now())
            ->first();

        Log::info('Scanner page loaded', [
            'siswa_id' => $siswa->id,
            'siswa_name' => $siswa->name,
            'kelas_id' => $siswa->kelas_id,
            'already_attended' => !!$todayPresensi
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
            $qrCode = $request->input('qr_code');
            
            if (!$qrCode) {
                Log::warning('QR Code empty');
                return response()->json([
                    'success' => false,
                    'message' => 'QR Code tidak valid'
                ], 400);
            }

            Log::info('Searching for session with QR code', ['qr_code' => $qrCode]);

            // Cari session berdasarkan QR code
            $session = PresensiSession::where('qr_code', $qrCode)
                ->with('kelas.jurusan')
                ->first();

            if (!$session) {
                Log::warning('Session not found', ['qr_code' => $qrCode]);
                return response()->json([
                    'success' => false,
                    'message' => 'QR Code tidak ditemukan atau sudah tidak berlaku'
                ], 404);
            }

            Log::info('Session found', [
                'session_id' => $session->id,
                'kelas_id' => $session->kelas_id,
                'status' => $session->status,
                'tanggal' => $session->tanggal->format('Y-m-d')
            ]);

            // Cek apakah session masih aktif
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

            // Cek waktu presensi
            $now = Carbon::now();
            $sessionDate = $session->tanggal;
            
            // PERBAIKAN: Format jam_mulai dan jam_selesai dengan benar
            $jamMulai = Carbon::parse(
                $sessionDate->format('Y-m-d') . ' ' . $session->jam_mulai->format('H:i:s')
            );
            $jamSelesai = Carbon::parse(
                $sessionDate->format('Y-m-d') . ' ' . $session->jam_selesai->format('H:i:s')
            );

            Log::info('Time validation', [
                'now' => $now->toDateTimeString(),
                'session_date' => $sessionDate->format('Y-m-d'),
                'jam_mulai' => $jamMulai->toDateTimeString(),
                'jam_selesai' => $jamSelesai->toDateTimeString()
            ]);

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

            if ($now->lt($jamMulai)) {
                Log::warning('Too early', [
                    'now' => $now->toTimeString(),
                    'jam_mulai' => $jamMulai->toTimeString()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Presensi belum dimulai. Waktu mulai: ' . $jamMulai->format('H:i')
                ], 400);
            }

            if ($now->gt($jamSelesai)) {
                Log::warning('Too late', [
                    'now' => $now->toTimeString(),
                    'jam_selesai' => $jamSelesai->toTimeString()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Waktu presensi sudah berakhir. Waktu selesai: ' . $jamSelesai->format('H:i')
                ], 400);
            }

            // Return data session yang valid
            $responseData = [
                'session_id' => $session->id,
                'kelas' => $session->kelas->nama_kelas,
                'jurusan' => $session->kelas->jurusan->nama_jurusan,
                'tanggal' => $session->tanggal->format('d M Y'),
                'jam_mulai' => $session->jam_mulai->format('H:i'),
                'jam_selesai' => $session->jam_selesai->format('H:i'),
                'latitude' => (float) $session->latitude,
                'longitude' => (float) $session->longitude,
                'radius' => (int) $session->radius,
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
     * Submit Presensi setelah scan QR
     */
    public function submitPresensi(Request $request)
    {
        Log::info('Submit Presensi Request', [
            'request_data' => $request->all(),
            'user_id' => Auth::id()
        ]);

        try {
            $validated = $request->validate([
                'session_id' => 'required|exists:presensi_sessions,id',
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
            ]);

            $siswa = Auth::user();

            // Cek apakah siswa memiliki kelas
            if (!$siswa->kelas_id) {
                Log::warning('Student has no class', ['siswa_id' => $siswa->id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Anda belum terdaftar di kelas manapun'
                ], 400);
            }

            // Ambil session
            $session = PresensiSession::findOrFail($validated['session_id']);

            Log::info('Session retrieved', [
                'session_id' => $session->id,
                'session_kelas_id' => $session->kelas_id,
                'siswa_kelas_id' => $siswa->kelas_id
            ]);

            // Cek apakah siswa di kelas yang sama dengan session
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

            // Cek apakah sudah presensi hari ini
            $existingPresensi = Presensi::where('siswa_id', $siswa->id)
                ->where('kelas_id', $session->kelas_id)
                ->whereDate('tanggal_presensi', $session->tanggal)
                ->first();

            if ($existingPresensi) {
                Log::warning('Already attended', [
                    'siswa_id' => $siswa->id,
                    'presensi_id' => $existingPresensi->id
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah melakukan presensi hari ini',
                    'presensi' => [
                        'status' => $existingPresensi->status,
                        'waktu' => $existingPresensi->created_at->format('H:i:s')
                    ]
                ], 400);
            }

            // Validasi lokasi menggunakan Haversine formula
            $isValidLocation = $this->validateLocation(
                $validated['latitude'],
                $validated['longitude'],
                $session->latitude,
                $session->longitude,
                $session->radius
            );

            Log::info('Location validation result', [
                'siswa_lat' => $validated['latitude'],
                'siswa_lng' => $validated['longitude'],
                'session_lat' => $session->latitude,
                'session_lng' => $session->longitude,
                'radius' => $session->radius,
                'is_valid' => $isValidLocation
            ]);

            // Buat presensi baru
            $presensi = Presensi::create([
                'session_id' => $session->id,
                'kelas_id' => $session->kelas_id,
                'siswa_id' => $siswa->id,
                'tanggal_presensi' => $session->tanggal,
                'waktu_absen' => now(),
                'status' => 'hadir',
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'metode' => 'qr',
                'is_valid_location' => $isValidLocation,
                'keterangan' => !$isValidLocation ? 'Lokasi di luar radius yang ditentukan' : null,
            ]);

            Log::info('Presensi created successfully', [
                'presensi_id' => $presensi->id,
                'siswa_id' => $siswa->id,
                'is_valid_location' => $isValidLocation
            ]);

            return response()->json([
                'success' => true,
                'message' => $isValidLocation 
                    ? 'Presensi berhasil dicatat!' 
                    : 'Presensi berhasil, namun lokasi Anda di luar radius yang ditentukan',
                'data' => [
                    'presensi_id' => $presensi->id,
                    'status' => $presensi->status,
                    'waktu' => $presensi->created_at->format('H:i:s'),
                    'tanggal' => $presensi->tanggal_presensi->format('d M Y'),
                    'is_valid_location' => $isValidLocation
                ]
            ]);

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
     * Validasi jarak lokasi menggunakan Haversine formula
     */
    private function validateLocation($lat1, $lon1, $lat2, $lon2, $radiusInMeters)
    {
        if (!$lat2 || !$lon2) {
            Log::info('No target coordinates, location considered valid');
            return true;
        }

        $earthRadius = 6371000;
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

        return $distance <= $radiusInMeters;
    }
}
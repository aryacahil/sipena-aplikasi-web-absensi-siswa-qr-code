<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\PresensiSession;
use App\Models\Presensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PresensiController extends Controller
{
    /**
     * Halaman scan QR Code
     */
    public function scan($code)
    {
        try {
            // Cari session berdasarkan QR code
            $session = PresensiSession::where('qr_code', $code)
                ->with(['kelas.jurusan'])
                ->firstOrFail();
            
            // Auto-update status jika expired
            $session->updateStatusIfExpired();
            
            // Refresh data setelah update
            $session->refresh();
            
            return view('siswa.presensi.scan', compact('session'));
            
        } catch (\Exception $e) {
            Log::error('Error loading scan page', [
                'code' => $code,
                'error' => $e->getMessage()
            ]);
            
            return view('siswa.presensi.scan-error', [
                'message' => 'QR Code tidak valid atau sudah tidak aktif'
            ]);
        }
    }
    
    /**
     * Proses presensi dari scan QR Code
     */
    public function submitPresensi(Request $request)
    {
        try {
            $validated = $request->validate([
                'session_id' => 'required|exists:presensi_sessions,id',
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
            ]);
            
            $siswa = Auth::user();
            $session = PresensiSession::findOrFail($validated['session_id']);
            
            // Validasi 1: Cek apakah siswa punya kelas
            if (!$siswa->kelas_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda belum terdaftar di kelas manapun. Hubungi admin untuk penempatan kelas.'
                ], 422);
            }
            
            // Validasi 2: Cek apakah siswa di kelas yang sama dengan session
            if ($siswa->kelas_id != $session->kelas_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'QR Code ini bukan untuk kelas Anda. Kelas Anda: ' . ($siswa->kelas ? $siswa->kelas->nama_kelas : 'Belum ada')
                ], 422);
            }
            
            // Validasi 3: Cek apakah session masih aktif
            if (!$session->isActive()) {
                $statusText = $session->getStatusText();
                
                if ($statusText === 'waiting') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Sesi presensi belum dimulai. Mulai: ' . $session->jam_mulai->format('H:i')
                    ], 422);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Sesi presensi sudah berakhir atau expired'
                    ], 422);
                }
            }
            
            // Validasi 4: Cek apakah sudah presensi hari ini
            $today = Carbon::today();
            $existingPresensi = Presensi::where('siswa_id', $siswa->id)
                ->where('kelas_id', $session->kelas_id)
                ->whereDate('tanggal_presensi', $today)
                ->first();
            
            if ($existingPresensi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah melakukan presensi hari ini pada jam ' . 
                                $existingPresensi->created_at->format('H:i')
                ], 422);
            }
            
            // Validasi 5: Validasi lokasi
            $distance = PresensiSession::calculateDistance(
                $validated['latitude'],
                $validated['longitude'],
                $session->latitude,
                $session->longitude
            );
            
            $isValidLocation = $distance <= $session->radius;
            
            if (!$isValidLocation) {
                return response()->json([
                    'success' => false,
                    'message' => sprintf(
                        'Anda berada di luar radius yang diizinkan. Jarak Anda: %.0f meter (Maksimal: %d meter)',
                        $distance,
                        $session->radius
                    )
                ], 422);
            }
            
            // Semua validasi lolos - Simpan presensi
            $presensi = Presensi::create([
                'session_id' => $session->id,
                'kelas_id' => $session->kelas_id,
                'siswa_id' => $siswa->id,
                'tanggal_presensi' => $today,
                'status' => 'hadir',
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'metode' => 'qr',
                'is_valid_location' => true,
                'keterangan' => 'Presensi via QR Code'
            ]);
            
            Log::info('Presensi berhasil', [
                'siswa_id' => $siswa->id,
                'siswa_name' => $siswa->name,
                'kelas' => $session->kelas->nama_kelas,
                'waktu' => $presensi->created_at,
                'jarak' => round($distance, 2) . ' meter'
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Presensi berhasil disimpan!',
                'data' => [
                    'nama' => $siswa->name,
                    'kelas' => $session->kelas->nama_kelas,
                    'waktu' => $presensi->created_at->format('H:i:s'),
                    'tanggal' => $presensi->tanggal_presensi->format('d M Y'),
                    'status' => 'hadir'
                ]
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Error submit presensi', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan presensi. Silakan coba lagi.'
            ], 500);
        }
    }
}
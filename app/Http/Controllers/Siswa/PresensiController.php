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
     * Halaman scanner QR Code (tanpa parameter)
     */
    public function index()
    {
        return view('siswa.presensi.index');
    }
    
    /**
     * Proses presensi dari scan QR Code
     */
    public function submitPresensi(Request $request)
    {
        try {
            // LOG REQUEST DATA
            Log::info('═══════════════════════════════════════════════════════');
            Log::info('🎯 SUBMIT PRESENSI REQUEST');
            Log::info('═══════════════════════════════════════════════════════');
            Log::info('User ID: ' . Auth::id());
            Log::info('User Name: ' . Auth::user()->name);
            Log::info('Request Data:', $request->all());
            Log::info('───────────────────────────────────────────────────────');
            
            $validated = $request->validate([
                'qr_code' => 'required|string',
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
            ]);
            
            $siswa = Auth::user();
            
            // LOG SEARCH QR CODE
            Log::info('🔍 SEARCHING FOR QR CODE');
            Log::info('Received QR Code: "' . $validated['qr_code'] . '"');
            Log::info('QR Code Length: ' . strlen($validated['qr_code']));
            Log::info('QR Code Type: ' . gettype($validated['qr_code']));
            
            // Cari session berdasarkan QR code
            $session = PresensiSession::where('qr_code', $validated['qr_code'])->first();
            
            // LOG RESULT
            if ($session) {
                Log::info('✅ SESSION FOUND!');
                Log::info('Session ID: ' . $session->id);
                Log::info('Session QR Code: "' . $session->qr_code . '"');
                Log::info('Kelas ID: ' . $session->kelas_id);
                Log::info('Kelas Name: ' . ($session->kelas ? $session->kelas->nama_kelas : 'N/A'));
                Log::info('Status: ' . $session->status);
            } else {
                Log::warning('❌ SESSION NOT FOUND!');
                Log::warning('Searched QR: "' . $validated['qr_code'] . '"');
                
                // Log all available sessions for comparison
                $allSessions = PresensiSession::select('id', 'qr_code', 'kelas_id', 'status')
                    ->get()
                    ->map(function($s) use ($validated) {
                        return [
                            'id' => $s->id,
                            'qr_code' => $s->qr_code,
                            'match' => ($s->qr_code === $validated['qr_code']) ? 'YES' : 'NO',
                            'qr_length' => strlen($s->qr_code),
                            'status' => $s->status,
                        ];
                    })
                    ->toArray();
                
                Log::warning('Available Sessions:', $allSessions);
            }
            
            Log::info('───────────────────────────────────────────────────────');
            
            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => 'QR Code tidak valid atau tidak ditemukan'
                ], 422);
            }
            
            // Auto-update status jika expired
            $session->updateStatusIfExpired();
            $session->refresh();
            
            // Validasi 1: Cek apakah siswa punya kelas
            if (!$siswa->kelas_id) {
                Log::warning('❌ Siswa belum punya kelas');
                return response()->json([
                    'success' => false,
                    'message' => 'Anda belum terdaftar di kelas manapun. Hubungi admin untuk penempatan kelas.'
                ], 422);
            }
            
            // Validasi 2: Cek apakah siswa di kelas yang sama dengan session
            if ($siswa->kelas_id != $session->kelas_id) {
                Log::warning('❌ Kelas tidak cocok');
                Log::warning('Siswa Kelas ID: ' . $siswa->kelas_id);
                Log::warning('Session Kelas ID: ' . $session->kelas_id);
                
                return response()->json([
                    'success' => false,
                    'message' => 'QR Code ini bukan untuk kelas Anda. Kelas Anda: ' . ($siswa->kelas ? $siswa->kelas->nama_kelas : 'Belum ada')
                ], 422);
            }
            
            // Validasi 3: Cek apakah session masih aktif
            if (!$session->isActive()) {
                Log::warning('❌ Session tidak aktif');
                Log::warning('Session Status: ' . $session->status);
                
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
                Log::warning('❌ Sudah presensi hari ini');
                Log::warning('Existing Presensi ID: ' . $existingPresensi->id);
                
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
            
            Log::info('📍 LOCATION VALIDATION');
            Log::info('Siswa Location: ' . $validated['latitude'] . ', ' . $validated['longitude']);
            Log::info('Session Location: ' . $session->latitude . ', ' . $session->longitude);
            Log::info('Distance: ' . round($distance, 2) . ' meters');
            Log::info('Radius: ' . $session->radius . ' meters');
            Log::info('Valid: ' . ($isValidLocation ? 'YES' : 'NO'));
            Log::info('───────────────────────────────────────────────────────');
            
            if (!$isValidLocation) {
                Log::warning('❌ Lokasi tidak valid - di luar radius');
                
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
            
            Log::info('✅ PRESENSI BERHASIL DISIMPAN!');
            Log::info('Presensi ID: ' . $presensi->id);
            Log::info('Siswa: ' . $siswa->name);
            Log::info('Kelas: ' . $session->kelas->nama_kelas);
            Log::info('Waktu: ' . $presensi->created_at->format('Y-m-d H:i:s'));
            Log::info('Jarak: ' . round($distance, 2) . ' meter');
            Log::info('═══════════════════════════════════════════════════════');
            
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
            Log::error('❌ VALIDATION ERROR');
            Log::error('Errors:', $e->errors());
            Log::error('Request:', $request->all());
            Log::error('═══════════════════════════════════════════════════════');
            
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('❌ EXCEPTION ERROR');
            Log::error('Message: ' . $e->getMessage());
            Log::error('File: ' . $e->getFile() . ':' . $e->getLine());
            Log::error('User ID: ' . Auth::id());
            Log::error('Request:', $request->all());
            Log::error('Trace: ' . $e->getTraceAsString());
            Log::error('═══════════════════════════════════════════════════════');
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan presensi. Silakan coba lagi.'
            ], 500);
        }
    }
}
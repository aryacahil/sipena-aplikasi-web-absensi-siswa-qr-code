<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\PresensiSession;
use App\Models\Presensi;
use Illuminate\Http\Request;

class ScanController extends Controller
{
    public function scan()
    {
        return view('siswa.presensi.scan');
    }

    public function verifyForm($code)
    {
        $session = PresensiSession::where('qr_code', $code)
            ->with('kelas.jurusan')
            ->first();

        if (!$session) {
            return redirect()->route('siswa.home')
                ->with('error', 'QR Code tidak valid');
        }

        $sudahAbsen = Presensi::where('presensi_session_id', $session->id)
            ->where('siswa_id', auth()->id())
            ->first();

        return view('siswa.presensi.verify', compact('session', 'sudahAbsen'));
    }

    public function verify(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $session = PresensiSession::where('qr_code', $request->qr_code)
            ->where('status', 'active')
            ->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'QR Code tidak valid atau session sudah ditutup'
            ], 404);
        }

        if ($session->isExpired()) {
            return response()->json([
                'success' => false,
                'message' => 'Session presensi sudah berakhir'
            ], 400);
        }

        $existingPresensi = Presensi::where('presensi_session_id', $session->id)
            ->where('siswa_id', auth()->id())
            ->first();

        if ($existingPresensi) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan presensi pada session ini'
            ], 400);
        }

        $distance = Presensi::calculateDistance(
            $session->latitude,
            $session->longitude,
            $request->latitude,
            $request->longitude
        );

        $isValidLocation = $distance <= $session->radius;

        if (!$isValidLocation) {
            return response()->json([
                'success' => false,
                'message' => 'Lokasi Anda terlalu jauh dari lokasi presensi. Jarak: ' . round($distance) . ' meter (maksimal ' . $session->radius . ' meter)',
                'distance' => round($distance),
                'max_distance' => $session->radius
            ], 400);
        }

        $presensi = Presensi::create([
            'presensi_session_id' => $session->id,
            'siswa_id' => auth()->id(),
            'waktu_absen' => now(),
            'status' => 'hadir',
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'tipe_absen' => 'qr',
            'is_valid_location' => true,
        ]);

        // TODO: Kirim notifikasi WhatsApp ke orang tua
        // $this->sendWhatsAppNotification($presensi);

        return response()->json([
            'success' => true,
            'message' => 'Presensi berhasil dicatat',
            'data' => [
                'waktu' => $presensi->waktu_absen->format('d/m/Y H:i:s'),
                'distance' => round($distance) . ' meter',
                'kelas' => $session->kelas->nama_kelas
            ]
        ]);
    }

    public function history()
    {
        $presensis = Presensi::with(['session.kelas.jurusan'])
            ->where('siswa_id', auth()->id())
            ->latest()
            ->paginate(20);
            
        return view('siswa.presensi.history', compact('presensis'));
    }

    /**
     * Kirim notifikasi WhatsApp ke orang tua
     * TODO: Implementasi API WhatsApp
     */
    private function sendWhatsAppNotification($presensi)
    {
        $siswa = $presensi->siswa;
        $session = $presensi->session;
        
        if (!$siswa->parent_phone) {
            return;
        }

        $message = "Assalamualaikum,\n\n";
        $message .= "Informasi Presensi:\n";
        $message .= "Nama: {$siswa->name}\n";
        $message .= "Kelas: {$session->kelas->nama_kelas}\n";
        $message .= "Status: " . strtoupper($presensi->status) . "\n";
        $message .= "Waktu: {$presensi->waktu_absen->format('d/m/Y H:i:s')}\n";
        $message .= "Lokasi: Valid (" . round($presensi->distance ?? 0) . "m)\n\n";
        $message .= "Terima kasih.";

        // TODO: Kirim via API WhatsApp (Twilio, WA Business API, dll)
        // Contoh menggunakan Twilio atau Fonnte
    }
}
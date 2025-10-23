<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\PresensiSession;
use App\Models\Presensi;
use App\Models\Kelas;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PresensiController extends Controller
{
    /**
     * Tampilkan daftar session presensi yang dibuat guru
     */
    public function index()
    {
        $sessions = PresensiSession::with(['kelas.jurusan', 'guru'])
            ->where('created_by', auth()->id())
            ->latest()
            ->paginate(10);
            
        return view('guru.presensi.index', compact('sessions'));
    }

    /**
     * Form untuk buat session presensi baru
     */
    public function create()
    {
        // Guru bisa pilih semua kelas
        $kelas = Kelas::with('jurusan')->orderBy('tingkat')->orderBy('nama_kelas')->get();
        
        return view('guru.presensi.create', compact('kelas'));
    }

    /**
     * Simpan session presensi baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'tanggal' => 'required|date',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required|after:jam_mulai',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'nullable|integer|min:50|max:500',
            'keterangan' => 'nullable|string',
        ], [
            'kelas_id.required' => 'Kelas wajib dipilih',
            'tanggal.required' => 'Tanggal wajib diisi',
            'jam_mulai.required' => 'Jam mulai wajib diisi',
            'jam_selesai.required' => 'Jam selesai wajib diisi',
            'jam_selesai.after' => 'Jam selesai harus setelah jam mulai',
            'latitude.required' => 'Lokasi GPS wajib diisi',
            'longitude.required' => 'Lokasi GPS wajib diisi',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['radius'] = $validated['radius'] ?? 200;
        $validated['status'] = 'active';

        $session = PresensiSession::create($validated);

        return redirect()->route('guru.presensi.show', $session->id)
            ->with('success', 'Session presensi berhasil dibuat');
    }

    /**
     * Tampilkan detail session presensi & QR Code
     */
    public function show($id)
    {
        $session = PresensiSession::with([
            'kelas.jurusan', 
            'kelas.siswa' => function($query) {
                $query->where('status', 'active');
            }, 
            'presensis.siswa', 
            'guru'
        ])->findOrFail($id);
            
        // Cek apakah guru yang membuat session ini
        if ($session->created_by !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses ke session presensi ini');
        }
        
        // Generate QR Code
        $qrCodeUrl = route('siswa.presensi.verify-form', ['code' => $session->qr_code]);
        $qrCode = QrCode::size(300)
            ->margin(1)
            ->generate($qrCodeUrl);
            
        // Hitung statistik
        $totalSiswa = $session->kelas->siswa->count();
        $totalHadir = $session->presensis->where('status', 'hadir')->count();
        $totalIzin = $session->presensis->where('status', 'izin')->count();
        $totalSakit = $session->presensis->where('status', 'sakit')->count();
        $belumAbsen = $totalSiswa - $session->presensis->count();
        
        // Siswa yang belum absen
        $siswaIds = $session->presensis->pluck('siswa_id')->toArray();
        $siswaBelumAbsen = $session->kelas->siswa->whereNotIn('id', $siswaIds);
        
        return view('guru.presensi.show', compact(
            'session', 
            'qrCode', 
            'totalSiswa', 
            'totalHadir', 
            'totalIzin', 
            'totalSakit', 
            'belumAbsen',
            'siswaBelumAbsen'
        ));
    }

    /**
     * Tutup session presensi
     */
    public function close($id)
    {
        $session = PresensiSession::where('id', $id)
            ->where('created_by', auth()->id())
            ->firstOrFail();
            
        $session->update(['status' => 'closed']);
        
        return redirect()->back()
            ->with('success', 'Session presensi ditutup');
    }

    /**
     * Buka kembali session presensi
     */
    public function reopen($id)
    {
        $session = PresensiSession::where('id', $id)
            ->where('created_by', auth()->id())
            ->firstOrFail();
            
        $session->update(['status' => 'active']);
        
        return redirect()->back()
            ->with('success', 'Session presensi dibuka kembali');
    }

    /**
     * Hapus session presensi
     */
    public function destroy($id)
    {
        $session = PresensiSession::where('id', $id)
            ->where('created_by', auth()->id())
            ->firstOrFail();
            
        $session->delete();
        
        return redirect()->route('guru.presensi.index')
            ->with('success', 'Session presensi berhasil dihapus');
    }

    /**
     * Download QR Code sebagai gambar PNG
     */
    public function downloadQr($id)
    {
        $session = PresensiSession::where('id', $id)
            ->where('created_by', auth()->id())
            ->firstOrFail();
            
        $qrCodeUrl = route('siswa.presensi.verify-form', ['code' => $session->qr_code]);
        
        $qrCode = QrCode::format('png')
            ->size(500)
            ->margin(2)
            ->generate($qrCodeUrl);
            
        $filename = 'qr-presensi-' . $session->kelas->kode_kelas . '-' . $session->tanggal->format('d-m-Y') . '.png';
            
        return response($qrCode)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Absen manual untuk siswa (izin/sakit/alpha)
     */
    public function absenManual(Request $request, $sessionId)
    {
        $validated = $request->validate([
            'siswa_id' => 'required|exists:users,id',
            'status' => 'required|in:hadir,izin,sakit,alpha',
            'keterangan' => 'nullable|string|max:500',
        ], [
            'siswa_id.required' => 'Siswa wajib dipilih',
            'status.required' => 'Status presensi wajib dipilih',
        ]);

        $session = PresensiSession::where('id', $sessionId)
            ->where('created_by', auth()->id())
            ->firstOrFail();

        // Cek apakah siswa sudah absen
        $existingPresensi = Presensi::where('presensi_session_id', $session->id)
            ->where('siswa_id', $validated['siswa_id'])
            ->first();

        if ($existingPresensi) {
            return redirect()->back()
                ->with('error', 'Siswa sudah melakukan presensi');
        }

        // Cek apakah siswa ada di kelas ini
        $siswa = \App\Models\User::where('id', $validated['siswa_id'])
            ->where('kelas_id', $session->kelas_id)
            ->first();

        if (!$siswa) {
            return redirect()->back()
                ->with('error', 'Siswa tidak terdaftar di kelas ini');
        }

        Presensi::create([
            'presensi_session_id' => $session->id,
            'siswa_id' => $validated['siswa_id'],
            'waktu_absen' => now(),
            'status' => $validated['status'],
            'keterangan' => $validated['keterangan'],
            'tipe_absen' => 'manual',
            'is_valid_location' => true,
        ]);

        return redirect()->back()
            ->with('success', 'Presensi manual berhasil dicatat');
    }

    /**
     * Update status presensi siswa
     */
    public function updatePresensi(Request $request, $sessionId, $presensiId)
    {
        $validated = $request->validate([
            'status' => 'required|in:hadir,izin,sakit,alpha',
            'keterangan' => 'nullable|string|max:500',
        ], [
            'status.required' => 'Status presensi wajib dipilih',
        ]);

        $session = PresensiSession::where('id', $sessionId)
            ->where('created_by', auth()->id())
            ->firstOrFail();

        $presensi = Presensi::where('id', $presensiId)
            ->where('presensi_session_id', $session->id)
            ->firstOrFail();

        $presensi->update($validated);

        return redirect()->back()
            ->with('success', 'Status presensi berhasil diupdate');
    }

    /**
     * Hapus presensi siswa
     */
    public function deletePresensi($sessionId, $presensiId)
    {
        $session = PresensiSession::where('id', $sessionId)
            ->where('created_by', auth()->id())
            ->firstOrFail();

        $presensi = Presensi::where('id', $presensiId)
            ->where('presensi_session_id', $session->id)
            ->firstOrFail();

        $presensi->delete();

        return redirect()->back()
            ->with('success', 'Presensi berhasil dihapus');
    }
}
<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\PresensiSession;
use App\Models\Presensi;
use App\Models\Kelas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PresensiController extends Controller
{
    public function index(Request $request)
    {
        $query = Presensi::with(['session.kelas', 'siswa']);

        // Filter
        if ($request->filled('kelas_id')) {
            $query->whereHas('session', function($q) use ($request) {
                $q->where('kelas_id', $request->kelas_id);
            });
        }

        if ($request->filled('tanggal')) {
            $query->whereHas('session', function($q) use ($request) {
                $q->whereDate('tanggal', $request->tanggal);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $presensis = $query->latest()->paginate(15);
        $kelas = Kelas::with('jurusan')->get();

        // Statistics
        $stats = [
            'hadir' => Presensi::where('status', 'hadir')->count(),
            'izin' => Presensi::where('status', 'izin')->count(),
            'sakit' => Presensi::where('status', 'sakit')->count(),
            'alpha' => Presensi::where('status', 'alpha')->count(),
        ];

        return view('admin.presensi.index', compact('presensis', 'kelas', 'stats'));
    }

    public function create(PresensiSession $session)
    {
        $session->load('kelas.siswa');
        
        // Get siswa yang belum presensi
        $siswaIds = $session->presensis()->pluck('siswa_id')->toArray();
        $siswaAvailable = $session->kelas->siswa()
            ->whereNotIn('id', $siswaIds)
            ->get();

        return view('admin.presensi.create', compact('session', 'siswaAvailable'));
    }

    public function store(Request $request, PresensiSession $session)
    {
        $validated = $request->validate([
            'siswa_id' => 'required|exists:users,id',
            'status' => 'required|in:hadir,izin,sakit,alpha',
            'keterangan' => 'nullable|string|max:500',
            'bukti_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        // Check if already exists
        $exists = Presensi::where('session_id', $session->id)
            ->where('siswa_id', $validated['siswa_id'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'Siswa sudah melakukan presensi');
        }

        $validated['session_id'] = $session->id;
        $validated['waktu_presensi'] = now();
        $validated['metode'] = 'manual';

        // Handle file upload
        if ($request->hasFile('bukti_file')) {
            $file = $request->file('bukti_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('bukti_presensi', $filename, 'public');
            $validated['bukti_file'] = $path;
        }

        Presensi::create($validated);

        return redirect()
            ->route('admin.qrcode.show', $session->id)
            ->with('success', 'Presensi berhasil ditambahkan');
    }

    public function edit(Presensi $presensi)
    {
        $presensi->load(['session', 'siswa']);
        return view('admin.presensi.edit', compact('presensi'));
    }

    public function update(Request $request, Presensi $presensi)
    {
        $validated = $request->validate([
            'status' => 'required|in:hadir,izin,sakit,alpha',
            'keterangan' => 'nullable|string|max:500',
            'bukti_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        // Handle file upload
        if ($request->hasFile('bukti_file')) {
            // Delete old file
            if ($presensi->bukti_file) {
                Storage::disk('public')->delete($presensi->bukti_file);
            }

            $file = $request->file('bukti_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('bukti_presensi', $filename, 'public');
            $validated['bukti_file'] = $path;
        }

        $presensi->update($validated);

        return redirect()
            ->route('admin.presensi.index')
            ->with('success', 'Presensi berhasil diperbarui');
    }

    public function destroy(Presensi $presensi)
    {
        // Delete file if exists
        if ($presensi->bukti_file) {
            Storage::disk('public')->delete($presensi->bukti_file);
        }

        $presensi->delete();

        return redirect()
            ->route('admin.presensi.index')
            ->with('success', 'Presensi berhasil dihapus');
    }

    public function bulkCreate(Request $request, PresensiSession $session)
    {
        $validated = $request->validate([
            'siswa_ids' => 'required|array',
            'siswa_ids.*' => 'exists:users,id',
            'status' => 'required|in:hadir,izin,sakit,alpha',
        ]);

        $created = 0;
        foreach ($validated['siswa_ids'] as $siswaId) {
            // Check if not exists
            $exists = Presensi::where('session_id', $session->id)
                ->where('siswa_id', $siswaId)
                ->exists();

            if (!$exists) {
                Presensi::create([
                    'session_id' => $session->id,
                    'siswa_id' => $siswaId,
                    'status' => $validated['status'],
                    'waktu_presensi' => now(),
                    'metode' => 'manual',
                ]);
                $created++;
            }
        }

        return redirect()
            ->route('guru.qrcode.show', $session->id)
            ->with('success', "{$created} presensi berhasil ditambahkan");
    }
}
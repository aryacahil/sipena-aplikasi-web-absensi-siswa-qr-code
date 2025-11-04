<?php
// File: app/Http/Controllers/Admin/PresensiController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PresensiSession;
use App\Models\Presensi;
use App\Models\Kelas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PresensiController extends Controller
{
    /**
     * Tampilkan daftar kelas untuk dipilih
     */
    public function index(Request $request)
    {
        $query = Kelas::with(['jurusan', 'siswa']);

        // Filter berdasarkan jurusan
        if ($request->filled('jurusan_id')) {
            $query->where('jurusan_id', $request->jurusan_id);
        }

        // Search
        if ($request->filled('search')) {
            $query->where('nama_kelas', 'like', '%' . $request->search . '%');
        }

        $kelasList = $query->withCount('siswa')->get();
        $jurusans = \App\Models\Jurusan::all();

        // Statistics (hari ini)
        $today = now()->format('Y-m-d');
        $stats = [
            'total_kelas' => $kelasList->count(),
            'total_siswa' => User::where('role', 2)->count(),
            'hadir_hari_ini' => Presensi::whereDate('tanggal_presensi', $today)
                ->where('status', 'hadir')->count(),
            'alpha_hari_ini' => Presensi::whereDate('tanggal_presensi', $today)
                ->where('status', 'alpha')->count(),
        ];

        return view('admin.presensi.index', compact('kelasList', 'jurusans', 'stats'));
    }

    /**
     * Tampilkan detail presensi per kelas
     * TIDAK LAGI BERGANTUNG PADA SESSION!
     */
    public function showKelas(Request $request, Kelas $kelas)
    {
        $kelas->load(['jurusan', 'siswa']);
        
        // Default tanggal: hari ini
        $filterDate = $request->filled('tanggal') ? $request->tanggal : now()->format('Y-m-d');

        // Ambil semua siswa di kelas
        $allSiswa = $kelas->siswa;

        // Build attendance data berdasarkan TANGGAL SAJA (bukan session!)
        $attendanceData = [];
        foreach ($allSiswa as $siswa) {
            // Cari presensi siswa di tanggal tersebut
            $presensi = Presensi::where('kelas_id', $kelas->id)
                ->where('siswa_id', $siswa->id)
                ->whereDate('tanggal_presensi', $filterDate)
                ->first();

            $attendanceData[] = [
                'siswa' => $siswa,
                'presensi' => $presensi,
                'status' => $presensi ? $presensi->status : 'belum',
            ];
        }

        // Statistics untuk kelas ini pada tanggal tersebut
        $stats = [
            'total_siswa' => $allSiswa->count(),
            'hadir' => collect($attendanceData)->where('status', 'hadir')->count(),
            'izin' => collect($attendanceData)->where('status', 'izin')->count(),
            'sakit' => collect($attendanceData)->where('status', 'sakit')->count(),
            'alpha' => collect($attendanceData)->where('status', 'alpha')->count(),
            'belum' => collect($attendanceData)->where('status', 'belum')->count(),
        ];

        // Cek apakah ada sesi aktif (untuk info saja, tidak mempengaruhi input manual)
        $activeSession = PresensiSession::where('kelas_id', $kelas->id)
            ->whereDate('tanggal', $filterDate)
            ->where('status', 'active')
            ->first();

        // Return JSON if AJAX request
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'kelas' => [
                    'id' => $kelas->id,
                    'nama_kelas' => $kelas->nama_kelas,
                    'kode_kelas' => $kelas->kode_kelas,
                    'jurusan' => [
                        'nama_jurusan' => $kelas->jurusan->nama_jurusan,
                    ],
                ],
                'attendance_data' => collect($attendanceData)->map(function($item) {
                    return [
                        'siswa' => [
                            'id' => $item['siswa']->id,
                            'name' => $item['siswa']->name,
                            'email' => $item['siswa']->email,
                        ],
                        'presensi' => $item['presensi'] ? [
                            'id' => $item['presensi']->id,
                            'status' => $item['presensi']->status,
                            'waktu_presensi' => $item['presensi']->created_at ? 
                                $item['presensi']->created_at->format('H:i:s') : '-',
                            'metode' => $item['presensi']->metode,
                            'keterangan' => $item['presensi']->keterangan,
                        ] : null,
                        'status' => $item['status'],
                    ];
                }),
                'stats' => $stats,
                'active_session' => $activeSession ? [
                    'id' => $activeSession->id,
                    'tanggal' => $activeSession->tanggal->format('d M Y'),
                    'jam_mulai' => $activeSession->jam_mulai->format('H:i'),
                    'jam_selesai' => $activeSession->jam_selesai->format('H:i'),
                ] : null,
                'filter_date' => $filterDate,
            ]);
        }

        return view('admin.presensi.kelas', compact(
            'kelas', 
            'attendanceData', 
            'stats', 
            'activeSession',
            'filterDate'
        ));
    }

    /**
     * Store manual presensi (TANPA SESSION!) - FIXED!
     */
    public function storeManual(Request $request, Kelas $kelas)
    {
        // Log request untuk debug
        Log::info('storeManual called', [
            'kelas_id' => $kelas->id,
            'kelas_name' => $kelas->nama_kelas,
            'request_data' => $request->all()
        ]);

        // Validate input
        $validated = $request->validate([
            'siswa_id' => 'required|exists:users,id',
            'tanggal_presensi' => 'required|date',
            'status' => 'required|in:hadir,izin,sakit,alpha',
            'keterangan' => 'nullable|string|max:500',
        ]);

        // Verify siswa belongs to kelas
        $siswa = User::findOrFail($validated['siswa_id']);
        
        if ($siswa->kelas_id != $kelas->id) {
            Log::warning('Siswa tidak di kelas ini', [
                'siswa_kelas_id' => $siswa->kelas_id,
                'target_kelas_id' => $kelas->id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Siswa tidak terdaftar di kelas ini'
            ], 422);
        }

        // Check if already exists (unique per tanggal per kelas)
        $exists = Presensi::where('kelas_id', $kelas->id)
            ->where('siswa_id', $validated['siswa_id'])
            ->whereDate('tanggal_presensi', $validated['tanggal_presensi'])
            ->exists();

        if ($exists) {
            Log::warning('Presensi sudah ada', [
                'kelas_id' => $kelas->id,
                'siswa_id' => $validated['siswa_id'],
                'tanggal' => $validated['tanggal_presensi']
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Siswa sudah melakukan presensi pada tanggal ini'
            ], 422);
        }

        // ⭐ CRITICAL: Create presensi dengan kelas_id dari route parameter
        $presensi = Presensi::create([
            'kelas_id' => $kelas->id, // Dari route parameter, BUKAN dari request
            'siswa_id' => $validated['siswa_id'],
            'tanggal_presensi' => $validated['tanggal_presensi'],
            'status' => $validated['status'],
            'metode' => 'manual',
            'keterangan' => $validated['keterangan'] ?? null,
            'session_id' => null, // TIDAK ADA SESSION!
            'latitude' => null,
            'longitude' => null,
            'is_valid_location' => true,
        ]);

        Log::info('Presensi created successfully', [
            'presensi_id' => $presensi->id,
            'kelas_id' => $presensi->kelas_id,
            'siswa_id' => $presensi->siswa_id,
            'tanggal' => $presensi->tanggal_presensi
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Presensi berhasil ditambahkan',
                'data' => [
                    'presensi' => [
                        'id' => $presensi->id,
                        'status' => $presensi->status,
                        'waktu_presensi' => $presensi->created_at->format('H:i:s'),
                        'metode' => $presensi->metode,
                    ],
                ],
            ]);
        }

        return redirect()
            ->route('admin.presensi.index')
            ->with('success', 'Presensi berhasil ditambahkan');
    }

    /**
     * Edit presensi
     */
    public function edit(Presensi $presensi)
    {
        try {
            // Load relasi dengan eager loading
            $presensi->load(['kelas', 'siswa']);
            
            // Validasi data relasi exist
            if (!$presensi->siswa) {
                throw new \Exception('Data siswa tidak ditemukan');
            }
            
            if (!$presensi->kelas) {
                throw new \Exception('Data kelas tidak ditemukan');
            }
            
            Log::info('Edit presensi called', [
                'presensi_id' => $presensi->id,
                'siswa_id' => $presensi->siswa_id,
                'siswa_name' => $presensi->siswa->name ?? 'N/A',
                'status' => $presensi->status
            ]);
            
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'presensi' => [
                        'id' => $presensi->id,
                        'status' => $presensi->status,
                        'keterangan' => $presensi->keterangan ?? '',
                        'siswa' => [
                            'name' => $presensi->siswa->name,
                        ],
                    ],
                ]);
            }
            
            return view('admin.presensi.edit', compact('presensi'));
        } catch (\Exception $e) {
            Log::error('Error in edit presensi', [
                'presensi_id' => $presensi->id ?? 'unknown',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memuat data presensi: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Gagal memuat data presensi: ' . $e->getMessage());
        }
    }

    /**
     * Update presensi
     */
    public function update(Request $request, Presensi $presensi)
    {
        $validated = $request->validate([
            'status' => 'required|in:hadir,izin,sakit,alpha',
            'keterangan' => 'nullable|string|max:500',
            'bukti_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        // Handle file upload
        if ($request->hasFile('bukti_file')) {
            if ($presensi->bukti_file) {
                Storage::disk('public')->delete($presensi->bukti_file);
            }

            $file = $request->file('bukti_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('bukti_presensi', $filename, 'public');
            $validated['bukti_file'] = $path;
        }

        $presensi->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Presensi berhasil diperbarui',
            ]);
        }

        return redirect()
            ->route('admin.presensi.index')
            ->with('success', 'Presensi berhasil diperbarui');
    }

    /**
     * Delete presensi
     */
    public function destroy(Presensi $presensi)
    {
        $kelasId = $presensi->kelas_id;
        
        if ($presensi->bukti_file) {
            Storage::disk('public')->delete($presensi->bukti_file);
        }

        $presensi->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Presensi berhasil dihapus'
            ]);
        }

        return redirect()
            ->route('admin.presensi.index')
            ->with('success', 'Presensi berhasil dihapus');
    }
}
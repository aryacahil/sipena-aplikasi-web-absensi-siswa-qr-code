<?php

namespace App\Http\Controllers\Admin;

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
        // Tampilkan daftar kelas
        $query = Kelas::with(['jurusan', 'siswa']);

        // Filter berdasarkan jurusan jika ada
        if ($request->filled('jurusan_id')) {
            $query->where('jurusan_id', $request->jurusan_id);
        }

        // Search
        if ($request->filled('search')) {
            $query->where('nama_kelas', 'like', '%' . $request->search . '%');
        }

        $kelasList = $query->withCount('siswa')->get();

        // Get all jurusan for filter
        $jurusans = \App\Models\Jurusan::all();

        // Statistics (total semua kelas)
        $today = now()->format('Y-m-d');
        $stats = [
            'total_kelas' => $kelasList->count(),
            'total_siswa' => User::where('role', 'siswa')->count(),
            'hadir_hari_ini' => Presensi::whereHas('session', function($q) use ($today) {
                $q->whereDate('tanggal', $today);
            })->where('status', 'hadir')->count(),
            'alpha_hari_ini' => Presensi::whereHas('session', function($q) use ($today) {
                $q->whereDate('tanggal', $today);
            })->where('status', 'alpha')->count(),
        ];

        return view('admin.presensi.index', compact('kelasList', 'jurusans', 'stats'));
    }

    public function showKelas(Request $request, $kelasId)
    {
        $kelas = Kelas::with(['jurusan', 'siswa'])->findOrFail($kelasId);
        
        // Get active session untuk kelas ini (hari ini)
        $today = now()->format('Y-m-d');
        $activeSession = PresensiSession::where('kelas_id', $kelasId)
            ->whereDate('tanggal', $today)
            ->where('status', 'active')
            ->first();

        // Get all sessions untuk filter tanggal
        $sessionsQuery = PresensiSession::where('kelas_id', $kelasId)
            ->with(['presensis.siswa']);

        // Filter berdasarkan tanggal
        $filterDate = $request->filled('tanggal') ? $request->tanggal : $today;
        $sessionsQuery->whereDate('tanggal', $filterDate);

        $sessions = $sessionsQuery->latest()->get();

        // Get semua siswa di kelas
        $allSiswa = $kelas->siswa;

        // Build attendance data
        $attendanceData = [];
        foreach ($allSiswa as $siswa) {
            $presensi = null;
            $sessionInfo = null;
            
            // Cari presensi siswa di session yang ada
            foreach ($sessions as $session) {
                $found = $session->presensis->where('siswa_id', $siswa->id)->first();
                if ($found) {
                    $presensi = $found;
                    $sessionInfo = $session;
                    break;
                }
            }

            $attendanceData[] = [
                'siswa' => $siswa,
                'presensi' => $presensi,
                'session' => $sessionInfo,
                'status' => $presensi ? $presensi->status : 'belum',
            ];
        }

        // Statistics untuk kelas ini
        $stats = [
            'total_siswa' => $allSiswa->count(),
            'hadir' => collect($attendanceData)->where('status', 'hadir')->count(),
            'izin' => collect($attendanceData)->where('status', 'izin')->count(),
            'sakit' => collect($attendanceData)->where('status', 'sakit')->count(),
            'alpha' => collect($attendanceData)->where('status', 'alpha')->count(),
            'belum' => collect($attendanceData)->where('status', 'belum')->count(),
        ];

        // Get available dates untuk filter
        $availableDates = PresensiSession::where('kelas_id', $kelasId)
            ->selectRaw('DATE(tanggal) as date')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(30)
            ->pluck('date');

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
                            'waktu_presensi' => $item['presensi']->created_at ? $item['presensi']->created_at->format('H:i:s') : '-',
                            'metode' => $item['presensi']->metode,
                            'keterangan' => $item['presensi']->keterangan,
                        ] : null,
                        'session' => $item['session'] ? [
                            'id' => $item['session']->id,
                            'tanggal' => $item['session']->tanggal->format('d M Y'),
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
                'available_dates' => $availableDates,
                'filter_date' => $filterDate,
            ]);
        }

        return view('admin.presensi.kelas', compact(
            'kelas', 
            'attendanceData', 
            'stats', 
            'activeSession',
            'availableDates'
        ));
    }

    public function storeManual(Request $request, PresensiSession $session)
    {
        $validated = $request->validate([
            'siswa_id' => 'required|exists:users,id',
            'status' => 'required|in:hadir,izin,sakit,alpha',
            'keterangan' => 'nullable|string|max:500',
        ]);

        // Check if siswa belongs to the session's kelas
        $siswa = User::findOrFail($validated['siswa_id']);
        if (!$session->kelas->siswa->contains($siswa)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Siswa tidak terdaftar di kelas ini'
                ], 422);
            }
            return back()->with('error', 'Siswa tidak terdaftar di kelas ini');
        }

        // Check if already exists
        $exists = Presensi::where('session_id', $session->id)
            ->where('siswa_id', $validated['siswa_id'])
            ->exists();

        if ($exists) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Siswa sudah melakukan presensi'
                ], 422);
            }
            return back()->with('error', 'Siswa sudah melakukan presensi');
        }

        // Create presensi data
        $presensiData = [
            'session_id' => $session->id,
            'siswa_id' => $validated['siswa_id'],
            'status' => $validated['status'],
            'metode' => 'manual',
        ];

        // Add keterangan if provided
        if (!empty($validated['keterangan'])) {
            $presensiData['keterangan'] = $validated['keterangan'];
        }

        $presensi = Presensi::create($presensiData);

        if ($request->wantsJson()) {
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
            ->route('admin.presensi.kelas', $session->kelas_id)
            ->with('success', 'Presensi berhasil ditambahkan');
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
            ->route('admin.presensi.kelas', $session->kelas_id)
            ->with('success', 'Presensi berhasil ditambahkan');
    }

    public function edit(Presensi $presensi)
    {
        $presensi->load(['session', 'siswa']);
        
        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'presensi' => [
                    'id' => $presensi->id,
                    'status' => $presensi->status,
                    'keterangan' => $presensi->keterangan,
                    'siswa' => [
                        'name' => $presensi->siswa->name,
                    ],
                ],
            ]);
        }
        
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

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Presensi berhasil diperbarui',
                'data' => [
                    'presensi' => [
                        'id' => $presensi->id,
                        'status' => $presensi->status,
                        'keterangan' => $presensi->keterangan,
                    ],
                ],
            ]);
        }

        return redirect()
            ->route('admin.presensi.kelas', $presensi->session->kelas_id)
            ->with('success', 'Presensi berhasil diperbarui');
    }

    public function destroy(Presensi $presensi)
    {
        $kelasId = $presensi->session->kelas_id;
        
        // Delete file if exists
        if ($presensi->bukti_file) {
            Storage::disk('public')->delete($presensi->bukti_file);
        }

        $presensi->delete();

        // Return JSON for AJAX request
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Presensi berhasil dihapus'
            ]);
        }

        return redirect()
            ->route('admin.presensi.kelas', $kelasId)
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
            ->route('admin.presensi.kelas', $session->kelas_id)
            ->with('success', "{$created} presensi berhasil ditambahkan");
    }

    public function show(Presensi $presensi)
    {
        $presensi->load(['session.kelas', 'siswa']);
        
        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'presensi' => [
                    'id' => $presensi->id,
                    'status' => $presensi->status,
                    'waktu_presensi' => $presensi->waktu_presensi->format('d M Y H:i:s'),
                    'keterangan' => $presensi->keterangan,
                    'metode' => $presensi->metode,
                    'latitude' => $presensi->latitude,
                    'longitude' => $presensi->longitude,
                    'notifikasi_terkirim' => $presensi->notifikasi_terkirim,
                    'siswa' => [
                        'name' => $presensi->siswa->name,
                        'email' => $presensi->siswa->email,
                    ],
                    'session' => [
                        'tanggal' => $presensi->session->tanggal->format('Y-m-d'),
                        'kelas' => [
                            'nama_kelas' => $presensi->session->kelas->nama_kelas,
                        ],
                    ],
                ],
            ]);
        }
        
        return view('admin.presensi.show', compact('presensi'));
    }
}
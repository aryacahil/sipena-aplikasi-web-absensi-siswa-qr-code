<?php

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
    public function index(Request $request)
    {
        $query = Kelas::with(['jurusan', 'siswa']);

        if ($request->filled('jurusan_id')) {
            $query->where('jurusan_id', $request->jurusan_id);
        }

        if ($request->filled('search')) {
            $query->where('nama_kelas', 'like', '%' . $request->search . '%');
        }

        $kelasList = $query->withCount('siswa')->get();
        $jurusans = \App\Models\Jurusan::all();

        $today = now()->format('Y-m-d');
        $stats = [
            'total_kelas' => $kelasList->count(),
            'total_siswa' => User::where('role', 2)->count(),
            'hadir_hari_ini' => Presensi::whereDate('tanggal_presensi', $today)
                ->whereNotNull('waktu_checkin') // FIXED: Check waktu_checkin
                ->count(),
            'alpha_hari_ini' => Presensi::whereDate('tanggal_presensi', $today)
                ->where('status', 'alpha')
                ->count(),
        ];

        return view('admin.presensi.index', compact('kelasList', 'jurusans', 'stats'));
    }

    public function showKelas(Request $request, Kelas $kelas)
    {
        $kelas->load(['jurusan', 'siswa']);
        
        $filterDate = $request->filled('tanggal') ? $request->tanggal : now()->format('Y-m-d');

        $allSiswa = $kelas->siswa;

        $attendanceData = [];
        foreach ($allSiswa as $siswa) {
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

        $stats = [
            'total_siswa' => $allSiswa->count(),
            'hadir' => collect($attendanceData)->where('status', 'hadir')->count(),
            'izin' => collect($attendanceData)->where('status', 'izin')->count(),
            'sakit' => collect($attendanceData)->where('status', 'sakit')->count(),
            'alpha' => collect($attendanceData)->where('status', 'alpha')->count(),
            'belum' => collect($attendanceData)->where('status', 'belum')->count(),
        ];

        $activeSession = PresensiSession::where('kelas_id', $kelas->id)
            ->whereDate('tanggal', $filterDate)
            ->where('status', 'active')
            ->first();

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
                            'nis' => $item['siswa']->nis,
                        ],
                        'presensi' => $item['presensi'] ? [
                            'id' => $item['presensi']->id,
                            'status' => $item['presensi']->status,
                            'waktu_checkin' => $item['presensi']->waktu_checkin ? 
                                $item['presensi']->waktu_checkin->format('H:i:s') : '-',
                            'waktu_checkout' => $item['presensi']->waktu_checkout ? 
                                $item['presensi']->waktu_checkout->format('H:i:s') : '-',
                            'metode' => $item['presensi']->metode,
                            'keterangan' => $item['presensi']->keterangan_checkin ?? $item['presensi']->keterangan_checkout,
                        ] : null,
                        'status' => $item['status'],
                    ];
                }),
                'stats' => $stats,
                'active_session' => $activeSession ? [
                    'id' => $activeSession->id,
                    'tanggal' => $activeSession->tanggal->format('d M Y'),
                    'jam_checkin_mulai' => $activeSession->jam_checkin_mulai->format('H:i'),
                    'jam_checkin_selesai' => $activeSession->jam_checkin_selesai->format('H:i'),
                    'jam_checkout_mulai' => $activeSession->jam_checkout_mulai->format('H:i'),
                    'jam_checkout_selesai' => $activeSession->jam_checkout_selesai->format('H:i'),
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

    public function storeManual(Request $request, Kelas $kelas)
    {
        Log::info('storeManual called', [
            'kelas_id' => $kelas->id,
            'kelas_name' => $kelas->nama_kelas,
            'request_data' => $request->all()
        ]);

        $validated = $request->validate([
            'siswa_id' => 'required|exists:users,id',
            'tanggal_presensi' => 'required|date',
            'status' => 'required|in:hadir,izin,sakit,alpha',
            'keterangan' => 'nullable|string|max:500',
        ]);

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

        // FIXED: Gunakan struktur baru dengan checkin/checkout
        $presensi = Presensi::create([
            'kelas_id' => $kelas->id,
            'siswa_id' => $validated['siswa_id'],
            'tanggal_presensi' => $validated['tanggal_presensi'],
            'status' => $validated['status'],
            'metode' => 'manual',
            'keterangan_checkin' => $validated['keterangan'] ?? null,
            'session_id' => null,
            'latitude_checkin' => null,
            'longitude_checkin' => null,
            'latitude_checkout' => null,
            'longitude_checkout' => null,
            'is_valid_location_checkin' => true,
            'is_valid_location_checkout' => true,
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
                        'waktu_checkin' => $presensi->waktu_checkin->format('H:i:s'), // FIXED
                        'metode' => $presensi->metode,
                    ],
                ],
            ]);
        }

        return redirect()
            ->route('admin.presensi.index')
            ->with('success', 'Presensi berhasil ditambahkan');
    }

    public function edit(Presensi $presensi)
    {
        try {
            $presensi->load(['kelas', 'siswa']);
            
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
                        'keterangan' => $presensi->keterangan_checkin ?? $presensi->keterangan_checkout ?? '', // FIXED
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

    public function update(Request $request, Presensi $presensi)
    {
        $validated = $request->validate([
            'status' => 'required|in:hadir,izin,sakit,alpha',
            'keterangan' => 'nullable|string|max:500',
            'bukti_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($request->hasFile('bukti_file')) {
            if ($presensi->bukti_file) {
                Storage::disk('public')->delete($presensi->bukti_file);
            }

            $file = $request->file('bukti_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('bukti_presensi', $filename, 'public');
            $validated['bukti_file'] = $path;
        }

        // FIXED: Update keterangan_checkin (karena manual presensi set di checkin)
        $updateData = [
            'status' => $validated['status'],
            'keterangan_checkin' => $validated['keterangan'] ?? null,
        ];

        if (isset($validated['bukti_file'])) {
            $updateData['bukti_file'] = $validated['bukti_file'];
        }

        $presensi->update($updateData);

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
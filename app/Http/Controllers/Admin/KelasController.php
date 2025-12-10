<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Jurusan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KelasController extends Controller
{
    public function index(Request $request)
    {
        $query = Kelas::with(['jurusan', 'waliKelas'])->withCount('siswa');
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_kelas', 'like', "%{$search}%")
                  ->orWhere('kode_kelas', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('tingkat')) {
            $query->where('tingkat', $request->tingkat);
        }
        
        if ($request->filled('jurusan_id')) {
            $query->where('jurusan_id', $request->jurusan_id);
        }
        
        $query->latest();
        
        $kelas = $query->paginate(10);
        $jurusans = Jurusan::all();
        
        $gurus = DB::table('users')
            ->where('role', 0)
            ->select('id', 'name', 'email')
            ->orderBy('name', 'asc')
            ->get();
        
        return view('admin.kelas.index', compact('kelas', 'jurusans', 'gurus'));
    }

    public function create()
    {
        $jurusans = Jurusan::all();
        
        $gurus = DB::table('users')
            ->where('role', 0)
            ->select('id', 'name', 'email')
            ->orderBy('name', 'asc')
            ->get();
        
        return view('admin.kelas.create', compact('jurusans', 'gurus'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'jurusan_id' => 'required|exists:jurusans,id',
            'nama_kelas' => 'required|string|max:255',
            'tingkat' => 'required|integer|in:10,11,12',
            'kode_kelas' => 'required|string|max:20|unique:kelas,kode_kelas',
            'wali_kelas_id' => 'nullable|exists:users,id',
        ], [
            'jurusan_id.required' => 'Jurusan wajib dipilih',
            'nama_kelas.required' => 'Nama kelas wajib diisi',
            'tingkat.required' => 'Tingkat wajib dipilih',
            'kode_kelas.required' => 'Kode kelas wajib diisi',
            'kode_kelas.unique' => 'Kode kelas sudah digunakan',
        ]);

        Kelas::create($validated);

        return redirect()->route('admin.kelas.index')
            ->with('success', 'Kelas berhasil ditambahkan');
    }

    public function show(Kelas $kela)
    {
        if (request()->wantsJson()) {
            $kela->load(['jurusan', 'waliKelas']);
            
            $siswaData = DB::table('users')
                ->where('kelas_id', $kela->id)
                ->where('role', 2)
                ->select('id', 'name', 'nis')
                ->orderBy('nis', 'asc')
                ->get();
            
            return response()->json([
                'success' => true,
                'kelas' => [
                    'id' => $kela->id,
                    'nama_kelas' => $kela->nama_kelas,
                    'kode_kelas' => $kela->kode_kelas,
                    'tingkat' => $kela->tingkat,
                    'jurusan' => [
                        'nama_jurusan' => $kela->jurusan->nama_jurusan,
                        'kode_jurusan' => $kela->jurusan->kode_jurusan,
                    ],
                    'wali_kelas' => $kela->waliKelas ? [
                        'name' => $kela->waliKelas->name,
                        'email' => $kela->waliKelas->email,
                    ] : null,
                    'siswa_count' => $siswaData->count(),
                    'siswa' => $siswaData->map(function($siswa) {
                        return [
                            'id' => $siswa->id,
                            'name' => $siswa->name,
                            'nis' => $siswa->nis ?? '-',
                        ];
                    }),
                ]
            ]);
        }

        $kela->load(['jurusan', 'waliKelas', 'siswa']);
        return view('admin.kelas.show', compact('kela'));
    }

    public function edit(Kelas $kela)
    {
        if (request()->wantsJson()) {
            $gurus = DB::table('users')
                ->where('role', 0)
                ->select('id', 'name', 'email')
                ->orderBy('name', 'asc')
                ->get();
            
            return response()->json([
                'success' => true,
                'kelas' => $kela,
                'jurusans' => Jurusan::all(),
                'gurus' => $gurus,
            ]);
        }

        $jurusans = Jurusan::all();
        
        $gurus = DB::table('users')
            ->where('role', 0)
            ->select('id', 'name', 'email')
            ->orderBy('name', 'asc')
            ->get();
        
        return view('admin.kelas.edit', compact('kela', 'jurusans', 'gurus'));
    }

    public function update(Request $request, Kelas $kela)
    {
        $validated = $request->validate([
            'jurusan_id' => 'required|exists:jurusans,id',
            'nama_kelas' => 'required|string|max:255',
            'tingkat' => 'required|integer|in:10,11,12',
            'kode_kelas' => 'required|string|max:20|unique:kelas,kode_kelas,' . $kela->id,
            'wali_kelas_id' => 'nullable|exists:users,id',
        ], [
            'jurusan_id.required' => 'Jurusan wajib dipilih',
            'nama_kelas.required' => 'Nama kelas wajib diisi',
            'tingkat.required' => 'Tingkat wajib dipilih',
            'kode_kelas.required' => 'Kode kelas wajib diisi',
            'kode_kelas.unique' => 'Kode kelas sudah digunakan',
        ]);

        $kela->update($validated);

        return redirect()->route('admin.kelas.index')
            ->with('success', 'Kelas berhasil diperbarui');
    }

    public function destroy(Kelas $kela)
    {
        try {
            $siswaCount = DB::table('users')
                ->where('kelas_id', $kela->id)
                ->count();
            
            if ($siswaCount > 0) {
                return redirect()->route('admin.kelas.index')
                    ->with('error', "Tidak dapat menghapus kelas karena masih ada {$siswaCount} siswa di kelas ini");
            }
            
            $kela->delete();
            return redirect()->route('admin.kelas.index')
                ->with('success', 'Kelas berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('admin.kelas.index')
                ->with('error', 'Gagal menghapus kelas: ' . $e->getMessage());
        }
    }

    public function availableSiswa(Kelas $kela)
    {
        $siswa = DB::table('users')
            ->where('role', 2)
            ->where(function($query) {
                $query->whereNull('kelas_id')
                      ->orWhere('kelas_id', '');
            })
            ->select('id', 'name', 'nis')
            ->orderBy('nis', 'asc')
            ->get();

        $formattedSiswa = $siswa->map(function($s) {
            return [
                'id' => $s->id,
                'name' => $s->name,
                'nis' => $s->nis ?? '-',
            ];
        });

        return response()->json([
            'success' => true,
            'siswa' => $formattedSiswa
        ]);
    }

    public function addSiswa(Request $request, Kelas $kela)
    {
        $request->validate([
            'siswa_ids' => 'required|array',
            'siswa_ids.*' => 'exists:users,id',
        ]);

        $addedCount = 0;
        $errors = [];

        foreach ($request->siswa_ids as $siswaId) {
            $siswa = DB::table('users')
                ->where('id', $siswaId)
                ->where('role', 2)
                ->whereNull('kelas_id')
                ->first();
            
            if ($siswa) {
                DB::table('users')
                    ->where('id', $siswaId)
                    ->update(['kelas_id' => $kela->id]);
                $addedCount++;
            } else {
                $userData = DB::table('users')->where('id', $siswaId)->first();
                if ($userData) {
                    $errors[] = "{$userData->name} (NIS: {$userData->nis})";
                } else {
                    $errors[] = "Siswa ID: $siswaId";
                }
            }
        }

        if ($addedCount > 0) {
            $message = "$addedCount siswa berhasil ditambahkan ke kelas";
            if (count($errors) > 0) {
                $message .= ". Gagal menambahkan: " . implode(', ', $errors);
            }
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'added_count' => $addedCount,
                'errors' => $errors
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Tidak ada siswa yang ditambahkan. ' . implode(', ', $errors),
            'errors' => $errors
        ], 422);
    }

    public function removeSiswa(Request $request, Kelas $kela)
    {
        if ($request->has('siswa_ids')) {
            $request->validate([
                'siswa_ids' => 'required|array',
                'siswa_ids.*' => 'exists:users,id',
            ]);

            $removedCount = 0;
            $errors = [];

            foreach ($request->siswa_ids as $siswaId) {
                $siswa = DB::table('users')
                    ->where('id', $siswaId)
                    ->where('kelas_id', $kela->id)
                    ->first();
                
                if ($siswa) {
                    DB::table('users')
                        ->where('id', $siswaId)
                        ->update(['kelas_id' => null]);
                    $removedCount++;
                } else {
                    $userData = DB::table('users')->where('id', $siswaId)->first();
                    if ($userData) {
                        $errors[] = "{$userData->name} (NIS: {$userData->nis})";
                    } else {
                        $errors[] = "Siswa ID: $siswaId";
                    }
                }
            }

            if ($removedCount > 0) {
                $message = "$removedCount siswa berhasil dikeluarkan dari kelas";
                if (count($errors) > 0) {
                    $message .= ". Gagal mengeluarkan: " . implode(', ', $errors);
                }
                
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'removed_count' => $removedCount,
                    'errors' => $errors
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Tidak ada siswa yang dikeluarkan. ' . implode(', ', $errors),
                'errors' => $errors
            ], 422);
        } 
        else {
            $request->validate([
                'siswa_id' => 'required|exists:users,id',
            ]);
            
            try {
                $siswa = DB::table('users')
                    ->where('id', $request->siswa_id)
                    ->where('kelas_id', $kela->id)
                    ->first();
                
                if (!$siswa) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Siswa tidak berada di kelas ini'
                    ], 422);
                }
                
                DB::table('users')
                    ->where('id', $request->siswa_id)
                    ->update(['kelas_id' => null]);

                return response()->json([
                    'success' => true,
                    'message' => "{$siswa->name} (NIS: {$siswa->nis}) berhasil dikeluarkan dari kelas"
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengeluarkan siswa dari kelas: ' . $e->getMessage()
                ], 500);
            }
        }
    }

    public function removeAllSiswa(Kelas $kela)
    {
        try {
            $siswaCount = DB::table('users')
                ->where('kelas_id', $kela->id)
                ->where('role', 2)
                ->count();
            
            if ($siswaCount == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada siswa di kelas ini'
                ], 422);
            }

            DB::table('users')
                ->where('kelas_id', $kela->id)
                ->where('role', 2)
                ->update(['kelas_id' => null]);

            return response()->json([
                'success' => true,
                'message' => "$siswaCount siswa berhasil dikeluarkan dari kelas"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengeluarkan semua siswa dari kelas: ' . $e->getMessage()
            ], 500);
        }
    }

    public function listAll()
    {
        try {
            $kelas = DB::table('kelas')
                ->join('jurusans', 'kelas.jurusan_id', '=', 'jurusans.id')
                ->select(
                    'kelas.id',
                    'kelas.nama_kelas',
                    'kelas.kode_kelas',
                    'kelas.tingkat',
                    'jurusans.kode_jurusan'
                )
                ->orderBy('kelas.tingkat', 'asc')
                ->orderBy('kelas.nama_kelas', 'asc')
                ->get();

            $kelasWithCount = $kelas->map(function($k) {
                $siswaCount = DB::table('users')
                    ->where('kelas_id', $k->id)
                    ->where('role', 2)
                    ->count();
                
                return [
                    'id' => $k->id,
                    'nama_kelas' => $k->nama_kelas,
                    'kode_kelas' => $k->kode_kelas,
                    'tingkat' => $k->tingkat,
                    'kode_jurusan' => $k->kode_jurusan,
                    'siswa_count' => $siswaCount,
                ];
            });

            return response()->json([
                'success' => true,
                'kelas' => $kelasWithCount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat daftar kelas: ' . $e->getMessage()
            ], 500);
        }
    }

    public function allSiswa()
    {
        try {
            $siswa = DB::table('users as u')
                ->leftJoin('kelas as k', 'u.kelas_id', '=', 'k.id')
                ->where('u.role', 2)
                ->select(
                    'u.id',
                    'u.name',
                    'u.nis',
                    'u.kelas_id',
                    'k.nama_kelas as kelas_nama'
                )
                ->orderBy('u.name', 'asc')
                ->get();

            $formattedSiswa = $siswa->map(function($s) {
                return [
                    'id' => $s->id,
                    'name' => $s->name,
                    'nis' => $s->nis ?? '-',
                    'kelas_id' => $s->kelas_id,
                    'kelas' => $s->kelas_nama ? [
                        'nama_kelas' => $s->kelas_nama
                    ] : null,
                ];
            });

            return response()->json([
                'success' => true,
                'siswa' => $formattedSiswa
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat daftar siswa: ' . $e->getMessage()
            ], 500);
        }
    }

    public function pindahSiswa(Request $request)
    {
        try {
            $request->validate([
                'siswa_ids' => 'required|array',
                'siswa_ids.*' => 'exists:users,id',
                'target_kelas_id' => 'required|exists:kelas,id',
            ], [
                'siswa_ids.required' => 'Pilih minimal satu siswa',
                'target_kelas_id.required' => 'Kelas tujuan wajib dipilih',
                'target_kelas_id.exists' => 'Kelas tujuan tidak valid',
            ]);

            $siswaIds = $request->siswa_ids;
            $targetKelasId = $request->target_kelas_id;

            $targetKelas = DB::table('kelas')
                ->where('id', $targetKelasId)
                ->first();

            if (!$targetKelas) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kelas tujuan tidak ditemukan'
                ], 404);
            }

            $movedCount = 0;
            $movedNames = [];
            $errors = [];

            foreach ($siswaIds as $siswaId) {
                $siswa = DB::table('users')
                    ->where('id', $siswaId)
                    ->where('role', 2)
                    ->first();
                
                if ($siswa) {
                    if ($siswa->kelas_id == $targetKelasId) {
                        $errors[] = "{$siswa->name} sudah berada di kelas tujuan";
                        continue;
                    }

                    DB::table('users')
                        ->where('id', $siswaId)
                        ->update(['kelas_id' => $targetKelasId]);
                    
                    $movedCount++;
                    $movedNames[] = $siswa->name;
                } else {
                    $errors[] = "Siswa ID {$siswaId} tidak ditemukan atau bukan siswa";
                }
            }

            if ($movedCount > 0) {
                $message = "{$movedCount} siswa berhasil dipindahkan ke {$targetKelas->nama_kelas}";
                
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'data' => [
                        'moved_count' => $movedCount,
                        'moved_names' => $movedNames,
                        'target_kelas' => $targetKelas->nama_kelas
                    ],
                    'errors' => $errors
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Tidak ada siswa yang dipindahkan. ' . implode(', ', $errors),
                'errors' => $errors
            ], 422);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memindahkan siswa: ' . $e->getMessage()
            ], 500);
        }
    }
}
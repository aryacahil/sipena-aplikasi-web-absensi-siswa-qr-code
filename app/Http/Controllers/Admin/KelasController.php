<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Jurusan;
use App\Models\User;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    public function index(Request $request)
    {
        $query = Kelas::with(['jurusan', 'waliKelas'])->withCount('siswa');
        
        // Filter berdasarkan pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_kelas', 'like', "%{$search}%")
                  ->orWhere('kode_kelas', 'like', "%{$search}%");
            });
        }
        
        // Filter berdasarkan tingkat
        if ($request->filled('tingkat')) {
            $query->where('tingkat', $request->tingkat);
        }
        
        // Filter berdasarkan jurusan
        if ($request->filled('jurusan_id')) {
            $query->where('jurusan_id', $request->jurusan_id);
        }
        
        // Default sorting (terbaru)
        $query->latest();
        
        $kelas = $query->paginate(10);
        $jurusans = Jurusan::all();
        $gurus = User::where('role', 0)->get(); 
        
        return view('admin.kelas.index', compact('kelas', 'jurusans', 'gurus'));
    }

    public function create()
    {
        $jurusans = Jurusan::all();
        $gurus = User::where('role', 0)->get(); 
        
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
            $kela->load(['jurusan', 'waliKelas', 'siswa']);
            
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
                    'siswa_count' => $kela->siswa->count(),
                    'siswa' => $kela->siswa->map(function($siswa) {
                        return [
                            'id' => $siswa->id,
                            'name' => $siswa->name,
                            'email' => $siswa->email,
                            'nisn' => $siswa->nisn ?? null,
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
            return response()->json([
                'success' => true,
                'kelas' => $kela,
                'jurusans' => Jurusan::all(),
                'gurus' => User::where('role', 0)->get(),
            ]);
        }

        $jurusans = Jurusan::all();
        $gurus = User::where('role', 0)->get();
        
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
            $kela->delete();
            return redirect()->route('admin.kelas.index')
                ->with('success', 'Kelas berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('admin.kelas.index')
                ->with('error', 'Gagal menghapus kelas');
        }
    }

    public function availableSiswa(Kelas $kela)
    {
        // Get siswa yang belum memiliki kelas atau role = 2 (siswa)
        $siswa = User::where('role', 2)
            ->whereNull('kelas_id')
            ->orWhere('kelas_id', '')
            ->get(['id', 'name', 'email']);

        return response()->json([
            'success' => true,
            'siswa' => $siswa
        ]);
    }

    public function addSiswa(Request $request, Kelas $kela)
    {
        $request->validate([
            'siswa_id' => 'required|exists:users,id',
        ]);

        $siswa = User::findOrFail($request->siswa_id);
        
        // Update kelas_id pada user
        $siswa->kelas_id = $kela->id;
        $siswa->save();

        return response()->json([
            'success' => true,
            'message' => 'Siswa berhasil ditambahkan ke kelas'
        ]);
    }

    public function removeSiswa(Kelas $kela, User $siswa)
    {
        try {
            // Set kelas_id menjadi null
            $siswa->kelas_id = null;
            $siswa->save();

            return response()->json([
                'success' => true,
                'message' => 'Siswa berhasil dikeluarkan dari kelas'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengeluarkan siswa dari kelas'
            ], 500);
        }
    }
}
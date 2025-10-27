<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jurusan;
use Illuminate\Http\Request;

class JurusanController extends Controller
{
    public function index(Request $request)
    {
        $query = Jurusan::withCount('kelas');
        
        // Search functionality
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kode_jurusan', 'like', "%{$search}%")
                  ->orWhere('nama_jurusan', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }
        
        $jurusans = $query->latest()->paginate(10);
        
        return view('admin.jurusan.index', compact('jurusans'));
    }

    public function create()
    {
        return view('admin.jurusan.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_jurusan' => 'required|string|max:10|unique:jurusans,kode_jurusan',
            'nama_jurusan' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ], [
            'kode_jurusan.required' => 'Kode jurusan wajib diisi',
            'kode_jurusan.unique' => 'Kode jurusan sudah digunakan',
            'nama_jurusan.required' => 'Nama jurusan wajib diisi',
        ]);

        Jurusan::create($validated);

        return redirect()->route('admin.jurusan.index')
            ->with('success', 'Jurusan berhasil ditambahkan');
    }

    public function show(Jurusan $jurusan)
    {
        // Load relasi kelas dengan wali kelas dan hitung jumlah siswa
        $jurusan->loadCount('kelas');
        $jurusan->load(['kelas' => function($query) {
            $query->with('waliKelas')->withCount('siswa');
        }]);
        
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'jurusan' => [
                    'id' => $jurusan->id,
                    'kode_jurusan' => $jurusan->kode_jurusan,
                    'nama_jurusan' => $jurusan->nama_jurusan,
                    'deskripsi' => $jurusan->deskripsi,
                    'kelas_count' => $jurusan->kelas_count,
                    'kelas' => $jurusan->kelas->map(function($kelas) {
                        return [
                            'id' => $kelas->id,
                            'nama_kelas' => $kelas->nama_kelas,
                            'siswa_count' => $kelas->siswa_count ?? 0,
                            'wali_kelas' => $kelas->waliKelas ? [
                                'id' => $kelas->waliKelas->id,
                                'name' => $kelas->waliKelas->name
                            ] : null
                        ];
                    }),
                    'created_at' => $jurusan->created_at,
                    'updated_at' => $jurusan->updated_at,
                ]
            ]);
        }
        
        return view('admin.jurusan.show', compact('jurusan'));
    }

    public function edit(Jurusan $jurusan)
    {
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'jurusan' => [
                    'id' => $jurusan->id,
                    'kode_jurusan' => $jurusan->kode_jurusan,
                    'nama_jurusan' => $jurusan->nama_jurusan,
                    'deskripsi' => $jurusan->deskripsi,
                ]
            ]);
        }
        
        return view('admin.jurusan.edit', compact('jurusan'));
    }

    public function update(Request $request, Jurusan $jurusan)
    {
        $validated = $request->validate([
            'kode_jurusan' => 'required|string|max:10|unique:jurusans,kode_jurusan,' . $jurusan->id,
            'nama_jurusan' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ], [
            'kode_jurusan.required' => 'Kode jurusan wajib diisi',
            'kode_jurusan.unique' => 'Kode jurusan sudah digunakan',
            'nama_jurusan.required' => 'Nama jurusan wajib diisi',
        ]);

        $jurusan->update($validated);

        return redirect()->route('admin.jurusan.index')
            ->with('success', 'Jurusan berhasil diperbarui');
    }

    public function destroy(Jurusan $jurusan)
    {
        try {
            // Cek apakah jurusan memiliki kelas
            if ($jurusan->kelas()->count() > 0) {
                return redirect()->route('admin.jurusan.index')
                    ->with('error', 'Jurusan tidak dapat dihapus karena masih memiliki ' . $jurusan->kelas()->count() . ' kelas');
            }
            
            $jurusan->delete();
            
            return redirect()->route('admin.jurusan.index')
                ->with('success', 'Jurusan berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('admin.jurusan.index')
                ->with('error', 'Terjadi kesalahan saat menghapus jurusan');
        }
    }
}
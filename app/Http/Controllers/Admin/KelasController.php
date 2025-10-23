<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Jurusan;
use App\Models\User;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    public function index()
    {
        $kelas = Kelas::with(['jurusan', 'waliKelas'])
            ->withCount('siswa')
            ->latest()
            ->paginate(10);
        
        // Tambahkan data untuk modal create/edit
        $jurusans = Jurusan::all();
        $gurus = User::whereRaw("role = 0")->get(); // Guru
        
        return view('admin.kelas.index', compact('kelas', 'jurusans', 'gurus'));
    }

    public function create()
    {
        $jurusans = Jurusan::all();
        $gurus = User::whereRaw("role = 0")->get(); // Guru
        
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
        $kela->load(['jurusan', 'waliKelas', 'siswa']);
        return view('admin.kelas.show', compact('kela'));
    }

    public function edit(Kelas $kela)
    {
        $jurusans = Jurusan::all();
        $gurus = User::whereRaw("role = 0")->get();
        
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
            ->with('success', 'Kelas berhasil diupdate');
    }

    public function destroy(Kelas $kela)
    {
        try {
            $kela->delete();
            return redirect()->route('admin.kelas.index')
                ->with('success', 'Kelas berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('admin.kelas.index')
                ->with('error', 'Kelas tidak dapat dihapus karena masih memiliki siswa');
        }
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jurusan;
use Illuminate\Http\Request;

class JurusanController extends Controller
{
    public function index()
    {
        $jurusans = Jurusan::withCount('kelas')->latest()->paginate(10);
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
        $jurusan->load('kelas.waliKelas', 'kelas.siswa');
        return view('admin.jurusan.show', compact('jurusan'));
    }

    public function edit(Jurusan $jurusan)
    {
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
            ->with('success', 'Jurusan berhasil diupdate');
    }

    public function destroy(Jurusan $jurusan)
    {
        try {
            $jurusan->delete();
            return redirect()->route('admin.jurusan.index')
                ->with('success', 'Jurusan berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('admin.jurusan.index')
                ->with('error', 'Jurusan tidak dapat dihapus karena masih memiliki kelas');
        }
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('kelas.jurusan'); // Tambahkan eager loading
        
        // Filter by role if provided
        if ($request->has('role') && $request->role !== '') {
            $query->where('role', $request->role);
        }
        
        $users = $query->latest()->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $kelas = Kelas::with('jurusan')->get(); // Ambil semua kelas
        return view('admin.users.create', compact('kelas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|in:0,1,2',
            'kelas_id' => 'required_if:role,2|nullable|exists:kelas,id', // Tambahkan ini
            'parent_phone' => 'required_if:role,2|nullable|string|max:20',
            'status' => 'required|in:active,inactive',
        ], [
            'name.required' => 'Nama wajib diisi',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah digunakan',
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password minimal 8 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
            'role.required' => 'Role wajib dipilih',
            'kelas_id.required_if' => 'Kelas wajib dipilih untuk siswa',
            'parent_phone.required_if' => 'No telepon orang tua wajib diisi untuk siswa',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil ditambahkan');
    }

    public function show(User $user)
    {
        $user->load('kelas.jurusan'); 
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $kelas = Kelas::with('jurusan')->get(); 
        return view('admin.users.edit', compact('user', 'kelas'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:8|confirmed',
            'role' => 'required|in:0,1,2',
            'kelas_id' => 'required_if:role,2|nullable|exists:kelas,id', 
            'parent_phone' => 'required_if:role,2|nullable|string|max:20',
            'status' => 'required|in:active,inactive',
        ], [
            'name.required' => 'Nama wajib diisi',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah digunakan',
            'password.min' => 'Password minimal 8 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
            'role.required' => 'Role wajib dipilih',
            'kelas_id.required_if' => 'Kelas wajib dipilih untuk siswa',
            'parent_phone.required_if' => 'No telepon orang tua wajib diisi untuk siswa',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil diupdate');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil dihapus');
    }
}
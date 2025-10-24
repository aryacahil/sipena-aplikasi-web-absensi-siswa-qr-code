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
        $query = User::with('kelas.jurusan');
        
        // Filter by role
        if ($request->has('role') && $request->role !== '') {
            $query->where('role', $request->role);
        }
        
        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }
        
        // Filter by kelas
        if ($request->has('kelas_id') && $request->kelas_id !== '') {
            $query->where('kelas_id', $request->kelas_id);
        }
        
        // Search by name or email
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $users = $query->latest()->paginate(10);
        $kelas = Kelas::with('jurusan')->get(); 
        
        return view('admin.users.index', compact('users', 'kelas'));
    }

    public function create()
    {
        $kelas = Kelas::with('jurusan')->get();
        return view('admin.users.create', compact('kelas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|in:0,1,2',
            'kelas_id' => 'required_if:role,2|nullable|exists:kelas,id',
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

    public function bulkDeleteByRole(Request $request)
{
    try {
        $validated = $request->validate([
            'role' => 'required|in:0,1,2'
        ]);

        $role = $validated['role'];
        $roleName = ['0' => 'guru', '1' => 'admin', '2' => 'siswa'][$role];
        
        // Hitung jumlah user yang akan dihapus
        $count = User::where('role', $role)->count();
        
        if ($count === 0) {
            return response()->json([
                'success' => false,
                'message' => "Tidak ada {$roleName} yang dapat dihapus"
            ], 404);
        }

        // Hapus semua user dengan role tertentu
        User::where('role', $role)->delete();

        return response()->json([
            'success' => true,
            'message' => "Berhasil menghapus {$count} {$roleName}",
            'count' => $count
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ], 500);
    }
}
}
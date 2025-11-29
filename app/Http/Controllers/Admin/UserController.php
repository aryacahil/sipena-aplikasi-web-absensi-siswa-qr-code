<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['kelas.jurusan']);

        // Filter berdasarkan role (gunakan raw value)
        if ($request->has('role') && $request->role !== '') {
            $query->whereRaw('role = ?', [$request->role]);
        }

        // Filter berdasarkan status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter berdasarkan kelas
        if ($request->has('kelas_id') && $request->kelas_id !== '') {
            $query->where('kelas_id', $request->kelas_id);
        }

        // Search (nama, email, atau NIS)
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(10);
        $kelas = Kelas::with('jurusan')->get();

        return view('admin.users.index', compact('users', 'kelas'));
    }

    public function store(Request $request)
    {
        try {
            // Log request untuk debugging
            Log::info('Store User Request:', $request->all());

            // Validasi dinamis berdasarkan role
            $rules = [
                'name' => 'required|string|max:255',
                'role' => 'required|in:0,1,2',
                'status' => 'required|in:active,inactive',
                'password' => 'required|string|min:6|confirmed',
            ];

            $messages = [
                'name.required' => 'Nama lengkap harus diisi',
                'role.required' => 'Role harus dipilih',
                'status.required' => 'Status harus dipilih',
                'password.required' => 'Password harus diisi',
                'password.min' => 'Password minimal 6 karakter',
                'password.confirmed' => 'Konfirmasi password tidak cocok',
            ];

            // Validasi berdasarkan role
            if ($request->role == '2') { 
                // Siswa - butuh NIS
                $rules['nis'] = 'required|string|max:20|unique:users,nis';
                $rules['kelas_id'] = 'nullable|exists:kelas,id';
                $rules['parent_phone'] = 'nullable|string|max:15';
                
                $messages['nis.required'] = 'NIS harus diisi untuk siswa';
                $messages['nis.unique'] = 'NIS sudah terdaftar';
                $messages['kelas_id.exists'] = 'Kelas tidak valid';
            } else { 
                // Admin atau Guru - butuh Email
                $rules['email'] = 'required|email|max:255|unique:users,email';
                
                $messages['email.required'] = 'Email harus diisi untuk admin/guru';
                $messages['email.email'] = 'Format email tidak valid';
                $messages['email.unique'] = 'Email sudah terdaftar';
            }

            // Validasi request
            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                Log::error('Validation Failed:', $validator->errors()->toArray());
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error', 'Validasi gagal: ' . $validator->errors()->first());
            }

            // Gunakan DB transaction untuk keamanan
            DB::beginTransaction();

            try {
                // Buat user dengan insert langsung untuk bypass accessor
                $userId = DB::table('users')->insertGetId([
                    'name' => $request->name,
                    'role' => $request->role, // Insert raw value
                    'status' => $request->status,
                    'password' => Hash::make($request->password),
                    'email' => $request->role != '2' ? $request->email : null,
                    'nis' => $request->role == '2' ? $request->nis : null,
                    'kelas_id' => $request->role == '2' ? $request->kelas_id : null,
                    'parent_phone' => $request->role == '2' ? $request->parent_phone : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::commit();

                Log::info('User Created Successfully:', ['id' => $userId, 'name' => $request->name]);

                return redirect()->route('admin.users.index')
                    ->with('success', 'User berhasil ditambahkan!');

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error Creating User:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan user: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $user = User::with(['kelas.jurusan'])->findOrFail($id);
            
            // Ambil raw role value
            $userData = $user->toArray();
            $userData['role'] = $user->getRawOriginal('role');
            
            return response()->json([
                'success' => true,
                'user' => $userData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan'
            ], 404);
        }
    }

    public function edit($id)
    {
        try {
            $user = User::with(['kelas.jurusan'])->findOrFail($id);
            
            // Ambil raw role value
            $userData = $user->toArray();
            $userData['role'] = $user->getRawOriginal('role');
            
            return response()->json([
                'success' => true,
                'user' => $userData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan'
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            // Log request untuk debugging
            Log::info('Update User Request:', ['id' => $id, 'data' => $request->all()]);

            // Validasi dinamis berdasarkan role
            $rules = [
                'name' => 'required|string|max:255',
                'role' => 'required|in:0,1,2',
                'status' => 'required|in:active,inactive',
                'password' => 'nullable|string|min:6|confirmed',
            ];

            $messages = [
                'name.required' => 'Nama lengkap harus diisi',
                'role.required' => 'Role harus dipilih',
                'status.required' => 'Status harus dipilih',
                'password.min' => 'Password minimal 6 karakter',
                'password.confirmed' => 'Konfirmasi password tidak cocok',
            ];

            // Validasi berdasarkan role
            if ($request->role == '2') { 
                // Siswa - butuh NIS
                $rules['nis'] = 'required|string|max:20|unique:users,nis,' . $id;
                $rules['kelas_id'] = 'nullable|exists:kelas,id';
                $rules['parent_phone'] = 'nullable|string|max:15';
                
                $messages['nis.required'] = 'NIS harus diisi untuk siswa';
                $messages['nis.unique'] = 'NIS sudah terdaftar';
            } else { 
                // Admin atau Guru - butuh Email
                $rules['email'] = 'required|email|max:255|unique:users,email,' . $id;
                
                $messages['email.required'] = 'Email harus diisi untuk admin/guru';
                $messages['email.email'] = 'Format email tidak valid';
                $messages['email.unique'] = 'Email sudah terdaftar';
            }

            // Validasi request
            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                Log::error('Validation Failed:', $validator->errors()->toArray());
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error', 'Validasi gagal: ' . $validator->errors()->first());
            }

            // Gunakan DB transaction untuk keamanan
            DB::beginTransaction();

            try {
                // Update dengan query builder untuk bypass accessor
                $updateData = [
                    'name' => $request->name,
                    'role' => $request->role, // Update raw value
                    'status' => $request->status,
                    'email' => $request->role != '2' ? $request->email : null,
                    'nis' => $request->role == '2' ? $request->nis : null,
                    'kelas_id' => $request->role == '2' ? $request->kelas_id : null,
                    'parent_phone' => $request->role == '2' ? $request->parent_phone : null,
                    'updated_at' => now(),
                ];

                // Update password jika diisi
                if ($request->filled('password')) {
                    $updateData['password'] = Hash::make($request->password);
                }

                DB::table('users')
                    ->where('id', $id)
                    ->update($updateData);

                DB::commit();

                Log::info('User Updated Successfully:', ['id' => $id, 'name' => $request->name]);

                return redirect()->route('admin.users.index')
                    ->with('success', 'User berhasil diperbarui!');

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error Updating User:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui user: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $userName = $user->name;
            $user->delete();

            return redirect()->route('admin.users.index')
                ->with('success', "User {$userName} berhasil dihapus!");
        } catch (\Exception $e) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Gagal menghapus user: ' . $e->getMessage());
        }
    }

    public function bulkDelete(Request $request)
    {
        try {
            $role = $request->input('role');
            
            if (!in_array($role, ['0', '2'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role tidak valid'
                ], 400);
            }

            // Gunakan raw query untuk delete
            $count = DB::table('users')->where('role', $role)->delete();
            
            $roleName = $role == '0' ? 'guru' : 'siswa';
            
            return response()->json([
                'success' => true,
                'message' => "{$count} {$roleName} berhasil dihapus"
            ]);
        } catch (\Exception $e) {
            Log::error('Bulk Delete Error:', ['message' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
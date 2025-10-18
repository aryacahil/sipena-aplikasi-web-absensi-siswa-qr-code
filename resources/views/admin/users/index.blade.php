@extends('layouts.admin')

@section('content')
<div class="bg-primary pt-10 pb-21"></div>
<div class="container-fluid mt-n22 px-6">
    <div class="row">
        <div class="col-lg-12 col-md-12 col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div class="mb-2 mb-lg-0">
                    <h3 class="mb-0 text-white">Manajemen User</h3>
                </div>
                <div>
                    <a href="{{ route('admin.users.create') }}" class="btn btn-white">
                        <i class="bi bi-plus-circle me-2"></i>Tambah User
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-6">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Daftar User</h4>
                    <div>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-primary {{ !request('role') ? 'active' : '' }}">Semua</a>
                        <a href="{{ route('admin.users.index', ['role' => '1']) }}" class="btn btn-sm btn-outline-danger {{ request('role') == '1' ? 'active' : '' }}">Admin</a>
                        <a href="{{ route('admin.users.index', ['role' => '0']) }}" class="btn btn-sm btn-outline-info {{ request('role') == '0' ? 'active' : '' }}">Guru</a>
                        <a href="{{ route('admin.users.index', ['role' => '2']) }}" class="btn btn-sm btn-outline-success {{ request('role') == '2' ? 'active' : '' }}">Siswa</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Foto</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Telepon</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $index => $user)
                                <tr>
                                    <td>{{ $users->firstItem() + $index }}</td>
                                    <td>
                                        <img src="{{ Avatar::create($user->name)->toBase64() }}" 
                                             alt="{{ $user->name }}" 
                                             class="rounded-circle" 
                                             width="40" 
                                             height="40">
                                    </td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @if($user->role == 'admin')
                                            <span class="badge bg-danger">Admin</span>
                                        @elseif($user->role == 'guru')
                                            <span class="badge bg-primary">Guru</span>
                                        @else
                                            <span class="badge bg-success">Siswa</span>
                                        @endif
                                    </td>
                                    <td>{{ $user->phone ?? '-' }}</td>
                                    <td>
                                        @if($user->status == 'active')
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-secondary">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.users.show', $user->id) }}" 
                                               class="btn btn-sm btn-info" 
                                               title="Detail">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.users.edit', $user->id) }}" 
                                               class="btn btn-sm btn-warning" 
                                               title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('admin.users.destroy', $user->id) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Yakin ingin menghapus user ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-sm btn-danger" 
                                                        title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="bi bi-inbox fs-1 text-muted"></i>
                                        <p class="text-muted mt-2">Tidak ada data user</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
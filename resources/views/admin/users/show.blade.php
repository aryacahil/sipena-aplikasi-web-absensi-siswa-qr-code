@extends('layouts.admin')

@section('content')
<div class="bg-primary pt-10 pb-21"></div>
<div class="container-fluid mt-n22 px-6">
    <div class="row">
        <div class="col-lg-12 col-md-12 col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div class="mb-2 mb-lg-0">
                    <h3 class="mb-0 text-white">Detail User</h3>
                </div>
                <div>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-white">
                        <i class="bi bi-arrow-left me-2"></i>Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-6">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <img src="{{ Avatar::create($user->name)->toBase64() }}" 
                         alt="{{ $user->name }}" 
                         class="rounded-circle mb-3" 
                         width="150" 
                         height="150">
                    <h4 class="mb-1">{{ $user->name }}</h4>
                    <p class="text-muted mb-3">{{ $user->email }}</p>
                    
                    @if($user->role == 'admin')
                        <span class="badge bg-danger fs-6">Admin</span>
                    @elseif($user->role == 'guru')
                        <span class="badge bg-primary fs-6">Guru</span>
                    @else
                        <span class="badge bg-success fs-6">Siswa</span>
                    @endif
                    
                    @if($user->status == 'active')
                        <span class="badge bg-success fs-6 ms-2">Aktif</span>
                    @else
                        <span class="badge bg-secondary fs-6 ms-2">Nonaktif</span>
                    @endif
                    
                    <div class="mt-4">
                        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-pencil me-1"></i>Edit
                        </a>
                        <form action="{{ route('admin.users.destroy', $user->id) }}" 
                              method="POST" 
                              class="d-inline"
                              onsubmit="return confirm('Yakin ingin menghapus user ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="bi bi-trash me-1"></i>Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Informasi User</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <td width="30%" class="fw-bold">Nama Lengkap</td>
                                <td>{{ $user->name }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Email</td>
                                <td>{{ $user->email }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Role</td>
                                <td>
                                    @if($user->role == 'admin')
                                        <span class="badge bg-danger">Admin</span>
                                    @elseif($user->role == 'guru')
                                        <span class="badge bg-primary">Guru</span>
                                    @else
                                        <span class="badge bg-success">Siswa</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">No. Telepon</td>
                                <td>{{ $user->phone ?? '-' }}</td>
                            </tr>
                            @if($user->role == 'siswa' && $user->parent_phone)
                            <tr>
                                <td class="fw-bold">No. Telepon Orang Tua</td>
                                <td>{{ $user->parent_phone }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td class="fw-bold">Alamat</td>
                                <td>{{ $user->address ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Status</td>
                                <td>
                                    @if($user->status == 'active')
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Terdaftar Sejak</td>
                                <td>{{ $user->created_at->format('d M Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Terakhir Update</td>
                                <td>{{ $user->updated_at->format('d M Y H:i') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
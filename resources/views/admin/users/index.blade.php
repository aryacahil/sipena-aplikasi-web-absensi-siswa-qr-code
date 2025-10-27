@extends('layouts.admin')

@section('content')
<!-- Meta tags untuk notifikasi -->
@if(session('success'))
<meta name="success-message" content="{{ session('success') }}">
@endif
@if(session('error'))
<meta name="error-message" content="{{ session('error') }}">
@endif

<!-- Hidden CSRF Token untuk AJAX -->
<input type="hidden" name="_token" value="{{ csrf_token() }}">

<div class="bg-primary pt-10 pb-21"></div>
<div class="container-fluid mt-n22 px-6">
    <!-- Header Section -->
    <div class="row">
        <div class="col-lg-12 col-md-12 col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div class="mb-2 mb-lg-0">
                    <h3 class="mb-0 text-white">Manajemen User</h3>
                    <p class="text-white-50 mb-0">Kelola data pengguna sistem</p>
                </div>
                <div>
                    <button type="button" class="btn btn-white" data-bs-toggle="modal" data-bs-target="#createUserModal">
                        <i class="bi bi-plus-circle me-2"></i>Tambah User
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Card -->
    <div class="row mt-6">
        <div class="col-md-12">
            <div class="card shadow-sm">
                
                <!-- Advanced Filter Section (Collapsible) -->
                <div class="collapse" id="advancedFilter">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-3">
                            <i class="bi bi-funnel me-2"></i>Filter & Pencarian
                        </h5>
                        <form action="{{ route('admin.users.index') }}" method="GET">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold small">Role</label>
                                    <select name="role" class="form-select">
                                        <option value="">Semua Role</option>
                                        <option value="1" {{ request('role') == '1' ? 'selected' : '' }}>Admin</option>
                                        <option value="0" {{ request('role') == '0' ? 'selected' : '' }}>Guru</option>
                                        <option value="2" {{ request('role') == '2' ? 'selected' : '' }}>Siswa</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold small">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="">Semua Status</option>
                                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Kelas</label>
                                    <select name="kelas_id" class="form-select">
                                        <option value="">Semua Kelas</option>
                                        @foreach($kelas as $k)
                                            <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>
                                                {{ $k->nama_kelas }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-search me-2"></i>Cari
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Table Header with Actions -->
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h4 class="mb-0">Daftar User</h4>
                        </div>
                        
                        <div class="d-flex gap-2 flex-wrap align-items-center">
                            <!-- Quick Filter Pills -->
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.users.index') }}" 
                                   class="btn btn-sm {{ !request('role') ? 'btn-primary' : 'btn-outline-primary' }}">
                                    Semua
                                </a>
                                <a href="{{ route('admin.users.index', ['role' => '1']) }}" 
                                   class="btn btn-sm {{ request('role') == '1' ? 'btn-danger' : 'btn-outline-danger' }}">
                                    Admin
                                </a>
                                <a href="{{ route('admin.users.index', ['role' => '0']) }}" 
                                   class="btn btn-sm {{ request('role') == '0' ? 'btn-info' : 'btn-outline-info' }}">
                                    Guru
                                </a>
                                <a href="{{ route('admin.users.index', ['role' => '2']) }}" 
                                   class="btn btn-sm {{ request('role') == '2' ? 'btn-success' : 'btn-outline-success' }}">
                                    Siswa
                                </a>
                            </div>

                            <!-- Hapus Dropdown -->
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-danger dropdown-toggle" type="button" 
                                        data-bs-toggle="dropdown">
                                    <i class="bi bi-trash me-1"></i>Hapus
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item text-danger" href="#" 
                                           onclick="confirmDeleteByRole('0', 'guru')">
                                            <i class="bi bi-person-x me-2"></i>Hapus Semua Guru
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item text-danger" href="#" 
                                           onclick="confirmDeleteByRole('2', 'siswa')">
                                            <i class="bi bi-people-fill me-2"></i>Hapus Semua Siswa
                                        </a>
                                    </li>
                                </ul>
                            </div>

                            <!-- Toggle Filter Button -->
                            <button class="btn btn-sm btn-outline-secondary" type="button" 
                                    data-bs-toggle="collapse" data-bs-target="#advancedFilter">
                                <i class="bi bi-funnel me-1"></i>Filter
                            </button>

                            <!-- Search Box -->
                            <form action="{{ route('admin.users.index') }}" method="GET" class="d-flex">
                                <div class="input-group" style="width: 250px;">
                                    <input type="text" name="search" class="form-control form-control-sm" 
                                           placeholder="Cari nama atau email..." 
                                           value="{{ request('search') }}">
                                    <button class="btn btn-sm btn-outline-secondary" type="submit">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Table Body -->
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0 text-center align-middle" style="width: 60px;">ID</th>
                                    <th class="border-0 align-middle">Nama</th>
                                    <th class="border-0 align-middle">Email</th>
                                    <th class="border-0 text-center align-middle">Role</th>
                                    <th class="border-0 text-center align-middle">Status</th>
                                    <th class="border-0 text-center align-middle" style="width: 120px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $index => $user)
                                <tr>
                                    <td class="align-middle text-center">
                                        <span class="text-muted fw-semibold">{{ $users->firstItem() + $index }}</span>
                                    </td>
                                    <td class="align-middle">
                                        <div class="d-flex align-items-center">
                                            <img src="{{ Avatar::create($user->name)->toBase64() }}" 
                                                 alt="{{ $user->name }}" 
                                                 class="rounded-circle me-3" 
                                                 width="40" 
                                                 height="40">
                                            <div>
                                                <h6 class="mb-0">{{ $user->name }}</h6>
                                                @if($user->kelas)
                                                <small class="text-muted">{{ $user->kelas->nama_kelas }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <span class="text-muted">{{ $user->email }}</span>
                                    </td>
                                    <td class="align-middle text-center">
                                        @if($user->role == 'admin')
                                            <span class="badge bg-danger-soft text-danger">
                                                <i class="bi bi-shield-lock me-1"></i>Admin
                                            </span>
                                        @elseif($user->role == 'guru')
                                            <span class="badge bg-info-soft text-info">
                                                <i class="bi bi-person-badge me-1"></i>Guru
                                            </span>
                                        @else
                                            <span class="badge bg-success-soft text-success">
                                                <i class="bi bi-person me-1"></i>Siswa
                                            </span>
                                        @endif
                                    </td>
                                    <td class="align-middle text-center">
                                        @if($user->status == 'active')
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle me-1"></i>Aktif
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="bi bi-x-circle me-1"></i>Nonaktif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="align-middle text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <button type="button" 
                                                    class="btn btn-sm btn-warning btn-show-user" 
                                                    data-user-id="{{ $user->id }}"
                                                    title="Lihat">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-sm btn-primary btn-edit-user" 
                                                    data-user-id="{{ $user->id }}"
                                                    title="Ubah">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form action="{{ route('admin.users.destroy', $user->id) }}" 
                                                  method="POST" 
                                                  class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" 
                                                        class="btn btn-sm btn-danger btn-delete" 
                                                        data-name="{{ $user->name }}"
                                                        title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="py-4">
                                            <i class="bi bi-inbox fs-1 text-muted"></i>
                                            <p class="text-muted mt-3 mb-0">
                                                @if(request()->hasAny(['search', 'role', 'status', 'kelas_id']))
                                                    Tidak ada data user yang sesuai dengan filter
                                                @else
                                                    Tidak ada data user
                                                @endif
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination Footer -->
                @if($users->total() > 0)
                <div class="card-footer bg-white border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Menampilkan <strong>{{ $users->firstItem() }}</strong> sampai <strong>{{ $users->lastItem() }}</strong> dari <strong>{{ $users->total() }}</strong> data
                        </div>
                        <nav aria-label="Navigasi halaman">
                            @if ($users->hasPages())
                                <ul class="pagination mb-0">
                                    {{-- Previous Page Link --}}
                                    @if ($users->onFirstPage())
                                        <li class="page-item disabled" aria-disabled="true">
                                            <span class="page-link">&laquo;</span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $users->appends(request()->query())->previousPageUrl() }}" rel="prev">&laquo;</a>
                                        </li>
                                    @endif

                                    {{-- Pagination Elements --}}
                                    @php
                                        $start = max($users->currentPage() - 1, 1);
                                        $end = min($start + 2, $users->lastPage());
                                        $start = max($end - 2, 1);
                                    @endphp

                                    @if($start > 1)
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $users->appends(request()->query())->url(1) }}">1</a>
                                        </li>
                                        @if($start > 2)
                                            <li class="page-item disabled"><span class="page-link">...</span></li>
                                        @endif
                                    @endif

                                    @for ($i = $start; $i <= $end; $i++)
                                        @if ($i == $users->currentPage())
                                            <li class="page-item active" aria-current="page">
                                                <span class="page-link">{{ $i }}</span>
                                            </li>
                                        @else
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $users->appends(request()->query())->url($i) }}">{{ $i }}</a>
                                            </li>
                                        @endif
                                    @endfor

                                    @if($end < $users->lastPage())
                                        @if($end < $users->lastPage() - 1)
                                            <li class="page-item disabled"><span class="page-link">...</span></li>
                                        @endif
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $users->appends(request()->query())->url($users->lastPage()) }}">{{ $users->lastPage() }}</a>
                                        </li>
                                    @endif

                                    {{-- Next Page Link --}}
                                    @if ($users->hasMorePages())
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $users->appends(request()->query())->nextPageUrl() }}" rel="next">&raquo;</a>
                                        </li>
                                    @else
                                        <li class="page-item disabled" aria-disabled="true">
                                            <span class="page-link">&raquo;</span>
                                        </li>
                                    @endif
                                </ul>
                            @endif
                        </nav>
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>
</div>

<!-- Modal Create User -->
<div class="modal fade" id="createUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-person-plus-fill me-2"></i>Tambah User Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.users.store') }}" method="POST" id="createUserForm">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-select" id="create_role" name="role" required onchange="toggleStudentFieldsCreate()">
                                <option value="">Pilih Role</option>
                                <option value="1">Admin</option>
                                <option value="0">Guru</option>
                                <option value="2">Siswa</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" name="status" required>
                                <option value="active">Aktif</option>
                                <option value="inactive">Nonaktif</option>
                            </select>
                        </div>
                        <div class="col-md-6" id="create_kelas_group" style="display: none;">
                            <label class="form-label">Kelas <span class="text-danger">*</span></label>
                            <select class="form-select" id="create_kelas_id" name="kelas_id">
                                <option value="">Pilih Kelas</option>
                                @foreach($kelas as $item)
                                    <option value="{{ $item->id }}">
                                        {{ $item->nama_kelas }} - {{ $item->jurusan->nama_jurusan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6" id="create_parent_phone_group" style="display: none;">
                            <label class="form-label">No. Telepon Orang Tua <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="create_parent_phone" 
                                   name="parent_phone" placeholder="08xxxxxxxxxx">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kata Sandi <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Konfirmasi Kata Sandi <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" name="password_confirmation" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Show User -->
<div class="modal fade" id="showUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-person-circle me-2"></i>Detail User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="showUserContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit User -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-pencil-square me-2"></i>Ubah User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editUserForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="text-center py-5" id="editUserLoading">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                    <div id="editUserFormContent" style="display: none;">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_name" name="name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="edit_email" name="email" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Role <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_role" name="role" required onchange="toggleStudentFieldsEdit()">
                                    <option value="">Pilih Role</option>
                                    <option value="1">Admin</option>
                                    <option value="0">Guru</option>
                                    <option value="2">Siswa</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_status" name="status" required>
                                    <option value="active">Aktif</option>
                                    <option value="inactive">Nonaktif</option>
                                </select>
                            </div>
                            <div class="col-md-6" id="edit_kelas_group" style="display: none;">
                                <label class="form-label">Kelas <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_kelas_id" name="kelas_id">
                                    <option value="">Pilih Kelas</option>
                                    @foreach($kelas as $item)
                                        <option value="{{ $item->id }}">
                                            {{ $item->nama_kelas }} - {{ $item->jurusan->nama_jurusan }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6" id="edit_parent_phone_group" style="display: none;">
                                <label class="form-label">No. Telepon Orang Tua</label>
                                <input type="text" class="form-control" id="edit_parent_phone" 
                                       name="parent_phone" placeholder="08xxxxxxxxxx">
                            </div>
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Kosongkan kata sandi jika tidak ingin mengubahnya
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kata Sandi Baru</label>
                                <input type="password" class="form-control" id="edit_password" name="password">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Konfirmasi Kata Sandi Baru</label>
                                <input type="password" class="form-control" id="edit_password_confirmation" 
                                       name="password_confirmation">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="editUserSubmitBtn" style="display: none;">Perbarui</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<link rel="stylesheet" href="{{ asset('css/admin/users.css') }}">
<script src="{{ asset('js/admin/users.js') }}"></script>
@endpush
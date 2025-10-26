@extends('layouts.admin')

@section('content')
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

    <!-- Main Content Card with Filter -->
    <div class="row mt-6">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <!-- Filter Section -->
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-3">
                        <i class="bi bi-funnel me-2"></i>Filter & Pencarian
                    </h5>
                    <form action="{{ route('admin.users.index') }}" method="GET">
                        <div class="row g-3">
                            <!-- Search -->
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Cari Nama/Email</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                                    <input type="text" name="search" class="form-control" 
                                           placeholder="Ketik nama atau email..." 
                                           value="{{ request('search') }}">
                                </div>
                            </div>
                            
                            <!-- Filter Role -->
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Role</label>
                                <select name="role" class="form-select">
                                    <option value="">Semua Role</option>
                                    <option value="1" {{ request('role') == '1' ? 'selected' : '' }}>Admin</option>
                                    <option value="0" {{ request('role') == '0' ? 'selected' : '' }}>Guru</option>
                                    <option value="2" {{ request('role') == '2' ? 'selected' : '' }}>Siswa</option>
                                </select>
                            </div>
                            
                            <!-- Filter Status -->
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">Semua Status</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                                </select>
                            </div>
                            
                            <!-- Filter Kelas -->
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Kelas</label>
                                <select name="kelas_id" class="form-select">
                                    <option value="">Semua Kelas</option>
                                    @foreach($kelas as $k)
                                        <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>
                                            {{ $k->nama_kelas }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Buttons -->
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary btn-sm" title="Cari">
                                        <i class="bi bi-search"></i>
                                    </button>
                                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm" title="Reset">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Table Header -->
                <div class="card-header bg-white border-bottom border-top">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h4 class="mb-0">Daftar User</h4>
                        <div class="d-flex gap-2 flex-wrap">
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

                            <!-- Bulk Actions Dropdown -->
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-danger dropdown-toggle" type="button" 
                                        id="bulkActionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-trash me-1"></i> Hapus Massal
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="bulkActionsDropdown">
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
                        </div>
                    </div>
                </div>

                <!-- Table Body -->
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0">No</th>
                                    <th class="border-0">User</th>
                                    <th class="border-0">Email</th>
                                    <th class="border-0">Role</th>
                                    <th class="border-0">Status</th>
                                    <th class="border-0 text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $index => $user)
                                <tr>
                                    <td class="align-middle">
                                        <span class="text-muted">{{ $users->firstItem() + $index }}</span>
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
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <span class="text-muted">{{ $user->email }}</span>
                                    </td>
                                    <td class="align-middle">
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
                                    <td class="align-middle">
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
                                    <td class="align-middle text-end">
                                        <div class="btn-group" role="group">
                                            <button type="button" 
                                                    class="btn btn-sm btn-light btn-show-user" 
                                                    data-user-id="{{ $user->id }}"
                                                    title="Detail">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-sm btn-light btn-edit-user" 
                                                    data-user-id="{{ $user->id }}"
                                                    title="Edit">
                                                <i class="bi bi-pencil text-warning"></i>
                                            </button>
                                            <form action="{{ route('admin.users.destroy', $user->id) }}" 
                                                  method="POST" 
                                                  class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" 
                                                        class="btn btn-sm btn-light btn-delete" 
                                                        data-name="{{ $user->name }}"
                                                        title="Hapus">
                                                    <i class="bi bi-trash text-danger"></i>
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

                @if($users->total() > 0)
                <div class="card-footer bg-white border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            Menampilkan {{ $users->firstItem() }} - {{ $users->lastItem() }} 
                            dari {{ $users->total() }} user
                        </div>
                        <div>
                            {{ $users->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Create User -->
<div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="createUserModalLabel">
                    <i class="bi bi-person-plus-fill me-2"></i>Tambah User Baru
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.users.store') }}" method="POST" id="createUserForm">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="create_name" class="form-label fw-semibold">
                                Nama Lengkap <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="create_name" name="name" required>
                        </div>

                        <div class="col-md-6">
                            <label for="create_email" class="form-label fw-semibold">
                                Email <span class="text-danger">*</span>
                            </label>
                            <input type="email" class="form-control" id="create_email" name="email" required>
                        </div>

                        <div class="col-md-6">
                            <label for="create_role" class="form-label fw-semibold">
                                Role <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="create_role" name="role" required onchange="toggleStudentFieldsCreate()">
                                <option value="">Pilih Role</option>
                                <option value="1">Admin</option>
                                <option value="0">Guru</option>
                                <option value="2">Siswa</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="create_status" class="form-label fw-semibold">
                                Status <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="create_status" name="status" required>
                                <option value="active">Aktif</option>
                                <option value="inactive">Nonaktif</option>
                            </select>
                        </div>

                        <div class="col-md-6" id="create_kelas_group" style="display: none;">
                            <label for="create_kelas_id" class="form-label fw-semibold">
                                Kelas <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="create_kelas_id" name="kelas_id">
                                <option value="">Pilih Kelas</option>
                                @foreach($kelas as $item)
                                    <option value="{{ $item->id }}">
                                        {{ $item->nama_kelas }} - {{ $item->jurusan->nama_jurusan }} (Tingkat {{ $item->tingkat }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6" id="create_parent_phone_group" style="display: none;">
                            <label for="create_parent_phone" class="form-label fw-semibold">
                                No. Telepon Orang Tua <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="create_parent_phone" 
                                   name="parent_phone" placeholder="08xxxxxxxxxx">
                            <small class="text-muted">Untuk notifikasi WhatsApp</small>
                        </div>

                        <div class="col-md-6">
                            <label for="create_password" class="form-label fw-semibold">
                                Password <span class="text-danger">*</span>
                            </label>
                            <input type="password" class="form-control" id="create_password" name="password" required>
                            <small class="text-muted">Minimal 8 karakter</small>
                        </div>

                        <div class="col-md-6">
                            <label for="create_password_confirmation" class="form-label fw-semibold">
                                Konfirmasi Password <span class="text-danger">*</span>
                            </label>
                            <input type="password" class="form-control" id="create_password_confirmation" 
                                   name="password_confirmation" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Show User -->
<div class="modal fade" id="showUserModal" tabindex="-1" aria-labelledby="showUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="showUserModalLabel">
                    <i class="bi bi-person-circle me-2"></i>Detail User
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="showUserContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit User -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="editUserModalLabel">
                    <i class="bi bi-pencil-square me-2"></i>Edit User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editUserForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="text-center py-5" id="editUserLoading">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <div id="editUserFormContent" style="display: none;">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="edit_name" class="form-label fw-semibold">
                                    Nama Lengkap <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="edit_name" name="name" required>
                            </div>

                            <div class="col-md-6">
                                <label for="edit_email" class="form-label fw-semibold">
                                    Email <span class="text-danger">*</span>
                                </label>
                                <input type="email" class="form-control" id="edit_email" name="email" required>
                            </div>

                            <div class="col-md-6">
                                <label for="edit_role" class="form-label fw-semibold">
                                    Role <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="edit_role" name="role" required onchange="toggleStudentFieldsEdit()">
                                    <option value="">Pilih Role</option>
                                    <option value="1">Admin</option>
                                    <option value="0">Guru</option>
                                    <option value="2">Siswa</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="edit_status" class="form-label fw-semibold">
                                    Status <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="edit_status" name="status" required>
                                    <option value="active">Aktif</option>
                                    <option value="inactive">Nonaktif</option>
                                </select>
                            </div>

                            <div class="col-md-6" id="edit_kelas_group" style="display: none;">
                                <label for="edit_kelas_id" class="form-label fw-semibold">
                                    Kelas <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="edit_kelas_id" name="kelas_id">
                                    <option value="">Pilih Kelas</option>
                                    @foreach($kelas as $item)
                                        <option value="{{ $item->id }}">
                                            {{ $item->nama_kelas }} - {{ $item->jurusan->nama_jurusan }} (Tingkat {{ $item->tingkat }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6" id="edit_parent_phone_group" style="display: none;">
                                <label for="edit_parent_phone" class="form-label fw-semibold">
                                    No. Telepon Orang Tua <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="edit_parent_phone" 
                                       name="parent_phone" placeholder="08xxxxxxxxxx">
                            </div>

                            <div class="col-12">
                                <div class="alert alert-info mb-0">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <strong>Info:</strong> Kosongkan password jika tidak ingin mengubahnya
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="edit_password" class="form-label fw-semibold">Password Baru</label>
                                <input type="password" class="form-control" id="edit_password" name="password">
                                <small class="text-muted">Minimal 8 karakter</small>
                            </div>

                            <div class="col-md-6">
                                <label for="edit_password_confirmation" class="form-label fw-semibold">
                                    Konfirmasi Password Baru
                                </label>
                                <input type="password" class="form-control" id="edit_password_confirmation" 
                                       name="password_confirmation">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-primary" id="editUserSubmitBtn" style="display: none;">
                        <i class="bi bi-save me-2"></i>Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<style>
.badge-soft {
    padding: 0.5rem 0.75rem;
    font-weight: 500;
}

.bg-danger-soft {
    background-color: rgba(220, 53, 69, 0.1);
}

.bg-info-soft {
    background-color: rgba(13, 202, 240, 0.1);
}

.bg-success-soft {
    background-color: rgba(25, 135, 84, 0.1);
}

.table > :not(caption) > * > * {
    padding: 1rem 0.75rem;
}

.btn-light:hover {
    background-color: #f8f9fa;
    border-color: #dee2e6;
}
</style>

<script>
// Toggle student fields in create form
function toggleStudentFieldsCreate() {
    const role = document.getElementById('create_role').value;
    const kelasGroup = document.getElementById('create_kelas_group');
    const parentPhoneGroup = document.getElementById('create_parent_phone_group');
    const kelasSelect = document.getElementById('create_kelas_id');
    const parentPhoneInput = document.getElementById('create_parent_phone');
    
    if (role == '2') {
        kelasGroup.style.display = 'block';
        parentPhoneGroup.style.display = 'block';
        kelasSelect.required = true;
        parentPhoneInput.required = true;
    } else {
        kelasGroup.style.display = 'none';
        parentPhoneGroup.style.display = 'none';
        kelasSelect.required = false;
        parentPhoneInput.required = false;
        kelasSelect.value = '';
        parentPhoneInput.value = '';
    }
}

// Toggle student fields in edit form
function toggleStudentFieldsEdit() {
    const role = document.getElementById('edit_role').value;
    const kelasGroup = document.getElementById('edit_kelas_group');
    const parentPhoneGroup = document.getElementById('edit_parent_phone_group');
    const kelasSelect = document.getElementById('edit_kelas_id');
    const parentPhoneInput = document.getElementById('edit_parent_phone');
    
    if (role == '2') {
        kelasGroup.style.display = 'block';
        parentPhoneGroup.style.display = 'block';
        kelasSelect.required = true;
        parentPhoneInput.required = true;
    } else {
        kelasGroup.style.display = 'none';
        parentPhoneGroup.style.display = 'none';
        kelasSelect.required = false;
        parentPhoneInput.required = false;
    }
}

// Show user details
document.querySelectorAll('.btn-show-user').forEach(button => {
    button.addEventListener('click', function() {
        const userId = this.getAttribute('data-user-id');
        const modal = new bootstrap.Modal(document.getElementById('showUserModal'));
        const content = document.getElementById('showUserContent');
        
        content.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3 text-muted">Memuat data pengguna...</p>
            </div>
        `;
        
        modal.show();

        fetch(`/admin/users/${userId}`)
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const userContent = doc.querySelector('.row.mt-6');
                
                if (userContent) {
                    content.innerHTML = `
                        <div class="row align-items-center g-4">
                            <div class="col-md-5 text-center border-end">
                                ${userContent.querySelector('.col-md-4')?.innerHTML || ''}
                            </div>
                            <div class="col-md-7">
                                ${userContent.querySelector('.col-md-8')?.innerHTML || ''}
                            </div>
                        </div>
                    `;
                } else {
                    content.innerHTML = `
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Tidak dapat menemukan data user.
                        </div>
                    `;
                }
            })
            .catch(error => {
                content.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Gagal memuat data user.
                    </div>
                `;
            });
    });
});

// Edit user
document.querySelectorAll('.btn-edit-user').forEach(button => {
    button.addEventListener('click', function() {
        const userId = this.getAttribute('data-user-id');
        const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
        const form = document.getElementById('editUserForm');
        const loading = document.getElementById('editUserLoading');
        const content = document.getElementById('editUserFormContent');
        const submitBtn = document.getElementById('editUserSubmitBtn');
        
        loading.style.display = 'block';
        content.style.display = 'none';
        submitBtn.style.display = 'none';
        
        form.action = `/admin/users/${userId}`;
        
        modal.show();
        
        fetch(`/admin/users/${userId}/edit`)
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                const nameInput = doc.querySelector('input[name="name"]');
                const emailInput = doc.querySelector('input[name="email"]');
                const roleSelect = doc.querySelector('select[name="role"]');
                const statusSelect = doc.querySelector('select[name="status"]');
                const kelasSelect = doc.querySelector('select[name="kelas_id"]');
                const parentPhoneInput = doc.querySelector('input[name="parent_phone"]');
                
                if (nameInput) document.getElementById('edit_name').value = nameInput.value;
                if (emailInput) document.getElementById('edit_email').value = emailInput.value;
                
                if (roleSelect) {
                    const selectedRole = roleSelect.querySelector('option[selected]');
                    if (selectedRole) {
                        document.getElementById('edit_role').value = selectedRole.value;
                    }
                }
                
                if (statusSelect) {
                    const selectedStatus = statusSelect.querySelector('option[selected]');
                    if (selectedStatus) {
                        document.getElementById('edit_status').value = selectedStatus.value;
                    }
                }
                
                if (kelasSelect) {
                    const selectedKelas = kelasSelect.querySelector('option[selected]');
                    if (selectedKelas) {
                        document.getElementById('edit_kelas_id').value = selectedKelas.value;
                    }
                }
                
                if (parentPhoneInput) {
                    document.getElementById('edit_parent_phone').value = parentPhoneInput.value || '';
                }
                
                toggleStudentFieldsEdit();
                
                loading.style.display = 'none';
                content.style.display = 'block';
                submitBtn.style.display = 'inline-block';
            })
            .catch(error => {
                console.error('Error:', error);
                loading.innerHTML = `
                    <div class="alert alert-danger m-3">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Gagal memuat data user
                    </div>
                `;
            });
    });
});

// Success/Error notifications with SweetAlert2
@if(session('success'))
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '{{ session("success") }}',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true,
        toast: true,
        position: 'top-end'
    });
@endif

@if(session('error'))
    Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: '{{ session("error") }}',
        confirmButtonText: 'OK',
        confirmButtonColor: '#dc3545'
    });
@endif

// Delete confirmation with SweetAlert2
document.querySelectorAll('.btn-delete').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        const form = this.closest('form');
        const userName = this.getAttribute('data-name');
        
        Swal.fire({
            title: 'Hapus User?',
            html: `Apakah Anda yakin ingin menghapus user<br><strong>${userName}</strong>?<br><br><small class="text-muted">Data yang sudah dihapus tidak dapat dikembalikan!</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-trash me-1"></i> Ya, Hapus!',
            cancelButtonText: '<i class="bi bi-x-circle me-1"></i> Batal',
            reverseButtons: true,
            focusCancel: true
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Menghapus...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                form.submit();
            }
        });
    });
});

// Bulk Delete Function with SweetAlert2
function confirmDeleteByRole(role, roleName) {
    event.preventDefault();

    Swal.fire({
        title: `Hapus Semua ${roleName.charAt(0).toUpperCase() + roleName.slice(1)}?`,
        html: `
            <p class="mb-2">Semua data <strong>${roleName}</strong> akan dihapus permanen.</p>
            <div class="alert alert-danger small">
                <i class="bi bi-exclamation-triangle me-1"></i>
                Tindakan ini <strong>tidak dapat dibatalkan</strong>.
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-trash me-1"></i> Ya, Hapus Semua',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Menghapus...',
                html: `Sedang menghapus semua ${roleName}...<br><small class="text-muted">Mohon tunggu</small>`,
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('{{ route("admin.users.bulk-delete") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ role: role })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil Dihapus!',
                        html: `
                            <p>${data.message}</p>
                            <div class="alert alert-success mt-3">
                                <i class="bi bi-check-circle me-2"></i>
                                <strong>${data.count} ${roleName}</strong> telah dihapus dari sistem
                            </div>
                        `,
                        confirmButtonColor: '#28a745'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Menghapus',
                        text: data.message,
                        confirmButtonColor: '#dc3545'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Terjadi Kesalahan',
                    html: `
                        <p>Gagal menghapus ${roleName}</p>
                        <p class="text-muted small">${error.message}</p>
                    `,
                    confirmButtonColor: '#dc3545'
                });
            });
        }
    });
}

// Reset form when create modal is closed
document.getElementById('createUserModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('createUserForm').reset();
    toggleStudentFieldsCreate();
});

// Reset form when edit modal is closed
document.getElementById('editUserModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('editUserForm').reset();
    document.getElementById('editUserLoading').style.display = 'block';
    document.getElementById('editUserFormContent').style.display = 'none';
    document.getElementById('editUserSubmitBtn').style.display = 'none';
    document.getElementById('edit_password').value = '';
    document.getElementById('edit_password_confirmation').value = '';
});
</script>
@endpush
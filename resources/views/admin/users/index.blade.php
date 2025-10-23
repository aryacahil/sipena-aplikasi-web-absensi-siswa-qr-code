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
                    <button type="button" class="btn btn-white" data-bs-toggle="modal" data-bs-target="#createUserModal">
                        <i class="bi bi-plus-circle me-2"></i>Tambah User
                    </button>
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
                                    <td>
                                        @if($user->status == 'active')
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-secondary">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" 
                                                    class="btn btn-sm btn-info btn-show-user" 
                                                    data-user-id="{{ $user->id }}"
                                                    title="Detail">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-sm btn-warning btn-edit-user" 
                                                    data-user-id="{{ $user->id }}"
                                                    title="Edit">
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
                                    <td colspan="7" class="text-center py-4">
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

<!-- Modal Create User -->
<div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createUserModalLabel">
                    <i class="bi bi-person-plus-fill me-2"></i>Tambah User Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.users.store') }}" method="POST" id="createUserForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <!-- Nama -->
                        <div class="col-md-6 mb-3">
                            <label for="create_name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control" 
                                   id="create_name" 
                                   name="name" 
                                   required>
                        </div>

                        <!-- Email -->
                        <div class="col-md-6 mb-3">
                            <label for="create_email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" 
                                   class="form-control" 
                                   id="create_email" 
                                   name="email" 
                                   required>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Role -->
                        <div class="col-md-6 mb-3">
                            <label for="create_role" class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-select" 
                                    id="create_role" 
                                    name="role" 
                                    required
                                    onchange="toggleStudentFieldsCreate()">
                                <option value="">Pilih Role</option>
                                <option value="1">Admin</option>
                                <option value="0">Guru</option>
                                <option value="2">Siswa</option>
                            </select>
                        </div>

                        <!-- Status -->
                        <div class="col-md-6 mb-3">
                            <label for="create_status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" 
                                    id="create_status" 
                                    name="status" 
                                    required>
                                <option value="active">Aktif</option>
                                <option value="inactive">Nonaktif</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Kelas (untuk siswa) -->
                        <div class="col-md-6 mb-3" id="create_kelas_group" style="display: none;">
                            <label for="create_kelas_id" class="form-label">Kelas <span class="text-danger">*</span></label>
                            <select class="form-select" 
                                    id="create_kelas_id" 
                                    name="kelas_id">
                                <option value="">Pilih Kelas</option>
                                @foreach($kelas as $item)
                                    <option value="{{ $item->id }}">
                                        {{ $item->nama_kelas }} - {{ $item->jurusan->nama_jurusan }} (Tingkat {{ $item->tingkat }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Pilih kelas untuk siswa</small>
                        </div>

                        <!-- Telepon Orang Tua (untuk siswa) -->
                        <div class="col-md-6 mb-3" id="create_parent_phone_group" style="display: none;">
                            <label for="create_parent_phone" class="form-label">No. Telepon Orang Tua <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control" 
                                   id="create_parent_phone" 
                                   name="parent_phone" 
                                   placeholder="08xxxxxxxxxx">
                            <small class="text-muted">Untuk notifikasi WhatsApp ke orang tua</small>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Password -->
                        <div class="col-md-6 mb-3">
                            <label for="create_password" class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" 
                                   class="form-control" 
                                   id="create_password" 
                                   name="password" 
                                   required>
                            <small class="text-muted">Minimal 8 karakter</small>
                        </div>

                        <!-- Konfirmasi Password -->
                        <div class="col-md-6 mb-3">
                            <label for="create_password_confirmation" class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                            <input type="password" 
                                   class="form-control" 
                                   id="create_password_confirmation" 
                                   name="password_confirmation" 
                                   required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="showUserModalLabel">
                    <i class="bi bi-person-circle me-2"></i>Detail User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="showUserContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit User -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
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
                        <div class="row">
                            <!-- Nama -->
                            <div class="col-md-6 mb-3">
                                <label for="edit_name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       id="edit_name" 
                                       name="name" 
                                       required>
                            </div>

                            <!-- Email -->
                            <div class="col-md-6 mb-3">
                                <label for="edit_email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" 
                                       class="form-control" 
                                       id="edit_email" 
                                       name="email" 
                                       required>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Role -->
                            <div class="col-md-6 mb-3">
                                <label for="edit_role" class="form-label">Role <span class="text-danger">*</span></label>
                                <select class="form-select" 
                                        id="edit_role" 
                                        name="role" 
                                        required
                                        onchange="toggleStudentFieldsEdit()">
                                    <option value="">Pilih Role</option>
                                    <option value="1">Admin</option>
                                    <option value="0">Guru</option>
                                    <option value="2">Siswa</option>
                                </select>
                            </div>

                            <!-- Status -->
                            <div class="col-md-6 mb-3">
                                <label for="edit_status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" 
                                        id="edit_status" 
                                        name="status" 
                                        required>
                                    <option value="active">Aktif</option>
                                    <option value="inactive">Nonaktif</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Kelas (untuk siswa) -->
                            <div class="col-md-6 mb-3" id="edit_kelas_group" style="display: none;">
                                <label for="edit_kelas_id" class="form-label">Kelas <span class="text-danger">*</span></label>
                                <select class="form-select" 
                                        id="edit_kelas_id" 
                                        name="kelas_id">
                                    <option value="">Pilih Kelas</option>
                                    @foreach($kelas as $item)
                                        <option value="{{ $item->id }}">
                                            {{ $item->nama_kelas }} - {{ $item->jurusan->nama_jurusan }} (Tingkat {{ $item->tingkat }})
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Pilih kelas untuk siswa</small>
                            </div>

                            <!-- Telepon Orang Tua (untuk siswa) -->
                            <div class="col-md-6 mb-3" id="edit_parent_phone_group" style="display: none;">
                                <label for="edit_parent_phone" class="form-label">No. Telepon Orang Tua <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       id="edit_parent_phone" 
                                       name="parent_phone" 
                                       placeholder="08xxxxxxxxxx">
                                <small class="text-muted">Untuk notifikasi WhatsApp ke orang tua</small>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Info:</strong> Kosongkan password jika tidak ingin mengubahnya
                        </div>

                        <div class="row">
                            <!-- Password -->
                            <div class="col-md-6 mb-3">
                                <label for="edit_password" class="form-label">Password Baru</label>
                                <input type="password" 
                                       class="form-control" 
                                       id="edit_password" 
                                       name="password">
                                <small class="text-muted">Minimal 8 karakter (kosongkan jika tidak diubah)</small>
                            </div>

                            <!-- Konfirmasi Password -->
                            <div class="col-md-6 mb-3">
                                <label for="edit_password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                                <input type="password" 
                                       class="form-control" 
                                       id="edit_password_confirmation" 
                                       name="password_confirmation">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

    // Show user details
    document.querySelectorAll('.btn-show-user').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-user-id');
            const modal = new bootstrap.Modal(document.getElementById('showUserModal'));
            const content = document.getElementById('showUserContent');
            
            // Show loading
            content.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;
            
            modal.show();
            
            // Fetch user data
            fetch(`/admin/users/${userId}`)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const userContent = doc.querySelector('.row.mt-6');
                    
                    if (userContent) {
                        content.innerHTML = userContent.innerHTML;
                    }
                })
                .catch(error => {
                    content.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Gagal memuat data user
                        </div>
                    `;
                });
        });
    });

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

    // Edit user
    document.querySelectorAll('.btn-edit-user').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-user-id');
            const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
            const form = document.getElementById('editUserForm');
            const loading = document.getElementById('editUserLoading');
            const content = document.getElementById('editUserFormContent');
            const submitBtn = document.getElementById('editUserSubmitBtn');
            
            // Show loading
            loading.style.display = 'block';
            content.style.display = 'none';
            submitBtn.style.display = 'none';
            
            // Set form action
            form.action = `/admin/users/${userId}`;
            
            modal.show();
            
            // Fetch user data
            fetch(`/admin/users/${userId}/edit`)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    // Get user data from the edit page
                    const nameInput = doc.querySelector('input[name="name"]');
                    const emailInput = doc.querySelector('input[name="email"]');
                    const roleSelect = doc.querySelector('select[name="role"]');
                    const statusSelect = doc.querySelector('select[name="status"]');
                    const kelasSelect = doc.querySelector('select[name="kelas_id"]');
                    const parentPhoneInput = doc.querySelector('input[name="parent_phone"]');
                    
                    // Fill form with user data
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
                    
                    // Toggle student fields based on role
                    toggleStudentFieldsEdit();
                    
                    // Hide loading, show form
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

    // Success/Error notifications
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
            confirmButtonText: 'OK'
        });
    @endif

    // Delete confirmation
    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            const userName = this.getAttribute('data-name');
            
            Swal.fire({
                title: 'Hapus User?',
                html: `Apakah Anda yakin ingin menghapus user<br><strong>${userName}</strong>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
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
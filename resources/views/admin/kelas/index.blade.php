@extends('layouts.admin')

@section('content')
<div class="bg-primary pt-10 pb-21"></div>
<div class="container-fluid mt-n22 px-6">
    <div class="row">
        <div class="col-lg-12 col-md-12 col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div class="mb-2 mb-lg-0">
                    <h3 class="mb-0 text-white">Manajemen Kelas</h3>
                </div>
                <div>
                    <button type="button" class="btn btn-white" data-bs-toggle="modal" data-bs-target="#createKelasModal">
                        <i class="bi bi-plus-circle me-2"></i>Tambah Kelas
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-6">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Daftar Kelas</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Tingkat</th>
                                    <th>Nama Kelas</th>
                                    <th>Jurusan</th>
                                    <th>Wali Kelas</th>
                                    <th>Jumlah Siswa</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($kelas as $index => $k)
                                <tr>
                                    <td>{{ $kelas->firstItem() + $index }}</td>
                                    <td><span class="badge bg-primary">{{ $k->tingkat }}</span></td>
                                    <td>{{ $k->nama_kelas }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $k->jurusan->kode_jurusan }}</span>
                                        {{ $k->jurusan->nama_jurusan }}
                                    </td>
                                    <td>{{ $k->waliKelas->name ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-success">{{ $k->siswa_count ?? 0 }} Siswa</span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-info btn-show-kelas" 
                                                    data-kelas-id="{{ $k->id }}" title="Detail">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-warning btn-edit-kelas" 
                                                    data-kelas-id="{{ $k->id }}" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form action="{{ route('admin.kelas.destroy', $k->id) }}" 
                                                  method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-danger btn-delete" 
                                                        data-name="{{ $k->nama_kelas }}" title="Hapus">
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
                                        <p class="text-muted mt-2">Tidak ada data kelas</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $kelas->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Create Kelas -->
<div class="modal fade" id="createKelasModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle me-2"></i>Tambah Kelas Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.kelas.store') }}" method="POST" id="createKelasForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="create_jurusan_id" class="form-label">Jurusan <span class="text-danger">*</span></label>
                            <select class="form-select" id="create_jurusan_id" name="jurusan_id" required>
                                <option value="">Pilih Jurusan</option>
                                @foreach($jurusans as $jurusan)
                                    <option value="{{ $jurusan->id }}">
                                        {{ $jurusan->kode_jurusan }} - {{ $jurusan->nama_jurusan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="create_tingkat" class="form-label">Tingkat <span class="text-danger">*</span></label>
                            <select class="form-select" id="create_tingkat" name="tingkat" required>
                                <option value="">Pilih Tingkat</option>
                                <option value="10">Kelas 10</option>
                                <option value="11">Kelas 11</option>
                                <option value="12">Kelas 12</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="create_kode_kelas" class="form-label">Kode Kelas <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="create_kode_kelas" 
                                   name="kode_kelas" placeholder="Contoh: X-TKJ-1" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="create_nama_kelas" class="form-label">Nama Kelas <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="create_nama_kelas" 
                                   name="nama_kelas" placeholder="Contoh: X TKJ 1" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="create_wali_kelas_id" class="form-label">Wali Kelas</label>
                        <select class="form-select" id="create_wali_kelas_id" name="wali_kelas_id">
                            <option value="">Pilih Wali Kelas (Opsional)</option>
                            @foreach($gurus as $guru)
                                <option value="{{ $guru->id }}">
                                    {{ $guru->name }} - {{ $guru->email }}
                                </option>
                            @endforeach
                        </select>
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

<!-- Modal Show Kelas -->
<div class="modal fade" id="showKelasModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-info-circle me-2"></i>Detail Kelas
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="showKelasContent">
                <div class="text-center py-5">
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

<!-- Modal Edit Kelas -->
<div class="modal fade" id="editKelasModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-pencil-square me-2"></i>Edit Kelas
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editKelasForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="text-center py-5" id="editKelasLoading">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <div id="editKelasFormContent" style="display: none;">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_jurusan_id" class="form-label">Jurusan <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_jurusan_id" name="jurusan_id" required>
                                    <option value="">Pilih Jurusan</option>
                                    @foreach($jurusans as $jurusan)
                                        <option value="{{ $jurusan->id }}">
                                            {{ $jurusan->kode_jurusan }} - {{ $jurusan->nama_jurusan }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_tingkat" class="form-label">Tingkat <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_tingkat" name="tingkat" required>
                                    <option value="">Pilih Tingkat</option>
                                    <option value="10">Kelas 10</option>
                                    <option value="11">Kelas 11</option>
                                    <option value="12">Kelas 12</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_kode_kelas" class="form-label">Kode Kelas <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_kode_kelas" name="kode_kelas" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_nama_kelas" class="form-label">Nama Kelas <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_nama_kelas" name="nama_kelas" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_wali_kelas_id" class="form-label">Wali Kelas</label>
                            <select class="form-select" id="edit_wali_kelas_id" name="wali_kelas_id">
                                <option value="">Pilih Wali Kelas (Opsional)</option>
                                @foreach($gurus as $guru)
                                    <option value="{{ $guru->id }}">
                                        {{ $guru->name }} - {{ $guru->email }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-primary" id="editKelasSubmitBtn" style="display: none;">
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
    // Show kelas details
    document.querySelectorAll('.btn-show-kelas').forEach(btn => {
        btn.addEventListener('click', function() {
            const kelasId = this.getAttribute('data-kelas-id');
            const modal = new bootstrap.Modal(document.getElementById('showKelasModal'));
            const content = document.getElementById('showKelasContent');
            
            content.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;
            
            modal.show();
            
            fetch(`/admin/kelas/${kelasId}`)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const kelasContent = doc.querySelector('.row.mt-6');
                    
                    if (kelasContent) {
                        content.innerHTML = kelasContent.innerHTML;
                    }
                })
                .catch(error => {
                    content.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Gagal memuat data kelas
                        </div>
                    `;
                });
        });
    });

    // Edit kelas
    document.querySelectorAll('.btn-edit-kelas').forEach(btn => {
        btn.addEventListener('click', function() {
            const kelasId = this.getAttribute('data-kelas-id');
            const modal = new bootstrap.Modal(document.getElementById('editKelasModal'));
            const form = document.getElementById('editKelasForm');
            const loading = document.getElementById('editKelasLoading');
            const content = document.getElementById('editKelasFormContent');
            const submitBtn = document.getElementById('editKelasSubmitBtn');
            
            loading.style.display = 'block';
            content.style.display = 'none';
            submitBtn.style.display = 'none';
            
            form.action = `/admin/kelas/${kelasId}`;
            
            modal.show();
            
            fetch(`/admin/kelas/${kelasId}/edit`)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    const jurusanSelect = doc.querySelector('select[name="jurusan_id"]');
                    const tingkatSelect = doc.querySelector('select[name="tingkat"]');
                    const kodeInput = doc.querySelector('input[name="kode_kelas"]');
                    const namaInput = doc.querySelector('input[name="nama_kelas"]');
                    const waliSelect = doc.querySelector('select[name="wali_kelas_id"]');
                    
                    if (jurusanSelect) {
                        const selectedOption = jurusanSelect.querySelector('option[selected]');
                        if (selectedOption) {
                            document.getElementById('edit_jurusan_id').value = selectedOption.value;
                        }
                    }
                    
                    if (tingkatSelect) {
                        const selectedOption = tingkatSelect.querySelector('option[selected]');
                        if (selectedOption) {
                            document.getElementById('edit_tingkat').value = selectedOption.value;
                        }
                    }
                    
                    if (kodeInput) document.getElementById('edit_kode_kelas').value = kodeInput.value;
                    if (namaInput) document.getElementById('edit_nama_kelas').value = namaInput.value;
                    
                    if (waliSelect) {
                        const selectedOption = waliSelect.querySelector('option[selected]');
                        if (selectedOption) {
                            document.getElementById('edit_wali_kelas_id').value = selectedOption.value;
                        }
                    }
                    
                    loading.style.display = 'none';
                    content.style.display = 'block';
                    submitBtn.style.display = 'inline-block';
                })
                .catch(error => {
                    loading.innerHTML = `
                        <div class="alert alert-danger m-3">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Gagal memuat data kelas
                        </div>
                    `;
                });
        });
    });

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

    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            const name = this.getAttribute('data-name');
            
            Swal.fire({
                title: 'Hapus Kelas?',
                html: `Apakah Anda yakin ingin menghapus kelas<br><strong>${name}</strong>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-trash me-1"></i> Ya, Hapus!',
                cancelButtonText: '<i class="bi bi-x-circle me-1"></i> Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Menghapus...',
                        html: 'Mohon tunggu sebentar',
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

    // Reset forms
    document.getElementById('createKelasModal').addEventListener('hidden.bs.modal', function () {
        document.getElementById('createKelasForm').reset();
    });

    document.getElementById('editKelasModal').addEventListener('hidden.bs.modal', function () {
        document.getElementById('editKelasForm').reset();
        document.getElementById('editKelasLoading').style.display = 'block';
        document.getElementById('editKelasFormContent').style.display = 'none';
        document.getElementById('editKelasSubmitBtn').style.display = 'none';
    });
</script>
@endpush
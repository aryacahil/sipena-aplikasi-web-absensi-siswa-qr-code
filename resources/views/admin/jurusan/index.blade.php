@extends('layouts.admin')

@section('content')
<div class="bg-primary pt-10 pb-21"></div>
<div class="container-fluid mt-n22 px-6">
    <div class="row">
        <div class="col-lg-12 col-md-12 col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div class="mb-2 mb-lg-0">
                    <h3 class="mb-0 text-white">Manajemen Jurusan</h3>
                </div>
                <div>
                    <button type="button" class="btn btn-white" data-bs-toggle="modal" data-bs-target="#createJurusanModal">
                        <i class="bi bi-plus-circle me-2"></i>Tambah Jurusan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-6">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Daftar Jurusan</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Kode Jurusan</th>
                                    <th>Nama Jurusan</th>
                                    <th>Jumlah Kelas</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($jurusans as $index => $jurusan)
                                <tr>
                                    <td>{{ $jurusans->firstItem() + $index }}</td>
                                    <td><span class="badge bg-primary">{{ $jurusan->kode_jurusan }}</span></td>
                                    <td>{{ $jurusan->nama_jurusan }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $jurusan->kelas_count }} Kelas</span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-info btn-show-jurusan" 
                                                    data-jurusan-id="{{ $jurusan->id }}" title="Detail">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-warning btn-edit-jurusan" 
                                                    data-jurusan-id="{{ $jurusan->id }}" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form action="{{ route('admin.jurusan.destroy', $jurusan->id) }}" 
                                                  method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-danger btn-delete" 
                                                        data-name="{{ $jurusan->nama_jurusan }}" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <i class="bi bi-inbox fs-1 text-muted"></i>
                                        <p class="text-muted mt-2">Tidak ada data jurusan</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $jurusans->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Create Jurusan -->
<div class="modal fade" id="createJurusanModal" tabindex="-1" aria-labelledby="createJurusanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createJurusanModalLabel">
                    <i class="bi bi-plus-circle me-2"></i>Tambah Jurusan Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.jurusan.store') }}" method="POST" id="createJurusanForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="create_kode_jurusan" class="form-label">Kode Jurusan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="create_kode_jurusan" 
                                   name="kode_jurusan" placeholder="Contoh: TKJ" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="create_nama_jurusan" class="form-label">Nama Jurusan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="create_nama_jurusan" 
                                   name="nama_jurusan" placeholder="Contoh: Teknik Komputer dan Jaringan" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="create_deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="create_deskripsi" name="deskripsi" 
                                  rows="4" placeholder="Deskripsi singkat tentang jurusan"></textarea>
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

<!-- Modal Show Jurusan -->
<div class="modal fade" id="showJurusanModal" tabindex="-1" aria-labelledby="showJurusanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="showJurusanModalLabel">
                    <i class="bi bi-info-circle me-2"></i>Detail Jurusan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="showJurusanContent">
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

<!-- Modal Edit Jurusan -->
<div class="modal fade" id="editJurusanModal" tabindex="-1" aria-labelledby="editJurusanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editJurusanModalLabel">
                    <i class="bi bi-pencil-square me-2"></i>Edit Jurusan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editJurusanForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="text-center py-5" id="editJurusanLoading">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <div id="editJurusanFormContent" style="display: none;">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_kode_jurusan" class="form-label">Kode Jurusan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_kode_jurusan" 
                                       name="kode_jurusan" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_nama_jurusan" class="form-label">Nama Jurusan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_nama_jurusan" 
                                       name="nama_jurusan" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_deskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="edit_deskripsi" name="deskripsi" rows="4"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-primary" id="editJurusanSubmitBtn" style="display: none;">
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
    // Show jurusan details
    document.querySelectorAll('.btn-show-jurusan').forEach(button => {
        button.addEventListener('click', function() {
            const jurusanId = this.getAttribute('data-jurusan-id');
            const modal = new bootstrap.Modal(document.getElementById('showJurusanModal'));
            const content = document.getElementById('showJurusanContent');
            
            content.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;
            
            modal.show();
            
            fetch(`/admin/jurusan/${jurusanId}`)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const jurusanContent = doc.querySelector('.row.mt-6');
                    
                    if (jurusanContent) {
                        content.innerHTML = jurusanContent.innerHTML;
                    }
                })
                .catch(error => {
                    content.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Gagal memuat data jurusan
                        </div>
                    `;
                });
        });
    });

    // Edit jurusan
    document.querySelectorAll('.btn-edit-jurusan').forEach(button => {
        button.addEventListener('click', function() {
            const jurusanId = this.getAttribute('data-jurusan-id');
            const modal = new bootstrap.Modal(document.getElementById('editJurusanModal'));
            const form = document.getElementById('editJurusanForm');
            const loading = document.getElementById('editJurusanLoading');
            const content = document.getElementById('editJurusanFormContent');
            const submitBtn = document.getElementById('editJurusanSubmitBtn');
            
            loading.style.display = 'block';
            content.style.display = 'none';
            submitBtn.style.display = 'none';
            
            form.action = `/admin/jurusan/${jurusanId}`;
            
            modal.show();
            
            fetch(`/admin/jurusan/${jurusanId}/edit`)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    const kodeInput = doc.querySelector('input[name="kode_jurusan"]');
                    const namaInput = doc.querySelector('input[name="nama_jurusan"]');
                    const deskripsiTextarea = doc.querySelector('textarea[name="deskripsi"]');
                    
                    if (kodeInput) document.getElementById('edit_kode_jurusan').value = kodeInput.value;
                    if (namaInput) document.getElementById('edit_nama_jurusan').value = namaInput.value;
                    if (deskripsiTextarea) document.getElementById('edit_deskripsi').value = deskripsiTextarea.value;
                    
                    loading.style.display = 'none';
                    content.style.display = 'block';
                    submitBtn.style.display = 'inline-block';
                })
                .catch(error => {
                    loading.innerHTML = `
                        <div class="alert alert-danger m-3">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Gagal memuat data jurusan
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
            confirmButtonText: 'OK',
            confirmButtonColor: '#dc3545'
        });
    @endif

    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            const name = this.getAttribute('data-name');
            
            Swal.fire({
                title: 'Hapus Jurusan?',
                html: `Apakah Anda yakin ingin menghapus jurusan<br><strong>"${name}"</strong>?<br><br><small class="text-muted">Data yang sudah dihapus tidak dapat dikembalikan!</small>`,
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

    // Reset form when create modal is closed
    document.getElementById('createJurusanModal').addEventListener('hidden.bs.modal', function () {
        document.getElementById('createJurusanForm').reset();
    });

    // Reset form when edit modal is closed
    document.getElementById('editJurusanModal').addEventListener('hidden.bs.modal', function () {
        document.getElementById('editJurusanForm').reset();
        document.getElementById('editJurusanLoading').style.display = 'block';
        document.getElementById('editJurusanFormContent').style.display = 'none';
        document.getElementById('editJurusanSubmitBtn').style.display = 'none';
    });
</script>
@endpush
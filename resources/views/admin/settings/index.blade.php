@extends('layouts.admin')

@section('title', 'Pengaturan Sekolah')

@section('content')
<div class="bg-primary pt-10 pb-21"></div>
<div class="container-fluid mt-n22 px-6">
    <!-- Page Header -->
    <div class="row">
        <div class="col-lg-12 col-md-12 col-12">
            <div class="mb-4">
                <h3 class="mb-0 text-white">Pengaturan Sekolah</h3>
                <p class="text-white-50 mb-0">Kelola informasi sekolah dan tahun pelajaran</p>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show d-none" role="alert">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show d-none" role="alert">
        {{ session('error') }}
    </div>
    @endif

    <div class="row g-4">
        <!-- School Settings Card -->
        <div class="col-lg-5 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h4 class="mb-0">
                        <i class="bi bi-building text-primary me-2"></i>Profil Sekolah
                    </h4>
                    <p class="text-muted small mb-0 mt-1">Informasi dasar sekolah</p>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.settings.school.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Logo Preview -->
                        <div class="text-center mb-4">
                            <div class="position-relative d-inline-block">
                                <img id="logoPreview" 
                                     src="{{ isset($setting) && $setting->logo_path ? $setting->logo_url : asset('admin_assets/images/brand/logo/logo_sekolah.png') }}" 
                                     alt="Logo Sekolah" 
                                     class="rounded shadow-sm"
                                     style="max-height: 150px; max-width: 200px; object-fit: contain; background: #f8f9fa; padding: 15px;">
                                <label for="logo" class="position-absolute bottom-0 end-0 btn btn-sm btn-primary rounded-circle" style="width: 35px; height: 35px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-camera"></i>
                                </label>
                            </div>
                            <input type="file" 
                                   class="d-none @error('logo') is-invalid @enderror" 
                                   id="logo" 
                                   name="logo" 
                                   accept="image/*"
                                   onchange="previewLogo(event)">
                            <div class="mt-2">
                                <small class="text-muted">Format: JPG, PNG, GIF (Max: 2MB)</small>
                            </div>
                            @error('logo')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- School Name -->
                        <div class="mb-3">
                            <label for="school_name" class="form-label fw-semibold">
                                Nama Sekolah <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('school_name') is-invalid @enderror" 
                                   id="school_name" 
                                   name="school_name" 
                                   value="{{ old('school_name', isset($setting) ? $setting->school_name : '') }}" 
                                   placeholder="SMKN 1 Bendo"
                                   required>
                            @error('school_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Address -->
                        <div class="mb-3">
                            <label for="address" class="form-label fw-semibold">Alamat</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" 
                                      name="address" 
                                      rows="3"
                                      placeholder="Masukkan alamat lengkap sekolah">{{ old('address', isset($setting) ? $setting->address : '') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Phone & Email Row -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label fw-semibold">Telepon</label>
                                <input type="text" 
                                       class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" 
                                       name="phone" 
                                       value="{{ old('phone', isset($setting) ? $setting->phone : '') }}"
                                       placeholder="0812-xxxx-xxxx">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label fw-semibold">Email</label>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email', isset($setting) ? $setting->email : '') }}"
                                       placeholder="info@sekolah.sch.id">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Website -->
                        <div class="mb-4">
                            <label for="website" class="form-label fw-semibold">Website</label>
                            <input type="url" 
                                   class="form-control @error('website') is-invalid @enderror" 
                                   id="website" 
                                   name="website" 
                                   value="{{ old('website', isset($setting) ? $setting->website : '') }}"
                                   placeholder="https://sekolah.sch.id">
                            @error('website')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-check-circle me-2"></i>Simpan Pengaturan
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Academic Years Card -->
        <div class="col-lg-7 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">
                                <i class="bi bi-calendar-event text-success me-2"></i>Tahun Pelajaran
                            </h4>
                            <p class="text-muted small mb-0 mt-1">Kelola periode tahun pelajaran</p>
                        </div>
                        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addYearModal">
                            <i class="bi bi-plus-circle me-1"></i>Tambah
                        </button>
                    </div>
                </div>
                <div class="card-body p-4">
                    @forelse($academicYears as $year)
                    <div class="card mb-3 {{ $year->is_active ? 'border-success' : '' }}">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-2">
                                        <h5 class="mb-0 fw-bold">{{ $year->year }}</h5>
                                        @if($year->is_active)
                                            <span class="badge bg-success ms-2">
                                                <i class="bi bi-check-circle me-1"></i>Aktif
                                            </span>
                                        @else
                                            <span class="badge bg-secondary ms-2">Tidak Aktif</span>
                                        @endif
                                    </div>
                                    <div class="text-muted small">
                                        <i class="bi bi-calendar3 me-1"></i>
                                        {{ $year->start_date->format('d M Y') }} - {{ $year->end_date->format('d M Y') }}
                                    </div>
                                </div>
                                <div class="d-flex gap-1">
                                    @if(!$year->is_active)
                                    <form action="{{ route('admin.settings.academic-year.activate', $year) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Aktifkan">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                    </form>
                                    @endif
                                    
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-warning" 
                                            onclick="editYear({{ $year->id }}, '{{ $year->year }}', '{{ $year->start_date->format('Y-m-d') }}', '{{ $year->end_date->format('Y-m-d') }}', {{ $year->is_active ? 'true' : 'false' }})"
                                            title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    
                                    @if(!$year->is_active)
                                    <form action="{{ route('admin.settings.academic-year.delete', $year) }}" 
                                          method="POST" 
                                          class="d-inline"
                                          onsubmit="return confirm('Yakin ingin menghapus tahun pelajaran ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-5">
                        <i class="bi bi-calendar-x text-muted" style="font-size: 4rem; opacity: 0.3;"></i>
                        <p class="text-muted mt-3 mb-0">Belum ada tahun pelajaran</p>
                        <button type="button" class="btn btn-success btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#addYearModal">
                            <i class="bi bi-plus-circle me-1"></i>Tambah Tahun Pelajaran
                        </button>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Academic Year Modal -->
<div class="modal fade" id="addYearModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('admin.settings.academic-year.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle text-success me-2"></i>
                        Tambah Tahun Pelajaran
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="year" class="form-label fw-semibold">
                            Tahun Pelajaran <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="year" 
                               name="year" 
                               placeholder="2024/2025" 
                               required>
                        <small class="text-muted">Contoh: 2024/2025</small>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label fw-semibold">
                                Tanggal Mulai <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control" id="start_date" name="start_date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="end_date" class="form-label fw-semibold">
                                Tanggal Selesai <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control" id="end_date" name="end_date" required>
                        </div>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1">
                        <label class="form-check-label" for="is_active">
                            Jadikan tahun pelajaran aktif
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-1"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Academic Year Modal -->
<div class="modal fade" id="editYearModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="editYearForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square text-warning me-2"></i>
                        Edit Tahun Pelajaran
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_year" class="form-label fw-semibold">
                            Tahun Pelajaran <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="edit_year" name="year" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_start_date" class="form-label fw-semibold">
                                Tanggal Mulai <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control" id="edit_start_date" name="start_date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_end_date" class="form-label fw-semibold">
                                Tanggal Selesai <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control" id="edit_end_date" name="end_date" required>
                        </div>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active" value="1">
                        <label class="form-check-label" for="edit_is_active">
                            Jadikan tahun pelajaran aktif
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-check-circle me-1"></i>Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function previewLogo(event) {
    const reader = new FileReader();
    reader.onload = function() {
        const preview = document.getElementById('logoPreview');
        preview.src = reader.result;
    }
    if (event.target.files && event.target.files[0]) {
        reader.readAsDataURL(event.target.files[0]);
    }
}

function editYear(id, year, startDate, endDate, isActive) {
    const form = document.getElementById('editYearForm');
    form.action = '{{ url("admin/settings/academic-year") }}/' + id;
    
    document.getElementById('edit_year').value = year;
    document.getElementById('edit_start_date').value = startDate;
    document.getElementById('edit_end_date').value = endDate;
    document.getElementById('edit_is_active').checked = isActive;
    
    const modal = new bootstrap.Modal(document.getElementById('editYearModal'));
    modal.show();
}

// Show SweetAlert for session messages
document.addEventListener('DOMContentLoaded', function() {
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '{{ session('success') }}',
            timer: 3000,
            showConfirmButton: false
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: '{{ session('error') }}',
            timer: 3000,
            showConfirmButton: false
        });
    @endif
});
</script>
@endpush
@endsection
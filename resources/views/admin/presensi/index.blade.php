@extends('layouts.admin')
@section('title', 'Manajemen Absensi')

@section('content')
@if(session('success'))
<meta name="success-message" content="{{ session('success') }}">
@endif
@if(session('error'))
<meta name="error-message" content="{{ session('error') }}">
@endif

<input type="hidden" name="_token" value="{{ csrf_token() }}">

<div class="bg-primary pt-10 pb-21"></div>
<div class="container-fluid mt-n22 px-6">
    <div class="row">
        <div class="col-lg-12 col-md-12 col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div class="mb-2 mb-lg-0">
                    <h3 class="mb-0 text-white">Manajemen Absensi</h3>
                    <p class="text-white-50 mb-0">Kelola Data absensi siswa</p>
                </div>
                <div>
                    <a href="{{ route('admin.qrcode.index') }}" class="btn btn-white">
                        <i class="bi bi-qr-code me-2"></i>Kelola QR Code
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-6">
        <div class="col-xl-3 col-lg-6 col-md-6 col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Kelas</h6>
                            <h2 class="mb-0 fw-bold text-primary">{{ $stats['total_kelas'] }}</h2>
                        </div>
                        <div class="icon-shape icon-md bg-primary-soft text-primary rounded-circle">
                            <i class="bi bi-building fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6 col-12 mt-6 mt-lg-0">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Siswa</h6>
                            <h2 class="mb-0 fw-bold text-info">{{ $stats['total_siswa'] }}</h2>
                        </div>
                        <div class="icon-shape icon-md bg-info-soft text-info rounded-circle">
                            <i class="bi bi-people-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6 col-12 mt-6 mt-xl-0">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Hadir Hari Ini</h6>
                            <h2 class="mb-0 fw-bold text-success">{{ $stats['hadir_hari_ini'] }}</h2>
                        </div>
                        <div class="icon-shape icon-md bg-success-soft text-success rounded-circle">
                            <i class="bi bi-check-circle-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6 col-12 mt-6 mt-xl-0">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Alpha Hari Ini</h6>
                            <h2 class="mb-0 fw-bold text-danger">{{ $stats['alpha_hari_ini'] }}</h2>
                        </div>
                        <div class="icon-shape icon-md bg-danger-soft text-danger rounded-circle">
                            <i class="bi bi-x-circle-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-6">
        <div class="col-md-12">
            <div class="card shadow-sm">
                
                <div class="card-header bg-white border-bottom">
                    <form action="{{ route('admin.presensi.index') }}" method="GET">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Filter Jurusan</label>
                                <select name="jurusan_id" class="form-select">
                                    <option value="">Semua Jurusan</option>
                                    @foreach($jurusans as $jurusan)
                                        <option value="{{ $jurusan->id }}" {{ request('jurusan_id') == $jurusan->id ? 'selected' : '' }}>
                                            {{ $jurusan->nama_jurusan }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Cari Kelas</label>
                                <input type="text" name="search" class="form-control" placeholder="Cari nama kelas..." value="{{ request('search') }}">
                            </div>
                            
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search me-2"></i>Cari
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">Daftar Kelas</h4>
                            <p class="text-muted small mb-0">Klik kelas untuk melihat data presensi</p>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row g-4">
                        @forelse($kelasList as $kelas)
                        <div class="col-lg-4 col-md-6">
                            <div class="card h-100 border hover-lift cursor-pointer btn-show-kelas" 
                                 data-kelas-id="{{ $kelas->id }}">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h5 class="mb-1 text-dark">{{ $kelas->nama_kelas }}</h5>
                                            <p class="text-muted small mb-0">
                                                <i class="bi bi-mortarboard me-1"></i>
                                                {{ $kelas->jurusan->nama_jurusan }}
                                            </p>
                                        </div>
                                        <div class="icon-shape icon-sm bg-primary-soft text-primary rounded">
                                            <i class="bi bi-building"></i>
                                        </div>
                                    </div>
                                    
                                    <div class="border-top pt-3 mt-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <small class="text-muted d-block mb-1">Total Siswa</small>
                                                <h4 class="mb-0 text-primary">{{ $kelas->siswa_count }}</h4>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-primary-soft text-primary">
                                                    {{ $kelas->kode_kelas }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3 pt-3 border-top">
                                        <div class="d-flex align-items-center text-primary">
                                            <i class="bi bi-arrow-right-circle me-2"></i>
                                            <small class="fw-semibold">Lihat Presensi</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12">
                            <div class="text-center py-5">
                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                <p class="text-muted mt-3 mb-0">Belum ada data kelas</p>
                            </div>
                        </div>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="showKelasModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-people-fill me-2"></i>Data Presensi Siswa
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="showKelasContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="text-muted mt-2">Memuat data...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addManualPresensiModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle me-2"></i>Tambah Presensi Manual
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addManualPresensiForm" method="POST">
                @csrf
                <!-- Hidden Fields (will be populated by JavaScript) -->
                <input type="hidden" id="manual_siswa_id" name="siswa_id">
                <input type="hidden" id="manual_tanggal_presensi" name="tanggal_presensi">
                <!-- kelas_id akan ditambahkan via JavaScript -->
                
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Menambahkan presensi untuk: <strong id="manual_siswa_name"></strong>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                Status <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="manual_status" name="status" required>
                                <option value="hadir">Hadir</option>
                                <option value="izin">Izin</option>
                                <option value="sakit">Sakit</option>
                                <option value="alpha">Alpha</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Keterangan</label>
                            <textarea class="form-control" 
                                      id="manual_keterangan" 
                                      name="keterangan" 
                                      rows="3" 
                                      placeholder="Masukkan keterangan (opsional)"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-2"></i>Simpan Presensi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editPresensiModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-pencil-square me-2"></i>Edit Presensi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editPresensiForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="text-center py-5" id="editPresensiLoading">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                    <div id="editPresensiFormContent" style="display: none;">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_status" name="status" required>
                                    <option value="hadir">Hadir</option>
                                    <option value="izin">Izin</option>
                                    <option value="sakit">Sakit</option>
                                    <option value="alpha">Alpha</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Keterangan</label>
                                <textarea class="form-control" id="edit_keterangan" name="keterangan" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="editPresensiSubmitBtn" style="display: none;">
                        <i class="bi bi-check-circle me-2"></i>Perbarui
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
@push('scripts')
<link rel="stylesheet" href="{{ asset('css/admin/presensi.css') }}">
<script src="{{ asset('js/admin/presensi.js') }}"></script>
@endpush
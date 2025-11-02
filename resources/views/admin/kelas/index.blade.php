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
                    <h3 class="mb-0 text-white">Manajemen Kelas</h3>
                    <p class="text-white-50 mb-0">Kelola data kelas sekolah</p>
                </div>
                <div>
                    <button type="button" class="btn btn-white" data-bs-toggle="modal" data-bs-target="#createKelasModal">
                        <i class="bi bi-plus-circle me-2"></i>Tambah Kelas
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
                        <form action="{{ route('admin.kelas.index') }}" method="GET">
                            <div class="row g-3">
                                <div class="col-md-5">
                                    <label class="form-label fw-semibold small">Tingkat</label>
                                    <select name="tingkat" class="form-select">
                                        <option value="">Semua Tingkat</option>
                                        <option value="10" {{ request('tingkat') == '10' ? 'selected' : '' }}>Kelas 10</option>
                                        <option value="11" {{ request('tingkat') == '11' ? 'selected' : '' }}>Kelas 11</option>
                                        <option value="12" {{ request('tingkat') == '12' ? 'selected' : '' }}>Kelas 12</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-5">
                                    <label class="form-label fw-semibold small">Jurusan</label>
                                    <select name="jurusan_id" class="form-select">
                                        <option value="">Semua Jurusan</option>
                                        @foreach($jurusans as $jurusan)
                                            <option value="{{ $jurusan->id }}" {{ request('jurusan_id') == $jurusan->id ? 'selected' : '' }}>
                                                {{ $jurusan->kode_jurusan }} - {{ $jurusan->nama_jurusan }}
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
                            <h4 class="mb-0">Daftar Kelas</h4>
                        </div>
                        
                        <div class="d-flex gap-2 flex-wrap align-items-center">
                            <!-- Quick Filter Pills -->
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.kelas.index') }}" 
                                   class="btn btn-sm {{ !request('tingkat') ? 'btn-primary' : 'btn-outline-primary' }}">
                                    Semua
                                </a>
                                <a href="{{ route('admin.kelas.index', ['tingkat' => '10']) }}" 
                                   class="btn btn-sm {{ request('tingkat') == '10' ? 'btn-success' : 'btn-outline-success' }}">
                                    Kelas 10
                                </a>
                                <a href="{{ route('admin.kelas.index', ['tingkat' => '11']) }}" 
                                   class="btn btn-sm {{ request('tingkat') == '11' ? 'btn-info' : 'btn-outline-info' }}">
                                    Kelas 11
                                </a>
                                <a href="{{ route('admin.kelas.index', ['tingkat' => '12']) }}" 
                                   class="btn btn-sm {{ request('tingkat') == '12' ? 'btn-warning' : 'btn-outline-warning' }}">
                                    Kelas 12
                                </a>
                            </div>

                            <!-- Toggle Filter Button -->
                            <button class="btn btn-sm btn-outline-secondary" type="button" 
                                    data-bs-toggle="collapse" data-bs-target="#advancedFilter">
                                <i class="bi bi-funnel me-1"></i>Filter
                            </button>

                            <!-- Search Box -->
                            <form action="{{ route('admin.kelas.index') }}" method="GET" class="d-flex">
                                <div class="input-group" style="width: 250px;">
                                    <input type="text" name="search" class="form-control form-control-sm" 
                                           placeholder="Cari kelas..." 
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
                    <th class="border-0 text-center align-middle" style="width: 60px;">No</th>
                    <th class="border-0 text-center align-middle" style="width: 100px;">Tingkat</th>
                    <th class="border-0 align-middle">Kode Kelas</th>
                    <th class="border-0 text-center align-middle" style="width: 120px;">Jurusan</th>
                    <th class="border-0 text-center align-middle" style="width: 120px;">Jumlah Siswa</th>
                    <th class="border-0 text-center align-middle" style="width: 150px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($kelas as $index => $k)
                <tr>
                    <td class="align-middle text-center">
                        <span class="text-muted fw-semibold">{{ $kelas->firstItem() + $index }}</span>
                    </td>
                    <td class="align-middle text-center">
                        <span class="badge bg-primary-soft text-primary fs-6">{{ $k->tingkat }}</span>
                    </td>
                    <td class="align-middle">
                        <h6 class="mb-0">{{ $k->kode_kelas }}</h6>
                    </td>
                    <td class="align-middle text-center">
                        <span class="badge bg-info-soft text-info fs-6">{{ strtoupper($k->jurusan->kode_jurusan) }}</span>
                    </td>
                    <td class="align-middle text-center">
                        <span class="badge bg-success-soft text-success">
                            <i class="bi bi-people-fill me-1"></i>{{ $k->siswa_count ?? 0 }} Siswa
                        </span>
                    </td>
                    <td class="align-middle text-center">
                        <div class="d-flex justify-content-center gap-1">
                            <button type="button" 
                                    class="btn btn-sm btn-warning btn-show-kelas" 
                                    data-kelas-id="{{ $k->id }}"
                                    title="Lihat">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button type="button" 
                                    class="btn btn-sm btn-primary btn-edit-kelas" 
                                    data-kelas-id="{{ $k->id }}"
                                    title="Ubah">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form action="{{ route('admin.kelas.destroy', $k->id) }}" 
                                  method="POST" 
                                  class="d-inline delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="button" 
                                        class="btn btn-sm btn-danger btn-delete" 
                                        data-name="{{ $k->kode_kelas }}"
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
                                @if(request()->hasAny(['search', 'tingkat', 'jurusan_id']))
                                    Tidak ada data kelas yang sesuai dengan filter
                                @else
                                    Tidak ada data kelas
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
                @if($kelas->total() > 0)
                <div class="card-footer bg-white border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Menampilkan <strong>{{ $kelas->firstItem() }}</strong> sampai <strong>{{ $kelas->lastItem() }}</strong> dari <strong>{{ $kelas->total() }}</strong> data
                        </div>
                        <nav aria-label="Navigasi halaman">
                            @if ($kelas->hasPages())
                                <ul class="pagination mb-0">
                                    {{-- Previous Page Link --}}
                                    @if ($kelas->onFirstPage())
                                        <li class="page-item disabled" aria-disabled="true">
                                            <span class="page-link">&laquo;</span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $kelas->appends(request()->query())->previousPageUrl() }}" rel="prev">&laquo;</a>
                                        </li>
                                    @endif

                                    {{-- Pagination Elements --}}
                                    @php
                                        $start = max($kelas->currentPage() - 1, 1);
                                        $end = min($start + 2, $kelas->lastPage());
                                        $start = max($end - 2, 1);
                                    @endphp

                                    @if($start > 1)
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $kelas->appends(request()->query())->url(1) }}">1</a>
                                        </li>
                                        @if($start > 2)
                                            <li class="page-item disabled"><span class="page-link">...</span></li>
                                        @endif
                                    @endif

                                    @for ($i = $start; $i <= $end; $i++)
                                        @if ($i == $kelas->currentPage())
                                            <li class="page-item active" aria-current="page">
                                                <span class="page-link">{{ $i }}</span>
                                            </li>
                                        @else
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $kelas->appends(request()->query())->url($i) }}">{{ $i }}</a>
                                            </li>
                                        @endif
                                    @endfor

                                    @if($end < $kelas->lastPage())
                                        @if($end < $kelas->lastPage() - 1)
                                            <li class="page-item disabled"><span class="page-link">...</span></li>
                                        @endif
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $kelas->appends(request()->query())->url($kelas->lastPage()) }}">{{ $kelas->lastPage() }}</a>
                                        </li>
                                    @endif

                                    {{-- Next Page Link --}}
                                    @if ($kelas->hasMorePages())
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $kelas->appends(request()->query())->nextPageUrl() }}" rel="next">&raquo;</a>
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

<!-- Modal Create Kelas -->
<div class="modal fade" id="createKelasModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
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
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Jurusan <span class="text-danger">*</span></label>
                            <select class="form-select" name="jurusan_id" required>
                                <option value="">Pilih Jurusan</option>
                                @foreach($jurusans as $jurusan)
                                    <option value="{{ $jurusan->id }}">
                                        {{ $jurusan->kode_jurusan }} - {{ $jurusan->nama_jurusan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tingkat <span class="text-danger">*</span></label>
                            <select class="form-select" name="tingkat" required>
                                <option value="">Pilih Tingkat</option>
                                <option value="10">Kelas 10</option>
                                <option value="11">Kelas 11</option>
                                <option value="12">Kelas 12</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kode Kelas <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="kode_kelas" placeholder="Contoh: X-TKJ-1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama Kelas <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama_kelas" placeholder="Contoh: X TKJ 1" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Wali Kelas</label>
                            <select class="form-select" name="wali_kelas_id">
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Show Kelas -->
<div class="modal fade" id="showKelasModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-info-circle me-2"></i>Detail Kelas
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="showKelasContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Add Siswa -->
<div class="modal fade" id="addSiswaModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title">
                    <i class="bi bi-person-plus me-2"></i>Tambah Siswa ke Kelas
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="add_siswa_kelas_id">
                
                <!-- Search Box -->
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text bg-light">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" 
                               class="form-control" 
                               id="search_siswa" 
                               placeholder="Cari siswa berdasarkan nama atau email...">
                    </div>
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        Hanya siswa yang belum memiliki kelas yang ditampilkan
                    </small>
                </div>

                <!-- Stats Badge & Actions -->
                <div class="d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded">
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted">
                            <i class="bi bi-people-fill me-2"></i>Siswa Tersedia:
                        </span>
                        <span class="badge bg-primary" id="available_siswa_count">0</span>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary btn-select-all-add-siswa">
                        <i class="bi bi-check-square me-1"></i>Pilih Semua
                    </button>
                </div>

                <!-- Siswa List Container -->
                <div id="siswa_list_container" style="max-height: 400px; overflow-y: auto;">
                    <div class="text-center py-4">
                        <div class="spinner-border text-secondary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted mt-2">Memuat daftar siswa...</p>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light d-flex justify-content-between">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Tutup
                </button>
                <button type="button" 
                        class="btn btn-primary btn-add-selected-to-class" 
                        style="display: none;">
                    <i class="bi bi-plus-circle me-1"></i>
                    Tambahkan Terpilih (<span class="selected-count">0</span>)
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Kelas -->
<div class="modal fade" id="editKelasModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-pencil-square me-2"></i>Ubah Kelas
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editKelasForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="text-center py-5" id="editKelasLoading">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                    <div id="editKelasFormContent" style="display: none;">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Jurusan <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_jurusan_id" name="jurusan_id" required>
                                    <option value="">Pilih Jurusan</option>
                                    @foreach($jurusans as $jurusan)
                                        <option value="{{ $jurusan->id }}">
                                            {{ $jurusan->kode_jurusan }} - {{ $jurusan->nama_jurusan }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tingkat <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_tingkat" name="tingkat" required>
                                    <option value="">Pilih Tingkat</option>
                                    <option value="10">Kelas 10</option>
                                    <option value="11">Kelas 11</option>
                                    <option value="12">Kelas 12</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kode Kelas <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_kode_kelas" name="kode_kelas" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nama Kelas <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_nama_kelas" name="nama_kelas" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Wali Kelas</label>
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="editKelasSubmitBtn" style="display: none;">Perbarui</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Add Siswa -->
<div class="modal fade" id="addSiswaModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-person-plus me-2"></i>Tambah Siswa ke Kelas
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addSiswaForm">
                @csrf
                <input type="hidden" name="kelas_id" id="add_siswa_kelas_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Pilih Siswa <span class="text-danger">*</span></label>
                        <select class="form-select" id="add_siswa_id" name="siswa_id" required>
                            <option value="">Memuat daftar siswa...</option>
                        </select>
                        <small class="text-muted">Hanya siswa yang belum memiliki kelas yang ditampilkan</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Tambahkan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<link rel="stylesheet" href="{{ asset('css/admin/kelas.css') }}">
<script src="{{ asset('js/admin/kelas.js') }}"></script>
@endpush
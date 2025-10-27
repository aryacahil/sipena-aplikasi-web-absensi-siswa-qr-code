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
                    <h3 class="mb-0 text-white">Manajemen Jurusan</h3>
                    <p class="text-white-50 mb-0">Kelola data jurusan sekolah</p>
                </div>
                <div>
                    <button type="button" class="btn btn-white" data-bs-toggle="modal" data-bs-target="#createJurusanModal">
                        <i class="bi bi-plus-circle me-2"></i>Tambah Jurusan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Card -->
    <div class="row mt-6">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <!-- Table Header -->
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h4 class="mb-0">Daftar Jurusan</h4>
                        </div>
                        
                        <div class="d-flex gap-2 flex-wrap align-items-center">
                            <!-- Search Box -->
                            <form action="{{ route('admin.jurusan.index') }}" method="GET" class="d-flex">
                                <div class="input-group" style="width: 250px;">
                                    <input type="text" name="search" class="form-control form-control-sm" 
                                           placeholder="Cari jurusan..." 
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
                                    <th class="border-0 align-middle" style="width: 150px;">Kode Jurusan</th>
                                    <th class="border-0 align-middle">Nama Jurusan</th>
                                    <th class="border-0 text-center align-middle" style="width: 120px;">Jumlah Kelas</th>
                                    <th class="border-0 text-center align-middle" style="width: 120px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($jurusans as $index => $jurusan)
                                <tr>
                                    <td class="align-middle text-center">
                                        <span class="text-muted fw-semibold">{{ $jurusans->firstItem() + $index }}</span>
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge bg-primary-soft text-primary fs-6">{{ $jurusan->kode_jurusan }}</span>
                                    </td>
                                    <td class="align-middle">
                                        <h6 class="mb-0">{{ $jurusan->nama_jurusan }}</h6>
                                        @if($jurusan->deskripsi)
                                        <small class="text-muted">{{ Str::limit($jurusan->deskripsi, 50) }}</small>
                                        @endif
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="badge bg-info-soft text-info">
                                            <i class="bi bi-building me-1"></i>{{ $jurusan->kelas_count }} Kelas
                                        </span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <button type="button" 
                                                    class="btn btn-sm btn-warning btn-show-jurusan" 
                                                    data-jurusan-id="{{ $jurusan->id }}"
                                                    title="Lihat">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-sm btn-primary btn-edit-jurusan" 
                                                    data-jurusan-id="{{ $jurusan->id }}"
                                                    title="Ubah">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form action="{{ route('admin.jurusan.destroy', $jurusan->id) }}" 
                                                  method="POST" 
                                                  class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" 
                                                        class="btn btn-sm btn-danger btn-delete" 
                                                        data-name="{{ $jurusan->nama_jurusan }}"
                                                        title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="py-4">
                                            <i class="bi bi-inbox fs-1 text-muted"></i>
                                            <p class="text-muted mt-3 mb-0">
                                                @if(request('search'))
                                                    Tidak ada data jurusan yang sesuai dengan pencarian
                                                @else
                                                    Tidak ada data jurusan
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
                @if($jurusans->total() > 0)
                <div class="card-footer bg-white border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Menampilkan <strong>{{ $jurusans->firstItem() }}</strong> sampai <strong>{{ $jurusans->lastItem() }}</strong> dari <strong>{{ $jurusans->total() }}</strong> data
                        </div>
                        <nav aria-label="Navigasi halaman">
                            @if ($jurusans->hasPages())
                                <ul class="pagination mb-0">
                                    {{-- Previous Page Link --}}
                                    @if ($jurusans->onFirstPage())
                                        <li class="page-item disabled" aria-disabled="true">
                                            <span class="page-link">&laquo;</span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $jurusans->appends(request()->query())->previousPageUrl() }}" rel="prev">&laquo;</a>
                                        </li>
                                    @endif

                                    {{-- Pagination Elements --}}
                                    @php
                                        $start = max($jurusans->currentPage() - 1, 1);
                                        $end = min($start + 2, $jurusans->lastPage());
                                        $start = max($end - 2, 1);
                                    @endphp

                                    @if($start > 1)
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $jurusans->appends(request()->query())->url(1) }}">1</a>
                                        </li>
                                        @if($start > 2)
                                            <li class="page-item disabled"><span class="page-link">...</span></li>
                                        @endif
                                    @endif

                                    @for ($i = $start; $i <= $end; $i++)
                                        @if ($i == $jurusans->currentPage())
                                            <li class="page-item active" aria-current="page">
                                                <span class="page-link">{{ $i }}</span>
                                            </li>
                                        @else
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $jurusans->appends(request()->query())->url($i) }}">{{ $i }}</a>
                                            </li>
                                        @endif
                                    @endfor

                                    @if($end < $jurusans->lastPage())
                                        @if($end < $jurusans->lastPage() - 1)
                                            <li class="page-item disabled"><span class="page-link">...</span></li>
                                        @endif
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $jurusans->appends(request()->query())->url($jurusans->lastPage()) }}">{{ $jurusans->lastPage() }}</a>
                                        </li>
                                    @endif

                                    {{-- Next Page Link --}}
                                    @if ($jurusans->hasMorePages())
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $jurusans->appends(request()->query())->nextPageUrl() }}" rel="next">&raquo;</a>
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

<!-- Modal Create Jurusan -->
<div class="modal fade" id="createJurusanModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle me-2"></i>Tambah Jurusan Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.jurusan.store') }}" method="POST" id="createJurusanForm">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Kode Jurusan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="kode_jurusan" placeholder="Contoh: TKJ" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama Jurusan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama_jurusan" placeholder="Contoh: Teknik Komputer Jaringan" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="deskripsi" rows="4" placeholder="Deskripsi singkat tentang jurusan (opsional)"></textarea>
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

<!-- Modal Show Jurusan -->
<div class="modal fade" id="showJurusanModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-info-circle me-2"></i>Detail Jurusan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="showJurusanContent">
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

<!-- Modal Edit Jurusan -->
<div class="modal fade" id="editJurusanModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-pencil-square me-2"></i>Ubah Jurusan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editJurusanForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="text-center py-5" id="editJurusanLoading">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                    <div id="editJurusanFormContent" style="display: none;">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Kode Jurusan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_kode_jurusan" name="kode_jurusan" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nama Jurusan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_nama_jurusan" name="nama_jurusan" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Deskripsi</label>
                                <textarea class="form-control" id="edit_deskripsi" name="deskripsi" rows="4"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="editJurusanSubmitBtn" style="display: none;">Perbarui</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<link rel="stylesheet" href="{{ asset('css/admin/jurusan.css') }}">
<script src="{{ asset('js/admin/jurusan.js') }}"></script>
@endpush
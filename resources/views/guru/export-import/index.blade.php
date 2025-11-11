@extends('layouts.guru')
@section('title', 'Ekspor & Impor')

@section('content')
<div class="bg-primary pt-10 pb-21"></div>
<div class="container-fluid mt-n22 px-6">
    <div class="row">
        <div class="col-lg-12">
            <div class="mb-4">
                <h3 class="mb-0 text-white">Export & Import Data</h3>
                <p class="text-white-50 mb-0">Kelola export dan import data siswa & absensi</p>
            </div>
        </div>
    </div>

    <!-- Export Section -->
    <div class="row mt-6">
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h4 class="mb-0">
                        <i class="bi bi-download me-2 text-success"></i>Export Data Siswa
                    </h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('guru.export.siswa') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Filter Kelas</label>
                            <select name="kelas_id" class="form-select">
                                <option value="">Semua Kelas</option>
                                @foreach($kelas as $k)
                                    <option value="{{ $k->id }}">{{ $k->nama_kelas }} - {{ $k->jurusan->nama_jurusan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="active">Aktif</option>
                                <option value="inactive">Nonaktif</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-file-earmark-excel me-2"></i>Export Excel
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h4 class="mb-0">
                        <i class="bi bi-download me-2 text-info"></i>Export Data Absensi
                    </h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('guru.export.presensi') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Filter Kelas</label>
                            <select name="kelas_id" class="form-select">
                                <option value="">Semua Kelas</option>
                                @foreach($kelas as $k)
                                    <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Mulai</label>
                                <input type="date" name="tanggal_mulai" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Akhir</label>
                                <input type="date" name="tanggal_akhir" class="form-control">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status Presensi</label>
                            <select name="status" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="hadir">Hadir</option>
                                <option value="izin">Izin</option>
                                <option value="sakit">Sakit</option>
                                <option value="alpha">Alpha</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-info w-100">
                            <i class="bi bi-file-earmark-excel me-2"></i>Export Excel
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Section -->
    <div class="row mt-4">
        <div class="col-lg-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h4 class="mb-0">
                        <i class="bi bi-upload me-2 text-primary"></i>Import Data Siswa
                    </h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Panduan Import:</strong>
                        <ol class="mb-0 mt-2">
                            <li>Download template Excel terlebih dahulu</li>
                            <li>Isi data siswa sesuai format template</li>
                            <li>Upload file Excel yang sudah diisi</li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <a href="{{ route('guru.download.template') }}" class="btn btn-outline-primary w-100 mb-3">
                                <i class="bi bi-download me-2"></i>Download Template
                            </a>
                        </div>
                        <div class="col-md-6">
                            <form action="{{ route('guru.import.siswa') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-upload me-2"></i>Upload & Import
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '{{ session("success") }}',
            timer: 3000,
            showConfirmButton: false
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: '{{ session("error") }}'
        });
    @endif
});
</script>
@endpush
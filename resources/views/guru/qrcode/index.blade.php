@extends('layouts.guru')
@section('title', 'Generate QR Code')

@section('content')
@if(session('success'))
<meta name="success-message" content="{{ session('success') }}">
@endif
@if(session('error'))
<meta name="error-message" content="{{ session('error') }}">
@endif

<input type="hidden" name="_token" value="{{ csrf_token() }}">
<input type="hidden" id="baseRoute" value="{{ request()->is('admin/*') ? 'admin' : 'guru' }}">

<div class="bg-primary pt-10 pb-21"></div>
<div class="container-fluid mt-n22 px-6">
    <div class="row">
        <div class="col-lg-12 col-md-12 col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div class="mb-2 mb-lg-0">
                    <h3 class="mb-0 text-white">Generate QR Code Presensi</h3>
                    <p class="text-white-50 mb-0">Kelola QR Code untuk absensi siswa</p>
                </div>
                <div>
                    <a href="{{ route('guru.presensi.index') }}" class="btn btn-white me-2">
                        <i class="bi bi-clipboard-check me-2"></i>Lihat Presensi
                    </a>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createQRModal">
                        <i class="bi bi-plus-circle me-2"></i>Generate QR Code
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & List -->
    <div class="row mt-6">
        <div class="col-md-12">
            <div class="card shadow-sm">
                
                <!-- Advanced Filter (Collapsed by default) -->
                <div class="collapse" id="advancedFilter">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-3">
                            <i class="bi bi-funnel me-2"></i>Filter QR Code
                        </h5>
                        <form action="{{ route('guru.qrcode.index') }}" method="GET">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Kelas</label>
                                    <select name="kelas_id" class="form-select">
                                        <option value="">Semua Kelas</option>
                                        @foreach($kelas as $k)
                                            <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>
                                                {{ $k->nama_kelas }} - {{ $k->jurusan->nama_jurusan }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold small">Tanggal</label>
                                    <input type="date" name="tanggal" class="form-control" value="{{ request('tanggal') }}">
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold small">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="">Semua Status</option>
                                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                                        <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
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

                <!-- Card Header with Quick Filters -->
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h4 class="mb-0">Daftar QR Code</h4>
                        </div>
                        
                        <div class="d-flex gap-2 flex-wrap">
                            <div class="btn-group" role="group">
                                <a href="{{ route('guru.qrcode.index') }}" 
                                   class="btn btn-sm {{ !request('status') ? 'btn-primary' : 'btn-outline-primary' }}">
                                    Semua
                                </a>
                                <a href="{{ route('guru.qrcode.index', ['status' => 'active']) }}" 
                                   class="btn btn-sm {{ request('status') == 'active' ? 'btn-success' : 'btn-outline-success' }}">
                                    Aktif
                                </a>
                                <a href="{{ route('guru.qrcode.index', ['status' => 'expired']) }}" 
                                   class="btn btn-sm {{ request('status') == 'expired' ? 'btn-secondary' : 'btn-outline-secondary' }}">
                                    Expired
                                </a>
                            </div>

                            <button class="btn btn-sm btn-outline-secondary" type="button" 
                                    data-bs-toggle="collapse" data-bs-target="#advancedFilter">
                                <i class="bi bi-funnel me-1"></i>Filter
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Table Content -->
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0 text-center align-middle" style="width: 60px;">No</th>
                                    <th class="border-0 align-middle">Kelas</th>
                                    <th class="border-0 text-center align-middle" style="width: 120px;">Tanggal</th>
                                    <th class="border-0 text-center align-middle" style="width: 150px;">Waktu</th>
                                    <th class="border-0 text-center align-middle" style="width: 100px;">Presensi</th>
                                    <th class="border-0 text-center align-middle" style="width: 100px;">Status</th>
                                    <th class="border-0 text-center align-middle" style="width: 200px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sessions as $index => $session)
                                <tr>
                                    <td class="align-middle text-center">
                                        <span class="text-muted fw-semibold">{{ $sessions->firstItem() + $index }}</span>
                                    </td>
                                    <td class="align-middle">
                                        <div>
                                            <h6 class="mb-0">{{ $session->kelas->nama_kelas }}</h6>
                                        </div>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="badge bg-primary-soft text-primary">
                                            {{ $session->tanggal->format('d M Y') }}
                                        </span>
                                    </td>
                                    <td class="align-middle text-center" style="white-space: nowrap;">
                                        <small class="text-muted">
                                            <i class="bi bi-clock me-1"></i>
                                            {{ $session->jam_mulai->format('H:i') }} - {{ $session->jam_selesai->format('H:i') }}
                                        </small>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="badge bg-success-soft text-success">
                                            <i class="bi bi-people-fill me-1"></i>
                                            {{ $session->presensis_count }}
                                        </span>
                                    </td>
                                    <td class="align-middle text-center">
                                        @if($session->status === 'active' && $session->isActive())
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle me-1"></i>Aktif
                                            </span>
                                        @elseif($session->getStatusText() === 'waiting')
                                            <span class="badge bg-warning">
                                                <i class="bi bi-clock me-1"></i>Menunggu
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="bi bi-x-circle me-1"></i>Expired
                                            </span>
                                        @endif
                                    </td>
                                    <td class="align-middle text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <button type="button" 
                                                    class="btn btn-sm btn-warning btn-show-qr" 
                                                    data-session-id="{{ $session->id }}"
                                                    title="Lihat Detail">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            
                                            <a href="{{ route('guru.qrcode.download', $session->id) }}" 
                                               class="btn btn-sm btn-info" 
                                               title="Download QR Code">
                                                <i class="bi bi-download"></i>
                                            </a>
                                            
                                            <button type="button" 
                                                    class="btn btn-sm {{ $session->status === 'active' ? 'btn-secondary' : 'btn-success' }} btn-toggle-status" 
                                                    data-session-id="{{ $session->id }}"
                                                    data-current-status="{{ $session->status }}"
                                                    title="{{ $session->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                <i class="bi bi-{{ $session->status === 'active' ? 'x-circle' : 'check-circle' }}"></i>
                                            </button>
                                            
                                            <form action="{{ route('guru.qrcode.destroy', $session->id) }}" 
                                                  method="POST" 
                                                  class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" 
                                                        class="btn btn-sm btn-danger btn-delete" 
                                                        data-name="{{ $session->kelas->nama_kelas }} - {{ $session->tanggal->format('d M Y') }}"
                                                        title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="py-4">
                                            <i class="bi bi-inbox fs-1 text-muted"></i>
                                            <p class="text-muted mt-3 mb-0">
                                                @if(request()->hasAny(['kelas_id', 'tanggal', 'status']))
                                                    Tidak ada QR Code yang sesuai dengan filter
                                                @else
                                                    Belum ada QR Code yang dibuat
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

                <!-- Pagination -->
                @if($sessions->total() > 0)
                <div class="card-footer bg-white border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Menampilkan <strong>{{ $sessions->firstItem() }}</strong> sampai <strong>{{ $sessions->lastItem() }}</strong> dari <strong>{{ $sessions->total() }}</strong> data
                        </div>
                        {{ $sessions->links() }}
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>
</div>

<!-- Modal Create QR Code -->
<div class="modal fade" id="createQRModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-qr-code me-2"></i>Generate QR Code Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('guru.qrcode.store') }}" method="POST" id="createQRForm">
                @csrf
                <div class="modal-body">
                    <div class="row g-4">
                        <!-- Left Column: Form -->
                        <div class="col-lg-6">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Kelas <span class="text-danger">*</span></label>
                                    <select class="form-select" name="kelas_id" required>
                                        <option value="">Pilih Kelas</option>
                                        @foreach($kelas as $k)
                                            <option value="{{ $k->id }}">
                                                {{ $k->nama_kelas }} - {{ $k->jurusan->nama_jurusan }} ({{ $k->siswa_count }} siswa)
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Tanggal <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="tanggal" required value="{{ date('Y-m-d') }}">
                                </div>
                                
                                <div class="col-6">
                                    <label class="form-label fw-semibold">Jam Mulai <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control" name="jam_mulai" required>
                                </div>
                                
                                <div class="col-6">
                                    <label class="form-label fw-semibold">Jam Selesai <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control" name="jam_selesai" required>
                                </div>
                                
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Radius Lokasi (meter) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="radius" id="radius" value="200" min="50" max="1000" required>
                                    <small class="text-muted">Jarak maksimal siswa dari lokasi (50-1000 meter)</small>
                                </div>
                                
                                <div class="col-12">
                                    <input type="hidden" name="latitude" id="latitude">
                                    <input type="hidden" name="longitude" id="longitude">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right Column: Map -->
                        <div class="col-lg-6">
                            <label class="form-label fw-semibold">Lokasi Presensi <span class="text-danger">*</span></label>
                            <div class="mb-2">
                                <button type="button" class="btn btn-sm btn-primary me-2" id="getLocationBtn">
                                    <i class="bi bi-geo-alt-fill me-1"></i>Lokasi Saya
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="searchLocationBtn">
                                    <i class="bi bi-search me-1"></i>Cari Alamat
                                </button>
                            </div>
                            
                            <div id="searchBox" class="mb-2" style="display: none;">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="searchAddressInput" placeholder="Contoh: SMKN 1 Bendo, Kab. Magetan">
                                    <button class="btn btn-primary" type="button" id="searchAddressBtn">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div id="map" style="height: 400px; border-radius: 8px;"></div>
                            <small class="text-muted mt-2 d-block">
                                <i class="bi bi-info-circle me-1"></i>Klik pada peta untuk memilih lokasi presensi
                            </small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-qr-code me-1"></i>Generate QR Code
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Show QR Code -->
<div class="modal fade" id="showQRModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-qr-code me-2"></i>Detail QR Code
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="showQRContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="text-muted mt-2">Memuat data...</p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
.hover-lift {
    transition: transform 0.2s, box-shadow 0.2s;
}
.hover-lift:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
}
.cursor-pointer {
    cursor: pointer;
}
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="{{ asset('css/guru/qrcode.css') }}"></script>
<script src="{{ asset('js/guru/qrcode.js') }}"></script>
@endpush
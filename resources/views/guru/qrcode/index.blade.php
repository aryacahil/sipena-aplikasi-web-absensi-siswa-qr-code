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
<input type="hidden" id="baseRoute" value="guru">

<div class="bg-primary pt-10 pb-21"></div>
<div class="container-fluid mt-n22 px-6">
    <div class="row">
        <div class="col-lg-12 col-md-12 col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div class="mb-2 mb-lg-0">
                    <h3 class="mb-0 text-white">Generate QR Code</h3>
                    <p class="text-white-50 mb-0">Kelola QR Code untuk absensi siswa</p>
                </div>
                <div>
                    <button type="button" class="btn btn-white" data-bs-toggle="modal" data-bs-target="#createQRModal">
                        <i class="bi bi-qr-code me-2"></i>Generate QR Code
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & List -->
    <div class="row mt-6">
        <div class="col-md-12">
            <div class="card shadow-sm rounded-3">
                
                <!-- Advanced Filter -->
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

                <!-- Card Header -->
                <div class="card-header bg-white border-bottom rounded-top">
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
    <!-- Desktop Table View -->
    <div class="table-responsive d-none d-md-block">
        <table class="table table-hover table-nowrap mb-0 qr-table">
            <thead class="table-light">
                <tr>
                    <th class="border-0 text-center align-middle">No</th>
                    <th class="border-0 align-middle">Kelas</th>
                    <th class="border-0 text-center align-middle">Tanggal</th>
                    <th class="border-0 text-center align-middle">Waktu</th>
                    <th class="border-0 text-center align-middle">Presensi</th>
                    <th class="border-0 text-center align-middle">Status</th>
                    <th class="border-0 text-center align-middle">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sessions as $index => $session)
                @php
                    $statusText = $session->getStatusText();
                    $currentPhase = $session->getCurrentPhase();
                @endphp
                <tr>
                    <td class="align-middle text-center">
                        <span class="text-muted fw-semibold">{{ $sessions->firstItem() + $index }}</span>
                    </td>
                    <td class="align-middle">
                        <h6 class="mb-0">{{ $session->kelas->nama_kelas }}</h6>
                    </td>
                    <td class="align-middle text-center">
                        <span class="badge bg-primary-soft text-primary">
                            <i class="bi bi-calendar3 me-1"></i>
                            {{ $session->tanggal->format('d M Y') }}
                        </span>
                    </td>
                    <td class="align-middle text-center">
                        <small class="text-muted d-block">
                            <i class="bi bi-clock me-1"></i>
                            {{ $session->jam_checkin_mulai->format('H:i') }}
                        </small>
                        <small class="text-muted d-block">
                            {{ $session->jam_checkout_selesai->format('H:i') }}
                        </small>
                    </td>
                    <td class="align-middle text-center">
                        <span class="badge bg-success-soft text-success">
                            <i class="bi bi-people-fill me-1"></i>
                            {{ $session->presensis_count }}
                        </span>
                    </td>
                    <td class="align-middle text-center">
                        @if($statusText === 'checkin_active')
                            <span class="badge bg-success">
                                <i class="bi bi-box-arrow-in-right me-1"></i>Check-In
                            </span>
                        @elseif($statusText === 'checkout_active')
                            <span class="badge bg-warning text-dark">
                                <i class="bi bi-box-arrow-right me-1"></i>Check-Out
                            </span>
                        @elseif($statusText === 'waiting')
                            <span class="badge bg-info">
                                <i class="bi bi-clock-history me-1"></i>Menunggu
                            </span>
                        @elseif($statusText === 'between_sessions')
                            <span class="badge bg-primary">
                                <i class="bi bi-hourglass-split me-1"></i>Jeda
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
                                    class="btn btn-sm btn-primary btn-show-qr" 
                                    data-session-id="{{ $session->id }}"
                                    title="Lihat Detail">
                                <i class="bi bi-eye"></i>
                            </button>
                            
                            <button type="button" 
                                    class="btn btn-sm btn-success btn-download-both" 
                                    data-session-id="{{ $session->id }}"
                                    data-kelas-name="{{ $session->kelas->kode_kelas }}"
                                    data-tanggal="{{ $session->tanggal->format('Ymd') }}"
                                    title="Download QR">
                                <i class="bi bi-download"></i>
                            </button>
                            
                            <button type="button" 
                                    class="btn btn-sm {{ $session->status === 'active' ? 'btn-dark' : 'btn-info' }} btn-toggle-status" 
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

    <!-- Mobile Card View -->
    <div class="d-md-none">
        @forelse($sessions as $index => $session)
        @php
            $statusText = $session->getStatusText();
            $currentPhase = $session->getCurrentPhase();
        @endphp
        <div class="card mb-3 mx-3 shadow-sm">
            <div class="card-body p-3">
                <!-- Header: No & Kelas -->
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="badge bg-primary mb-1">#{{ $sessions->firstItem() + $index }}</span>
                        <h6 class="card-title mb-0 fw-bold">{{ $session->kelas->nama_kelas }}</h6>
                    </div>
                    <span class="badge bg-success-soft text-success">
                        <i class="bi bi-people-fill me-1"></i>{{ $session->presensis_count }}
                    </span>
                </div>

                <!-- Status Badge -->
                <div class="mb-2">
                    @if($statusText === 'checkin_active')
                        <span class="badge bg-success">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Check-In Aktif
                        </span>
                    @elseif($statusText === 'checkout_active')
                        <span class="badge bg-warning text-dark">
                            <i class="bi bi-box-arrow-right me-1"></i>Check-Out Aktif
                        </span>
                    @elseif($statusText === 'waiting')
                        <span class="badge bg-info">
                            <i class="bi bi-clock-history me-1"></i>Menunggu
                        </span>
                    @elseif($statusText === 'between_sessions')
                        <span class="badge bg-primary">
                            <i class="bi bi-hourglass-split me-1"></i>Jeda Sesi
                        </span>
                    @else
                        <span class="badge bg-secondary">
                            <i class="bi bi-x-circle me-1"></i>Expired
                        </span>
                    @endif
                </div>

                <!-- Info: Tanggal & Waktu -->
                <div class="border-top pt-2 mb-2">
                    <div class="row g-2">
                        <div class="col-6">
                            <small class="text-muted d-block">Tanggal:</small>
                            <span class="badge bg-primary-soft text-primary w-100">
                                <i class="bi bi-calendar3 me-1"></i>
                                {{ $session->tanggal->format('d M Y') }}
                            </span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Waktu:</small>
                            <small class="d-block fw-semibold">
                                <i class="bi bi-clock me-1"></i>
                                {{ $session->jam_checkin_mulai->format('H:i') }} - 
                                {{ $session->jam_checkout_selesai->format('H:i') }}
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="border-top pt-2">
                    <small class="text-muted d-block mb-2">Aksi:</small>
                    <div class="d-grid gap-2">
                        <div class="row g-2">
                            <div class="col-6">
                                <button type="button" 
                                        class="btn btn-sm btn-primary w-100 btn-show-qr" 
                                        data-session-id="{{ $session->id }}">
                                    <i class="bi bi-eye me-1"></i>Lihat
                                </button>
                            </div>
                            <div class="col-6">
                                <button type="button" 
                                        class="btn btn-sm btn-success w-100 btn-download-both" 
                                        data-session-id="{{ $session->id }}"
                                        data-kelas-name="{{ $session->kelas->kode_kelas }}"
                                        data-tanggal="{{ $session->tanggal->format('Ymd') }}">
                                    <i class="bi bi-download me-1"></i>Download
                                </button>
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <button type="button" 
                                        class="btn btn-sm {{ $session->status === 'active' ? 'btn-dark' : 'btn-info' }} w-100 btn-toggle-status" 
                                        data-session-id="{{ $session->id }}"
                                        data-current-status="{{ $session->status }}">
                                    <i class="bi bi-{{ $session->status === 'active' ? 'x-circle' : 'check-circle' }} me-1"></i>
                                    {{ $session->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </div>
                            <div class="col-6">
                                <form action="{{ route('guru.qrcode.destroy', $session->id) }}" 
                                      method="POST" 
                                      class="delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" 
                                            class="btn btn-sm btn-danger w-100 btn-delete" 
                                            data-name="{{ $session->kelas->nama_kelas }} - {{ $session->tanggal->format('d M Y') }}">
                                        <i class="bi bi-trash me-1"></i>Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-5 text-muted mx-3">
            <i class="bi bi-inbox fs-1 d-block mb-3"></i>
            <p class="mb-0">
                @if(request()->hasAny(['kelas_id', 'tanggal', 'status']))
                    Tidak ada QR Code yang sesuai dengan filter
                @else
                    Belum ada QR Code yang dibuat
                @endif
            </p>
        </div>
        @endforelse
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
                    <i class="bi bi-qr-code me-2"></i>Buat QR Code Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('guru.qrcode.store') }}" method="POST" id="createQRForm">
                @csrf
                <div class="modal-body" style="max-height: 75vh; overflow-y: auto;">
                    <div class="alert alert-warning mb-4">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Perhatian!</strong> Jika kelas sudah memiliki QR Code aktif, QR Code lama akan otomatis terhapus.
                    </div>
                    
                    <div class="row g-4">
                        <!-- LEFT SIDE: Form Fields -->
                        <div class="col-lg-6">
                            <div class="row g-3">
                                <!-- Kelas -->
                                <div class="col-12">
                                    <label class="form-label fw-semibold">
                                        Kelas <span class="text-danger">*</span>
                                    </label>
                                    <select name="kelas_id" class="form-select" required>
                                        <option value="">Pilih Kelas</option>
                                        @foreach($kelas as $k)
                                            <option value="{{ $k->id }}">
                                                {{ $k->nama_kelas }} - {{ $k->jurusan->nama_jurusan }} 
                                                ({{ $k->siswa_count ?? 0 }} siswa)
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Tanggal -->
                                <div class="col-12">
                                    <label class="form-label fw-semibold">
                                        Tanggal <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" 
                                           name="tanggal" 
                                           class="form-control" 
                                           value="{{ date('Y-m-d') }}"
                                           min="{{ date('Y-m-d') }}"
                                           required>
                                </div>

                                <!-- Waktu Check-In -->
                                <div class="col-12">
                                    <h6 class="mb-3 border-bottom pb-2">
                                        <i class="bi bi-box-arrow-in-right text-success me-2"></i>
                                        Waktu Check-In
                                    </h6>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        Jam Mulai <span class="text-danger">*</span>
                                    </label>
                                    <input type="time" 
                                           name="jam_checkin_mulai" 
                                           class="form-control" 
                                           value="07:00"
                                           required>
                                    <small class="text-muted">Mulai check-in</small>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        Jam Selesai <span class="text-danger">*</span>
                                    </label>
                                    <input type="time" 
                                           name="jam_checkin_selesai" 
                                           class="form-control" 
                                           value="08:00"
                                           required>
                                    <small class="text-muted">Batas check-in</small>
                                </div>

                                <!-- Waktu Check-Out -->
                                <div class="col-12">
                                    <h6 class="mb-3 border-bottom pb-2">
                                        <i class="bi bi-box-arrow-right text-danger me-2"></i>
                                        Waktu Check-Out
                                    </h6>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        Jam Mulai <span class="text-danger">*</span>
                                    </label>
                                    <input type="time" 
                                           name="jam_checkout_mulai" 
                                           class="form-control" 
                                           value="15:00"
                                           required>
                                    <small class="text-muted">Mulai check-out</small>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        Jam Selesai <span class="text-danger">*</span>
                                    </label>
                                    <input type="time" 
                                           name="jam_checkout_selesai" 
                                           class="form-control" 
                                           value="16:00"
                                           required>
                                    <small class="text-muted">Batas check-out</small>
                                </div>

                                <!-- Lokasi Input Fields -->
                                <div class="col-12">
                                    <h6 class="mb-3 border-bottom pb-2">
                                        <i class="bi bi-geo-alt text-primary me-2"></i>
                                        Koordinat Lokasi
                                    </h6>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        Latitude <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           name="latitude_checkin" 
                                           id="latitude"
                                           class="form-control" 
                                           placeholder="-7.6298"
                                           readonly
                                           required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        Longitude <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           name="longitude_checkin" 
                                           id="longitude"
                                           class="form-control" 
                                           placeholder="111.5239"
                                           readonly
                                           required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        Radius (m) <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" 
                                           name="radius_checkin" 
                                           id="radius"
                                           class="form-control" 
                                           value="200"
                                           min="50"
                                           max="1000"
                                           required>
                                    <small class="text-muted">50-1000m</small>
                                </div>
                            </div>
                        </div>

                        <!-- RIGHT SIDE: Map -->
                        <div class="col-lg-6">
                            <div class="sticky-top" style="top: 20px;">
                                <h6 class="mb-3 border-bottom pb-2">
                                    <i class="bi bi-map text-primary me-2"></i>
                                    Pilih Lokasi di Peta
                                </h6>

                                <div class="alert alert-info mb-3">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <small>Klik pada peta atau gunakan tombol di bawah untuk menentukan lokasi presensi.</small>
                                </div>

                                <!-- Map Action Buttons -->
                                <div class="d-grid gap-2 mb-3">
                                    <button type="button" class="btn btn-primary" id="getLocationBtn">
                                        <i class="bi bi-geo-alt me-2"></i>Gunakan Lokasi Saat Ini
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" id="searchLocationBtn">
                                        <i class="bi bi-search me-2"></i>Cari Alamat
                                    </button>
                                </div>

                                <!-- Search Box -->
                                <div id="searchBox" style="display: none;" class="mb-3">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="searchAddressInput" placeholder="Cari alamat...">
                                        <button class="btn btn-primary" type="button" id="searchAddressBtn">
                                            <i class="bi bi-search"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Map Container -->
                                <div id="map" style="height: 450px; border-radius: 8px; border: 2px solid #dee2e6;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-2"></i>Generate QR Code
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

<!-- Leaflet CSS & JS -->
@push('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<link rel="stylesheet" href="{{ asset('css/guru/qrcode.css') }}">
<script src="{{ asset('js/guru/qrcode.js') }}"></script>
@endpush
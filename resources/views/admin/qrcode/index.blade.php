@extends('layouts.admin')
@section('title', 'Generate QR Code')

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
                    <h3 class="mb-0 text-white">Generate QR Code Presensi</h3>
                    <p class="text-white-50 mb-0">Buat dan kelola QR Code untuk absensi siswa</p>
                </div>
                <div>
                    <button type="button" class="btn btn-white" data-bs-toggle="modal" data-bs-target="#createQRModal">
                        <i class="bi bi-plus-circle me-2"></i>Buat QR Code
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-6">
        <div class="col-md-12">
            <div class="card shadow-sm">
                
                <div class="collapse" id="advancedFilter">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-3">
                            <i class="bi bi-funnel me-2"></i>Filter QR Code
                        </h5>
                        <form action="{{ route('admin.qrcode.index') }}" method="GET">
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

                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h4 class="mb-0">Daftar QR Code</h4>
                        </div>
                        
                        <div class="d-flex gap-2 flex-wrap">
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.qrcode.index') }}" 
                                   class="btn btn-sm {{ !request('status') ? 'btn-primary' : 'btn-outline-primary' }}">
                                    Semua
                                </a>
                                <a href="{{ route('admin.qrcode.index', ['status' => 'active']) }}" 
                                   class="btn btn-sm {{ request('status') == 'active' ? 'btn-success' : 'btn-outline-success' }}">
                                    Aktif
                                </a>
                                <a href="{{ route('admin.qrcode.index', ['status' => 'expired']) }}" 
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

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0 text-center" style="width: 60px;">No</th>
                                    <th class="border-0">Kelas</th>
                                    <th class="border-0 text-center">Tanggal</th>
                                    <th class="border-0 text-center">Waktu</th>
                                    <th class="border-0 text-center">Presensi</th>
                                    <th class="border-0 text-center">Status</th>
                                    <th class="border-0 text-center" style="width: 150px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sessions as $index => $session)
                                <tr>
                                    <td class="text-center">
                                        <span class="text-muted fw-semibold">{{ $sessions->firstItem() + $index }}</span>
                                    </td>
                                    <td>
                                        <h6 class="mb-0">{{ $session->kelas->nama_kelas }}</h6>
                                        <small class="text-muted">{{ $session->kelas->jurusan->nama_jurusan }}</small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary-soft text-primary">
                                            {{ $session->tanggal->format('d M Y') }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <small class="text-muted">
                                            {{ $session->jam_mulai->format('H:i') }} - 
                                            {{ $session->jam_selesai->format('H:i') }}
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info-soft text-info">
                                            <i class="bi bi-people-fill me-1"></i>
                                            {{ $session->presensis_count }} Siswa
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($session->status == 'active')
                                            @if($session->isActive())
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle me-1"></i>Aktif
                                                </span>
                                            @else
                                                <span class="badge bg-warning">
                                                    <i class="bi bi-clock me-1"></i>Menunggu
                                                </span>
                                            @endif
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="bi bi-x-circle me-1"></i>Expired
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <button type="button"
                                                    class="btn btn-sm btn-info btn-show-qr" 
                                                    data-session-id="{{ $session->id }}"
                                                    title="Detail">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <a href="{{ route('admin.qrcode.download', $session->id) }}" 
                                               class="btn btn-sm btn-success" 
                                               title="Download">
                                                <i class="bi bi-download"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-warning btn-toggle-status" 
                                                    data-session-id="{{ $session->id }}"
                                                    data-current-status="{{ $session->status }}"
                                                    title="Toggle Status">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </button>
                                            <form action="{{ route('admin.qrcode.destroy', $session->id) }}" 
                                                  method="POST" class="d-inline delete-form">
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
                                        <i class="bi bi-qr-code fs-1 text-muted"></i>
                                        <p class="text-muted mt-3 mb-0">Belum ada QR Code</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($sessions->total() > 0)
                <div class="card-footer bg-white border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Menampilkan <strong>{{ $sessions->firstItem() }}</strong> sampai 
                            <strong>{{ $sessions->lastItem() }}</strong> dari 
                            <strong>{{ $sessions->total() }}</strong> data
                        </div>
                        {{ $sessions->links() }}
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="createQRModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-qr-code me-2"></i>Buat QR Code Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.qrcode.store') }}" method="POST" id="createQRForm">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
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

                        <div class="col-md-6">
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

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Jam Mulai <span class="text-danger">*</span>
                            </label>
                            <input type="time" 
                                   name="jam_mulai" 
                                   class="form-control" 
                                   value="07:00"
                                   required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Jam Selesai <span class="text-danger">*</span>
                            </label>
                            <input type="time" 
                                   name="jam_selesai" 
                                   class="form-control" 
                                   value="08:00"
                                   required>
                        </div>

                        <!-- MAP SECTION -->
                        <div class="col-12">
                            <div class="alert alert-info mb-3">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Pilih Lokasi Presensi</strong><br>
                                Klik pada peta atau gunakan tombol di bawah untuk menentukan lokasi
                            </div>

                            <div class="mb-3">
                                <button type="button" class="btn btn-primary w-100 mb-2" id="getLocationBtn">
                                    <i class="bi bi-geo-alt me-2"></i>Gunakan Lokasi Saat Ini
                                </button>
                                <button type="button" class="btn btn-outline-secondary w-100" id="searchLocationBtn">
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
                            <div id="map" style="height: 400px; border-radius: 8px; border: 2px solid #dee2e6;"></div>
                            
                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        Latitude <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           name="latitude" 
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
                                           name="longitude" 
                                           id="longitude"
                                           class="form-control" 
                                           placeholder="111.5239"
                                           readonly
                                           required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        Radius (meter) <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" 
                                           name="radius" 
                                           id="radius"
                                           class="form-control" 
                                           value="200"
                                           min="50"
                                           max="1000"
                                           required>
                                    <small class="text-muted">50-1000 meter</small>
                                </div>
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

<div class="modal fade" id="showQRModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-qr-code me-2"></i>Detail QR Code Presensi
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
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
#map {
    z-index: 1;
}
.leaflet-container {
    z-index: 1;
}
</style>

<script>
let map;
let marker;
let circle;

document.addEventListener('DOMContentLoaded', function() {
    const createQRModal = document.getElementById('createQRModal');
    
    createQRModal.addEventListener('shown.bs.modal', function() {
        if (!map) {
            initMap();
        }
    });
    
    // Search location button
    document.getElementById('searchLocationBtn').addEventListener('click', function() {
        const searchBox = document.getElementById('searchBox');
        searchBox.style.display = searchBox.style.display === 'none' ? 'block' : 'none';
    });
    
    // Search address
    document.getElementById('searchAddressBtn').addEventListener('click', function() {
        const address = document.getElementById('searchAddressInput').value;
        if (address) {
            searchAddress(address);
        }
    });
    
    // Enter key on search
    document.getElementById('searchAddressInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('searchAddressBtn').click();
        }
    });
    
    // Get current location
    document.getElementById('getLocationBtn').addEventListener('click', function() {
        getCurrentLocation();
    });
    
    // Radius change
    document.getElementById('radius').addEventListener('change', function() {
        if (circle) {
            circle.setRadius(parseInt(this.value));
        }
    });
});

function initMap() {
    // Default location (Indonesia center)
    const defaultLat = -2.5489;
    const defaultLng = 118.0149;
    
    map = L.map('map').setView([defaultLat, defaultLng], 5);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);
    
    // Click on map
    map.on('click', function(e) {
        setLocation(e.latlng.lat, e.latlng.lng);
    });
}

function setLocation(lat, lng) {
    document.getElementById('latitude').value = lat.toFixed(8);
    document.getElementById('longitude').value = lng.toFixed(8);
    
    // Remove existing marker and circle
    if (marker) {
        map.removeLayer(marker);
    }
    if (circle) {
        map.removeLayer(circle);
    }
    
    // Add new marker
    marker = L.marker([lat, lng]).addTo(map);
    
    // Add circle
    const radius = parseInt(document.getElementById('radius').value) || 200;
    circle = L.circle([lat, lng], {
        color: 'red',
        fillColor: '#f03',
        fillOpacity: 0.2,
        radius: radius
    }).addTo(map);
    
    // Center map
    map.setView([lat, lng], 16);
}

function getCurrentLocation() {
    const btn = document.getElementById('getLocationBtn');
    const originalHTML = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengambil lokasi...';
    
    if (!navigator.geolocation) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Browser Anda tidak mendukung Geolocation'
        });
        btn.disabled = false;
        btn.innerHTML = originalHTML;
        return;
    }
    
    navigator.geolocation.getCurrentPosition(
        (position) => {
            setLocation(position.coords.latitude, position.coords.longitude);
            
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Lokasi Berhasil Diambil';
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-success');
            
            setTimeout(() => {
                btn.classList.remove('btn-success');
                btn.classList.add('btn-primary');
                btn.innerHTML = originalHTML;
            }, 2000);
        },
        (error) => {
            btn.disabled = false;
            btn.innerHTML = originalHTML;
            
            let errorMessage = 'Gagal mengambil lokasi';
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    errorMessage = 'Izin akses lokasi ditolak';
                    break;
                case error.POSITION_UNAVAILABLE:
                    errorMessage = 'Informasi lokasi tidak tersedia';
                    break;
                case error.TIMEOUT:
                    errorMessage = 'Waktu permintaan lokasi habis';
                    break;
            }
            
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: errorMessage
            });
        }
    );
}

function searchAddress(address) {
    // Using Nominatim (OpenStreetMap) geocoding
    const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data && data.length > 0) {
                const lat = parseFloat(data[0].lat);
                const lng = parseFloat(data[0].lon);
                setLocation(lat, lng);
                
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Lokasi ditemukan',
                    timer: 1500,
                    showConfirmButton: false
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Tidak Ditemukan',
                    text: 'Alamat tidak ditemukan, coba kata kunci lain'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Gagal mencari lokasi'
            });
        });
}
</script>

@push('scripts')
<link rel="stylesheet" href="{{ asset('css/admin/qrcode.css') }}">
<script src="{{ asset('js/admin/qrcode.js') }}"></script>
@endpush
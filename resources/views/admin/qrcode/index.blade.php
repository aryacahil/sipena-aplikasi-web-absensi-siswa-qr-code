@extends('layouts.admin')

@section('content')
<div class="bg-primary pt-10 pb-21"></div>
<div class="container-fluid mt-n22 px-6">
    <div class="row">
        <div class="col-lg-12 col-md-12 col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div class="mb-2 mb-lg-0">
                    <h3 class="mb-0 text-white">Generate QR Code Presensi</h3>
                </div>
                <div>
                    <button type="button" class="btn btn-white" data-bs-toggle="modal" data-bs-target="#createQRModal">
                        <i class="bi bi-qr-code me-2"></i>Buat QR Code
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-6">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Daftar Session Presensi Hari Ini</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Kelas</th>
                                    <th>Waktu</th>
                                    <th>Status</th>
                                    <th>Jumlah Hadir</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sessions as $index => $session)
                                <tr>
                                    <td>{{ $sessions->firstItem() + $index }}</td>
                                    <td>{{ $session->tanggal->format('d/m/Y') }}</td>
                                    <td>
                                        <strong>{{ $session->kelas->nama_kelas }}</strong><br>
                                        <small class="text-muted">{{ $session->kelas->jurusan->nama_jurusan }}</small>
                                    </td>
                                    <td>{{ $session->jam_mulai }} - {{ $session->jam_selesai }}</td>
                                    <td>
                                        @if($session->status == 'active')
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-secondary">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">
                                            {{ $session->presensis->count() }} / {{ $session->kelas->siswa->count() }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.qrcode.show', $session->id) }}" 
                                               class="btn btn-sm btn-info" title="Lihat Detail">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <form action="{{ route('admin.qrcode.destroy', $session->id) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" 
                                                        title="Nonaktifkan"
                                                        onclick="return confirm('Nonaktifkan session ini?')">
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="bi bi-inbox fs-1 text-muted"></i>
                                        <p class="text-muted mt-2">Belum ada session presensi hari ini</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $sessions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Create QR Code -->
<div class="modal fade" id="createQRModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-qr-code me-2"></i>Buat QR Code Presensi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.qrcode.store') }}" method="POST" id="createQRForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="kelas_id" class="form-label">Kelas <span class="text-danger">*</span></label>
                            <select class="form-select" id="kelas_id" name="kelas_id" required>
                                <option value="">Pilih Kelas</option>
                                @foreach($dataKelas as $k)
                                    <option value="{{ $k->id }}">
                                        {{ $k->nama_kelas }} - {{ $k->jurusan->nama_jurusan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tanggal" class="form-label">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tanggal" name="tanggal" 
                                   value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="jam_mulai" class="form-label">Jam Mulai <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="jam_mulai" name="jam_mulai" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="jam_selesai" class="form-label">Jam Selesai <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="jam_selesai" name="jam_selesai" required>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Lokasi GPS akan terdeteksi otomatis</strong><br>
                        <small>Pastikan browser mengizinkan akses lokasi</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Lokasi Saat Ini</label>
                        <div id="location-status" class="alert alert-warning">
                            <i class="bi bi-geo-alt me-2"></i>Menunggu deteksi lokasi...
                        </div>
                        <input type="hidden" id="latitude" name="latitude" required>
                        <input type="hidden" id="longitude" name="longitude" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="radius" class="form-label">Radius Maksimal (meter)</label>
                        <input type="number" class="form-control" id="radius" name="radius" 
                               value="200" min="50" max="1000">
                        <small class="text-muted">Default: 200 meter</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                        <i class="bi bi-qr-code me-2"></i>Generate QR Code
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Detect GPS location
document.getElementById('createQRModal').addEventListener('shown.bs.modal', function() {
    const locationStatus = document.getElementById('location-status');
    const submitBtn = document.getElementById('submitBtn');
    
    if (navigator.geolocation) {
        locationStatus.innerHTML = '<i class="bi bi-geo-alt me-2"></i>Mendeteksi lokasi...';
        
        navigator.geolocation.getCurrentPosition(
            function(position) {
                document.getElementById('latitude').value = position.coords.latitude;
                document.getElementById('longitude').value = position.coords.longitude;
                
                locationStatus.className = 'alert alert-success';
                locationStatus.innerHTML = `
                    <i class="bi bi-check-circle me-2"></i>
                    <strong>Lokasi terdeteksi!</strong><br>
                    Latitude: ${position.coords.latitude.toFixed(6)}<br>
                    Longitude: ${position.coords.longitude.toFixed(6)}
                `;
                
                submitBtn.disabled = false;
            },
            function(error) {
                locationStatus.className = 'alert alert-danger';
                locationStatus.innerHTML = `
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Gagal mendeteksi lokasi!</strong><br>
                    ${error.message}<br>
                    <small>Pastikan Anda mengizinkan akses lokasi di browser</small>
                `;
                submitBtn.disabled = true;
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    } else {
        locationStatus.className = 'alert alert-danger';
        locationStatus.innerHTML = `
            <i class="bi bi-exclamation-triangle me-2"></i>
            Browser tidak mendukung Geolocation
        `;
    }
});

@if(session('success'))
    alert('{{ session("success") }}');
@endif

@if(session('error'))
    alert('{{ session("error") }}');
@endif
</script>
@endpush
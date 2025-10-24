@extends('layouts.siswa')

@section('content')
<div class="bg-primary pt-10 pb-21"></div>
<div class="container-fluid mt-n22 px-6">
    <div class="row">
        <div class="col-lg-12 col-md-12 col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div class="mb-2 mb-lg-0">
                    <h3 class="mb-0 text-white">Verifikasi Presensi</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-6">
        <div class="col-md-8 mx-auto">
            @if($sudahAbsen)
                <!-- Sudah Absen -->
                <div class="card border-success">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-check-circle me-2"></i>Anda Sudah Absen
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-success">
                            <h5><i class="bi bi-info-circle me-2"></i>Status Presensi</h5>
                            <hr>
                            <p class="mb-1"><strong>Waktu Absen:</strong> {{ $sudahAbsen->waktu_absen->format('d/m/Y H:i:s') }}</p>
                            <p class="mb-1"><strong>Status:</strong> 
                                <span class="badge bg-{{ $sudahAbsen->status_badge }}">
                                    {{ strtoupper($sudahAbsen->status) }}
                                </span>
                            </p>
                            <p class="mb-1"><strong>Tipe:</strong> 
                                <span class="badge bg-{{ $sudahAbsen->tipe_absen == 'qr' ? 'primary' : 'secondary' }}">
                                    {{ $sudahAbsen->tipe_absen == 'qr' ? 'QR Code' : 'Manual' }}
                                </span>
                            </p>
                        </div>
                        
                        <div class="text-center mt-4">
                            <a href="{{ route('siswa.home') }}" class="btn btn-primary">
                                <i class="bi bi-house me-2"></i>Kembali ke Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <!-- Form Verifikasi -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-qr-code me-2"></i>Informasi Session Presensi
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Info Session -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Kelas:</strong></p>
                                <p class="text-muted">
                                    <span class="badge bg-primary">{{ $session->kelas->nama_kelas }}</span>
                                    {{ $session->kelas->jurusan->nama_jurusan }}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Tanggal:</strong></p>
                                <p class="text-muted">{{ $session->tanggal->format('d F Y') }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Waktu:</strong></p>
                                <p class="text-muted">{{ $session->jam_mulai }} - {{ $session->jam_selesai }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Status Session:</strong></p>
                                <p>
                                    <span class="badge bg-{{ $session->status == 'active' ? 'success' : 'secondary' }}">
                                        {{ $session->status == 'active' ? 'AKTIF' : 'TIDAK AKTIF' }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        <!-- Status GPS -->
                        <div id="gps-status" class="alert alert-info">
                            <i class="bi bi-geo-alt me-2"></i>Mendeteksi lokasi GPS...
                        </div>

                        <!-- Info GPS -->
                        <div id="gps-info" style="display: none;">
                            <div class="card bg-light mb-3">
                                <div class="card-body">
                                    <h6 class="mb-2">Lokasi Saat Ini:</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Latitude:</strong> <span id="current-lat">-</span></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Longitude:</strong> <span id="current-lng">-</span></p>
                                        </div>
                                    </div>
                                    <p class="mb-0 text-muted small mt-2">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Radius maksimal: {{ $session->radius }} meter
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Tombol Absen -->
                        <div class="text-center">
                            <button type="button" class="btn btn-success btn-lg" id="btn-presensi" disabled>
                                <i class="bi bi-check-circle me-2"></i>Lakukan Presensi
                            </button>
                            <br>
                            <a href="{{ route('siswa.home') }}" class="btn btn-secondary mt-3">
                                <i class="bi bi-arrow-left me-2"></i>Batal
                            </a>
                        </div>

                        <!-- Instruksi -->
                        <div class="alert alert-warning mt-4">
                            <h6 class="mb-2"><i class="bi bi-exclamation-triangle me-2"></i>Perhatian:</h6>
                            <ul class="mb-0">
                                <li>Pastikan GPS aktif dan izinkan akses lokasi</li>
                                <li>Anda harus berada dalam radius {{ $session->radius }} meter dari lokasi presensi</li>
                                <li>Session akan berakhir pada {{ $session->tanggal->format('d/m/Y') }} {{ $session->jam_selesai }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@if(!$sudahAbsen)
<script>
let currentLatitude = null;
let currentLongitude = null;
let gpsWatchId = null;

// Init GPS
function initGPS() {
    const gpsStatus = document.getElementById('gps-status');
    const btnPresensi = document.getElementById('btn-presensi');
    const gpsInfo = document.getElementById('gps-info');
    
    if (!navigator.geolocation) {
        gpsStatus.className = 'alert alert-danger';
        gpsStatus.innerHTML = '<i class="bi bi-exclamation-triangle me-2"></i>Browser tidak mendukung GPS!';
        return;
    }

    gpsWatchId = navigator.geolocation.watchPosition(
        function(position) {
            currentLatitude = position.coords.latitude;
            currentLongitude = position.coords.longitude;
            
            gpsStatus.className = 'alert alert-success';
            gpsStatus.innerHTML = `
                <i class="bi bi-check-circle me-2"></i>
                <strong>GPS Aktif!</strong> Akurasi: ${Math.round(position.coords.accuracy)} meter
            `;
            
            document.getElementById('current-lat').textContent = currentLatitude.toFixed(6);
            document.getElementById('current-lng').textContent = currentLongitude.toFixed(6);
            gpsInfo.style.display = 'block';
            
            btnPresensi.disabled = false;
        },
        function(error) {
            let errorMsg = 'Gagal mendapatkan lokasi. ';
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    errorMsg += 'Silakan izinkan akses lokasi.';
                    break;
                case error.POSITION_UNAVAILABLE:
                    errorMsg += 'Pastikan GPS aktif.';
                    break;
                case error.TIMEOUT:
                    errorMsg += 'Timeout. Coba lagi.';
                    break;
            }
            
            gpsStatus.className = 'alert alert-danger';
            gpsStatus.innerHTML = `<i class="bi bi-exclamation-triangle me-2"></i>${errorMsg}`;
            btnPresensi.disabled = true;
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        }
    );
}

// Proses Presensi
document.getElementById('btn-presensi')?.addEventListener('click', function() {
    if (!currentLatitude || !currentLongitude) {
        Swal.fire({
            icon: 'error',
            title: 'GPS Belum Aktif',
            text: 'Tunggu hingga lokasi GPS terdeteksi',
            confirmButtonColor: '#dc3545'
        });
        return;
    }

    Swal.fire({
        title: 'Memproses Presensi...',
        html: 'Memvalidasi lokasi dan mencatat kehadiran Anda',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch('{{ route("siswa.presensi.verify") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            qr_code: '{{ $session->qr_code }}',
            latitude: currentLatitude,
            longitude: currentLongitude
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Presensi Berhasil!',
                html: `
                    <div class="text-start">
                        <p class="mb-2">${data.message}</p>
                        <hr>
                        <p class="mb-1"><strong>Waktu:</strong> ${data.data.waktu}</p>
                        <p class="mb-1"><strong>Kelas:</strong> ${data.data.kelas}</p>
                        <p class="mb-0"><strong>Jarak:</strong> ${data.data.distance}</p>
                    </div>
                `,
                confirmButtonText: 'OK',
                confirmButtonColor: '#28a745'
            }).then(() => {
                window.location.href = '{{ route("siswa.home") }}';
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Presensi Gagal',
                html: `<p>${data.message}</p>${data.distance ? '<p class="small text-muted">Jarak Anda: ' + data.distance + ' meter<br>Maksimal: ' + data.max_distance + ' meter</p>' : ''}`,
                confirmButtonText: 'OK',
                confirmButtonColor: '#dc3545'
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Terjadi Kesalahan',
            text: 'Gagal menghubungi server. Silakan coba lagi.',
            confirmButtonColor: '#dc3545'
        });
    });
});

// Init saat halaman dimuat
document.addEventListener('DOMContentLoaded', initGPS);

// Cleanup
window.addEventListener('beforeunload', function() {
    if (gpsWatchId !== null) {
        navigator.geolocation.clearWatch(gpsWatchId);
    }
});
</script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endif
@endsection
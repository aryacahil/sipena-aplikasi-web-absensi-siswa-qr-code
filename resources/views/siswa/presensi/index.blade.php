@extends('layouts.siswa')
@section('title', 'Scan QR Code Presensi')

@section('content')
<div class="bg-primary pt-10 pb-21"></div>
<div class="container-fluid mt-n22 px-6">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="bi bi-qr-code-scan me-2"></i>Scan QR Code Presensi
                        </h4>
                        <span class="badge bg-{{ $session->isActive() ? 'success' : ($session->getStatusText() === 'waiting' ? 'warning' : 'secondary') }}">
                            {{ $session->isActive() ? 'Sesi Aktif' : ($session->getStatusText() === 'waiting' ? 'Menunggu' : 'Expired') }}
                        </span>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Informasi Sesi -->
                    <div class="alert alert-info mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="mb-2">
                                    <i class="bi bi-building me-2"></i>{{ $session->kelas->nama_kelas }}
                                </h6>
                                <small class="text-muted">{{ $session->kelas->jurusan->nama_jurusan }}</small>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <h6 class="mb-2">
                                    <i class="bi bi-calendar3 me-2"></i>{{ $session->tanggal->format('d M Y') }}
                                </h6>
                                <small class="text-muted">
                                    {{ $session->jam_mulai->format('H:i') }} - {{ $session->jam_selesai->format('H:i') }}
                                </small>
                            </div>
                        </div>
                    </div>

                    @if($session->isActive())
                        <!-- Scanner Section -->
                        <div id="scanner-container" class="mb-4">
                            <div class="position-relative">
                                <video id="preview" style="width: 100%; max-height: 400px; border-radius: 8px; background: #000;"></video>
                                <div id="scanner-overlay" class="scanner-overlay">
                                    <div class="scanner-frame"></div>
                                    <p class="text-white text-center mt-3">Posisikan QR Code di dalam frame</p>
                                </div>
                            </div>
                        </div>

                        <!-- Tombol Kontrol -->
                        <div class="d-flex gap-2 justify-content-center mb-4">
                            <button type="button" id="btnStartScan" class="btn btn-success">
                                <i class="bi bi-play-fill me-2"></i>Mulai Scan
                            </button>
                            <button type="button" id="btnStopScan" class="btn btn-danger" style="display: none;">
                                <i class="bi bi-stop-fill me-2"></i>Hentikan Scan
                            </button>
                        </div>

                        <!-- Status Messages -->
                        <div id="status-message" class="alert alert-info text-center" style="display: none;">
                            <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                            <span id="status-text">Memproses...</span>
                        </div>

                        <!-- Info Lokasi -->
                        <div class="alert alert-warning">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Perhatian:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Pastikan GPS/Lokasi Anda aktif</li>
                                <li>Anda harus berada dalam radius {{ $session->radius }} meter dari lokasi</li>
                                <li>Presensi hanya bisa dilakukan sekali per hari</li>
                            </ul>
                        </div>
                    @elseif($session->getStatusText() === 'waiting')
                        <div class="alert alert-warning text-center py-5">
                            <i class="bi bi-clock fs-1 mb-3 d-block"></i>
                            <h5>Sesi Belum Dimulai</h5>
                            <p class="mb-0">Sesi presensi akan dimulai pada {{ $session->jam_mulai->format('H:i') }}</p>
                        </div>
                    @else
                        <div class="alert alert-secondary text-center py-5">
                            <i class="bi bi-x-circle fs-1 mb-3 d-block"></i>
                            <h5>Sesi Telah Berakhir</h5>
                            <p class="mb-0">Sesi presensi sudah tidak aktif</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-5">
                <div class="mb-4">
                    <i class="bi bi-check-circle text-success" style="font-size: 5rem;"></i>
                </div>
                <h3 class="mb-3">Presensi Berhasil!</h3>
                <div id="success-details" class="text-start">
                    <!-- Details will be inserted here -->
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<style>
.scanner-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    pointer-events: none;
}

.scanner-frame {
    width: 250px;
    height: 250px;
    border: 3px solid #28a745;
    border-radius: 12px;
    box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.5);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { border-color: #28a745; }
    50% { border-color: #20c997; }
}

#preview {
    object-fit: cover;
}
</style>

@endsection

@push('scripts')
<!-- Instascan Library for QR Code Scanning -->
<script src="https://rawgit.com/schmich/instascan/master/instascan.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = '{{ csrf_token() }}';
    const sessionId = {{ $session->id }};
    const isActive = {{ $session->isActive() ? 'true' : 'false' }};
    
    if (!isActive) {
        return; // Don't initialize scanner if session not active
    }
    
    let scanner = null;
    let cameras = [];
    let currentLocation = null;
    
    const preview = document.getElementById('preview');
    const btnStart = document.getElementById('btnStartScan');
    const btnStop = document.getElementById('btnStopScan');
    const statusMessage = document.getElementById('status-message');
    const statusText = document.getElementById('status-text');
    
    // Initialize Scanner
    scanner = new Instascan.Scanner({ 
        video: preview,
        scanPeriod: 5,
        mirror: false
    });
    
    // Get Cameras
    Instascan.Camera.getCameras().then(function (availableCameras) {
        cameras = availableCameras;
        
        if (cameras.length === 0) {
            Swal.fire({
                icon: 'error',
                title: 'Kamera Tidak Ditemukan',
                text: 'Tidak ada kamera yang tersedia di perangkat Anda'
            });
        }
    }).catch(function (error) {
        console.error('Error getting cameras:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Gagal mengakses kamera: ' + error
        });
    });
    
    // Start Scanning
    btnStart.addEventListener('click', function() {
        if (cameras.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan',
                text: 'Kamera belum tersedia, silakan refresh halaman'
            });
            return;
        }
        
        // Get location first
        if (!navigator.geolocation) {
            Swal.fire({
                icon: 'error',
                title: 'Geolocation Tidak Didukung',
                text: 'Browser Anda tidak mendukung fitur lokasi'
            });
            return;
        }
        
        showStatus('Mengambil lokasi Anda...');
        
        navigator.geolocation.getCurrentPosition(
            function(position) {
                currentLocation = {
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude
                };
                
                hideStatus();
                
                // Start scanner with back camera if available
                const backCamera = cameras.find(cam => cam.name.toLowerCase().includes('back')) || cameras[0];
                
                scanner.start(backCamera).then(function() {
                    btnStart.style.display = 'none';
                    btnStop.style.display = 'inline-block';
                }).catch(function(error) {
                    console.error('Error starting scanner:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal memulai scanner: ' + error
                    });
                });
            },
            function(error) {
                hideStatus();
                let errorMessage = 'Gagal mengambil lokasi';
                
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        errorMessage = 'Akses lokasi ditolak. Silakan izinkan akses lokasi di pengaturan browser Anda.';
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
                    title: 'Error Lokasi',
                    text: errorMessage
                });
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    });
    
    // Stop Scanning
    btnStop.addEventListener('click', function() {
        if (scanner) {
            scanner.stop();
            btnStart.style.display = 'inline-block';
            btnStop.style.display = 'none';
        }
    });
    
    // QR Code Detected
    scanner.addListener('scan', function(content) {
        console.log('QR Code detected:', content);
        
        // Stop scanner
        scanner.stop();
        btnStart.style.display = 'inline-block';
        btnStop.style.display = 'none';
        
        // Check if we have location
        if (!currentLocation) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Lokasi belum diambil. Silakan coba lagi.'
            });
            return;
        }
        
        // Submit presensi
        submitPresensi(content);
    });
    
    function submitPresensi(qrContent) {
        showStatus('Memproses presensi...');
        
        fetch('/siswa/presensi/submit', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                session_id: sessionId,
                latitude: currentLocation.latitude,
                longitude: currentLocation.longitude
            })
        })
        .then(response => response.json())
        .then(data => {
            hideStatus();
            
            if (data.success) {
                showSuccessModal(data.data);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Presensi Gagal',
                    text: data.message || 'Terjadi kesalahan'
                });
            }
        })
        .catch(error => {
            hideStatus();
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Terjadi kesalahan saat memproses presensi'
            });
        });
    }
    
    function showSuccessModal(data) {
        const details = document.getElementById('success-details');
        details.innerHTML = `
            <div class="card bg-light">
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Nama:</strong> ${data.nama}
                    </div>
                    <div class="mb-2">
                        <strong>Kelas:</strong> ${data.kelas}
                    </div>
                    <div class="mb-2">
                        <strong>Tanggal:</strong> ${data.tanggal}
                    </div>
                    <div class="mb-2">
                        <strong>Waktu:</strong> ${data.waktu}
                    </div>
                    <div>
                        <strong>Status:</strong> 
                        <span class="badge bg-success">Hadir</span>
                    </div>
                </div>
            </div>
        `;
        
        const modal = new bootstrap.Modal(document.getElementById('successModal'));
        modal.show();
        
        // Redirect after 3 seconds
        setTimeout(() => {
            window.location.href = '/siswa/home';
        }, 3000);
    }
    
    function showStatus(message) {
        statusText.textContent = message;
        statusMessage.style.display = 'block';
    }
    
    function hideStatus() {
        statusMessage.style.display = 'none';
    }
});
</script>
@endpush
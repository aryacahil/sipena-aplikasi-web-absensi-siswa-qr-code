@extends('layouts.siswa')

@section('content')
<div class="bg-primary pt-10 pb-21"></div>
<div class="container-fluid mt-n22 px-6">
    <div class="row">
        <div class="col-lg-12 col-md-12 col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div class="mb-2 mb-lg-0">
                    <h3 class="mb-0 text-white">Scan QR Code Presensi</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-6">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="bi bi-qr-code-scan me-2"></i>Scanner QR Code
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Alert untuk status GPS -->
                    <div id="gps-status" class="alert alert-info">
                        <i class="bi bi-geo-alt me-2"></i>Meminta akses lokasi GPS...
                    </div>

                    <!-- QR Scanner Container -->
                    <div id="scanner-container" class="text-center mb-4" style="display: none;">
                        <div id="reader" style="width: 100%; max-width: 500px; margin: 0 auto;"></div>
                    </div>

                    <!-- Status Scanner -->
                    <div id="scanner-status" class="text-center mb-3">
                        <p class="text-muted">Mengaktifkan kamera...</p>
                    </div>

                    <!-- Tombol Kontrol -->
                    <div class="text-center">
                        <button type="button" class="btn btn-primary" id="start-scan" disabled>
                            <i class="bi bi-camera-fill me-2"></i>Mulai Scan
                        </button>
                        <button type="button" class="btn btn-danger" id="stop-scan" style="display: none;">
                            <i class="bi bi-stop-circle me-2"></i>Berhenti Scan
                        </button>
                    </div>

                    <!-- Info GPS -->
                    <div class="mt-4" id="gps-info" style="display: none;">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="mb-3">Lokasi Saat Ini:</h6>
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
                                    Pastikan Anda berada dalam radius 200 meter dari lokasi presensi
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Instruksi -->
                    <div class="alert alert-secondary mt-4">
                        <h6 class="mb-2"><i class="bi bi-info-circle me-2"></i>Instruksi:</h6>
                        <ol class="mb-0">
                            <li>Aktifkan GPS di perangkat Anda</li>
                            <li>Izinkan akses kamera dan lokasi</li>
                            <li>Arahkan kamera ke QR Code yang ditampilkan guru</li>
                            <li>Tunggu hingga QR Code terdeteksi otomatis</li>
                            <li>Sistem akan memverifikasi lokasi Anda secara otomatis</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Html5-QRCode Library -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
let html5QrcodeScanner;
let currentLatitude = null;
let currentLongitude = null;
let gpsWatchId = null;

// Fungsi untuk mendapatkan lokasi GPS
function initGPS() {
    const gpsStatus = document.getElementById('gps-status');
    const startScanBtn = document.getElementById('start-scan');
    const gpsInfo = document.getElementById('gps-info');
    
    if (!navigator.geolocation) {
        gpsStatus.className = 'alert alert-danger';
        gpsStatus.innerHTML = '<i class="bi bi-exclamation-triangle me-2"></i>Browser Anda tidak mendukung GPS!';
        return;
    }

    gpsStatus.innerHTML = '<i class="bi bi-geo-alt me-2"></i>Mendeteksi lokasi GPS...';

    // Dapatkan posisi dan pantau perubahan
    gpsWatchId = navigator.geolocation.watchPosition(
        function(position) {
            currentLatitude = position.coords.latitude;
            currentLongitude = position.coords.longitude;
            
            gpsStatus.className = 'alert alert-success';
            gpsStatus.innerHTML = `
                <i class="bi bi-check-circle me-2"></i>
                <strong>GPS Aktif!</strong> Akurasi: ${Math.round(position.coords.accuracy)} meter
            `;
            
            // Update info GPS
            document.getElementById('current-lat').textContent = currentLatitude.toFixed(6);
            document.getElementById('current-lng').textContent = currentLongitude.toFixed(6);
            gpsInfo.style.display = 'block';
            
            // Enable scan button
            startScanBtn.disabled = false;
        },
        function(error) {
            let errorMsg = '';
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    errorMsg = 'Anda menolak akses lokasi. Silakan izinkan akses lokasi di pengaturan browser.';
                    break;
                case error.POSITION_UNAVAILABLE:
                    errorMsg = 'Informasi lokasi tidak tersedia. Pastikan GPS aktif.';
                    break;
                case error.TIMEOUT:
                    errorMsg = 'Waktu permintaan lokasi habis. Coba lagi.';
                    break;
                default:
                    errorMsg = 'Terjadi kesalahan saat mendapatkan lokasi.';
            }
            
            gpsStatus.className = 'alert alert-danger';
            gpsStatus.innerHTML = `<i class="bi bi-exclamation-triangle me-2"></i>${errorMsg}`;
            startScanBtn.disabled = true;
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        }
    );
}

// Fungsi untuk memulai scan
function startScanning() {
    const scannerContainer = document.getElementById('scanner-container');
    const scannerStatus = document.getElementById('scanner-status');
    const startBtn = document.getElementById('start-scan');
    const stopBtn = document.getElementById('stop-scan');
    
    scannerContainer.style.display = 'block';
    startBtn.style.display = 'none';
    stopBtn.style.display = 'inline-block';
    
    html5QrcodeScanner = new Html5Qrcode("reader");
    
    const config = {
        fps: 10,
        qrbox: { width: 250, height: 250 },
        aspectRatio: 1.0
    };

    html5QrcodeScanner.start(
        { facingMode: "environment" },
        config,
        onScanSuccess,
        onScanError
    ).then(() => {
        scannerStatus.innerHTML = '<p class="text-success"><i class="bi bi-camera-video me-2"></i>Kamera aktif - Arahkan ke QR Code</p>';
    }).catch(err => {
        scannerStatus.innerHTML = '<p class="text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Gagal mengaktifkan kamera: ' + err + '</p>';
        stopScanning();
    });
}

// Fungsi untuk berhenti scan
function stopScanning() {
    if (html5QrcodeScanner) {
        html5QrcodeScanner.stop().then(() => {
            document.getElementById('scanner-container').style.display = 'none';
            document.getElementById('start-scan').style.display = 'inline-block';
            document.getElementById('stop-scan').style.display = 'none';
            document.getElementById('scanner-status').innerHTML = '<p class="text-muted">Scanner dihentikan</p>';
        });
    }
}

// Fungsi saat QR Code berhasil di-scan
function onScanSuccess(decodedText, decodedResult) {
    stopScanning();
    
    // Tampilkan loading
    Swal.fire({
        title: 'Memverifikasi...',
        html: 'Memproses QR Code dan memvalidasi lokasi GPS',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Extract QR code dari URL jika ada
    let qrCode = decodedText;
    try {
        const url = new URL(decodedText);
        const pathParts = url.pathname.split('/');
        qrCode = pathParts[pathParts.length - 1];
    } catch (e) {
        // Jika bukan URL, gunakan decodedText langsung
    }
    
    // Kirim data ke server
    fetch('{{ route("siswa.presensi.verify") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            qr_code: qrCode,
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
                    <p class="mb-2">${data.message}</p>
                    <div class="text-start mt-3">
                        <p class="mb-1"><strong>Waktu:</strong> ${data.data.waktu}</p>
                        <p class="mb-1"><strong>Kelas:</strong> ${data.data.kelas}</p>
                        <p class="mb-0"><strong>Jarak:</strong> ${data.data.distance}</p>
                    </div>
                `,
                confirmButtonText: 'OK',
                confirmButtonColor: '#28a745'
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Presensi Gagal',
                html: `<p>${data.message}</p>`,
                confirmButtonText: 'Coba Lagi',
                confirmButtonColor: '#dc3545'
            }).then(() => {
                startScanning();
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Terjadi Kesalahan',
            text: 'Gagal menghubungi server. Silakan coba lagi.',
            confirmButtonText: 'OK',
            confirmButtonColor: '#dc3545'
        });
    });
}

// Fungsi saat scan error (diabaikan)
function onScanError(errorMessage) {
    // Abaikan error scanning, biarkan user mencoba terus
}

// Event listeners
document.getElementById('start-scan').addEventListener('click', startScanning);
document.getElementById('stop-scan').addEventListener('click', stopScanning);

// Initialize GPS saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    initGPS();
});

// Cleanup saat halaman ditutup
window.addEventListener('beforeunload', function() {
    if (gpsWatchId !== null) {
        navigator.geolocation.clearWatch(gpsWatchId);
    }
    if (html5QrcodeScanner) {
        html5QrcodeScanner.stop();
    }
});
</script>

<!-- SweetAlert2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
#reader {
    border: 2px solid #198754;
    border-radius: 8px;
}

#scanner-container {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
}
</style>
@endsection
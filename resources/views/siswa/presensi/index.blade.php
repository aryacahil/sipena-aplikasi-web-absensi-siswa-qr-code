@extends('layouts.siswa')
@section('title', 'Scan QR Code Presensi')

@section('content')
<div class="bg-primary pt-10 pb-21"></div>
<div class="container-fluid mt-n22 px-6">
    <div class="row">
        <div class="col-lg-12">
            <div class="mb-4">
                <h3 class="mb-0 text-white">Presensi dengan QR Code</h3>
                <p class="text-white-50 mb-0">Scan QR Code untuk melakukan presensi</p>
            </div>
        </div>
    </div>

    <div class="row mt-6">
        <div class="col-lg-8 mx-auto">
            
            {{-- Alert Status Presensi --}}
            @if($todayPresensi)
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-info-circle-fill fs-2 me-3"></i>
                    <div class="flex-grow-1">
                        <h5 class="alert-heading mb-1">Status Presensi Hari Ini</h5>
                        <p class="mb-2">
                            @if($todayPresensi->waktu_checkin)
                                <strong>✅ Check-In:</strong> {{ $todayPresensi->waktu_checkin->format('H:i:s') }}
                            @else
                                <strong>⏳ Check-In:</strong> Belum
                            @endif
                            
                            @if($todayPresensi->waktu_checkout)
                                | <strong>✅ Check-Out:</strong> {{ $todayPresensi->waktu_checkout->format('H:i:s') }}
                            @else
                                | <strong>⏳ Check-Out:</strong> Belum
                            @endif
                        </p>
                        
                        <small class="text-muted">
                            @if(!$todayPresensi->waktu_checkin)
                                <i class="bi bi-arrow-right-circle me-1"></i>Anda bisa scan QR Check-In
                            @elseif(!$todayPresensi->waktu_checkout)
                                <i class="bi bi-arrow-right-circle me-1"></i>Anda bisa scan QR Check-Out
                            @else
                                <i class="bi bi-check-circle me-1"></i>Presensi hari ini sudah lengkap
                            @endif
                        </small>
                        
                        {{-- Validasi lokasi --}}
                        @php
                            $hasInvalidCheckin = $todayPresensi->waktu_checkin && !$todayPresensi->is_valid_location_checkin;
                            $hasInvalidCheckout = $todayPresensi->waktu_checkout && !$todayPresensi->is_valid_location_checkout;
                        @endphp
                        
                        @if($hasInvalidCheckin || $hasInvalidCheckout)
                        <div class="mt-2">
                            <small class="text-warning">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Catatan: 
                                @if($hasInvalidCheckin)
                                    Check-in dilakukan di luar radius
                                @endif
                                @if($hasInvalidCheckin && $hasInvalidCheckout)
                                    dan 
                                @endif
                                @if($hasInvalidCheckout)
                                    Check-out dilakukan di luar radius
                                @endif
                            </small>
                        </div>
                        @endif
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="bi bi-qr-code-scan me-2 text-primary"></i>
                            Scanner QR Code
                        </h4>
                        <span class="badge bg-secondary" id="statusBadge">
                            <i class="bi bi-circle-fill me-1"></i>
                            Tidak Aktif
                        </span>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Scanner Container dengan style responsif -->
                    <div id="scanner-container" class="mb-4">
                        <div id="reader"></div>
                    </div>

                    <div class="alert alert-info mb-3">
                        <h6 class="alert-heading">
                            <i class="bi bi-info-circle me-2"></i>Cara Menggunakan:
                        </h6>
                        <ol class="mb-0 ps-3">
                            <li>Klik tombol "Mulai Scan" di bawah</li>
                            <li>Arahkan kamera ke QR Code yang ditampilkan oleh guru</li>
                            <li>Tunggu hingga QR Code terdeteksi secara otomatis</li>
                            <li>Izinkan akses lokasi untuk validasi kehadiran</li>
                        </ol>
                    </div>

                    <div id="scan-result" class="alert" style="display: none;"></div>

                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-primary btn-lg" id="startScanBtn">
                            <i class="bi bi-camera me-2"></i>Mulai Scan
                        </button>
                        <button type="button" class="btn btn-danger btn-lg" id="stopScanBtn" style="display: none;">
                            <i class="bi bi-stop-circle me-2"></i>Hentikan Scan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden CSRF Token -->
<input type="hidden" id="csrf-token-input" value="{{ csrf_token() }}">

<!-- Custom CSS untuk Scanner Mobile-Friendly -->
<style>
    /* Scanner Container Base */
    #scanner-container {
        position: relative;
        width: 100%;
        max-width: 100%;
        margin: 0 auto;
    }

    #reader {
        border-radius: 12px;
        overflow: hidden;
        border: 3px solid #dee2e6;
        background: #000;
        position: relative;
    }

    /* Video element styling */
    #reader video {
        width: 100% !important;
        height: auto !important;
        border-radius: 8px;
    }

    /* Canvas styling */
    #reader canvas {
        width: 100% !important;
        height: auto !important;
    }

    /* Desktop & Tablet (landscape) */
    @media (min-width: 768px) {
        #reader {
            min-height: 400px;
        }
    }

    /* Mobile Portrait - Kotak scan lebih besar dan proporsional */
    @media (max-width: 767px) {
        #scanner-container {
            padding: 0;
        }

        #reader {
            min-height: 320px;
            border-radius: 16px;
            border-width: 2px;
        }

        #reader video {
            border-radius: 14px;
        }
    }

    /* Small Mobile */
    @media (max-width: 375px) {
        #reader {
            min-height: 280px;
            border-radius: 12px;
        }
    }

    /* Scanning indicator */
    #reader.scanning::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 80%;
        max-width: 280px;
        height: 80%;
        max-height: 280px;
        border: 3px solid #0d6efd;
        border-radius: 16px;
        box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
        pointer-events: none;
        animation: scanPulse 2s ease-in-out infinite;
    }

    @keyframes scanPulse {
        0%, 100% {
            opacity: 0.6;
            transform: translate(-50%, -50%) scale(0.95);
        }
        50% {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1);
        }
    }

    /* Status badge mobile */
    @media (max-width: 576px) {
        #statusBadge {
            font-size: 0.75rem;
        }
    }
</style>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
(function() {
    let scanner = null;
    let isScanning = false;
    
    function getCSRFToken() {
        let token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!token) {
            token = document.getElementById('csrf-token-input')?.value;
        }
        if (!token && typeof Laravel !== 'undefined' && Laravel.csrfToken) {
            token = Laravel.csrfToken;
        }
        return token || '';
    }
    
    const csrfToken = getCSRFToken();
    const validateRoute = '{{ route("siswa.presensi.validate") }}';
    const submitRoute = '{{ route("siswa.presensi.submit") }}';
    
    if (!csrfToken) {
        setTimeout(() => {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error Configuration',
                    text: 'CSRF Token tidak ditemukan. Silakan refresh halaman.',
                    confirmButtonText: 'Refresh',
                    allowOutsideClick: false
                }).then(() => {
                    window.location.reload();
                });
            }
        }, 1000);
        return;
    }
    
    function calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371000;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLon / 2) * Math.sin(dLon / 2);
        
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        const distance = R * c;
        
        return Math.round(distance);
    }
    
    function checkFakeGPS(position) {
        const accuracy = position.coords.accuracy;
        let warnings = [];
        
        if (accuracy !== null && accuracy < 3) {
            warnings.push('GPS accuracy terlalu sempurna (' + accuracy + 'm)');
        }
        
        if (position.coords.altitude === null || position.coords.altitude === 0) {
            warnings.push('Data altitude tidak valid');
        }
        
        if (position.coords.isMock === true || position.mocked === true) {
            warnings.push('GPS Mock terdeteksi oleh sistem');
        }
        
        return {
            isSuspicious: warnings.length >= 2,
            warnings: warnings,
            accuracy: accuracy
        };
    }
    
    function getResponsiveQRBox() {
        const width = window.innerWidth;
        
        if (width < 576) {
            return { width: Math.min(width * 0.75, 250), height: Math.min(width * 0.75, 250) };
        }
        else if (width < 768) {
            return { width: 280, height: 280 };
        }
        else {
            return { width: 300, height: 300 };
        }
    }
    
    function init() {
        const startBtn = document.getElementById('startScanBtn');
        const stopBtn = document.getElementById('stopScanBtn');
        
        if (startBtn) {
            startBtn.addEventListener('click', function() {
                if (isScanning) return;
                startScanning();
            });
        }
        
        if (stopBtn) {
            stopBtn.addEventListener('click', function() {
                stopScanning();
            });
        }
    }
    
    function startScanning() {
        const startBtn = document.getElementById('startScanBtn');
        const readerElement = document.getElementById('reader');
        
        startBtn.disabled = true;
        startBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memulai...';
        
        if (!scanner) {
            scanner = new Html5Qrcode("reader");
        }
        
        const qrBox = getResponsiveQRBox();
        
        scanner.start(
            { facingMode: "environment" },
            { 
                fps: 10, 
                qrbox: qrBox,
                aspectRatio: 1.0
            },
            onScanSuccess,
            onScanError
        ).then(() => {
            isScanning = true;
            readerElement.classList.add('scanning');
            updateUI(true);
        }).catch(err => {
            startBtn.disabled = false;
            startBtn.innerHTML = '<i class="bi bi-camera me-2"></i>Mulai Scan';
            showAlert('error', 'Gagal Memulai Kamera', err.message);
        });
    }
    
    function stopScanning() {
        if (!isScanning || !scanner) return;
        
        const readerElement = document.getElementById('reader');
        
        scanner.stop().then(() => {
            isScanning = false;
            readerElement.classList.remove('scanning');
            updateUI(false);
        }).catch(err => {
            isScanning = false;
            readerElement.classList.remove('scanning');
            updateUI(false);
        });
    }
    
    function onScanSuccess(decodedText) {
        stopScanning();
        
        let qrCode = decodedText.trim();
        
        try {
            if (decodedText.includes('http://') || decodedText.includes('https://')) {
                const url = new URL(decodedText);
                const pathParts = url.pathname.split('/');
                qrCode = pathParts[pathParts.length - 1];
            } else if (decodedText.includes('/')) {
                const parts = decodedText.split('/');
                qrCode = parts[parts.length - 1];
            }
        } catch (e) {
        }
        
        if (!qrCode || qrCode.length < 10) {
            showAlert('error', 'QR Code Tidak Valid', 'Format QR Code salah');
            return;
        }
        
        showLoading('Memvalidasi QR Code...');
        validateQRCode(qrCode);
    }
    
    function onScanError(error) {
    }
    
    function validateQRCode(qrCode) {
        const payload = { qr_code: qrCode };
        
        const headers = {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        };
        
        fetch(validateRoute, {
            method: 'POST',
            headers: headers,
            body: JSON.stringify(payload),
            credentials: 'same-origin'
        })
        .then(response => {
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    throw new Error('Invalid JSON response: ' + text.substring(0, 200));
                }
            });
        })
        .then(data => {
            if (data.success) {
                window.sessionData = data.data;
                requestLocationAndSubmit(data.data);
            } else {
                throw new Error(data.message || 'QR Code tidak valid');
            }
        })
        .catch(error => {
            showAlert('error', 'Validasi Gagal', error.message);
        });
    }
    
    function requestLocationAndSubmit(sessionData) {
        if (!navigator.geolocation) {
            showAlert('error', 'Error', 'Browser tidak mendukung geolocation');
            return;
        }
        
        showLoading('Mengambil lokasi Anda...');
        
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const gpsCheck = checkFakeGPS(position);
                
                if (gpsCheck.isSuspicious) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'GPS Mencurigakan!',
                        html: `
                            <p class="mb-3">Sistem mendeteksi kemungkinan Fake GPS.</p>
                            <div class="alert alert-warning text-start mb-0">
                                <strong>Peringatan:</strong>
                                <ul class="mb-0 mt-2 small">
                                    ${gpsCheck.warnings.map(w => `<li>${w}</li>`).join('')}
                                </ul>
                            </div>
                            <p class="mt-3 text-muted small">Matikan aplikasi Fake GPS dan coba lagi dengan lokasi asli.</p>
                        `,
                        confirmButtonColor: '#dc3545',
                        confirmButtonText: 'OK, Saya Mengerti'
                    });
                    return;
                }
                
                const userLat = position.coords.latitude;
                const userLng = position.coords.longitude;
                const sessionLat = sessionData.latitude;
                const sessionLng = sessionData.longitude;
                const allowedRadius = sessionData.radius;
                
                const distance = calculateDistance(userLat, userLng, sessionLat, sessionLng);
                
                if (distance > allowedRadius) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Lokasi Terlalu Jauh!',
                        html: `
                            <p class="mb-3">Anda berada di luar radius yang diizinkan.</p>
                            <div class="alert alert-danger text-start mb-0">
                                <div class="mb-2"><strong>Jarak Anda:</strong> ${distance} meter</div>
                                <div class="mb-2"><strong>Radius Maksimal:</strong> ${allowedRadius} meter</div>
                                <div><strong>Selisih:</strong> ${distance - allowedRadius} meter</div>
                            </div>
                            <p class="mt-3 text-muted small">Datang lebih dekat ke lokasi presensi.</p>
                        `,
                        confirmButtonColor: '#dc3545',
                        confirmButtonText: 'OK, Saya Mengerti'
                    });
                    return;
                }
                
                submitPresensi(sessionData.session_id, userLat, userLng, distance, gpsCheck.accuracy);
            },
            (error) => {
                let msg = 'Gagal mengambil lokasi';
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        msg = 'Izin lokasi ditolak. Mohon izinkan akses lokasi.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        msg = 'Informasi lokasi tidak tersedia. Pastikan GPS aktif.';
                        break;
                    case error.TIMEOUT:
                        msg = 'Request timeout. Pastikan GPS aktif.';
                        break;
                }
                
                showAlert('error', 'Gagal Mengambil Lokasi', msg);
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    }
    
    function submitPresensi(sessionId, lat, lng, distance, gpsAccuracy) {
        const sessionData = window.sessionData;
        
        if (!sessionData) {
            showAlert('error', 'Error', 'Data sesi tidak ditemukan. Silakan scan ulang.');
            return;
        }
        
        const payload = {
            qr_code_id: sessionData.qr_code_id,
            session_id: sessionId,
            type: sessionData.type,
            latitude: lat,
            longitude: lng,
            distance: distance,
            gps_accuracy: gpsAccuracy
        };
        
        fetch(submitRoute, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload),
            credentials: 'same-origin'
        })
        .then(response => {
            return response.text().then(text => {
                return JSON.parse(text);
            });
        })
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Presensi Berhasil!',
                    html: `
                        <p class="mb-3">${data.message}</p>
                        <div class="alert alert-info mb-0 text-start">
                            <div class="mb-2"><strong>Status:</strong> ${data.data.status}</div>
                            <div class="mb-2"><strong>Waktu:</strong> ${data.data.waktu}</div>
                            <div class="mb-2"><strong>Tanggal:</strong> ${data.data.tanggal}</div>
                            <div><strong>Jarak:</strong> ${distance} meter</div>
                        </div>
                    `,
                    confirmButtonColor: '#198754',
                    allowOutsideClick: false
                }).then(() => window.location.reload());
            } else {
                throw new Error(data.message || 'Gagal menyimpan presensi');
            }
        })
        .catch(error => {
            showAlert('error', 'Gagal Menyimpan Presensi', error.message);
        });
    }
    
    function updateUI(scanning) {
        const startBtn = document.getElementById('startScanBtn');
        const stopBtn = document.getElementById('stopScanBtn');
        const statusBadge = document.getElementById('statusBadge');
        
        if (scanning) {
            startBtn.style.display = 'none';
            stopBtn.style.display = 'block';
            statusBadge.innerHTML = '<i class="bi bi-circle-fill text-success me-1"></i>Sedang Memindai';
            statusBadge.className = 'badge bg-success';
        } else {
            startBtn.style.display = 'block';
            startBtn.disabled = false;
            startBtn.innerHTML = '<i class="bi bi-camera me-2"></i>Mulai Scan';
            stopBtn.style.display = 'none';
            statusBadge.innerHTML = '<i class="bi bi-circle-fill text-secondary me-1"></i>Tidak Aktif';
            statusBadge.className = 'badge bg-secondary';
        }
    }
    
    function showLoading(message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: message,
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
        }
    }
    
    function showAlert(icon, title, text) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: icon,
                title: title,
                text: text,
                confirmButtonColor: icon === 'success' ? '#198754' : (icon === 'error' ? '#dc3545' : '#0d6efd')
            });
        } else {
            alert(title + '\n' + text);
        }
    }
    
    let resizeTimer;
    window.addEventListener('resize', function() {
        if (!isScanning) return;
        
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            if (scanner && isScanning) {
                stopScanning();
                setTimeout(() => startScanning(), 500);
            }
        }, 250);
    });
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>
@endsection
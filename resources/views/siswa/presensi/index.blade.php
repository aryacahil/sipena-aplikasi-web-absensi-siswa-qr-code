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
            
            @if($todayPresensi)
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-check-circle-fill fs-2 me-3"></i>
                    <div class="flex-grow-1">
                        <h5 class="alert-heading mb-1">Anda Sudah Presensi Hari Ini!</h5>
                        <p class="mb-2">
                            <strong>Status:</strong> {{ ucfirst($todayPresensi->status) }} | 
                            <strong>Waktu:</strong> {{ $todayPresensi->created_at->format('H:i:s') }} |
                            <strong>Metode:</strong> {{ $todayPresensi->metode == 'qr' ? 'QR Code' : 'Manual' }}
                        </p>
                        @if(!$todayPresensi->is_valid_location)
                        <small class="text-warning">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            Catatan: Lokasi di luar radius yang ditentukan
                        </small>
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
                    <div id="scanner-container" class="mb-4">
                        <div id="reader" style="border-radius: 8px; overflow: hidden; border: 2px solid #dee2e6;"></div>
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
                        
                        <!-- TEST BUTTON -->
                        <div class="mt-3">
                            <button type="button" class="btn btn-warning w-100" id="testQRBtn">
                                <i class="bi bi-bug me-2"></i>Test dengan QR Code Manual
                            </button>
                            <small class="text-muted d-block mt-2 text-center">
                                Untuk testing, gunakan: <code>IojRZFbqsmCJEi9HLNKqND4Vx0rlhFjc</code>
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            @if(Auth::user()->kelas)
            <div class="card shadow-sm mt-4">
                <div class="card-body">
                    <h6 class="mb-3">
                        <i class="bi bi-info-circle me-2"></i>Informasi Anda
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <small class="text-muted d-block">Nama</small>
                            <strong>{{ Auth::user()->name }}</strong>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Kelas</small>
                            <strong>{{ Auth::user()->kelas->nama_kelas }}</strong>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Jurusan</small>
                            <strong>{{ Auth::user()->kelas->jurusan->nama_jurusan }}</strong>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Tanggal</small>
                            <strong>{{ now()->format('d M Y') }}</strong>
                        </div>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>

<!-- Hidden CSRF Token -->
<input type="hidden" id="csrf-token-input" value="{{ csrf_token() }}">

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
(function() {
    console.log('%c=== QR SCANNER SCRIPT LOADED ===', 'color: green; font-size: 16px; font-weight: bold;');
    
    let scanner = null;
    let isScanning = false;
    const alreadyAttended = {{ $todayPresensi ? 'true' : 'false' }};
    
    // Get CSRF Token with multiple fallbacks
    function getCSRFToken() {
        console.log('Getting CSRF token...');
        
        // Try meta tag
        let token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        console.log('From meta tag:', token || 'not found');
        
        // Try hidden input
        if (!token) {
            token = document.getElementById('csrf-token-input')?.value;
            console.log('From hidden input:', token || 'not found');
        }
        
        // Try Laravel global
        if (!token && typeof Laravel !== 'undefined' && Laravel.csrfToken) {
            token = Laravel.csrfToken;
            console.log('From Laravel global:', token || 'not found');
        }
        
        console.log('Final CSRF Token:', token ? '✓ Found' : '✗ NOT FOUND');
        if (token) {
            console.log('Token length:', token.length);
            console.log('Token preview:', token.substring(0, 10) + '...');
        }
        
        return token || '';
    }
    
    const csrfToken = getCSRFToken();
    const validateRoute = '{{ route("siswa.presensi.validate") }}';
    const submitRoute = '{{ route("siswa.presensi.submit") }}';
    
    console.log('Config:', {
        alreadyAttended: alreadyAttended,
        csrfTokenFound: !!csrfToken,
        validateRoute: validateRoute,
        submitRoute: submitRoute
    });
    
    // Cek CSRF token availability
    if (!csrfToken) {
        console.error('%c✗ CSRF TOKEN NOT FOUND!', 'color: red; font-size: 16px; font-weight: bold;');
        console.error('This will cause validation to fail!');
        
        // Show alert to user
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
    
    function init() {
        console.log('Initializing...');
        
        const startBtn = document.getElementById('startScanBtn');
        const stopBtn = document.getElementById('stopScanBtn');
        const testQRBtn = document.getElementById('testQRBtn');
        
        if (startBtn) {
            startBtn.addEventListener('click', function() {
                console.log('>>> START SCAN CLICKED <<<');
                if (isScanning) return;
                if (alreadyAttended) {
                    showAlert('info', 'Sudah Presensi', 'Anda sudah presensi hari ini');
                    return;
                }
                startScanning();
            });
        }
        
        if (stopBtn) {
            stopBtn.addEventListener('click', function() {
                console.log('>>> STOP SCAN CLICKED <<<');
                stopScanning();
            });
        }
        
        if (testQRBtn) {
            testQRBtn.addEventListener('click', function() {
                console.log('%c>>> TEST QR CLICKED <<<', 'color: orange; font-size: 16px; font-weight: bold;');
                
                if (alreadyAttended) {
                    showAlert('info', 'Sudah Presensi', 'Anda sudah presensi hari ini');
                    return;
                }
                
                const testCode = prompt('Masukkan QR Code:\n\n(Default: IojRZFbqsmCJEi9HLNKqND4Vx0rlhFjc)', 'IojRZFbqsmCJEi9HLNKqND4Vx0rlhFjc');
                
                if (testCode && testCode.trim()) {
                    console.log('%cTesting with QR:', 'color: yellow; font-weight: bold;', testCode.trim());
                    showLoading('Testing validasi QR Code...');
                    validateQRCode(testCode.trim());
                }
            });
        }
        
        console.log('✓ Initialization complete');
    }
    
    function startScanning() {
        console.log('Starting camera...');
        
        const startBtn = document.getElementById('startScanBtn');
        startBtn.disabled = true;
        startBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memulai...';
        
        if (!scanner) {
            scanner = new Html5Qrcode("reader");
        }
        
        scanner.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: { width: 250, height: 250 } },
            onScanSuccess,
            onScanError
        ).then(() => {
            console.log('✓ Camera started');
            isScanning = true;
            updateUI(true);
        }).catch(err => {
            console.error('✗ Camera error:', err);
            startBtn.disabled = false;
            startBtn.innerHTML = '<i class="bi bi-camera me-2"></i>Mulai Scan';
            showAlert('error', 'Gagal Memulai Kamera', err.message);
        });
    }
    
    function stopScanning() {
        if (!isScanning || !scanner) return;
        
        scanner.stop().then(() => {
            console.log('✓ Scanner stopped');
            isScanning = false;
            updateUI(false);
        }).catch(err => {
            console.error('Stop error:', err);
            isScanning = false;
            updateUI(false);
        });
    }
    
    function onScanSuccess(decodedText) {
        console.log('%c========= QR DETECTED =========', 'color: orange; font-size: 16px; font-weight: bold;');
        console.log('Raw QR:', decodedText);
        
        stopScanning();
        
        let qrCode = decodedText;
        try {
            const url = new URL(decodedText);
            qrCode = url.pathname.split('/').pop();
            console.log('Extracted from URL:', qrCode);
        } catch (e) {
            console.log('Not a URL, using as is');
        }
        
        console.log('%cFinal QR Code:', 'color: yellow; font-weight: bold;', qrCode);
        
        showLoading('Memvalidasi QR Code...');
        validateQRCode(qrCode);
    }
    
    function onScanError(error) {
        // Silent
    }
    
    function validateQRCode(qrCode) {
        console.log('%c>>> VALIDATING QR CODE <<<', 'color: cyan; font-size: 14px; font-weight: bold;');
        console.log('QR Code:', qrCode);
        console.log('Route:', validateRoute);
        console.log('CSRF Token:', csrfToken);
        console.log('CSRF Token length:', csrfToken.length);
        
        const payload = { qr_code: qrCode };
        console.log('Payload:', payload);
        
        const headers = {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        };
        console.log('Request headers:', headers);
        
        fetch(validateRoute, {
            method: 'POST',
            headers: headers,
            body: JSON.stringify(payload),
            credentials: 'same-origin'
        })
        .then(response => {
            console.log('%c>>> RESPONSE RECEIVED <<<', 'color: magenta; font-weight: bold;');
            console.log('Status:', response.status);
            console.log('Status Text:', response.statusText);
            console.log('OK:', response.ok);
            console.log('Headers:', Object.fromEntries(response.headers.entries()));
            
            return response.text().then(text => {
                console.log('Raw response text:', text);
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('JSON parse error:', e);
                    throw new Error('Invalid JSON response: ' + text.substring(0, 200));
                }
            });
        })
        .then(data => {
            console.log('%c✓ VALIDATION RESPONSE:', 'color: lime; font-weight: bold;');
            console.log(data);
            
            if (data.success) {
                console.log('%c✓ QR Valid! Getting location...', 'color: lime;');
                requestLocationAndSubmit(data.data);
            } else {
                console.warn('%c✗ QR Invalid:', 'color: orange;', data.message);
                throw new Error(data.message || 'QR Code tidak valid');
            }
        })
        .catch(error => {
            console.error('%c✗ VALIDATION ERROR:', 'color: red; font-weight: bold;');
            console.error('Error type:', error.name);
            console.error('Error message:', error.message);
            console.error('Error stack:', error.stack);
            showAlert('error', 'Validasi Gagal', error.message);
        });
    }
    
    function requestLocationAndSubmit(sessionData) {
        console.log('%c>>> REQUESTING LOCATION <<<', 'color: purple; font-weight: bold;');
        console.log('Session data:', sessionData);
        
        if (!navigator.geolocation) {
            showAlert('error', 'Error', 'Browser tidak mendukung geolocation');
            return;
        }
        
        showLoading('Mengambil lokasi Anda...');
        
        navigator.geolocation.getCurrentPosition(
            (position) => {
                console.log('%c✓ LOCATION OBTAINED:', 'color: lime; font-weight: bold;');
                console.log('Latitude:', position.coords.latitude);
                console.log('Longitude:', position.coords.longitude);
                console.log('Accuracy:', position.coords.accuracy, 'meters');
                
                submitPresensi(sessionData.session_id, position.coords.latitude, position.coords.longitude);
            },
            (error) => {
                console.error('%c✗ LOCATION ERROR:', 'color: red; font-weight: bold;');
                console.error('Code:', error.code);
                console.error('Message:', error.message);
                
                let msg = 'Gagal mengambil lokasi';
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        msg = 'Izin lokasi ditolak. Mohon izinkan akses lokasi.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        msg = 'Informasi lokasi tidak tersedia.';
                        break;
                    case error.TIMEOUT:
                        msg = 'Request timeout.';
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
    
    function submitPresensi(sessionId, lat, lng) {
        console.log('%c>>> SUBMITTING PRESENSI <<<', 'color: blue; font-weight: bold;');
        console.log('Session ID:', sessionId);
        console.log('Latitude:', lat);
        console.log('Longitude:', lng);
        console.log('CSRF Token:', csrfToken);
        
        const payload = {
            session_id: sessionId,
            latitude: lat,
            longitude: lng
        };
        console.log('Payload:', payload);
        
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
            console.log('%c>>> SUBMIT RESPONSE <<<', 'color: magenta; font-weight: bold;');
            console.log('Status:', response.status);
            
            return response.text().then(text => {
                console.log('Raw response:', text);
                return JSON.parse(text);
            });
        })
        .then(data => {
            console.log('%c✓ SUBMIT RESPONSE DATA:', 'color: lime; font-weight: bold;');
            console.log(data);
            
            if (data.success) {
                const locationWarning = !data.data.is_valid_location ? 
                    '<p class="text-warning mt-2 mb-0 small"><i class="bi bi-exclamation-triangle me-1"></i>Lokasi di luar radius</p>' : '';
                
                Swal.fire({
                    icon: 'success',
                    title: 'Presensi Berhasil!',
                    html: `
                        <p class="mb-3">${data.message}</p>
                        <div class="alert alert-info mb-0 text-start">
                            <div class="mb-2"><strong>Status:</strong> ${data.data.status}</div>
                            <div class="mb-2"><strong>Waktu:</strong> ${data.data.waktu}</div>
                            <div><strong>Tanggal:</strong> ${data.data.tanggal}</div>
                        </div>
                        ${locationWarning}
                    `,
                    confirmButtonColor: '#198754',
                    allowOutsideClick: false
                }).then(() => window.location.reload());
            } else {
                throw new Error(data.message || 'Gagal menyimpan presensi');
            }
        })
        .catch(error => {
            console.error('%c✗ SUBMIT ERROR:', 'color: red; font-weight: bold;');
            console.error(error);
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
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    console.log('%c=== READY ===', 'color: blue; font-size: 14px;');
    
})();
</script>
@endsection
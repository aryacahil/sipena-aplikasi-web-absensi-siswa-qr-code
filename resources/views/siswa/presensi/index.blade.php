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
                        <span class="badge bg-primary" id="statusBadge">Siap Scan</span>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Info Siswa -->
                    <div class="alert alert-info mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="mb-2">
                                    <i class="bi bi-person me-2"></i>{{ Auth::user()->name }}
                                </h6>
                                @if(Auth::user()->kelas)
                                    <small class="text-muted">{{ Auth::user()->kelas->nama_kelas }}</small>
                                @else
                                    <small class="text-warning">Belum ada kelas</small>
                                @endif
                            </div>
                            <div class="col-md-6 text-md-end">
                                <h6 class="mb-2">
                                    <i class="bi bi-calendar3 me-2"></i>{{ now()->format('d M Y') }}
                                </h6>
                                <small class="text-muted">{{ now()->format('H:i') }} WIB</small>
                            </div>
                        </div>
                    </div>

                    <!-- Scanner Container -->
<div id="scanner-container" class="mb-4 position-relative" style="min-height: 400px;">
    <!-- Html5-QRCode will inject here -->
    <div id="loading-indicator" class="text-center py-5">
        <div class="spinner-border text-primary mb-3" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="text-muted">Siap untuk memulai scanning...</p>
    </div>
</div>

<!-- Tambahkan instruksi scanning -->
<div class="alert alert-success mb-4" id="scan-tips" style="display: none;">
    <i class="bi bi-lightbulb me-2"></i>
    <strong>Tips Scanning:</strong>
    <ul class="mb-0 mt-2">
        <li>Tahan HP stabil dan jangan bergerak</li>
        <li>Pastikan QR Code berada di dalam kotak hijau</li>
        <li>Jaga jarak 15-30 cm dari QR Code</li>
        <li>Pastikan pencahayaan cukup terang</li>
        <li>QR Code harus terlihat jelas, tidak blur</li>
    </ul>
</div>

                    <!-- Tombol Kontrol -->
                    <div class="d-flex gap-2 justify-content-center mb-4">
                        <button type="button" id="btnStartScan" class="btn btn-success btn-lg">
                            <i class="bi bi-play-fill me-2"></i>Mulai Scan
                        </button>
                        <button type="button" id="btnStopScan" class="btn btn-danger btn-lg" style="display: none;">
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
                            <li>Anda harus berada dalam radius yang ditentukan</li>
                            <li>Presensi hanya bisa dilakukan sekali per hari</li>
                            <li>Scan QR Code yang ditampilkan oleh guru/admin</li>
                        </ul>
                    </div>
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
                <a href="{{ route('siswa.home') }}" class="btn btn-primary">Kembali ke Dashboard</a>
            </div>
        </div>
    </div>
</div>

<style>
<style>
#scanner-container {
    width: 100%;
    max-width: 600px;
    margin: 0 auto;
    border-radius: 8px;
    overflow: hidden;
    background: #000;
    position: relative;
}

#scanner-container video {
    width: 100% !important;
    height: auto !important;
    border-radius: 8px;
    display: block;
}

/* Styling for Html5Qrcode */
#scanner-container #html5-qrcode-button-camera-permission,
#scanner-container #html5-qrcode-anchor-scan-type-change {
    display: none !important;
}

#scanner-container #html5qr-code-full-region {
    background: #000;
    border-radius: 8px;
}

#scanner-container #html5qr-code-full-region__scan_region {
    border: 3px solid #28a745 !important;
    box-shadow: 0 0 10px rgba(40, 167, 69, 0.5);
}

#scanner-container #html5qr-code-full-region__scan_region video {
    border-radius: 8px;
}

/* Make scan region more visible */
#scanner-container #html5qr-code-full-region__dashboard {
    display: none !important;
}

/* Loading state */
#loading-indicator {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 1;
    background: rgba(255, 255, 255, 0.9);
    padding: 20px;
    border-radius: 8px;
    width: 80%;
}

/* Ensure full width on mobile */
@media (max-width: 768px) {
    #scanner-container {
        max-width: 100%;
    }
}

/* Add pulse animation to scan button */
@keyframes pulse {
    0% {
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
    }
    70% {
        transform: scale(1.05);
        box-shadow: 0 0 0 10px rgba(40, 167, 69, 0);
    }
    100% {
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
    }
}

#btnStartScan {
    animation: pulse 2s infinite;
}

#btnStopScan {
    animation: none;
}
</style>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('QR Scanner initialized');
    
    const csrfToken = '{{ csrf_token() }}';
    
    let html5QrCode = null;
    let currentLocation = null;
    let isProcessing = false;
    let isScanning = false;
    
    const btnStart = document.getElementById('btnStartScan');
    const btnStop = document.getElementById('btnStopScan');
    const statusMessage = document.getElementById('status-message');
    const statusText = document.getElementById('status-text');
    const statusBadge = document.getElementById('statusBadge');
    
    // PERBAIKAN 1: Check camera availability terlebih dahulu
    async function checkCameraAvailability() {
        try {
            const devices = await navigator.mediaDevices.enumerateDevices();
            const videoDevices = devices.filter(device => device.kind === 'videoinput');
            
            console.log('Available cameras:', videoDevices);
            
            if (videoDevices.length === 0) {
                throw new Error('Tidak ada kamera yang tersedia');
            }
            
            return videoDevices;
        } catch (error) {
            console.error('Error checking cameras:', error);
            throw error;
        }
    }
    
    // PERBAIKAN 2: Request camera permission explicitly
    async function requestCameraPermission() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ 
                video: { facingMode: 'environment' } 
            });
            
            // Stop the stream immediately, we just needed permission
            stream.getTracks().forEach(track => track.stop());
            
            console.log('Camera permission granted');
            return true;
        } catch (error) {
            console.error('Camera permission denied:', error);
            return false;
        }
    }
    
    // Start button click handler
    btnStart.addEventListener('click', async function() {
        console.log('Start button clicked');
        
        // Check geolocation support
        if (!navigator.geolocation) {
            Swal.fire({
                icon: 'error',
                title: 'Geolocation Tidak Didukung',
                text: 'Browser Anda tidak mendukung fitur lokasi'
            });
            return;
        }
        
        // PERBAIKAN 3: Check camera availability first
        try {
            await checkCameraAvailability();
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Kamera Tidak Tersedia',
                text: 'Tidak ada kamera yang terdeteksi di perangkat Anda'
            });
            return;
        }
        
        // PERBAIKAN 4: Request permission explicitly
        showStatus('Meminta izin kamera...');
        const hasPermission = await requestCameraPermission();
        
        if (!hasPermission) {
            hideStatus();
            Swal.fire({
                icon: 'error',
                title: 'Izin Kamera Ditolak',
                html: `
                    <p>Silakan izinkan akses kamera dengan cara:</p>
                    <ol class="text-start">
                        <li>Klik ikon gembok/info di address bar</li>
                        <li>Pilih "Site settings" atau "Pengaturan situs"</li>
                        <li>Ubah izin Camera menjadi "Allow"</li>
                        <li>Muat ulang halaman ini</li>
                    </ol>
                `
            });
            return;
        }
        
        // Get location
        showStatus('Mengambil lokasi Anda...');
        statusBadge.textContent = 'Mengambil Lokasi...';
        statusBadge.className = 'badge bg-warning';
        
        navigator.geolocation.getCurrentPosition(
            function(position) {
                currentLocation = {
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude
                };
                
                console.log('Lokasi didapat:', currentLocation);
                hideStatus();
                
                // Start camera after getting location
                startCamera();
            },
            function(error) {
                hideStatus();
                statusBadge.textContent = 'Error Lokasi';
                statusBadge.className = 'badge bg-danger';
                
                let errorMessage = 'Gagal mengambil lokasi';
                
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        errorMessage = 'Akses lokasi ditolak. Silakan izinkan akses lokasi di pengaturan browser.';
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
    
    // PERBAIKAN 5: Improve camera initialization
    async function startCamera() {
        console.log('Starting camera...');
        
        if (isScanning) {
            console.log('Already scanning');
            return;
        }
        
        try {
            // Initialize Html5Qrcode if not exists
            if (!html5QrCode) {
                html5QrCode = new Html5Qrcode("scanner-container");
            }
            
            showStatus('Mengaktifkan kamera...');
            
            // PERBAIKAN 6: Better camera config - more aggressive scanning
            const config = {
                fps: 30, // Increase FPS for better detection
                qrbox: function(viewfinderWidth, viewfinderHeight) {
                    // Make scan box larger and responsive
                    let minEdgeSize = Math.min(viewfinderWidth, viewfinderHeight);
                    let qrboxSize = Math.floor(minEdgeSize * 0.7);
                    return {
                        width: qrboxSize,
                        height: qrboxSize
                    };
                },
                aspectRatio: 1.0,
                videoConstraints: {
                    facingMode: { ideal: "environment" },
                    width: { ideal: 1920 },
                    height: { ideal: 1080 }
                },
                // Enable experimental features for better detection
                experimentalFeatures: {
                    useBarCodeDetectorIfSupported: true
                },
                rememberLastUsedCamera: true,
                showTorchButtonIfSupported: true
            };
            
            // Get available cameras
            const cameras = await Html5Qrcode.getCameras();
            console.log('Available cameras:', cameras);
            
            if (cameras.length === 0) {
                throw new Error('Tidak ada kamera yang tersedia');
            }
            
            // Use back camera if available, otherwise use first camera
            let cameraId = cameras[0].id;
            
            // Try to find back camera
            const backCamera = cameras.find(camera => 
                camera.label.toLowerCase().includes('back') || 
                camera.label.toLowerCase().includes('rear') ||
                camera.label.toLowerCase().includes('belakang')
            );
            
            if (backCamera) {
                cameraId = backCamera.id;
                console.log('Using back camera:', backCamera.label);
            } else {
                console.log('Using default camera:', cameras[0].label);
            }
            
            // Start scanning with specific camera
            await html5QrCode.start(
                cameraId,
                config,
                (decodedText, decodedResult) => {
                    console.log('✅ QR Code detected:', decodedText);
                    console.log('QR Result:', decodedResult);
                    
                    // Play beep sound on successful scan
                    playBeep();
                    
                    onScanSuccess(decodedText);
                },
                (errorMessage) => {
                    // Log scan attempts every 2 seconds to debug
                    if (!window.lastScanLog || Date.now() - window.lastScanLog > 2000) {
                        console.log('Scanning... (looking for QR code)');
                        window.lastScanLog = Date.now();
                    }
                }
            );
            
            isScanning = true;
            btnStart.style.display = 'none';
            btnStop.style.display = 'inline-block';
            statusBadge.textContent = 'Scanning...';
            statusBadge.className = 'badge bg-success';
            hideStatus();
            
            // Show scanning tips
            const scanTips = document.getElementById('scan-tips');
            if (scanTips) {
                scanTips.style.display = 'block';
            }
            
            // Hide loading indicator
            const loadingIndicator = document.getElementById('loading-indicator');
            if (loadingIndicator) {
                loadingIndicator.style.display = 'none';
            }
            
            console.log('Camera started successfully');
            
        } catch (err) {
            console.error('Error starting camera:', err);
            hideStatus();
            statusBadge.textContent = 'Error';
            statusBadge.className = 'badge bg-danger';
            
            let errorMsg = 'Gagal mengaktifkan kamera';
            let errorDetail = err.toString();
            
            if (errorDetail.includes('NotAllowedError') || errorDetail.includes('Permission')) {
                errorMsg = 'Akses kamera ditolak. Silakan izinkan akses kamera di pengaturan browser.';
            } else if (errorDetail.includes('NotFoundError')) {
                errorMsg = 'Kamera tidak ditemukan di perangkat Anda.';
            } else if (errorDetail.includes('NotReadableError') || errorDetail.includes('Could not start video source')) {
                errorMsg = 'Kamera sedang digunakan aplikasi lain. Tutup aplikasi lain yang menggunakan kamera.';
            } else if (errorDetail.includes('OverconstrainedError')) {
                errorMsg = 'Kamera tidak mendukung konfigurasi yang diminta.';
            }
            
            Swal.fire({
                icon: 'error',
                title: 'Error Kamera',
                html: `
                    <p>${errorMsg}</p>
                    <small class="text-muted d-block mt-2">Detail: ${errorDetail}</small>
                    <div class="alert alert-info mt-3 text-start">
                        <strong>Solusi:</strong>
                        <ol class="mb-0 mt-2">
                            <li>Pastikan tidak ada aplikasi lain yang menggunakan kamera</li>
                            <li>Refresh/muat ulang halaman ini</li>
                            <li>Periksa pengaturan izin kamera di browser</li>
                            <li>Coba browser lain (Chrome/Firefox)</li>
                        </ol>
                    </div>
                `
            });
        }
    }
    
    // Stop scanning
    btnStop.addEventListener('click', async function() {
        console.log('Stop button clicked');
        
        if (html5QrCode && isScanning) {
            try {
                await html5QrCode.stop();
                isScanning = false;
                btnStart.style.display = 'inline-block';
                btnStop.style.display = 'none';
                statusBadge.textContent = 'Siap Scan';
                statusBadge.className = 'badge bg-primary';
                isProcessing = false;
                
                // Hide scanning tips
                const scanTips = document.getElementById('scan-tips');
                if (scanTips) {
                    scanTips.style.display = 'none';
                }
                
                // Show loading indicator again
                const loadingIndicator = document.getElementById('loading-indicator');
                if (loadingIndicator) {
                    loadingIndicator.style.display = 'block';
                }
                
                console.log('Camera stopped');
            } catch (err) {
                console.error('Error stopping camera:', err);
            }
        }
    });
    
    // Handle QR code scan success
    function onScanSuccess(decodedText) {
        if (isProcessing) {
            console.log('⚠️ Already processing, ignoring scan');
            return;
        }
        
        console.log('🎯 Processing QR Code:', decodedText);
        console.log('📍 Current Location:', currentLocation);
        
        isProcessing = true;
        
        // Stop scanner
        if (html5QrCode && isScanning) {
            html5QrCode.stop().then(() => {
                isScanning = false;
                btnStart.style.display = 'inline-block';
                btnStop.style.display = 'none';
            });
        }
        
        statusBadge.textContent = 'Memproses...';
        statusBadge.className = 'badge bg-info';
        
        // Check location
        if (!currentLocation) {
            isProcessing = false;
            statusBadge.textContent = 'Error';
            statusBadge.className = 'badge bg-danger';
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Lokasi belum diambil. Silakan coba lagi.'
            });
            return;
        }
        
        // Extract QR code from URL
        let qrCode = decodedText;
        
        if (decodedText.includes('/siswa/presensi/scan/')) {
            const urlParts = decodedText.split('/');
            qrCode = urlParts[urlParts.length - 1];
        }
        
        console.log('Extracted QR Code:', qrCode);
        
        // Submit presensi
        submitPresensi(qrCode);
    }
    
    // Submit presensi to server
    function submitPresensi(qrCode) {
        showStatus('Memproses presensi...');
        
        fetch('/siswa/presensi/submit', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                qr_code: qrCode,
                latitude: currentLocation.latitude,
                longitude: currentLocation.longitude
            })
        })
        .then(response => response.json())
        .then(data => {
            hideStatus();
            isProcessing = false;
            
            if (data.success) {
                statusBadge.textContent = 'Berhasil';
                statusBadge.className = 'badge bg-success';
                showSuccessModal(data.data);
            } else {
                statusBadge.textContent = 'Gagal';
                statusBadge.className = 'badge bg-danger';
                Swal.fire({
                    icon: 'error',
                    title: 'Presensi Gagal',
                    text: data.message || 'Terjadi kesalahan',
                    confirmButtonText: 'Coba Lagi'
                }).then(() => {
                    statusBadge.textContent = 'Siap Scan';
                    statusBadge.className = 'badge bg-primary';
                });
            }
        })
        .catch(error => {
            hideStatus();
            isProcessing = false;
            statusBadge.textContent = 'Error';
            statusBadge.className = 'badge bg-danger';
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Terjadi kesalahan saat memproses presensi'
            });
        });
    }
    
    // Show success modal
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
    }
    
    // Helper functions
    function showStatus(message) {
        statusText.textContent = message;
        statusMessage.style.display = 'block';
    }
    
    function hideStatus() {
        statusMessage.style.display = 'none';
    }
    
    // Beep sound for successful scan
    function playBeep() {
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            
            oscillator.frequency.value = 800;
            oscillator.type = 'sine';
            
            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.2);
            
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.2);
        } catch (e) {
            console.log('Beep sound not available');
        }
    }
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        if (html5QrCode && isScanning) {
            html5QrCode.stop();
        }
    });
});
</script>
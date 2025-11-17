// ==================== GLOBAL VARIABLES ====================
let map;
let marker;
let circle;
let csrfToken;
let baseRoute;

// ==================== DOM READY ====================
document.addEventListener('DOMContentLoaded', function() {
    // Get CSRF Token
    const tokenInput = document.querySelector('input[name="_token"]');
    csrfToken = tokenInput ? tokenInput.value : '';
    
    // Get Base Route (admin or guru)
    const baseRouteInput = document.getElementById('baseRoute');
    baseRoute = baseRouteInput ? baseRouteInput.value : 'admin';

    // Initialize all event listeners
    initializeModalEvents();
    initializeMapEvents();
    initializeQRActions();
    initializeFormValidation();
    initializeNotifications();
});

// ==================== MODAL EVENTS ====================
function initializeModalEvents() {
    const createQRModal = document.getElementById('createQRModal');
    
    if (createQRModal) {
        // Initialize map when modal shown
        createQRModal.addEventListener('shown.bs.modal', function() {
            if (!map) {
                setTimeout(() => {
                    initMap();
                }, 300);
            } else {
                map.invalidateSize();
            }
        });
        
        // Reset form when modal closed
        createQRModal.addEventListener('hidden.bs.modal', function() {
            resetCreateForm();
        });
    }
}

function resetCreateForm() {
    const form = document.getElementById('createQRForm');
    if (form) {
        form.reset();
    }
    
    // Hide search box
    const searchBox = document.getElementById('searchBox');
    if (searchBox) {
        searchBox.style.display = 'none';
    }
    
    // Remove map markers
    if (map) {
        if (marker) {
            map.removeLayer(marker);
            marker = null;
        }
        if (circle) {
            map.removeLayer(circle);
            circle = null;
        }
    }
    
    // Clear location inputs
    document.getElementById('latitude').value = '';
    document.getElementById('longitude').value = '';
}

// ==================== MAP EVENTS ====================
function initializeMapEvents() {
    const searchLocationBtn = document.getElementById('searchLocationBtn');
    if (searchLocationBtn) {
        searchLocationBtn.addEventListener('click', function() {
            const searchBox = document.getElementById('searchBox');
            searchBox.style.display = searchBox.style.display === 'none' ? 'block' : 'none';
        });
    }
    
    const searchAddressBtn = document.getElementById('searchAddressBtn');
    if (searchAddressBtn) {
        searchAddressBtn.addEventListener('click', function() {
            const address = document.getElementById('searchAddressInput').value;
            if (address) {
                searchAddress(address);
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan!',
                    text: 'Masukkan alamat yang ingin dicari',
                    confirmButtonColor: '#0d6efd'
                });
            }
        });
    }
    
    const searchAddressInput = document.getElementById('searchAddressInput');
    if (searchAddressInput) {
        searchAddressInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('searchAddressBtn').click();
            }
        });
    }
    
    const getLocationBtn = document.getElementById('getLocationBtn');
    if (getLocationBtn) {
        getLocationBtn.addEventListener('click', function() {
            getCurrentLocation();
        });
    }
    
    const radiusInput = document.getElementById('radius');
    if (radiusInput) {
        radiusInput.addEventListener('change', function() {
            if (circle) {
                circle.setRadius(parseInt(this.value));
            }
        });
    }
}

// ==================== MAP FUNCTIONS ====================
function initMap() {
    // Default location (Indonesia center / Madiun area)
    const defaultLat = -7.6298;
    const defaultLng = 111.5239;
    
    map = L.map('map').setView([defaultLat, defaultLng], 13);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);
    
    // Click on map to set location
    map.on('click', function(e) {
        setLocation(e.latlng.lat, e.latlng.lng);
    });
    
    // Force map to render properly
    setTimeout(() => {
        map.invalidateSize();
    }, 100);
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
                    errorMessage = 'Izin akses lokasi ditolak. Pastikan Anda memberikan izin akses lokasi di browser.';
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
    const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}`;
    
    Swal.fire({
        title: 'Mencari lokasi...',
        text: 'Mohon tunggu',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });
    
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
                    text: 'Lokasi ditemukan: ' + data[0].display_name,
                    timer: 2000,
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
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Gagal mencari lokasi. Pastikan Anda terhubung ke internet.'
            });
        });
}

// ==================== QR CODE ACTIONS ====================
function initializeQRActions() {
    // Show QR Code Detail
    document.querySelectorAll('.btn-show-qr').forEach(button => {
        button.addEventListener('click', function() {
            const sessionId = this.getAttribute('data-session-id');
            showQRCodeDetail(sessionId);
        });
    });

    // Toggle Status
    document.querySelectorAll('.btn-toggle-status').forEach(button => {
        button.addEventListener('click', function() {
            const sessionId = this.getAttribute('data-session-id');
            const currentStatus = this.getAttribute('data-current-status');
            toggleQRStatus(sessionId, currentStatus);
        });
    });

    // Delete QR Code
    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('.delete-form');
            const qrName = this.getAttribute('data-name');
            deleteQRCode(form, qrName);
        });
    });
}

function showQRCodeDetail(sessionId) {
    const modal = new bootstrap.Modal(document.getElementById('showQRModal'));
    const content = document.getElementById('showQRContent');
    
    content.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="text-muted mt-2">Memuat data...</p>
        </div>
    `;
    
    modal.show();
    
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 30000);
    
    const baseUrl = `/${baseRoute}/qrcode/`;
    
    fetch(`${baseUrl}${sessionId}`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        signal: controller.signal
    })
    .then(response => {
        clearTimeout(timeoutId);
        if (!response.ok) {
            return response.text().then(text => {
                throw new Error(`HTTP ${response.status}: ${text.substring(0, 100)}`);
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            renderQRDetail(data, content, sessionId);
        } else {
            throw new Error(data.message || 'Gagal memuat data');
        }
    })
    .catch(error => {
        clearTimeout(timeoutId);
        
        let errorMessage = 'Gagal memuat data QR Code';
        if (error.name === 'AbortError') {
            errorMessage = 'Request timeout - Server terlalu lama merespon (>30 detik)';
        } else if (error.message) {
            errorMessage = error.message;
        }
        
        content.innerHTML = `
            <div class="alert alert-danger">
                <h5><i class="bi bi-exclamation-triangle me-2"></i>Error</h5>
                <p class="mb-3">${errorMessage}</p>
                <button class="btn btn-primary btn-sm" onclick="location.reload()">
                    <i class="bi bi-arrow-clockwise me-1"></i>Refresh Halaman
                </button>
            </div>
        `;
    });
}

function toggleQRStatus(sessionId, currentStatus) {
    const newStatus = currentStatus === 'active' ? 'expired' : 'active';
    
    Swal.fire({
        title: 'Ubah Status?',
        html: `Ubah status QR Code menjadi <strong>${newStatus === 'active' ? 'Aktif' : 'Expired'}</strong>?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0d6efd',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Ubah!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Mengubah status...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
            
            fetch(`/${baseRoute}/qrcode/${sessionId}/status`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ status: newStatus })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: data.message || 'Terjadi kesalahan',
                        confirmButtonColor: '#dc3545'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'Terjadi kesalahan saat mengubah status',
                    confirmButtonColor: '#dc3545'
                });
            });
        }
    });
}

function deleteQRCode(form, qrName) {
    Swal.fire({
        title: 'Hapus QR Code?',
        html: `Apakah Anda yakin ingin menghapus QR Code<br><strong>${qrName}</strong>?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Menghapus...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
            form.submit();
        }
    });
}

// ==================== RENDER QR DETAIL ====================
function renderQRDetail(data, container, sessionId) {
    const session = data.session;
    
    // Status badge - berdasarkan status_text dari backend
    let statusBadge = '';
    if (session.status_text === 'active') {
        statusBadge = '<span class="badge bg-success fs-6"><i class="bi bi-check-circle me-1"></i>Sedang Aktif</span>';
    } else if (session.status_text === 'waiting') {
        statusBadge = '<span class="badge bg-warning fs-6"><i class="bi bi-clock me-1"></i>Menunggu</span>';
    } else {
        statusBadge = '<span class="badge bg-secondary fs-6"><i class="bi bi-x-circle me-1"></i>Expired</span>';
    }

    container.innerHTML = `
        <div class="row g-4">
            <!-- QR Code Display -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="bg-light p-4 rounded-3 qr-code-container" style="display: inline-block; min-height: 350px; min-width: 350px; display: flex; align-items: center; justify-content: center;">
                            <div id="qrCodeDisplay" style="width: 100%; height: 100%;">
                                <div class="spinner-border text-primary" role="status"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Action Button -->
                <div class="mt-3">
                    <a href="/${baseRoute}/qrcode/${sessionId}/download" 
                       class="btn btn-success btn-lg w-100">
                        <i class="bi bi-download me-2"></i>Download QR Code
                    </a>
                </div>
            </div>

            <!-- Session Info -->
            <div class="col-lg-7">
                <!-- Status & Info -->
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">
                                <i class="bi bi-info-circle me-2"></i>Informasi Sesi
                            </h5>
                            ${statusBadge}
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-6">
                                <small class="text-muted d-block">Kelas</small>
                                <strong>${session.kelas.nama_kelas}</strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Jurusan</small>
                                <strong>${session.kelas.jurusan.nama_jurusan}</strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Tanggal</small>
                                <strong>${session.tanggal}</strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Waktu</small>
                                <strong>${session.jam_mulai} - ${session.jam_selesai}</strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Dibuat Oleh</small>
                                <strong>${session.creator.name}</strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Radius</small>
                                <strong>${session.radius} meter</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="card bg-success text-white h-100">
                            <div class="card-body text-center p-3 d-flex flex-column justify-content-center">
                                <div class="d-flex align-items-center justify-content-center mb-2">
                                    <i class="bi bi-check-circle-fill fs-3 me-2"></i>
                                    <h1 class="mb-0 fw-bold">${session.stats.hadir}</h1>
                                </div>
                                <p class="mb-0 fw-semibold">Hadir</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card bg-secondary text-white h-100">
                            <div class="card-body text-center p-3 d-flex flex-column justify-content-center">
                                <div class="d-flex align-items-center justify-content-center mb-2">
                                    <i class="bi bi-x-circle-fill fs-3 me-2"></i>
                                    <h1 class="mb-0 fw-bold">${session.stats.belum}</h1>
                                </div>
                                <p class="mb-0 fw-semibold">Belum Presensi</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Siswa List Tabs -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-white">
                        <ul class="nav nav-tabs card-header-tabs" id="siswaTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" 
                                        id="hadir-tab" 
                                        data-bs-toggle="tab" 
                                        data-bs-target="#hadir" 
                                        type="button">
                                    <i class="bi bi-check-circle me-2"></i>
                                    Sudah Presensi (${session.stats.hadir})
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" 
                                        id="belum-tab" 
                                        data-bs-toggle="tab" 
                                        data-bs-target="#belum" 
                                        type="button">
                                    <i class="bi bi-x-circle me-2"></i>
                                    Belum Presensi (${session.stats.belum})
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body p-0">
                        <div class="tab-content" id="siswaTabContent">
                            <div class="tab-pane fade show active" id="hadir" role="tabpanel">
                                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light sticky-top">
                                            <tr>
                                                <th style="width: 50px;" class="text-center">No</th>
                                                <th>Nama Siswa</th>
                                                <th class="text-center" style="width: 150px;">Waktu</th>
                                                <th class="text-center" style="width: 120px;">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${session.presensis && session.presensis.length > 0 ? 
                                                session.presensis.map((presensi, index) => `
                                                    <tr>
                                                        <td class="text-center text-muted fw-semibold">${index + 1}</td>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="avatar avatar-sm bg-success-soft text-success rounded-circle me-2">
                                                                    <i class="bi bi-person-check-fill"></i>
                                                                </div>
                                                                <span class="fw-semibold">${presensi.siswa.name}</span>
                                                            </div>
                                                        </td>
                                                        <td class="text-center">
                                                            <small class="text-muted">
                                                                <i class="bi bi-clock me-1"></i>${presensi.waktu_presensi}
                                                            </small>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge bg-success">
                                                                <i class="bi bi-check-circle me-1"></i>${presensi.status}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                `).join('')
                                                : `<tr><td colspan="4" class="text-center py-4">
                                                    <div class="alert alert-info mb-0">
                                                        <i class="bi bi-info-circle me-2"></i>
                                                        Belum ada siswa yang melakukan presensi
                                                    </div>
                                                </td></tr>`
                                            }
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="belum" role="tabpanel">
                                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light sticky-top">
                                            <tr>
                                                <th style="width: 50px;" class="text-center">No</th>
                                                <th>Nama Siswa</th>
                                                <th>Email</th>
                                                <th class="text-center" style="width: 120px;">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${session.siswa_belum_presensi && session.siswa_belum_presensi.length > 0 ? 
                                                session.siswa_belum_presensi.map((siswa, index) => `
                                                    <tr>
                                                        <td class="text-center text-muted fw-semibold">${index + 1}</td>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="avatar avatar-sm bg-secondary-soft text-secondary rounded-circle me-2">
                                                                    <i class="bi bi-person-x-fill"></i>
                                                                </div>
                                                                <span class="fw-semibold">${siswa.name}</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <small class="text-muted">
                                                                <i class="bi bi-envelope me-1"></i>${siswa.email}
                                                            </small>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge bg-secondary">
                                                                <i class="bi bi-x-circle me-1"></i>Belum
                                                            </span>
                                                        </td>
                                                    </tr>
                                                `).join('')
                                                : `<tr><td colspan="4" class="text-center py-4">
                                                    <div class="alert alert-success mb-0">
                                                        <i class="bi bi-check-circle me-2"></i>
                                                        Semua siswa sudah melakukan presensi
                                                    </div>
                                                </td></tr>`
                                            }
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Insert SVG QR Code after DOM is ready
    setTimeout(() => {
        const qrDisplay = document.getElementById('qrCodeDisplay');
        
        if (qrDisplay) {
            if (data.qr_code_svg) {
                if (typeof data.qr_code_svg === 'string' && data.qr_code_svg.includes('<svg')) {
                    qrDisplay.innerHTML = data.qr_code_svg;
                    
                    // Style SVG element
                    const svgElement = qrDisplay.querySelector('svg');
                    if (svgElement) {
                        svgElement.style.width = '100%';
                        svgElement.style.height = 'auto';
                        svgElement.style.maxWidth = '300px';
                    }
                } else {
                    qrDisplay.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i>
                            <p class="mb-0 mt-2">QR Code tidak valid</p>
                        </div>
                    `;
                }
            } else {
                qrDisplay.innerHTML = `
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-circle"></i>
                        <p class="mb-0 mt-2">QR Code tidak tersedia</p>
                    </div>
                `;
            }
        }
    }, 300);
}

// ==================== FORM VALIDATION ====================
function initializeFormValidation() {
    const createForm = document.getElementById('createQRForm');
    if (createForm) {
        createForm.addEventListener('submit', function(e) {
            const jamMulai = this.querySelector('input[name="jam_mulai"]').value;
            const jamSelesai = this.querySelector('input[name="jam_selesai"]').value;
            const latitude = this.querySelector('input[name="latitude"]').value;
            const longitude = this.querySelector('input[name="longitude"]').value;
            
            if (!latitude || !longitude) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan!',
                    text: 'Silakan pilih lokasi di peta terlebih dahulu',
                    confirmButtonColor: '#0d6efd'
                });
                return false;
            }
            
            if (jamSelesai <= jamMulai) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan!',
                    text: 'Jam selesai harus lebih besar dari jam mulai',
                    confirmButtonColor: '#0d6efd'
                });
                return false;
            }
        });
    }
}

// ==================== NOTIFICATIONS ====================
function initializeNotifications() {
    if (typeof Swal !== 'undefined') {
        const successMeta = document.querySelector('meta[name="success-message"]');
        const errorMeta = document.querySelector('meta[name="error-message"]');
        
        if (successMeta) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: successMeta.getAttribute('content'),
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        }
        
        if (errorMeta) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: errorMeta.getAttribute('content'),
                confirmButtonColor: '#dc3545'
            });
        }
    }
}
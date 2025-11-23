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
    baseRoute = baseRouteInput ? baseRouteInput.value : 'guru';

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
                initMap();
            }
            // PENTING: Invalidate size setelah modal terbuka
            setTimeout(function() {
                if (map) {
                    map.invalidateSize();
                }
            }, 200);
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
    
    // Pastikan container map ada
    const mapContainer = document.getElementById('map');
    if (!mapContainer) {
        console.error('Map container not found');
        return;
    }
    
    // Destroy existing map if any
    if (map) {
        map.remove();
        map = null;
    }
    
    map = L.map('map', {
        center: [defaultLat, defaultLng],
        zoom: 13,
        scrollWheelZoom: true
    });
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);
    
    // Click on map to set location
    map.on('click', function(e) {
        setLocation(e.latlng.lat, e.latlng.lng);
    });
    
    // PENTING: Invalidate size setelah map dibuat
    setTimeout(function() {
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
            console.error('Error:', error);
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
        console.error('Error:', error);
        
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
                console.error('Error:', error);
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
    
    // Status badge
    let statusBadge = '';
    if (session.status === 'active') {
        if (session.is_active) {
            statusBadge = '<span class="badge bg-success fs-6"><i class="bi bi-check-circle me-1"></i>Sedang Aktif</span>';
        } else {
            statusBadge = '<span class="badge bg-warning fs-6"><i class="bi bi-clock me-1"></i>Menunggu</span>';
        }
    } else {
        statusBadge = '<span class="badge bg-secondary fs-6"><i class="bi bi-x-circle me-1"></i>Expired</span>';
    }

    // Generate siswa list (hadir)
    let siswaListHtml = '';
    if (session.presensis && session.presensis.length > 0) {
        siswaListHtml = session.presensis.map((presensi, index) => `
            <div class="siswa-item d-flex justify-content-between align-items-center py-2 px-3 border-bottom">
                <div class="d-flex align-items-center">
                    <span class="me-3 text-muted fw-bold">${index + 1}</span>
                    <div>
                        <h6 class="mb-0">${presensi.siswa.name}</h6>
                        <small class="text-muted">
                            <i class="bi bi-clock me-1"></i>${presensi.waktu_presensi}
                        </small>
                    </div>
                </div>
                <span class="badge bg-success">
                    <i class="bi bi-check-circle me-1"></i>${presensi.status}
                </span>
            </div>
        `).join('');
    } else {
        siswaListHtml = `
            <div class="alert alert-info mb-0">
                <i class="bi bi-info-circle me-2"></i>
                Belum ada siswa yang melakukan presensi
            </div>
        `;
    }

    // Belum presensi list
    let belumPresensiHtml = '';
    if (session.siswa_belum_presensi && session.siswa_belum_presensi.length > 0) {
        belumPresensiHtml = session.siswa_belum_presensi.map((siswa, index) => `
            <div class="siswa-item d-flex justify-content-between align-items-center py-2 px-3 border-bottom">
                <div class="d-flex align-items-center">
                    <span class="me-3 text-muted fw-bold">${index + 1}</span>
                    <div>
                        <h6 class="mb-0">${siswa.name}</h6>
                        <small class="text-muted">
                            <i class="bi bi-envelope me-1"></i>${siswa.email}
                        </small>
                    </div>
                </div>
                <span class="badge bg-secondary">
                    <i class="bi bi-x-circle me-1"></i>Belum
                </span>
            </div>
        `).join('');
    } else {
        belumPresensiHtml = `
            <div class="alert alert-success mb-0">
                <i class="bi bi-check-circle me-2"></i>
                Semua siswa sudah melakukan presensi
            </div>
        `;
    }

    container.innerHTML = `
        <div class="row g-4">
            <!-- QR Code Display -->
            <div class="col-lg-5">
                <div class="card bg-gradient-primary text-white">
                    <div class="card-body text-center p-4">
                        <div class="bg-white p-3 rounded mb-3" style="display: inline-block;">
                            ${data.qr_code_svg}
                        </div>
                        <h5 class="text-white mb-2">${session.kelas.nama_kelas}</h5>
                        <p class="text-white-50 mb-0">
                            ${session.kelas.jurusan.nama_jurusan}
                        </p>
                        <p class="text-white-50 mb-0">
                            ${session.tanggal} | ${session.jam_mulai} - ${session.jam_selesai}
                        </p>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="d-grid gap-2 mt-3">
                    <a href="/${baseRoute}/qrcode/${sessionId}/download" 
                       class="btn btn-success btn-lg">
                        <i class="bi bi-download me-2"></i>Download QR Code
                    </a>
                    <button type="button" 
                            class="btn btn-primary btn-lg" 
                            onclick="window.print()">
                        <i class="bi bi-printer me-2"></i>Print QR Code
                    </button>
                    <a href="${session.scan_url}" 
                       class="btn btn-outline-primary btn-lg"
                       target="_blank">
                        <i class="bi bi-link-45deg me-2"></i>Buka Link Scan
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
                <div class="row g-3">
                    <div class="col-6">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <i class="bi bi-check-circle-fill fs-1 mb-2"></i>
                                <h2 class="mb-0">${session.stats.hadir}</h2>
                                <p class="mb-0">Hadir</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card bg-secondary text-white">
                            <div class="card-body text-center">
                                <i class="bi bi-x-circle-fill fs-1 mb-2"></i>
                                <h2 class="mb-0">${session.stats.belum}</h2>
                                <p class="mb-0">Belum Presensi</p>
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
                                <div style="max-height: 400px; overflow-y: auto;">
                                    ${siswaListHtml}
                                </div>
                            </div>
                            <div class="tab-pane fade" id="belum" role="tabpanel">
                                <div style="max-height: 400px; overflow-y: auto;">
                                    ${belumPresensiHtml}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
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
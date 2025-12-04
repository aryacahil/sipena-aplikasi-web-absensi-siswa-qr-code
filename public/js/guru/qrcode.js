let map;
let markerCheckin;
let circleCheckin;
let markerCheckout;
let circleCheckout;
let csrfToken;
let baseRoute;

document.addEventListener('DOMContentLoaded', function() {
    const tokenInput = document.querySelector('input[name="_token"]');
    csrfToken = tokenInput ? tokenInput.value : '';
    
    const baseRouteInput = document.getElementById('baseRoute');
    baseRoute = baseRouteInput ? baseRouteInput.value : 'admin';

    initializeModalEvents();
    initializeMapEvents();
    initializeQRActions();
    initializeFormValidation();
    initializeNotifications();
});

function initializeModalEvents() {
    const createQRModal = document.getElementById('createQRModal');
    
    if (createQRModal) {
        createQRModal.addEventListener('shown.bs.modal', function() {
            if (!map) {
                initMap();
            }
            setTimeout(function() {
                if (map) {
                    map.invalidateSize();
                }
            }, 200);
        });
        
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
    
    const searchBox = document.getElementById('searchBox');
    if (searchBox) {
        searchBox.style.display = 'none';
    }
    
    if (map) {
        if (markerCheckin) {
            map.removeLayer(markerCheckin);
            markerCheckin = null;
        }
        if (circleCheckin) {
            map.removeLayer(circleCheckin);
            circleCheckin = null;
        }
        if (markerCheckout) {
            map.removeLayer(markerCheckout);
            markerCheckout = null;
        }
        if (circleCheckout) {
            map.removeLayer(circleCheckout);
            circleCheckout = null;
        }
    }
    
    document.getElementById('latitude').value = '';
    document.getElementById('longitude').value = '';
}

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
            if (circleCheckin) {
                circleCheckin.setRadius(parseInt(this.value));
            }
        });
    }
}

function initMap() {
    const defaultLat = -7.6298;
    const defaultLng = 111.5239;
    
    const mapContainer = document.getElementById('map');
    if (!mapContainer) {
        console.error('Map container not found');
        return;
    }
    
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
    
    map.on('click', function(e) {
        setLocation(e.latlng.lat, e.latlng.lng);
    });
    
    setTimeout(function() {
        map.invalidateSize();
    }, 100);
}

function setLocation(lat, lng) {
    document.getElementById('latitude').value = lat.toFixed(8);
    document.getElementById('longitude').value = lng.toFixed(8);
    
    if (markerCheckin) {
        map.removeLayer(markerCheckin);
    }
    if (circleCheckin) {
        map.removeLayer(circleCheckin);
    }
    
    markerCheckin = L.marker([lat, lng], {
        icon: L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        })
    }).addTo(map).bindPopup('Lokasi Check-In & Check-Out');
    
    const radius = parseInt(document.getElementById('radius').value) || 200;
    circleCheckin = L.circle([lat, lng], {
        color: 'green',
        fillColor: '#0f0',
        fillOpacity: 0.2,
        radius: radius
    }).addTo(map);
    
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

function initializeQRActions() {
    document.querySelectorAll('.btn-show-qr').forEach(button => {
        button.addEventListener('click', function() {
            const sessionId = this.getAttribute('data-session-id');
            showQRCodeDetail(sessionId);
        });
    });

    document.querySelectorAll('.btn-download-both').forEach(button => {
        button.addEventListener('click', function() {
            const sessionId = this.getAttribute('data-session-id');
            const kelasName = this.getAttribute('data-kelas-name');
            const tanggal = this.getAttribute('data-tanggal');
            downloadBothQRCodes(sessionId, kelasName, tanggal);
        });
    });

    document.querySelectorAll('.btn-toggle-status').forEach(button => {
        button.addEventListener('click', function() {
            const sessionId = this.getAttribute('data-session-id');
            const currentStatus = this.getAttribute('data-current-status');
            toggleQRStatus(sessionId, currentStatus);
        });
    });

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

function downloadBothQRCodes(sessionId, kelasName, tanggal) {
    const downloadCheckin = fetch(`/${baseRoute}/qrcode/${sessionId}/download?type=checkin`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
        }
    })
    .then(response => {
        if (!response.ok) throw new Error('Failed to download Check-In QR');
        return response.blob();
    })
    .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `QR-CHECKIN-${kelasName}-${tanggal}.png`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
    });

    const downloadCheckout = fetch(`/${baseRoute}/qrcode/${sessionId}/download?type=checkout`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
        }
    })
    .then(response => {
        if (!response.ok) throw new Error('Failed to download Check-Out QR');
        return response.blob();
    })
    .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `QR-CHECKOUT-${kelasName}-${tanggal}.png`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
    });

    Promise.all([downloadCheckin, downloadCheckout])
        .catch(error => {
            console.error('Error downloading QR codes:', error);
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Gagal mendownload QR Code: ' + error.message,
                confirmButtonColor: '#dc3545'
            });
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

function renderQRDetail(data, container, sessionId) {
    const session = data.session;
    
    let statusBadge = '';
    if (session.status === 'active') {
        statusBadge = '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Aktif</span>';
    } else {
        statusBadge = '<span class="badge bg-secondary"><i class="bi bi-x-circle me-1"></i>Expired</span>';
    }

    let phaseBadge = '';
    if (session.current_phase === 'checkin') {
        phaseBadge = '<span class="badge bg-primary"><i class="bi bi-box-arrow-in-right me-1"></i>Fase Check-In</span>';
    } else if (session.current_phase === 'checkout') {
        phaseBadge = '<span class="badge bg-primary"><i class="bi bi-box-arrow-right me-1"></i>Fase Check-Out</span>';
    } else {
        phaseBadge = '<span class="badge bg-secondary"><i class="bi bi-clock me-1"></i>Belum Dimulai / Selesai</span>';
    }

    let siswaListHtml = '';
    if (session.presensis && session.presensis.length > 0) {
        siswaListHtml = session.presensis.map((presensi, index) => {
            let statusIcon = '';
            let statusBadgeClass = '';
            let statusText = '';
            
            if (presensi.waktu_checkout) {
                statusIcon = '<i class="bi bi-check-all me-1"></i>';
                statusBadgeClass = 'bg-success';
                statusText = 'Selesai';
            } else if (presensi.waktu_checkin) {
                statusIcon = '<i class="bi bi-check-circle me-1"></i>';
                statusBadgeClass = 'bg-primary';
                statusText = 'Check-In';
            }
            
            return `
                <div class="d-flex justify-content-between align-items-center py-3 px-4 border-bottom hover-bg-light">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                            <span class="fw-bold text-muted">${index + 1}</span>
                        </div>
                        <div>
                            <h6 class="mb-1 fw-semibold">${presensi.siswa.name}</h6>
                            <div class="d-flex gap-3 small text-muted">
                                <span><i class="bi bi-person-badge me-1"></i>NIS: ${presensi.siswa.nis}</span>
                                ${presensi.waktu_checkin ? `<span><i class="bi bi-box-arrow-in-right me-1"></i>${presensi.waktu_checkin}</span>` : ''}
                                ${presensi.waktu_checkout ? `<span><i class="bi bi-box-arrow-right me-1"></i>${presensi.waktu_checkout}</span>` : ''}
                            </div>
                        </div>
                    </div>
                    <span class="badge ${statusBadgeClass}">
                        ${statusIcon}${statusText}
                    </span>
                </div>
            `;
        }).join('');
    } else {
        siswaListHtml = `
            <div class="alert alert-light border mb-0 d-flex align-items-center">
                <i class="bi bi-info-circle text-muted fs-4 me-3"></i>
                <span>Belum ada siswa yang melakukan presensi</span>
            </div>
        `;
    }

    let belumPresensiHtml = '';
    if (session.siswa_belum_presensi && session.siswa_belum_presensi.length > 0) {
        belumPresensiHtml = session.siswa_belum_presensi.map((siswa, index) => `
            <div class="d-flex justify-content-between align-items-center py-3 px-4 border-bottom hover-bg-light">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <span class="fw-bold text-muted">${index + 1}</span>
                    </div>
                    <div>
                        <h6 class="mb-1 fw-semibold">${siswa.name}</h6>
                        <small class="text-muted">
                            ${siswa.nis}
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
            <div class="alert alert-light border mb-0 d-flex align-items-center">
                <i class="bi bi-check-circle text-success fs-4 me-3"></i>
                <span>Semua siswa sudah melakukan presensi</span>
            </div>
        `;
    }

    container.innerHTML = `
        <!-- Informasi Sesi -->
        <div class="card border mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
                    <div>
                        <h4 class="mb-2 fw-bold">${session.kelas.nama_kelas}</h4>
                        <p class="text-muted mb-0">
                            <i class="bi bi-mortarboard me-2"></i>${session.kelas.jurusan.nama_jurusan}
                        </p>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        ${statusBadge}
                        ${phaseBadge}
                    </div>
                </div>
                
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="text-center p-3 border rounded">
                            <i class="bi bi-calendar3 text-primary fs-3 mb-2"></i>
                            <h6 class="mb-1 small text-muted">Tanggal</h6>
                            <p class="mb-0 fw-semibold">${session.tanggal}</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 border rounded">
                            <i class="bi bi-box-arrow-in-right text-success fs-3 mb-2"></i>
                            <h6 class="mb-1 small text-muted">Check-In</h6>
                            <p class="mb-0 fw-semibold">${session.jam_checkin_mulai} - ${session.jam_checkin_selesai}</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 border rounded">
                            <i class="bi bi-box-arrow-right text-warning fs-3 mb-2"></i>
                            <h6 class="mb-1 small text-muted">Check-Out</h6>
                            <p class="mb-0 fw-semibold">${session.jam_checkout_mulai} - ${session.jam_checkout_selesai}</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 border rounded">
                            <i class="bi bi-geo-alt text-danger fs-3 mb-2"></i>
                            <h6 class="mb-1 small text-muted">Radius</h6>
                            <p class="mb-0 fw-semibold">${session.radius_checkin} meter</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- QR Code Cards -->
        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="card border h-100">
                    <div class="card-body text-center p-4">
                        <div class="mb-3">
                            <i class="bi bi-box-arrow-in-right text-success fs-1"></i>
                            <h5 class="mt-2 mb-0 fw-bold">QR Check-In</h5>
                        </div>
                        <div class="bg-light p-3 rounded-3 mb-3 d-inline-block">
                            ${data.qr_code_checkin_svg}
                        </div>
                        <p class="mb-3 text-muted">
                            <i class="bi bi-clock me-2"></i>${session.jam_checkin_mulai} - ${session.jam_checkin_selesai}
                        </p>
                        <a href="/${baseRoute}/qrcode/${sessionId}/download?type=checkin" 
                           class="btn btn-success w-100">
                            <i class="bi bi-download me-2"></i>Download Check-In
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card border h-100">
                    <div class="card-body text-center p-4">
                        <div class="mb-3">
                            <i class="bi bi-box-arrow-right text-warning fs-1"></i>
                            <h5 class="mt-2 mb-0 fw-bold">QR Check-Out</h5>
                        </div>
                        <div class="bg-light p-3 rounded-3 mb-3 d-inline-block">
                            ${data.qr_code_checkout_svg}
                        </div>
                        <p class="mb-3 text-muted">
                            <i class="bi bi-clock me-2"></i>${session.jam_checkout_mulai} - ${session.jam_checkout_selesai}
                        </p>
                        <a href="/${baseRoute}/qrcode/${sessionId}/download?type=checkout" 
                           class="btn btn-warning w-100">
                            <i class="bi bi-download me-2"></i>Download Check-Out
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border text-center">
                    <div class="card-body p-4">
                        <i class="bi bi-box-arrow-in-right text-success fs-1 mb-3"></i>
                        <h2 class="mb-1 fw-bold">${session.stats.checkin}</h2>
                        <p class="mb-0 text-muted">Sudah Check-In</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border text-center">
                    <div class="card-body p-4">
                        <i class="bi bi-box-arrow-right text-warning fs-1 mb-3"></i>
                        <h2 class="mb-1 fw-bold">${session.stats.checkout}</h2>
                        <p class="mb-0 text-muted">Sudah Check-Out</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border text-center">
                    <div class="card-body p-4">
                        <i class="bi bi-x-circle-fill text-secondary fs-1 mb-3"></i>
                        <h2 class="mb-1 fw-bold">${session.stats.belum}</h2>
                        <p class="mb-0 text-muted">Belum Presensi</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Siswa List -->
        <div class="card border">
            <div class="card-header bg-white border-bottom pt-3 px-4">
                <ul class="nav nav-pills" id="siswaTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" 
                                id="hadir-tab" 
                                data-bs-toggle="tab" 
                                data-bs-target="#hadir" 
                                type="button">
                            <i class="bi bi-check-circle me-2"></i>
                            Sudah Presensi <span class="badge bg-success ms-2">${session.stats.checkin + session.stats.checkout}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" 
                                id="belum-tab" 
                                data-bs-toggle="tab" 
                                data-bs-target="#belum" 
                                type="button">
                            <i class="bi bi-x-circle me-2"></i>
                            Belum Presensi <span class="badge bg-secondary ms-2">${session.stats.belum}</span>
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
    `;
}

function initializeFormValidation() {
    const createForm = document.getElementById('createQRForm');
    if (createForm) {
        createForm.addEventListener('submit', function(e) {
            const jamCheckinMulai = this.querySelector('input[name="jam_checkin_mulai"]').value;
            const jamCheckinSelesai = this.querySelector('input[name="jam_checkin_selesai"]').value;
            const jamCheckoutMulai = this.querySelector('input[name="jam_checkout_mulai"]').value;
            const jamCheckoutSelesai = this.querySelector('input[name="jam_checkout_selesai"]').value;
            const latitude = this.querySelector('input[name="latitude_checkin"]').value;
            const longitude = this.querySelector('input[name="longitude_checkin"]').value;
            
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
            
            if (jamCheckinSelesai <= jamCheckinMulai) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan!',
                    text: 'Jam selesai check-in harus lebih besar dari jam mulai check-in',
                    confirmButtonColor: '#0d6efd'
                });
                return false;
            }
            
            if (jamCheckoutMulai <= jamCheckinSelesai) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan!',
                    text: 'Jam mulai check-out harus lebih besar dari jam selesai check-in',
                    confirmButtonColor: '#0d6efd'
                });
                return false;
            }
            
            if (jamCheckoutSelesai <= jamCheckoutMulai) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan!',
                    text: 'Jam selesai check-out harus lebih besar dari jam mulai check-out',
                    confirmButtonColor: '#0d6efd'
                });
                return false;
            }
        });
    }
}

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
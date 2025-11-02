document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('input[name="_token"]').value;

    // ==================== GET CURRENT LOCATION ====================
    const getLocationBtn = document.getElementById('getLocationBtn');
    if (getLocationBtn) {
        getLocationBtn.addEventListener('click', function() {
            if (!navigator.geolocation) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Browser Anda tidak mendukung Geolocation',
                    confirmButtonColor: '#dc3545'
                });
                return;
            }

            // Show loading
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengambil lokasi...';

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    document.getElementById('latitude').value = position.coords.latitude.toFixed(8);
                    document.getElementById('longitude').value = position.coords.longitude.toFixed(8);
                    
                    this.disabled = false;
                    this.innerHTML = '<i class="bi bi-check-circle me-2"></i>Lokasi Berhasil Diambil';
                    this.classList.remove('btn-outline-primary');
                    this.classList.add('btn-success');

                    setTimeout(() => {
                        this.classList.remove('btn-success');
                        this.classList.add('btn-outline-primary');
                        this.innerHTML = '<i class="bi bi-geo-alt me-2"></i>Gunakan Lokasi Saat Ini';
                    }, 2000);
                },
                (error) => {
                    this.disabled = false;
                    this.innerHTML = '<i class="bi bi-geo-alt me-2"></i>Gunakan Lokasi Saat Ini';
                    
                    let errorMessage = 'Gagal mengambil lokasi';
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            errorMessage = 'Izin akses lokasi ditolak';
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
                        text: errorMessage,
                        confirmButtonColor: '#dc3545'
                    });
                }
            );
        });
    }

    // ==================== SHOW QR CODE ====================
    document.querySelectorAll('.btn-show-qr').forEach(button => {
        button.addEventListener('click', function() {
            const sessionId = this.getAttribute('data-session-id');
            const modal = new bootstrap.Modal(document.getElementById('showQRModal'));
            const content = document.getElementById('showQRContent');
            
            // Show loading
            content.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="text-muted mt-2">Memuat data...</p>
                </div>
            `;
            
            modal.show();
            
            // Fetch data
            fetch(`/admin/qrcode/${sessionId}`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderQRDetail(data, content, sessionId);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                content.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Gagal memuat data QR Code
                    </div>
                `;
            });
        });
    });

    // ==================== TOGGLE STATUS ====================
    document.querySelectorAll('.btn-toggle-status').forEach(button => {
        button.addEventListener('click', function() {
            const sessionId = this.getAttribute('data-session-id');
            const currentStatus = this.getAttribute('data-current-status');
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
                    
                    fetch(`/admin/qrcode/${sessionId}/status`, {
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
        });
    });

    // ==================== DELETE QR CODE ====================
    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('.delete-form');
            const qrName = this.getAttribute('data-name');
            
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
        });
    });

    // ==================== FORM VALIDATION ====================
    const createForm = document.getElementById('createQRForm');
    if (createForm) {
        createForm.addEventListener('submit', function(e) {
            const jamMulai = this.querySelector('input[name="jam_mulai"]').value;
            const jamSelesai = this.querySelector('input[name="jam_selesai"]').value;
            
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

    // ==================== MODAL RESET ====================
    const createModal = document.getElementById('createQRModal');
    if (createModal) {
        createModal.addEventListener('hidden.bs.modal', function() {
            const form = document.getElementById('createQRForm');
            if (form) form.reset();
            
            // Reset get location button
            const getLocBtn = document.getElementById('getLocationBtn');
            if (getLocBtn) {
                getLocBtn.classList.remove('btn-success');
                getLocBtn.classList.add('btn-outline-primary');
                getLocBtn.innerHTML = '<i class="bi bi-geo-alt me-2"></i>Gunakan Lokasi Saat Ini';
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

        // Generate siswa list
        let siswaListHtml = '';
        if (session.presensis && session.presensis.length > 0) {
            siswaListHtml = session.presensis.map((presensi, index) => `
                <div class="siswa-item d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <span class="me-3 text-muted fw-bold">${index + 1}</span>
                        <div>
                            <h6 class="mb-0">${presensi.siswa.name}</h6>
                            <small class="text-muted">
                                <i class="bi bi-clock me-1"></i>${presensi.waktu_presensi}
                            </small>
                        </div>
                    </div>
                    <span class="siswa-status hadir">
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
                <div class="siswa-item d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <span class="me-3 text-muted fw-bold">${index + 1}</span>
                        <div>
                            <h6 class="mb-0">${siswa.name}</h6>
                            <small class="text-muted">
                                <i class="bi bi-envelope me-1"></i>${siswa.email}
                            </small>
                        </div>
                    </div>
                    <span class="siswa-status belum">
                        <i class="bi bi-x-circle me-1"></i>Belum
                    </span>
                </div>
            `).join('');
        }

        container.innerHTML = `
            <div class="row g-4">
                <!-- QR Code Display -->
                <div class="col-lg-5">
                    <div class="qr-code-container">
                        <div class="qr-code-wrapper">
                            ${data.qr_code_svg}
                        </div>
                        <div class="mt-3">
                            <h5 class="text-white mb-2">${session.kelas.nama_kelas}</h5>
                            <p class="text-white-50 mb-0">
                                ${session.tanggal} | ${session.jam_mulai} - ${session.jam_selesai}
                            </p>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="d-grid gap-2 mt-3">
                        <a href="/admin/qrcode/${sessionId}/download" 
                           class="btn btn-success btn-lg no-print">
                            <i class="bi bi-download me-2"></i>Download QR Code
                        </a>
                        <button type="button" 
                                class="btn btn-primary btn-lg no-print" 
                                onclick="window.print()">
                            <i class="bi bi-printer me-2"></i>Print QR Code
                        </button>
                        <a href="${session.scan_url}" 
                           class="btn btn-outline-light btn-lg no-print"
                           target="_blank">
                            <i class="bi bi-link-45deg me-2"></i>Buka Link Scan
                        </a>
                    </div>
                </div>

                <!-- Session Info -->
                <div class="col-lg-7">
                    <!-- Status & Stats -->
                    <div class="session-info-card ${session.status}">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">
                                <i class="bi bi-info-circle me-2"></i>Informasi Sesi
                            </h5>
                            ${statusBadge}
                        </div>
                        
                        <div class="row g-3 mb-3">
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

                    <!-- Presensi Statistics -->
                    <div class="row g-3 mt-3">
                        <div class="col-6">
                            <div class="presensi-stat-card hadir">
                                <div class="stat-icon">
                                    <i class="bi bi-check-circle-fill"></i>
                                </div>
                                <div class="stat-number">${session.stats.hadir}</div>
                                <div class="stat-label">Hadir</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="presensi-stat-card alpha">
                                <div class="stat-icon">
                                    <i class="bi bi-x-circle-fill"></i>
                                </div>
                                <div class="stat-number">${session.stats.belum}</div>
                                <div class="stat-label">Belum Presensi</div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="mt-4 no-print">
                        <a href="/admin/presensi/session/${sessionId}/create" 
                           class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Tambah Presensi Manual
                        </a>
                        <a href="/admin/presensi?session_id=${sessionId}" 
                           class="btn btn-outline-primary">
                            <i class="bi bi-list-ul me-2"></i>Lihat Semua Presensi
                        </a>
                    </div>
                </div>

                <!-- Siswa List -->
                <div class="col-12">
                    <ul class="nav nav-tabs" id="siswaTab" role="tablist">
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
                    <div class="tab-content border border-top-0 p-3" id="siswaTabContent">
                        <div class="tab-pane fade show active" id="hadir" role="tabpanel">
                            <div style="max-height: 400px; overflow-y: auto;">
                                ${siswaListHtml}
                            </div>
                        </div>
                        <div class="tab-pane fade" id="belum" role="tabpanel">
                            <div style="max-height: 400px; overflow-y: auto;">
                                ${belumPresensiHtml || '<div class="alert alert-success mb-0"><i class="bi bi-check-circle me-2"></i>Semua siswa sudah melakukan presensi</div>'}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    // ==================== NOTIFICATIONS ====================
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
});
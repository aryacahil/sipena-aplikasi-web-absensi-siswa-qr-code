// ============================================
// PRESENSI.JS - Data Presensi Siswa
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('input[name="_token"]').value;

    // Check for success/error messages
    checkNotifications();

    // ============================================
    // SHOW KELAS - LIHAT DETAIL PRESENSI
    // ============================================
    document.querySelectorAll('.btn-show-kelas').forEach(button => {
        button.addEventListener('click', function() {
            const kelasId = this.dataset.kelasId;
            showKelasDetail(kelasId);
        });
    });

    function showKelasDetail(kelasId) {
        const modal = new bootstrap.Modal(document.getElementById('showKelasModal'));
        const contentDiv = document.getElementById('showKelasContent');
        
        // Show loading
        contentDiv.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="text-muted mt-2">Memuat data...</p>
            </div>
        `;
        
        modal.show();

        // Fetch kelas data
        fetch(`/admin/presensi/kelas/${kelasId}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderKelasDetail(data, contentDiv);
            } else {
                showError(contentDiv, 'Gagal memuat data');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError(contentDiv, 'Terjadi kesalahan saat memuat data');
        });
    }

    function renderKelasDetail(data, contentDiv) {
        const kelas = data.kelas;
        const attendanceData = data.attendance_data || [];
        const stats = data.stats || {};
        const activeSession = data.active_session;
        const availableDates = data.available_dates || [];
        const filterDate = data.filter_date || '';

        // PERUBAHAN: Hapus bagian header dengan nama kelas
        let html = `
            <!-- Info Section - TANPA JUDUL KELAS -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <i class="bi bi-mortarboard text-primary fs-4"></i>
                            <span class="text-muted">${kelas.jurusan.nama_jurusan}</span>
                            <span class="badge bg-primary-soft text-primary"># ${kelas.kode_kelas}</span>
                        </div>
                        ${activeSession ? `
                            <div>
                                <span class="badge bg-success">
                                    <i class="bi bi-clock me-1"></i>Sesi Aktif
                                </span>
                                <small class="text-muted ms-2">
                                    ${activeSession.jam_mulai} - ${activeSession.jam_selesai}
                                </small>
                            </div>
                        ` : ''}
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-2">
                    <div class="card bg-light border-0">
                        <div class="card-body p-3 text-center">
                            <div class="text-primary mb-1">
                                <i class="bi bi-people-fill fs-4"></i>
                            </div>
                            <h3 class="mb-0 fw-bold">${stats.total_siswa || 0}</h3>
                            <small class="text-muted">TOTAL</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="card bg-success-soft border-0">
                        <div class="card-body p-3 text-center">
                            <div class="text-success mb-1">
                                <i class="bi bi-check-circle-fill fs-4"></i>
                            </div>
                            <h3 class="mb-0 fw-bold text-success">${stats.hadir || 0}</h3>
                            <small class="text-muted">HADIR</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="card bg-warning-soft border-0">
                        <div class="card-body p-3 text-center">
                            <div class="text-warning mb-1">
                                <i class="bi bi-clipboard-check fs-4"></i>
                            </div>
                            <h3 class="mb-0 fw-bold text-warning">${stats.izin || 0}</h3>
                            <small class="text-muted">IZIN</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="card bg-info-soft border-0">
                        <div class="card-body p-3 text-center">
                            <div class="text-info mb-1">
                                <i class="bi bi-heart-pulse-fill fs-4"></i>
                            </div>
                            <h3 class="mb-0 fw-bold text-info">${stats.sakit || 0}</h3>
                            <small class="text-muted">SAKIT</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="card bg-danger-soft border-0">
                        <div class="card-body p-3 text-center">
                            <div class="text-danger mb-1">
                                <i class="bi bi-x-circle-fill fs-4"></i>
                            </div>
                            <h3 class="mb-0 fw-bold text-danger">${stats.alpha || 0}</h3>
                            <small class="text-muted">ALPHA</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="card bg-secondary-soft border-0">
                        <div class="card-body p-3 text-center">
                            <div class="text-secondary mb-1">
                                <i class="bi bi-clock fs-4"></i>
                            </div>
                            <h3 class="mb-0 fw-bold text-secondary">${stats.belum || 0}</h3>
                            <small class="text-muted">BELUM</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter & Actions -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex gap-2">
                    <input type="date" 
                           class="form-control form-control-sm" 
                           id="filterTanggal" 
                           value="${filterDate}"
                           style="width: auto;">
                    <button class="btn btn-sm btn-primary" id="btnFilterDate">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">NO</th>
                            <th>SISWA</th>
                            <th class="text-center">WAKTU</th>
                            <th class="text-center">STATUS</th>
                            <th class="text-center">METODE</th>
                            <th class="text-center" style="width: 100px;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        if (attendanceData.length === 0) {
            html += `
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <i class="bi bi-inbox fs-1 text-muted"></i>
                        <p class="text-muted mt-2 mb-0">Belum ada data presensi</p>
                    </td>
                </tr>
            `;
        } else {
            attendanceData.forEach((item, index) => {
                const siswa = item.siswa;
                const presensi = item.presensi;
                const status = item.status;
                
                let statusBadge = '';
                let statusIcon = '';
                
                switch(status) {
                    case 'hadir':
                        statusBadge = '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Hadir</span>';
                        statusIcon = 'check-circle-fill text-success';
                        break;
                    case 'izin':
                        statusBadge = '<span class="badge bg-warning"><i class="bi bi-clipboard-check me-1"></i>Izin</span>';
                        statusIcon = 'clipboard-check text-warning';
                        break;
                    case 'sakit':
                        statusBadge = '<span class="badge bg-info"><i class="bi bi-heart-pulse-fill me-1"></i>Sakit</span>';
                        statusIcon = 'heart-pulse-fill text-info';
                        break;
                    case 'alpha':
                        statusBadge = '<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Alpha</span>';
                        statusIcon = 'x-circle-fill text-danger';
                        break;
                    default:
                        statusBadge = '<span class="badge bg-secondary"><i class="bi bi-clock me-1"></i>Belum</span>';
                        statusIcon = 'clock text-secondary';
                }

                const metodeBadge = presensi && presensi.metode === 'qr' 
                    ? '<span class="badge bg-primary-soft text-primary"><i class="bi bi-qr-code me-1"></i>QR Code</span>'
                    : '<span class="badge bg-secondary-soft text-secondary"><i class="bi bi-pencil me-1"></i>Manual</span>';

                html += `
                    <tr>
                        <td class="text-center">${index + 1}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(siswa.name)}&background=random" 
                                     class="rounded-circle me-2" 
                                     width="32" 
                                     height="32"
                                     alt="${siswa.name}">
                                <div>
                                    <h6 class="mb-0">${siswa.name}</h6>
                                    <small class="text-muted">${siswa.email}</small>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <small class="text-muted">
                                ${presensi ? presensi.waktu_presensi : '-'}
                            </small>
                        </td>
                        <td class="text-center">${statusBadge}</td>
                        <td class="text-center">${presensi ? metodeBadge : '-'}</td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                ${status === 'belum' && activeSession ? `
                                    <button class="btn btn-sm btn-success btn-add-manual-presensi" 
                                            data-siswa-id="${siswa.id}"
                                            data-siswa-name="${siswa.name}"
                                            data-session-id="${activeSession.id}"
                                            title="Tambah Presensi">
                                        <i class="bi bi-plus-circle"></i>
                                    </button>
                                ` : ''}
                                ${presensi ? `
                                    <button class="btn btn-sm btn-primary btn-edit-presensi" 
                                            data-presensi-id="${presensi.id}"
                                            title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger btn-delete-presensi" 
                                            data-presensi-id="${presensi.id}"
                                            data-siswa-name="${siswa.name}"
                                            title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                ` : ''}
                            </div>
                        </td>
                    </tr>
                `;
            });
        }

        html += `
                    </tbody>
                </table>
            </div>
        `;

        contentDiv.innerHTML = html;

        // Attach event listeners
        attachModalEventListeners(kelas.id, activeSession);
    }

    function attachModalEventListeners(kelasId, activeSession) {
        // Filter date
        document.getElementById('btnFilterDate')?.addEventListener('click', function() {
            const tanggal = document.getElementById('filterTanggal').value;
            window.location.href = `/admin/presensi/kelas/${kelasId}?tanggal=${tanggal}`;
            // Set default ke hari ini jika kosong
            const today = new Date().toISOString().split('T')[0];
            filterInput.value = today;
        });

        // Add manual presensi
        document.querySelectorAll('.btn-add-manual-presensi').forEach(btn => {
            btn.addEventListener('click', function() {
                const siswaId = this.dataset.siswaId;
                const siswaName = this.dataset.siswaName;
                const sessionId = this.dataset.sessionId;
                openManualPresensiModal(siswaId, siswaName, sessionId);
            });
        });

        // Edit presensi
        document.querySelectorAll('.btn-edit-presensi').forEach(btn => {
            btn.addEventListener('click', function() {
                const presensiId = this.dataset.presensiId;
                openEditPresensiModal(presensiId);
            });
        });

        // Delete presensi
        document.querySelectorAll('.btn-delete-presensi').forEach(btn => {
            btn.addEventListener('click', function() {
                const presensiId = this.dataset.presensiId;
                const siswaName = this.dataset.siswaName;
                confirmDeletePresensi(presensiId, siswaName);
            });
        });
    }

    // ============================================
    // ADD MANUAL PRESENSI
    // ============================================
    function openManualPresensiModal(siswaId, siswaName, sessionId) {
        document.getElementById('manual_siswa_id').value = siswaId;
        document.getElementById('manual_siswa_name').textContent = siswaName;
        
        const form = document.getElementById('addManualPresensiForm');
        form.action = `/admin/presensi/session/${sessionId}/manual`;
        
        const modal = new bootstrap.Modal(document.getElementById('addManualPresensiModal'));
        modal.show();
    }

    document.getElementById('addManualPresensiForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const actionUrl = this.action;
        
        fetch(actionUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
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
                    text: data.message || 'Terjadi kesalahan'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Terjadi kesalahan saat menyimpan data'
            });
        });
    });

    // ============================================
    // EDIT PRESENSI
    // ============================================
    function openEditPresensiModal(presensiId) {
        const modal = new bootstrap.Modal(document.getElementById('editPresensiModal'));
        const form = document.getElementById('editPresensiForm');
        const loading = document.getElementById('editPresensiLoading');
        const content = document.getElementById('editPresensiFormContent');
        const submitBtn = document.getElementById('editPresensiSubmitBtn');
        
        loading.style.display = 'block';
        content.style.display = 'none';
        submitBtn.style.display = 'none';
        
        modal.show();
        
        fetch(`/admin/presensi/${presensiId}/edit`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                form.action = `/admin/presensi/${presensiId}`;
                document.getElementById('edit_status').value = data.presensi.status;
                document.getElementById('edit_keterangan').value = data.presensi.keterangan || '';
                
                loading.style.display = 'none';
                content.style.display = 'block';
                submitBtn.style.display = 'inline-block';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Gagal memuat data presensi'
            });
        });
    }

    document.getElementById('editPresensiForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const actionUrl = this.action;
        
        fetch(actionUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
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
                    text: data.message || 'Terjadi kesalahan'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Terjadi kesalahan saat memperbarui data'
            });
        });
    });

    // ============================================
    // DELETE PRESENSI
    // ============================================
    function confirmDeletePresensi(presensiId, siswaName) {
        Swal.fire({
            title: 'Hapus Presensi?',
            html: `Anda yakin ingin menghapus presensi <strong>${siswaName}</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                deletePresensi(presensiId);
            }
        });
    }

    function deletePresensi(presensiId) {
        fetch(`/admin/presensi/${presensiId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
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
                    text: data.message || 'Terjadi kesalahan'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Terjadi kesalahan saat menghapus data'
            });
        });
    }

    // ============================================
    // UTILITIES
    // ============================================
    function showError(container, message) {
        container.innerHTML = `
            <div class="text-center py-5">
                <i class="bi bi-exclamation-triangle fs-1 text-danger"></i>
                <p class="text-muted mt-3 mb-0">${message}</p>
            </div>
        `;
    }

    function checkNotifications() {
        const successMeta = document.querySelector('meta[name="success-message"]');
        const errorMeta = document.querySelector('meta[name="error-message"]');
        
        if (successMeta) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: successMeta.content,
                timer: 3000,
                showConfirmButton: false
            });
        }
        
        if (errorMeta) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: errorMeta.content
            });
        }
    }
});
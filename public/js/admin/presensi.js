document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('input[name="_token"]').value;
    let currentKelasId = null;
    let currentFilterDate = null;
    let currentSessionId = null;

    console.log('Presensi Index JS Loaded');

    // ==================== SHOW KELAS DETAIL ====================
    document.querySelectorAll('.btn-show-kelas').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const kelasId = this.getAttribute('data-kelas-id');
            currentKelasId = kelasId;
            currentFilterDate = null;
            
            console.log('Kelas clicked:', kelasId);
            
            const modal = new bootstrap.Modal(document.getElementById('showKelasModal'));
            const content = document.getElementById('showKelasContent');
            
            content.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="text-muted mt-2">Memuat data...</p>
                </div>
            `;
            
            modal.show();
            loadKelasData(kelasId, currentFilterDate);
        });
    });

    // ==================== LOAD KELAS DATA ====================
    function loadKelasData(kelasId, filterDate = null) {
        let url = `/admin/presensi/kelas/${kelasId}`;
        if (filterDate) {
            url += `?tanggal=${filterDate}`;
        }

        console.log('Loading data from:', url);

        fetch(url, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Data received:', data);
            if (data.success) {
                renderKelasDetail(data);
            } else {
                throw new Error('Data tidak valid');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('showKelasContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Gagal memuat data kelas. ${error.message}
                </div>
            `;
        });
    }

    // ==================== RENDER KELAS DETAIL ====================
    function renderKelasDetail(data) {
        const container = document.getElementById('showKelasContent');
        const kelas = data.kelas;
        const attendanceData = data.attendance_data;
        const stats = data.stats;
        const activeSession = data.active_session;
        const availableDates = data.available_dates;
        const filterDate = data.filter_date;

        // Store current session ID
        currentSessionId = activeSession ? activeSession.id : null;

        // Generate date filter options
        let dateOptions = '<option value="">Hari Ini</option>';
        if (availableDates && availableDates.length > 0) {
            availableDates.forEach(date => {
                const selected = date === filterDate ? 'selected' : '';
                const dateObj = new Date(date);
                const formattedDate = dateObj.toLocaleDateString('id-ID', {
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric'
                });
                dateOptions += `<option value="${date}" ${selected}>${formattedDate}</option>`;
            });
        }

        // Generate attendance list
        let attendanceHtml = '';
        if (attendanceData && attendanceData.length > 0) {
            attendanceData.forEach((item, index) => {
                let statusBadge = '';
                let statusIcon = '';

                switch(item.status) {
                    case 'hadir':
                        statusBadge = 'bg-success';
                        statusIcon = 'bi-check-circle-fill';
                        break;
                    case 'izin':
                        statusBadge = 'bg-warning';
                        statusIcon = 'bi-file-text-fill';
                        break;
                    case 'sakit':
                        statusBadge = 'bg-info';
                        statusIcon = 'bi-heart-pulse-fill';
                        break;
                    case 'alpha':
                        statusBadge = 'bg-danger';
                        statusIcon = 'bi-x-circle-fill';
                        break;
                    default:
                        statusBadge = 'bg-secondary';
                        statusIcon = 'bi-clock-fill';
                }

                const metodeBadge = item.presensi && item.presensi.metode === 'qr' 
                    ? '<span class="badge bg-primary-soft text-primary"><i class="bi bi-qr-code me-1"></i>QR</span>'
                    : item.presensi ? '<span class="badge bg-secondary-soft text-secondary"><i class="bi bi-pencil me-1"></i>Manual</span>' : '';

                attendanceHtml += `
                    <tr>
                        <td class="text-center">
                            <span class="text-muted fw-semibold">${index + 1}</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle me-3">
                                    ${item.siswa.name.charAt(0).toUpperCase()}
                                </div>
                                <div>
                                    <h6 class="mb-0">${item.siswa.name}</h6>
                                    <small class="text-muted">${item.siswa.email}</small>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            ${item.presensi ? `<small class="text-muted">${item.presensi.waktu_presensi}</small>` : '-'}
                        </td>
                        <td class="text-center">
                            <span class="badge ${statusBadge}">
                                <i class="bi ${statusIcon} me-1"></i>${item.status.toUpperCase()}
                            </span>
                        </td>
                        <td class="text-center">
                            ${metodeBadge}
                        </td>
                        <td class="text-center">
                            ${item.presensi ? `
                                <div class="d-flex justify-content-center gap-1">
                                    <button type="button" 
                                            class="btn btn-sm btn-primary btn-edit-presensi-modal" 
                                            data-presensi-id="${item.presensi.id}"
                                            title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" 
                                            class="btn btn-sm btn-danger btn-delete-presensi" 
                                            data-presensi-id="${item.presensi.id}"
                                            data-siswa-name="${item.siswa.name}"
                                            title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            ` : `
                                <button type="button" 
                                        class="btn btn-sm btn-success btn-add-presensi-manual" 
                                        data-siswa-id="${item.siswa.id}"
                                        data-siswa-name="${item.siswa.name}"
                                        title="Tambah Presensi">
                                    <i class="bi bi-plus-circle"></i>
                                </button>
                            `}
                        </td>
                    </tr>
                `;
            });
        } else {
            attendanceHtml = `
                <tr>
                    <td colspan="6" class="text-center py-4">
                        <i class="bi bi-inbox fs-3 text-muted"></i>
                        <p class="text-muted mt-2 mb-0">Belum ada data siswa</p>
                    </td>
                </tr>
            `;
        }

        container.innerHTML = `
            <div class="row g-4">
                <!-- Header Info -->
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1">${kelas.nama_kelas}</h4>
                            <p class="text-muted mb-0">
                                <i class="bi bi-mortarboard me-1"></i>${kelas.jurusan.nama_jurusan}
                                <span class="ms-3"><i class="bi bi-hash me-1"></i>${kelas.kode_kelas}</span>
                            </p>
                        </div>
                        ${activeSession ? `
                        <div>
                            <span class="badge bg-success fs-6">
                                <i class="bi bi-qr-code me-1"></i>Sesi Aktif
                            </span>
                            <p class="text-muted small mb-0 mt-1">
                                ${activeSession.jam_mulai} - ${activeSession.jam_selesai}
                            </p>
                        </div>
                        ` : ''}
                    </div>
                </div>

                <!-- Statistics -->
                <div class="col-12">
                    <div class="row g-3">
                        <div class="col">
                            <div class="stat-card bg-primary-soft">
                                <div class="stat-icon text-primary">
                                    <i class="bi bi-people-fill"></i>
                                </div>
                                <div class="stat-number">${stats.total_siswa}</div>
                                <div class="stat-label">Total</div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="stat-card bg-success-soft">
                                <div class="stat-icon text-success">
                                    <i class="bi bi-check-circle-fill"></i>
                                </div>
                                <div class="stat-number">${stats.hadir}</div>
                                <div class="stat-label">Hadir</div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="stat-card bg-warning-soft">
                                <div class="stat-icon text-warning">
                                    <i class="bi bi-file-text-fill"></i>
                                </div>
                                <div class="stat-number">${stats.izin}</div>
                                <div class="stat-label">Izin</div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="stat-card bg-info-soft">
                                <div class="stat-icon text-info">
                                    <i class="bi bi-heart-pulse-fill"></i>
                                </div>
                                <div class="stat-number">${stats.sakit}</div>
                                <div class="stat-label">Sakit</div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="stat-card bg-danger-soft">
                                <div class="stat-icon text-danger">
                                    <i class="bi bi-x-circle-fill"></i>
                                </div>
                                <div class="stat-number">${stats.alpha}</div>
                                <div class="stat-label">Alpha</div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="stat-card bg-secondary-soft">
                                <div class="stat-icon text-secondary">
                                    <i class="bi bi-clock-fill"></i>
                                </div>
                                <div class="stat-number">${stats.belum}</div>
                                <div class="stat-label">Belum</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter & Actions -->
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex gap-2">
                            <select class="form-select form-select-sm" id="filterDateSelect" style="width: auto;">
                                ${dateOptions}
                            </select>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="refreshDataBtn">
                                <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="col-12">
                    <div class="table-responsive">
                        <table class="table table-hover table-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 60px;">No</th>
                                    <th>Siswa</th>
                                    <th class="text-center">Waktu</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Metode</th>
                                    <th class="text-center" style="width: 150px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${attendanceHtml}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;

        attachModalEventListeners();
    }

    // ==================== ATTACH EVENT LISTENERS ====================
    function attachModalEventListeners() {
        // Filter date change
        const filterDateSelect = document.getElementById('filterDateSelect');
        if (filterDateSelect) {
            filterDateSelect.addEventListener('change', function() {
                currentFilterDate = this.value;
                loadKelasData(currentKelasId, currentFilterDate);
            });
        }

        // Refresh button
        const refreshBtn = document.getElementById('refreshDataBtn');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', function() {
                loadKelasData(currentKelasId, currentFilterDate);
            });
        }

        // Add manual presensi buttons
        document.querySelectorAll('.btn-add-presensi-manual').forEach(button => {
            button.addEventListener('click', function() {
                const siswaId = this.getAttribute('data-siswa-id');
                const siswaName = this.getAttribute('data-siswa-name');
                openAddManualModal(siswaId, siswaName);
            });
        });

        // Edit buttons
        document.querySelectorAll('.btn-edit-presensi-modal').forEach(button => {
            button.addEventListener('click', function() {
                const presensiId = this.getAttribute('data-presensi-id');
                openEditModal(presensiId);
            });
        });

        // Delete buttons
        document.querySelectorAll('.btn-delete-presensi').forEach(button => {
            button.addEventListener('click', function() {
                const presensiId = this.getAttribute('data-presensi-id');
                const siswaName = this.getAttribute('data-siswa-name');
                deletePresensi(presensiId, siswaName);
            });
        });
    }

    // ==================== ADD MANUAL PRESENSI ====================
    function openAddManualModal(siswaId, siswaName) {
        if (!currentSessionId) {
            Swal.fire({
                icon: 'error',
                title: 'Tidak Ada Sesi Aktif',
                text: 'Tidak ada sesi presensi aktif untuk hari ini. Silakan buat QR Code terlebih dahulu.',
                confirmButtonColor: '#dc3545'
            });
            return;
        }

        const modal = new bootstrap.Modal(document.getElementById('addManualPresensiModal'));
        const form = document.getElementById('addManualPresensiForm');
        
        // Set form action and data
        form.action = `/admin/presensi/session/${currentSessionId}/manual`;
        document.getElementById('manual_siswa_id').value = siswaId;
        document.getElementById('manual_siswa_name').textContent = siswaName;
        
        // Reset form
        form.reset();
        document.getElementById('manual_siswa_id').value = siswaId;
        document.getElementById('manual_status').value = 'hadir';
        document.getElementById('manual_keterangan').value = '';
        
        modal.show();
    }

    // Handle add manual form submit
    const addManualForm = document.getElementById('addManualPresensiForm');
    if (addManualForm) {
        addManualForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
            
            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addManualPresensiModal'));
                    modal.hide();
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message || 'Presensi berhasil ditambahkan',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    
                    loadKelasData(currentKelasId, currentFilterDate);
                } else {
                    throw new Error(data.message || 'Gagal menambahkan presensi');
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: error.message,
                    confirmButtonColor: '#dc3545'
                });
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }

    // ==================== EDIT PRESENSI ====================
    function openEditModal(presensiId) {
        const modal = new bootstrap.Modal(document.getElementById('editPresensiModal'));
        const form = document.getElementById('editPresensiForm');
        const loading = document.getElementById('editPresensiLoading');
        const formContent = document.getElementById('editPresensiFormContent');
        const submitBtn = document.getElementById('editPresensiSubmitBtn');
        
        loading.style.display = 'block';
        formContent.style.display = 'none';
        submitBtn.style.display = 'none';
        
        modal.show();
        
        fetch(`/admin/presensi/${presensiId}/edit`, {
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
                const presensi = data.presensi;
                
                form.action = `/admin/presensi/${presensi.id}`;
                document.getElementById('edit_status').value = presensi.status;
                document.getElementById('edit_keterangan').value = presensi.keterangan || '';
                
                loading.style.display = 'none';
                formContent.style.display = 'block';
                submitBtn.style.display = 'inline-block';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Gagal memuat data presensi',
                confirmButtonColor: '#dc3545'
            });
            modal.hide();
        });
    }

    // Handle edit form submit
    const editForm = document.getElementById('editPresensiForm');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memperbarui...';
            
            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editPresensiModal'));
                    modal.hide();
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Presensi berhasil diperbarui',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    
                    loadKelasData(currentKelasId, currentFilterDate);
                } else {
                    throw new Error(data.message || 'Gagal memperbarui presensi');
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: error.message,
                    confirmButtonColor: '#dc3545'
                });
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }

    // ==================== DELETE PRESENSI ====================
    function deletePresensi(presensiId, siswaName) {
        Swal.fire({
            title: 'Hapus Presensi?',
            html: `Hapus data presensi <strong>${siswaName}</strong>?`,
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

                fetch(`/admin/presensi/${presensiId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Presensi berhasil dihapus',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    loadKelasData(currentKelasId, currentFilterDate);
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: 'Gagal menghapus presensi',
                        confirmButtonColor: '#dc3545'
                    });
                });
            }
        });
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
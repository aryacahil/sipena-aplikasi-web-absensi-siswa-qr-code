document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('input[name="_token"]').value;

    checkNotifications();

    document.querySelectorAll('.btn-show-kelas').forEach(button => {
        button.addEventListener('click', function() {
            const kelasId = this.dataset.kelasId;
            showKelasDetail(kelasId);
        });
    });

    function showKelasDetail(kelasId, filterDate = null) {
        const modal = new bootstrap.Modal(document.getElementById('showKelasModal'));
        const contentDiv = document.getElementById('showKelasContent');
        
        contentDiv.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="text-muted mt-2">Memuat data...</p>
            </div>
        `;
        
        modal.show();

        const url = filterDate 
            ? `/admin/presensi/kelas/${kelasId}?tanggal=${filterDate}`
            : `/admin/presensi/kelas/${kelasId}`;

        fetch(url, {
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
                renderKelasDetail(data, contentDiv, kelasId);
            } else {
                showError(contentDiv, 'Gagal memuat data');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError(contentDiv, 'Terjadi kesalahan saat memuat data');
        });
    }

    function renderKelasDetail(data, contentDiv, kelasId) {
        const kelas = data.kelas;
        const attendanceData = data.attendance_data || [];
        const stats = data.stats || {};
        const activeSession = data.active_session;
        const filterDate = data.filter_date || '';

        function getAvatarColor(name) {
            const colors = [
                '#0d6efd', '#6610f2', '#d63384', '#dc3545', '#fd7e14', 
                '#ffc107', '#198754', '#20c997', '#0dcaf0', '#6c757d'
            ];
            let hash = 0;
            for (let i = 0; i < name.length; i++) {
                hash = name.charCodeAt(i) + ((hash << 5) - hash);
            }
            return colors[Math.abs(hash) % colors.length];
        }

        let html = `
            <!-- Info Section -->
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
                                <span class="badge bg-success fs-6">
                                    <i class="bi bi-clock me-1"></i>Sesi QR Aktif
                                </span>
                                <small class="text-muted ms-2">
                                    Checkin: ${activeSession.jam_checkin_mulai} - ${activeSession.jam_checkin_selesai} | 
                                    Checkout: ${activeSession.jam_checkout_mulai} - ${activeSession.jam_checkout_selesai}
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
                    <div class="card border-0" style="background-color: #d4edda;">
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
                    <div class="card border-0" style="background-color: #fff3cd;">
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
                    <div class="card border-0" style="background-color: #d1ecf1;">
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
                    <div class="card border-0" style="background-color: #f8d7da;">
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
                    <div class="card border-0" style="background-color: #e2e3e5;">
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
                    <button class="btn btn-sm btn-primary" id="btnFilterDate" data-kelas-id="${kelasId}">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;" class="text-center">NO</th>
                            <th>SISWA</th>
                            <th class="text-center" style="width: 100px;">MASUK</th>
                            <th class="text-center" style="width: 100px;">PULANG</th>
                            <th class="text-center" style="width: 100px;">STATUS</th>
                            <th class="text-center" style="width: 100px;">METODE</th>
                            <th class="text-center" style="width: 120px;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        if (attendanceData.length === 0) {
            html += `
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <i class="bi bi-inbox fs-1 text-muted"></i>
                        <p class="text-muted mt-2 mb-0">Belum ada data siswa</p>
                    </td>
                </tr>
            `;
        } else {
            attendanceData.forEach((item, index) => {
                const siswa = item.siswa;
                const presensi = item.presensi;
                const status = item.status;
                
                const avatarColor = getAvatarColor(siswa.name);
                const initial = siswa.name.charAt(0).toUpperCase();
                
                let statusBadge = '';
                
                switch(status) {
                    case 'hadir':
                        statusBadge = '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Hadir</span>';
                        break;
                    case 'izin':
                        statusBadge = '<span class="badge bg-warning"><i class="bi bi-clipboard-check me-1"></i>Izin</span>';
                        break;
                    case 'sakit':
                        statusBadge = '<span class="badge bg-info"><i class="bi bi-heart-pulse-fill me-1"></i>Sakit</span>';
                        break;
                    case 'alpha':
                        statusBadge = '<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Alpha</span>';
                        break;
                    default:
                        statusBadge = '<span class="badge bg-secondary"><i class="bi bi-clock me-1"></i>Belum</span>';
                }

                const metodeBadge = presensi && presensi.metode === 'qr' 
                    ? '<span class="badge bg-primary-soft text-primary"><i class="bi bi-qr-code me-1"></i>QR</span>'
                    : (presensi ? '<span class="badge bg-secondary-soft text-secondary"><i class="bi bi-pencil me-1"></i>Manual</span>' : '-');

                // ✅ FIXED: Tampilkan waktu checkin dan checkout terpisah
                const checkinTime = presensi && presensi.waktu_checkin && presensi.waktu_checkin !== '-'
                    ? `<small class="text-muted"><i class="bi bi-box-arrow-in-right me-1"></i>${presensi.waktu_checkin}</small>` 
                    : '<small class="text-muted">-</small>';

                const checkoutTime = presensi && presensi.waktu_checkout && presensi.waktu_checkout !== '-'
                    ? `<small class="text-success"><i class="bi bi-box-arrow-right me-1"></i>${presensi.waktu_checkout}</small>` 
                    : '<small class="text-muted">-</small>';

                html += `
                    <tr>
                        <td class="text-center">
                            <span class="fw-semibold text-muted">${index + 1}</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar-wrapper" style="background-color: ${avatarColor};">
                                    <span>${initial}</span>
                                </div>
                                <div class="siswa-info">
                                    <div class="siswa-name">${siswa.name}</div>
                                    <span class="siswa-nis">${siswa.nis}</span>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">${checkinTime}</td>
                        <td class="text-center">${checkoutTime}</td>
                        <td class="text-center">${statusBadge}</td>
                        <td class="text-center">${metodeBadge}</td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                ${status === 'belum' ? `
                                    <button class="btn btn-sm btn-success btn-add-manual-presensi" 
                                            data-siswa-id="${siswa.id}"
                                            data-siswa-name="${siswa.name}"
                                            data-kelas-id="${kelasId}"
                                            data-tanggal="${filterDate}"
                                            title="Tambah">
                                        <i class="bi bi-plus-circle"></i>
                                    </button>
                                ` : ''}
                                ${presensi ? `
                                    <button class="btn btn-sm btn-warning btn-edit-presensi" 
                                            data-presensi-id="${presensi.id}"
                                            title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger btn-delete-presensi" 
                                            data-presensi-id="${presensi.id}"
                                            data-siswa-name="${siswa.name}"
                                            data-kelas-id="${kelasId}"
                                            data-tanggal="${filterDate}"
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
        attachModalEventListeners(kelasId, filterDate);
    }

    function attachModalEventListeners(kelasId, currentFilterDate) {
        const btnFilterDate = document.getElementById('btnFilterDate');
        if (btnFilterDate) {
            btnFilterDate.addEventListener('click', function() {
                const tanggal = document.getElementById('filterTanggal').value;
                const currentModal = bootstrap.Modal.getInstance(document.getElementById('showKelasModal'));
                if (currentModal) {
                    currentModal.hide();
                }
                setTimeout(() => {
                    showKelasDetail(kelasId, tanggal);
                }, 300);
            });
        }

        document.querySelectorAll('.btn-add-manual-presensi').forEach(btn => {
            btn.addEventListener('click', function() {
                openManualPresensiModal(
                    this.dataset.siswaId,
                    this.dataset.siswaName,
                    this.dataset.kelasId,
                    this.dataset.tanggal
                );
            });
        });

        document.querySelectorAll('.btn-edit-presensi').forEach(btn => {
            btn.addEventListener('click', function() {
                openEditPresensiModal(this.dataset.presensiId);
            });
        });

        document.querySelectorAll('.btn-delete-presensi').forEach(btn => {
            btn.addEventListener('click', function() {
                confirmDeletePresensi(
                    this.dataset.presensiId,
                    this.dataset.siswaName,
                    this.dataset.kelasId,
                    this.dataset.tanggal
                );
            });
        });
    }

    function openManualPresensiModal(siswaId, siswaName, kelasId, tanggal) {
        document.getElementById('manual_siswa_id').value = siswaId;
        document.getElementById('manual_siswa_name').textContent = siswaName;
        
        const form = document.getElementById('addManualPresensiForm');
        form.action = `/admin/presensi/kelas/${kelasId}/manual`;
        form.dataset.kelasId = kelasId;
        form.dataset.tanggal = tanggal;
        
        let tanggalInput = document.getElementById('manual_tanggal_presensi');
        if (!tanggalInput) {
            tanggalInput = document.createElement('input');
            tanggalInput.type = 'hidden';
            tanggalInput.id = 'manual_tanggal_presensi';
            tanggalInput.name = 'tanggal_presensi';
            form.appendChild(tanggalInput);
        }
        tanggalInput.value = tanggal;
        
        let kelasInput = document.getElementById('manual_kelas_id');
        if (!kelasInput) {
            kelasInput = document.createElement('input');
            kelasInput.type = 'hidden';
            kelasInput.id = 'manual_kelas_id';
            kelasInput.name = 'kelas_id';
            form.appendChild(kelasInput);
        }
        kelasInput.value = kelasId;
        
        document.getElementById('manual_status').value = 'hadir';
        document.getElementById('manual_keterangan').value = '';
        
        const modal = new bootstrap.Modal(document.getElementById('addManualPresensiModal'));
        modal.show();
    }

    document.getElementById('addManualPresensiForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const actionUrl = this.action;
        const submitBtn = this.querySelector('button[type="submit"]');
        
        submitBtn.disabled = true;
        const originalHTML = submitBtn.innerHTML;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
        
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
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHTML;
            
            if (data.success) {
                const modalEl = document.getElementById('addManualPresensiModal');
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                
                modalInstance.hide();
                
                modalEl.addEventListener('hidden.bs.modal', function onHidden() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                    modalEl.removeEventListener('hidden.bs.modal', onHidden);
                }, { once: true });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: data.message || 'Terjadi kesalahan'
                });
            }
        })
        .catch(error => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHTML;
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Terjadi kesalahan saat menyimpan data'
            });
        });
    });

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
        
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 10000);
        
        fetch(`/admin/presensi/${presensiId}/edit`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            },
            signal: controller.signal
        })
        .then(response => {
            clearTimeout(timeoutId);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                form.action = `/admin/presensi/${presensiId}`;
                document.getElementById('edit_status').value = data.presensi.status;
                document.getElementById('edit_keterangan').value = data.presensi.keterangan || '';
                
                loading.style.display = 'none';
                content.style.display = 'block';
                submitBtn.style.display = 'inline-block';
            } else {
                throw new Error(data.message || 'Gagal memuat data');
            }
        })
        .catch(error => {
            clearTimeout(timeoutId);
            loading.style.display = 'none';
            modal.hide();
            
            let errorMessage = 'Gagal memuat data presensi';
            if (error.name === 'AbortError') {
                errorMessage = 'Request timeout - Server tidak merespon';
            } else {
                errorMessage += ': ' + error.message;
            }
            
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: errorMessage,
                footer: 'Coba refresh halaman atau hubungi administrator'
            });
        });
    }

    document.getElementById('editPresensiForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const actionUrl = this.action;
        const submitBtn = document.getElementById('editPresensiSubmitBtn');
        
        submitBtn.disabled = true;
        const originalHTML = submitBtn.innerHTML;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memperbarui...';
        
        fetch(actionUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHTML;
            
            if (data.success) {
                const modalEl = document.getElementById('editPresensiModal');
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                
                modalInstance.hide();
                
                modalEl.addEventListener('hidden.bs.modal', function onHidden() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                    modalEl.removeEventListener('hidden.bs.modal', onHidden);
                }, { once: true });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: data.message || 'Terjadi kesalahan'
                });
            }
        })
        .catch(error => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHTML;
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Terjadi kesalahan saat memperbarui data: ' + error.message
            });
        });
    });

    function confirmDeletePresensi(presensiId, siswaName, kelasId, tanggal) {
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
                deletePresensi(presensiId, kelasId, tanggal);
            }
        });
    }

    function deletePresensi(presensiId, kelasId, tanggal) {
        Swal.fire({
            title: 'Menghapus...',
            text: 'Mohon tunggu',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
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

    function showError(container, message) {
        container.innerHTML = `
            <div class="text-center py-5">
                <i class="bi bi-exclamation-triangle fs-1 text-danger"></i>
                <p class="text-muted mt-3 mb-0">${message}</p>
                <button class="btn btn-primary mt-3" onclick="location.reload()">
                    <i class="bi bi-arrow-clockwise me-2"></i>Muat Ulang
                </button>
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
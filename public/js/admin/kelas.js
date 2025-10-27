document.addEventListener('DOMContentLoaded', function() {
    console.log('Kelas Management JS Loaded');
    
    // CSRF Token Setup
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // ==================== SHOW KELAS ====================
    document.body.addEventListener('click', function(e) {
        const showButton = e.target.closest('.btn-show-kelas');
        if (showButton) {
            e.preventDefault();
            const kelasId = showButton.getAttribute('data-kelas-id');
            console.log('Show button clicked for kelas:', kelasId);
            
            const modalElement = document.getElementById('showKelasModal');
            const modal = new bootstrap.Modal(modalElement);
            const content = document.getElementById('showKelasContent');
            
            // Show loading
            content.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;
            
            modal.show();
            
            // Fetch data
            fetch(`/admin/kelas/${kelasId}`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const kelas = data.kelas;
                    
                    let siswaHtml = '';
                    if (kelas.siswa && kelas.siswa.length > 0) {
                        siswaHtml = kelas.siswa.map((siswa, index) => `
                            <div class="siswa-item d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                                <div>
                                    <strong>${index + 1}. ${siswa.name}</strong><br>
                                    <small class="text-muted">
                                        <i class="bi bi-envelope me-1"></i>${siswa.email}
                                        ${siswa.nisn ? `<span class="ms-2"><i class="bi bi-card-text me-1"></i>NISN: ${siswa.nisn}</span>` : ''}
                                    </small>
                                </div>
                                <button class="btn btn-sm btn-danger btn-remove-siswa" 
                                        data-siswa-id="${siswa.id}" 
                                        data-kelas-id="${kelas.id}"
                                        data-siswa-name="${siswa.name}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        `).join('');
                    } else {
                        siswaHtml = `
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                Belum ada siswa di kelas ini
                            </div>
                        `;
                    }
                    
                    content.innerHTML = `
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h4>${kelas.nama_kelas}</h4>
                                        <p class="mb-2">
                                            <span class="badge bg-primary me-2">Kode: ${kelas.kode_kelas}</span>
                                            <span class="badge bg-info me-2">Tingkat ${kelas.tingkat}</span>
                                            <span class="badge bg-success">${kelas.jurusan.kode_jurusan}</span>
                                        </p>
                                        <p class="mb-0">
                                            <i class="bi bi-person-badge me-2"></i>
                                            Wali Kelas: ${kelas.wali_kelas ? kelas.wali_kelas.name : '-'}
                                        </p>
                                    </div>
                                    <div class="text-end">
                                        <h2 class="mb-0">${kelas.siswa_count || 0}</h2>
                                        <small>Total Siswa</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">
                                <i class="bi bi-people-fill me-2"></i>Daftar Siswa
                            </h6>
                            <button class="btn btn-sm btn-primary btn-add-siswa-modal" data-kelas-id="${kelas.id}">
                                <i class="bi bi-plus-circle me-1"></i>Tambah Siswa
                            </button>
                        </div>

                        <div class="siswa-list">
                            ${siswaHtml}
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                content.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Gagal memuat data kelas
                    </div>
                `;
            });
        }
    });

    // ==================== EDIT KELAS ====================
    document.body.addEventListener('click', function(e) {
        const editButton = e.target.closest('.btn-edit-kelas');
        if (editButton) {
            e.preventDefault();
            const kelasId = editButton.getAttribute('data-kelas-id');
            console.log('Edit button clicked for kelas:', kelasId);
            
            const modalElement = document.getElementById('editKelasModal');
            const modal = new bootstrap.Modal(modalElement);
            const form = document.getElementById('editKelasForm');
            const loading = document.getElementById('editKelasLoading');
            const formContent = document.getElementById('editKelasFormContent');
            const submitBtn = document.getElementById('editKelasSubmitBtn');
            
            loading.style.display = 'block';
            formContent.style.display = 'none';
            submitBtn.style.display = 'none';
            
            modal.show();
            
            fetch(`/admin/kelas/${kelasId}/edit`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const kelas = data.kelas;
                    
                    form.action = `/admin/kelas/${kelas.id}`;
                    
                    document.getElementById('edit_jurusan_id').value = kelas.jurusan_id;
                    document.getElementById('edit_tingkat').value = kelas.tingkat;
                    document.getElementById('edit_kode_kelas').value = kelas.kode_kelas;
                    document.getElementById('edit_nama_kelas').value = kelas.nama_kelas;
                    document.getElementById('edit_wali_kelas_id').value = kelas.wali_kelas_id || '';
                    
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
                    text: 'Gagal memuat data kelas',
                    confirmButtonColor: '#dc3545'
                });
                modal.hide();
            });
        }
    });

    // ==================== DELETE KELAS ====================
    document.body.addEventListener('click', function(e) {
        const deleteButton = e.target.closest('.btn-delete');
        if (deleteButton) {
            e.preventDefault();
            const form = deleteButton.closest('.delete-form');
            const kelasName = deleteButton.getAttribute('data-name');
            console.log('Delete button clicked for:', kelasName);
            
            Swal.fire({
                title: 'Hapus Kelas?',
                html: `Apakah Anda yakin ingin menghapus kelas<br><strong>${kelasName}</strong>?`,
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
    });

    // ==================== ADD SISWA MODAL ====================
    document.body.addEventListener('click', function(e) {
        const addButton = e.target.closest('.btn-add-siswa-modal');
        if (addButton) {
            e.preventDefault();
            const kelasId = addButton.getAttribute('data-kelas-id');
            console.log('Add siswa button clicked for kelas:', kelasId);
            
            // Close show modal first
            const showModalElement = document.getElementById('showKelasModal');
            const showModal = bootstrap.Modal.getInstance(showModalElement);
            if (showModal) showModal.hide();
            
            // Show add siswa modal
            const addModalElement = document.getElementById('addSiswaModal');
            const modal = new bootstrap.Modal(addModalElement);
            document.getElementById('add_siswa_kelas_id').value = kelasId;
            
            // Load available siswa
            loadAvailableSiswa(kelasId);
            
            modal.show();
        }
    });

    // ==================== REMOVE SISWA ====================
    document.body.addEventListener('click', function(e) {
        const removeButton = e.target.closest('.btn-remove-siswa');
        if (removeButton) {
            e.preventDefault();
            const siswaId = removeButton.getAttribute('data-siswa-id');
            const kelasId = removeButton.getAttribute('data-kelas-id');
            const siswaName = removeButton.getAttribute('data-siswa-name');
            console.log('Remove siswa button clicked:', siswaName);
            
            Swal.fire({
                title: 'Keluarkan Siswa?',
                html: `Apakah Anda yakin ingin mengeluarkan<br><strong>${siswaName}</strong><br>dari kelas ini?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Keluarkan!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Mengeluarkan siswa...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });
                    
                    fetch(`/admin/kelas/${kelasId}/remove-siswa/${siswaId}`, {
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
                                // Refresh show modal
                                const showButton = document.querySelector(`[data-kelas-id="${kelasId}"].btn-show-kelas`);
                                if (showButton) {
                                    showButton.click();
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: data.message || 'Gagal mengeluarkan siswa',
                                confirmButtonColor: '#dc3545'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: 'Terjadi kesalahan saat mengeluarkan siswa',
                            confirmButtonColor: '#dc3545'
                        });
                    });
                }
            });
        }
    });

    // ==================== ADD SISWA FORM SUBMIT ====================
    const addSiswaForm = document.getElementById('addSiswaForm');
    if (addSiswaForm) {
        addSiswaForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const kelasId = document.getElementById('add_siswa_kelas_id').value;
            const siswaId = document.getElementById('add_siswa_id').value;
            
            if (!siswaId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan!',
                    text: 'Silakan pilih siswa terlebih dahulu',
                    confirmButtonColor: '#0d6efd'
                });
                return;
            }
            
            Swal.fire({
                title: 'Menambahkan siswa...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
            
            const formData = new FormData(this);
            
            fetch(`/admin/kelas/${kelasId}/add-siswa`, {
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
                        // Close add modal
                        const modalElement = document.getElementById('addSiswaModal');
                        const modal = bootstrap.Modal.getInstance(modalElement);
                        if (modal) modal.hide();
                        
                        // Refresh show modal
                        const showButton = document.querySelector(`[data-kelas-id="${kelasId}"].btn-show-kelas`);
                        if (showButton) {
                            showButton.click();
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: data.message || 'Gagal menambahkan siswa',
                        confirmButtonColor: '#dc3545'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'Terjadi kesalahan saat menambahkan siswa',
                    confirmButtonColor: '#dc3545'
                });
            });
        });
    }

    // ==================== FORM VALIDATION ====================
    const createForm = document.getElementById('createKelasForm');
    if (createForm) {
        createForm.addEventListener('submit', function(e) {
            const jurusan = this.querySelector('select[name="jurusan_id"]').value;
            const tingkat = this.querySelector('select[name="tingkat"]').value;
            const kodeKelas = this.querySelector('input[name="kode_kelas"]').value.trim();
            const namaKelas = this.querySelector('input[name="nama_kelas"]').value.trim();
            
            if (!jurusan || !tingkat || !kodeKelas || !namaKelas) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan!',
                    text: 'Semua field wajib diisi kecuali Wali Kelas',
                    confirmButtonColor: '#0d6efd'
                });
                return false;
            }
        });
    }

    const editForm = document.getElementById('editKelasForm');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            const jurusan = document.getElementById('edit_jurusan_id').value;
            const tingkat = document.getElementById('edit_tingkat').value;
            const kodeKelas = document.getElementById('edit_kode_kelas').value.trim();
            const namaKelas = document.getElementById('edit_nama_kelas').value.trim();
            
            if (!jurusan || !tingkat || !kodeKelas || !namaKelas) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan!',
                    text: 'Semua field wajib diisi kecuali Wali Kelas',
                    confirmButtonColor: '#0d6efd'
                });
                return false;
            }
        });
    }

    // ==================== MODAL RESET ====================
    const createModal = document.getElementById('createKelasModal');
    if (createModal) {
        createModal.addEventListener('hidden.bs.modal', function() {
            if (createForm) createForm.reset();
        });
    }

    const editModal = document.getElementById('editKelasModal');
    if (editModal) {
        editModal.addEventListener('hidden.bs.modal', function() {
            if (editForm) editForm.reset();
            document.getElementById('editKelasLoading').style.display = 'block';
            document.getElementById('editKelasFormContent').style.display = 'none';
            document.getElementById('editKelasSubmitBtn').style.display = 'none';
        });
    }

    // ==================== AUTO UPPERCASE FOR KODE KELAS ====================
    document.body.addEventListener('input', function(e) {
        if (e.target.matches('input[name="kode_kelas"]')) {
            e.target.value = e.target.value.toUpperCase();
        }
    });

    // ==================== NOTIFIKASI ====================
    if (typeof Swal !== 'undefined') {
        const urlParams = new URLSearchParams(window.location.search);
        const success = urlParams.get('success');
        const error = urlParams.get('error');
        
        if (success) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: decodeURIComponent(success),
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        }
        
        if (error) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: decodeURIComponent(error),
                confirmButtonColor: '#dc3545'
            });
        }
    }

    console.log('All event listeners attached successfully');
});

// ==================== LOAD AVAILABLE SISWA ====================
function loadAvailableSiswa(kelasId) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const selectElement = document.getElementById('add_siswa_id');
    
    selectElement.innerHTML = '<option value="">Memuat...</option>';
    
    fetch(`/admin/kelas/${kelasId}/available-siswa`, {
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
            if (data.siswa.length > 0) {
                selectElement.innerHTML = '<option value="">Pilih Siswa</option>' +
                    data.siswa.map(siswa => 
                        `<option value="${siswa.id}">${siswa.name} - ${siswa.email}</option>`
                    ).join('');
            } else {
                selectElement.innerHTML = '<option value="">Tidak ada siswa tersedia</option>';
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        selectElement.innerHTML = '<option value="">Gagal memuat data</option>';
    });
}
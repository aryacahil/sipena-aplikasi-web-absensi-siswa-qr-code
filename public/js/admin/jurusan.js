document.addEventListener('DOMContentLoaded', function() {
    // CSRF Token Setup
    const csrfToken = document.querySelector('input[name="_token"]').value;

    // ==================== SHOW JURUSAN ====================
    const showButtons = document.querySelectorAll('.btn-show-jurusan');
    showButtons.forEach(button => {
        button.addEventListener('click', function() {
            const jurusanId = this.getAttribute('data-jurusan-id');
            const modal = new bootstrap.Modal(document.getElementById('showJurusanModal'));
            const content = document.getElementById('showJurusanContent');
            
            // Show loading
            content.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            `;
            
            modal.show();
            
            // Fetch data
            fetch(`/admin/jurusan/${jurusanId}`, {
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
                    const jurusan = data.jurusan;
                    
                    let kelasHtml = '';
                    if (jurusan.kelas && jurusan.kelas.length > 0) {
                        kelasHtml = jurusan.kelas.map(kelas => `
                            <div class="col-md-6">
                                <div class="card border">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1">${kelas.nama_kelas}</h6>
                                                <small class="text-muted">
                                                    <i class="bi bi-people-fill me-1"></i>${kelas.siswa_count || 0} Siswa
                                                </small>
                                            </div>
                                            ${kelas.wali_kelas ? `
                                                <span class="badge bg-info-soft text-info">
                                                    <i class="bi bi-person-badge me-1"></i>
                                                    ${kelas.wali_kelas.name}
                                                </span>
                                            ` : ''}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `).join('');
                    } else {
                        kelasHtml = `
                            <div class="col-12">
                                <div class="alert alert-info mb-0">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Belum ada kelas di jurusan ini
                                </div>
                            </div>
                        `;
                    }
                    
                    content.innerHTML = `
                        <div class="row g-4">
                            <!-- Info Utama -->
                            <div class="col-12">
                                <div class="card bg-primary-soft border-0">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-auto">
                                                <div class="avatar avatar-xl bg-primary text-white">
                                                    <span class="fs-3 fw-bold">${jurusan.kode_jurusan}</span>
                                                </div>
                                            </div>
                                            <div class="col">
                                                <h4 class="mb-1">${jurusan.nama_jurusan}</h4>
                                                <p class="text-muted mb-0">
                                                    <i class="bi bi-building me-2"></i>
                                                    ${jurusan.kelas_count || 0} Kelas Terdaftar
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Deskripsi -->
                            ${jurusan.deskripsi ? `
                                <div class="col-12">
                                    <h6 class="mb-2">
                                        <i class="bi bi-info-circle me-2 text-primary"></i>Deskripsi
                                    </h6>
                                    <p class="text-muted mb-0">${jurusan.deskripsi}</p>
                                </div>
                            ` : ''}

                            <!-- Daftar Kelas -->
                            <div class="col-12">
                                <h6 class="mb-3">
                                    <i class="bi bi-building me-2 text-primary"></i>
                                    Daftar Kelas (${jurusan.kelas_count || 0})
                                </h6>
                                <div class="row g-3">
                                    ${kelasHtml}
                                </div>
                            </div>

                            <!-- Informasi Tambahan -->
                            <div class="col-12">
                                <div class="border-top pt-3">
                                    <div class="row text-muted small">
                                        <div class="col-md-6">
                                            <i class="bi bi-calendar-plus me-2"></i>
                                            <strong>Dibuat:</strong> ${new Date(jurusan.created_at).toLocaleDateString('id-ID', {
                                                day: 'numeric',
                                                month: 'long',
                                                year: 'numeric',
                                                hour: '2-digit',
                                                minute: '2-digit'
                                            })}
                                        </div>
                                        <div class="col-md-6">
                                            <i class="bi bi-calendar-check me-2"></i>
                                            <strong>Diperbarui:</strong> ${new Date(jurusan.updated_at).toLocaleDateString('id-ID', {
                                                day: 'numeric',
                                                month: 'long',
                                                year: 'numeric',
                                                hour: '2-digit',
                                                minute: '2-digit'
                                            })}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                content.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Gagal memuat data jurusan
                    </div>
                `;
            });
        });
    });

    // ==================== EDIT JURUSAN ====================
    const editButtons = document.querySelectorAll('.btn-edit-jurusan');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const jurusanId = this.getAttribute('data-jurusan-id');
            const modal = new bootstrap.Modal(document.getElementById('editJurusanModal'));
            const form = document.getElementById('editJurusanForm');
            const loading = document.getElementById('editJurusanLoading');
            const formContent = document.getElementById('editJurusanFormContent');
            const submitBtn = document.getElementById('editJurusanSubmitBtn');
            
            // Reset and show loading
            loading.style.display = 'block';
            formContent.style.display = 'none';
            submitBtn.style.display = 'none';
            
            modal.show();
            
            // Fetch data
            fetch(`/admin/jurusan/${jurusanId}/edit`, {
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
                    const jurusan = data.jurusan;
                    
                    // Set form action
                    form.action = `/admin/jurusan/${jurusan.id}`;
                    
                    // Fill form
                    document.getElementById('edit_kode_jurusan').value = jurusan.kode_jurusan;
                    document.getElementById('edit_nama_jurusan').value = jurusan.nama_jurusan;
                    document.getElementById('edit_deskripsi').value = jurusan.deskripsi || '';
                    
                    // Show form
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
                    text: 'Gagal memuat data jurusan',
                    confirmButtonColor: '#dc3545'
                });
                modal.hide();
            });
        });
    });

    // ==================== DELETE JURUSAN ====================
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('.delete-form');
            const jurusanName = this.getAttribute('data-name');
            
            Swal.fire({
                title: 'Hapus Jurusan?',
                html: `Apakah Anda yakin ingin menghapus<br><strong>${jurusanName}</strong>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
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
    // Create Form
    const createForm = document.getElementById('createJurusanForm');
    if (createForm) {
        createForm.addEventListener('submit', function(e) {
            const kodeJurusan = this.querySelector('input[name="kode_jurusan"]').value.trim();
            const namaJurusan = this.querySelector('input[name="nama_jurusan"]').value.trim();
            
            if (!kodeJurusan || !namaJurusan) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan!',
                    text: 'Kode dan Nama Jurusan wajib diisi',
                    confirmButtonColor: '#0d6efd'
                });
                return false;
            }
        });
    }

    // Edit Form
    const editForm = document.getElementById('editJurusanForm');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            const kodeJurusan = document.getElementById('edit_kode_jurusan').value.trim();
            const namaJurusan = document.getElementById('edit_nama_jurusan').value.trim();
            
            if (!kodeJurusan || !namaJurusan) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan!',
                    text: 'Kode dan Nama Jurusan wajib diisi',
                    confirmButtonColor: '#0d6efd'
                });
                return false;
            }
        });
    }

    // ==================== MODAL RESET ====================
    // Reset Create Modal
    const createModal = document.getElementById('createJurusanModal');
    if (createModal) {
        createModal.addEventListener('hidden.bs.modal', function() {
            createForm.reset();
        });
    }

    // Reset Edit Modal
    const editModal = document.getElementById('editJurusanModal');
    if (editModal) {
        editModal.addEventListener('hidden.bs.modal', function() {
            editForm.reset();
            document.getElementById('editJurusanLoading').style.display = 'block';
            document.getElementById('editJurusanFormContent').style.display = 'none';
            document.getElementById('editJurusanSubmitBtn').style.display = 'none';
        });
    }

    // ==================== SEARCH AUTO SUBMIT ====================
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('keyup', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (this.value.length >= 3 || this.value.length === 0) {
                    this.closest('form').submit();
                }
            }, 500);
        });
    }

    // ==================== AUTO UPPERCASE FOR KODE JURUSAN ====================
    const kodeJurusanInputs = document.querySelectorAll('input[name="kode_jurusan"]');
    kodeJurusanInputs.forEach(input => {
        input.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    });
});

// ==================== NOTIFIKASI (Di luar DOMContentLoaded) ====================
if (typeof Swal !== 'undefined') {
    // Check for success message
    const successMessage = document.querySelector('meta[name="success-message"]');
    if (successMessage) {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: successMessage.getAttribute('content'),
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false,
            toast: true,
            position: 'top-end',
            customClass: {
                popup: 'colored-toast'
            }
        });
    }

    // Check for error message
    const errorMessage = document.querySelector('meta[name="error-message"]');
    if (errorMessage) {
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: errorMessage.getAttribute('content'),
            confirmButtonColor: '#dc3545'
        });
    }
}
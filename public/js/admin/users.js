document.addEventListener('DOMContentLoaded', function() {
    
    function createAvatar(name) {
        const colors = ['#FF6B6B', '#4ECDC4', '#45B7D1', '#FFA07A', '#98D8C8', '#F7DC6F', '#BB8FCE'];
        const initial = name.charAt(0).toUpperCase();
        const colorIndex = name.charCodeAt(0) % colors.length;
        const color = colors[colorIndex];
        
        return `<svg width="120" height="120" xmlns="http://www.w3.org/2000/svg">
            <rect width="120" height="120" fill="${color}"/>
            <text x="50%" y="50%" font-size="50" fill="white" 
                  text-anchor="middle" dy=".3em" font-family="Arial, sans-serif" font-weight="bold">
                ${initial}
            </text>
        </svg>`;
    }

    window.toggleStudentFieldsCreate = function() {
        const role = document.getElementById('create_role').value;
        const nisGroup = document.getElementById('create_nis_group');
        const emailGroup = document.getElementById('create_email_group');
        const kelasGroup = document.getElementById('create_kelas_group');
        const parentPhoneGroup = document.getElementById('create_parent_phone_group');
        const emailInput = document.getElementById('create_email');
        const nisInput = document.getElementById('create_nis');
        const kelasSelect = document.getElementById('create_kelas_id');
        const parentPhoneInput = document.getElementById('create_parent_phone');
        
        if (role == '2') {
            nisGroup.style.display = 'block';
            kelasGroup.style.display = 'block';
            parentPhoneGroup.style.display = 'block';
            emailGroup.style.display = 'none';
            emailInput.value = '';
            emailInput.removeAttribute('required');
            nisInput.setAttribute('required', 'required');
        } else {
            emailGroup.style.display = 'block';
            nisGroup.style.display = 'none';
            kelasGroup.style.display = 'none';
            parentPhoneGroup.style.display = 'none';
            nisInput.value = '';
            kelasSelect.value = '';
            parentPhoneInput.value = '';
            emailInput.setAttribute('required', 'required');
            nisInput.removeAttribute('required');
        }
    };

    window.toggleStudentFieldsEdit = function() {
        const role = document.getElementById('edit_role').value;
        const nisGroup = document.getElementById('edit_nis_group');
        const emailGroup = document.getElementById('edit_email_group');
        const kelasGroup = document.getElementById('edit_kelas_group');
        const parentPhoneGroup = document.getElementById('edit_parent_phone_group');
        const emailInput = document.getElementById('edit_email');
        const nisInput = document.getElementById('edit_nis');
        const kelasSelect = document.getElementById('edit_kelas_id');
        const parentPhoneInput = document.getElementById('edit_parent_phone');
        
        if (role == '2') {
            nisGroup.style.display = 'block';
            kelasGroup.style.display = 'block';
            parentPhoneGroup.style.display = 'block';
            emailGroup.style.display = 'none';
            emailInput.value = '';
            emailInput.removeAttribute('required');
            nisInput.setAttribute('required', 'required');
        } else {
            emailGroup.style.display = 'block';
            nisGroup.style.display = 'none';
            kelasGroup.style.display = 'none';
            parentPhoneGroup.style.display = 'none';
            nisInput.value = '';
            kelasSelect.value = '';
            parentPhoneInput.value = '';
            emailInput.setAttribute('required', 'required');
            nisInput.removeAttribute('required');
        }
    };

    document.querySelectorAll('.btn-show-user').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const userId = this.getAttribute('data-user-id');
            const modal = new bootstrap.Modal(document.getElementById('showUserModal'));
            const content = document.getElementById('showUserContent');
            
            content.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';
            modal.show();

            fetch(`/admin/users/${userId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const user = data.user;
                    
                    let roleBadge = '';
                    if (user.role == 1) {
                        roleBadge = '<span class="badge bg-danger">Admin</span>';
                    } else if (user.role == 0) {
                        roleBadge = '<span class="badge bg-info">Guru</span>';
                    } else if (user.role == 2) {
                        roleBadge = '<span class="badge bg-success">Siswa</span>';
                    }
                    
                    let statusBadge = user.status === 'active' 
                        ? '<span class="badge bg-success">Aktif</span>' 
                        : '<span class="badge bg-secondary">Nonaktif</span>';
                    
                    let identifierInfo = '';
                    if (user.role == 2) {
                        identifierInfo = `<tr>
                            <td class="fw-semibold text-muted">NIS</td>
                            <td>${user.nis || '-'}</td>
                        </tr>`;
                    } else {
                        identifierInfo = `<tr>
                            <td class="fw-semibold text-muted">Email</td>
                            <td>${user.email || '-'}</td>
                        </tr>`;
                    }
                    
                    let kelasInfo = '';
                    if (user.role == 2) {
                        if (user.kelas) {
                            kelasInfo = `<tr>
                                <td class="fw-semibold text-muted">Kelas</td>
                                <td>${user.kelas.nama_kelas} - ${user.kelas.jurusan.nama_jurusan}</td>
                            </tr>`;
                        } else {
                            kelasInfo = `<tr>
                                <td class="fw-semibold text-muted">Kelas</td>
                                <td><span class="badge bg-warning">Belum Ada Kelas</span></td>
                            </tr>`;
                        }
                    }
                    
                    let parentPhoneInfo = '';
                    if (user.role == 2 && user.parent_phone) {
                        parentPhoneInfo = `<tr>
                            <td class="fw-semibold text-muted">No. Telepon Ortu</td>
                            <td>${user.parent_phone}</td>
                        </tr>`;
                    }
                    
                    const avatarSvg = createAvatar(user.name);
                    const displayIdentifier = user.role == 2 ? (user.nis || '-') : (user.email || '-');
                    
                    content.innerHTML = `
                        <div class="row g-4">
                            <div class="col-md-4 text-center">
                                <div class="mb-3">${avatarSvg}</div>
                                <h5 class="mb-2">${user.name}</h5>
                                <p class="text-muted mb-3">${displayIdentifier}</p>
                                <div class="mb-2">${roleBadge}</div>
                                <div>${statusBadge}</div>
                            </div>
                            <div class="col-md-8">
                                <h6 class="mb-3 border-bottom pb-2">Informasi Detail</h6>
                                <table class="table table-borderless">
                                    <tbody>
                                        <tr>
                                            <td class="fw-semibold text-muted" style="width: 40%;">Nama</td>
                                            <td>${user.name}</td>
                                        </tr>
                                        ${identifierInfo}
                                        <tr>
                                            <td class="fw-semibold text-muted">Role</td>
                                            <td>${roleBadge}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold text-muted">Status</td>
                                            <td>${statusBadge}</td>
                                        </tr>
                                        ${kelasInfo}
                                        ${parentPhoneInfo}
                                        <tr>
                                            <td class="fw-semibold text-muted">Terdaftar</td>
                                            <td>${new Date(user.created_at).toLocaleDateString('id-ID', { 
                                                day: 'numeric', 
                                                month: 'long', 
                                                year: 'numeric' 
                                            })}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    `;
                }
            })
            .catch(error => {
                content.innerHTML = '<div class="alert alert-danger">Gagal memuat data</div>';
            });
        });
    });

    document.querySelectorAll('.btn-edit-user').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const userId = this.getAttribute('data-user-id');
            const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
            const form = document.getElementById('editUserForm');
            const loading = document.getElementById('editUserLoading');
            const content = document.getElementById('editUserFormContent');
            const submitBtn = document.getElementById('editUserSubmitBtn');
            
            loading.style.display = 'block';
            content.style.display = 'none';
            submitBtn.style.display = 'none';
            form.action = `/admin/users/${userId}`;
            modal.show();
            
            fetch(`/admin/users/${userId}/edit`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const user = data.user;
                    document.getElementById('edit_name').value = user.name;
                    document.getElementById('edit_role').value = user.role;
                    document.getElementById('edit_status').value = user.status;
                    
                    if (user.role == 2) {
                        document.getElementById('edit_nis').value = user.nis || '';
                        document.getElementById('edit_email').value = '';
                    } else {
                        document.getElementById('edit_email').value = user.email || '';
                        document.getElementById('edit_nis').value = '';
                    }
                    
                    if (user.kelas_id) {
                        document.getElementById('edit_kelas_id').value = user.kelas_id;
                    } else {
                        document.getElementById('edit_kelas_id').value = '';
                    }
                    if (user.parent_phone) {
                        document.getElementById('edit_parent_phone').value = user.parent_phone;
                    } else {
                        document.getElementById('edit_parent_phone').value = '';
                    }
                    
                    toggleStudentFieldsEdit();
                    loading.style.display = 'none';
                    content.style.display = 'block';
                    submitBtn.style.display = 'inline-block';
                }
            })
            .catch(error => {
                loading.innerHTML = '<div class="alert alert-danger">Gagal memuat data</div>';
            });
        });
    });

    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            const userName = this.getAttribute('data-name');
            
            Swal.fire({
                title: 'Hapus User?',
                html: `Apakah Anda yakin ingin menghapus<br><strong>${userName}</strong>?`,
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

    window.confirmDeleteByRole = function(role, roleName) {
        event.preventDefault();
        Swal.fire({
            title: `Hapus Semua ${roleName.charAt(0).toUpperCase() + roleName.slice(1)}?`,
            html: `Semua data <strong>${roleName}</strong> akan dihapus permanen.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus Semua',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Menghapus...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                const csrfToken = document.querySelector('input[name="_token"]').value;

                fetch('/admin/users/bulk-delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ role: role })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message,
                            confirmButtonColor: '#28a745',
                            timer: 2000
                        }).then(() => {
                            window.location.reload();
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
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Terjadi kesalahan: ' + error.message
                    });
                });
            }
        });
    };

    document.getElementById('createUserModal').addEventListener('hidden.bs.modal', function () {
        document.getElementById('createUserForm').reset();
        toggleStudentFieldsCreate();
    });

    document.getElementById('editUserModal').addEventListener('hidden.bs.modal', function () {
        document.getElementById('editUserForm').reset();
        document.getElementById('editUserLoading').style.display = 'block';
        document.getElementById('editUserFormContent').style.display = 'none';
        document.getElementById('editUserSubmitBtn').style.display = 'none';
    });
});

if (typeof Swal !== 'undefined') {
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
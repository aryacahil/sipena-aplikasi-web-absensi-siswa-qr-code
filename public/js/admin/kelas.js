let allSiswaData = [];
let currentKelasId = null;
let selectedSiswaIds = new Set();

document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('input[name="_token"]').value;

    document.body.addEventListener('click', function(e) {
        const showButton = e.target.closest('.btn-show-kelas');
        if (showButton) {
            e.preventDefault();
            const kelasId = showButton.getAttribute('data-kelas-id');
            console.log('Show kelas:', kelasId);
            
            const modalElement = document.getElementById('showKelasModal');
            const modal = new bootstrap.Modal(modalElement);
            const content = document.getElementById('showKelasContent');
            
            content.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;
            
            modal.show();
            
            fetch(`/admin/kelas/${kelasId}`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Data received:', data);
                if (data.success) {
                    const kelas = data.kelas;
                    
                    let siswaHtml = '';
                    if (kelas.siswa && kelas.siswa.length > 0) {
                        siswaHtml = `
                            <div class="mb-3 d-flex justify-content-between align-items-center">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="selectAllSiswa">
                                    <label class="form-check-label fw-bold" for="selectAllSiswa">
                                        Pilih Semua
                                    </label>
                                </div>
                                <span class="badge bg-info">${kelas.siswa.length} siswa</span>
                            </div>
                            ${kelas.siswa.map((siswa, index) => `
                                <div class="siswa-item d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                                    <div class="d-flex align-items-center flex-grow-1">
                                        <input class="form-check-input me-3 siswa-checkbox" 
                                               type="checkbox" 
                                               value="${siswa.id}"
                                               data-siswa-name="${siswa.name}">
                                        <div>
                                            <strong>${index + 1}. ${siswa.name}</strong><br>
                                            <small class="text-muted">
                                                ${siswa.nis}
                                            </small>
                                        </div>
                                    </div>
                                    <button class="btn btn-sm btn-danger btn-remove-siswa-single" 
                                            data-siswa-id="${siswa.id}" 
                                            data-kelas-id="${kelas.id}"
                                            data-siswa-name="${siswa.name}"
                                            title="Keluarkan siswa">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            `).join('')}
                            <div class="mt-3 d-flex gap-2 flex-wrap">
                                <button class="btn btn-danger btn-remove-selected-siswa" 
                                        data-kelas-id="${kelas.id}"
                                        style="display: none;">
                                    <i class="bi bi-trash me-1"></i>Keluarkan Terpilih (<span class="selected-count">0</span>)
                                </button>
                                <button class="btn btn-outline-danger btn-remove-all-siswa" 
                                        data-kelas-id="${kelas.id}">
                                    <i class="bi bi-trash3 me-1"></i>Keluarkan Semua
                                </button>
                            </div>
                        `;
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
                            <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-primary btn-add-siswa-modal" data-kelas-id="${kelas.id}">
                                <i class="bi bi-plus-circle me-1"></i>Tambah Siswa
                            </button>
                            <button class="btn btn-sm btn-primary btn-add-siswa-modal" data-kelas-id="${kelas.id}">
                                <i class="bi bi-plus-circle me-1"></i>Pindah Kelas
                            </button>
                        </div>

                        <div class="siswa-list">
                            ${siswaHtml}
                        </div>
                    `;

                    initSelectAllCheckbox();
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

    function initSelectAllCheckbox() {
        const selectAllCheckbox = document.getElementById('selectAllSiswa');
        const siswaCheckboxes = document.querySelectorAll('.siswa-checkbox');
        const removeSelectedBtn = document.querySelector('.btn-remove-selected-siswa');
        const selectedCountSpan = document.querySelector('.selected-count');

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                siswaCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateSelectedCount();
            });
        }

        siswaCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateSelectedCount();
                
                if (selectAllCheckbox) {
                    const allChecked = Array.from(siswaCheckboxes).every(cb => cb.checked);
                    const someChecked = Array.from(siswaCheckboxes).some(cb => cb.checked);
                    selectAllCheckbox.checked = allChecked;
                    selectAllCheckbox.indeterminate = someChecked && !allChecked;
                }
            });
        });

        function updateSelectedCount() {
            const checkedBoxes = document.querySelectorAll('.siswa-checkbox:checked');
            const count = checkedBoxes.length;
            
            if (selectedCountSpan) {
                selectedCountSpan.textContent = count;
            }
            
            if (removeSelectedBtn) {
                removeSelectedBtn.style.display = count > 0 ? 'inline-block' : 'none';
            }
        }
    }

    document.body.addEventListener('click', function(e) {
        const removeSelectedBtn = e.target.closest('.btn-remove-selected-siswa');
        if (removeSelectedBtn) {
            e.preventDefault();
            const kelasId = removeSelectedBtn.getAttribute('data-kelas-id');
            const checkedBoxes = document.querySelectorAll('.siswa-checkbox:checked');
            const siswaIds = Array.from(checkedBoxes).map(cb => cb.value);
            const siswaNames = Array.from(checkedBoxes).map(cb => cb.getAttribute('data-siswa-name'));
            
            if (siswaIds.length === 0) return;
            
            Swal.fire({
                title: 'Keluarkan Siswa Terpilih?',
                html: `Apakah Anda yakin ingin mengeluarkan <strong>${siswaIds.length} siswa</strong> dari kelas ini?<br><br>` +
                      `<small class="text-muted">${siswaNames.slice(0, 3).join(', ')}${siswaNames.length > 3 ? ', ...' : ''}</small>`,
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
                    
                    fetch(`/admin/kelas/${kelasId}/remove-siswa`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            siswa_ids: siswaIds
                        })
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
                                text: data.message,
                                confirmButtonColor: '#dc3545'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: 'Terjadi kesalahan',
                            confirmButtonColor: '#dc3545'
                        });
                    });
                }
            });
        }
    });

    document.body.addEventListener('click', function(e) {
        const removeAllBtn = e.target.closest('.btn-remove-all-siswa');
        if (removeAllBtn) {
            e.preventDefault();
            const kelasId = removeAllBtn.getAttribute('data-kelas-id');
            
            Swal.fire({
                title: 'Keluarkan Semua Siswa?',
                html: 'Apakah Anda yakin ingin mengeluarkan <strong>SEMUA siswa</strong> dari kelas ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Keluarkan Semua!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Mengeluarkan semua siswa...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });
                    
                    fetch(`/admin/kelas/${kelasId}/remove-all-siswa`, {
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
                                text: data.message,
                                confirmButtonColor: '#dc3545'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: 'Terjadi kesalahan',
                            confirmButtonColor: '#dc3545'
                        });
                    });
                }
            });
        }
    });

    document.body.addEventListener('click', function(e) {
        const editButton = e.target.closest('.btn-edit-kelas');
        if (editButton) {
            e.preventDefault();
            const kelasId = editButton.getAttribute('data-kelas-id');
            console.log('Edit kelas:', kelasId);
            
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
            .then(response => response.json())
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

    document.body.addEventListener('click', function(e) {
        const deleteButton = e.target.closest('.btn-delete');
        if (deleteButton && deleteButton.closest('.delete-form')) {
            e.preventDefault();
            const form = deleteButton.closest('.delete-form');
            const kelasName = deleteButton.getAttribute('data-name');
            
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

    document.body.addEventListener('click', function(e) {
        const addButton = e.target.closest('.btn-add-siswa-modal');
        if (addButton) {
            e.preventDefault();
            const kelasId = addButton.getAttribute('data-kelas-id');
            console.log('Opening add siswa modal for kelas:', kelasId);
            
            currentKelasId = kelasId;
            selectedSiswaIds.clear();
            console.log('Cleared selected IDs:', Array.from(selectedSiswaIds));
            
            const showModalElement = document.getElementById('showKelasModal');
            const showModal = bootstrap.Modal.getInstance(showModalElement);
            if (showModal) showModal.hide();
            
            const addModalElement = document.getElementById('addSiswaModal');
            const modal = new bootstrap.Modal(addModalElement);
            document.getElementById('add_siswa_kelas_id').value = kelasId;
            
            const searchInput = document.getElementById('search_siswa');
            if (searchInput) searchInput.value = '';
            
            const addSelectedBtn = document.querySelector('.btn-add-selected-to-class');
            if (addSelectedBtn) {
                addSelectedBtn.style.display = 'none';
            }
            
            loadAvailableSiswaList(kelasId);
            
            modal.show();
        }
    });

    const searchInput = document.getElementById('search_siswa');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase().trim();
            console.log('Searching for:', searchTerm);
            
            if (searchTerm === '') {
                renderSiswaList(allSiswaData);
            } else {
                const filteredSiswa = allSiswaData.filter(siswa => 
                    siswa.name.toLowerCase().includes(searchTerm) || 
                    siswa.nis.toLowerCase().includes(searchTerm)
                );
                renderSiswaList(filteredSiswa);
            }
        });
    }

    document.body.addEventListener('click', function(e) {
        const removeButton = e.target.closest('.btn-remove-siswa-single');
        if (removeButton) {
            e.preventDefault();
            const siswaId = removeButton.getAttribute('data-siswa-id');
            const kelasId = removeButton.getAttribute('data-kelas-id');
            const siswaName = removeButton.getAttribute('data-siswa-name');
            
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
                    
                    fetch(`/admin/kelas/${kelasId}/remove-siswa`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            siswa_id: siswaId
                        })
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
                                text: data.message,
                                confirmButtonColor: '#dc3545'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: 'Terjadi kesalahan',
                            confirmButtonColor: '#dc3545'
                        });
                    });
                }
            });
        }
    });

    document.body.addEventListener('click', function(e) {
        const selectAllBtn = e.target.closest('.btn-select-all-add-siswa');
        if (selectAllBtn) {
            e.preventDefault();
            const allCheckboxes = document.querySelectorAll('.siswa-checkbox-add');
            
            if (selectedSiswaIds.size === allSiswaData.length) {
                selectedSiswaIds.clear();
                allCheckboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });
            } else {
                allSiswaData.forEach(siswa => {
                    selectedSiswaIds.add(siswa.id.toString());
                });
                allCheckboxes.forEach(checkbox => {
                    checkbox.checked = true;
                });
            }
            
            updateAddSelectedButton();
        }
    });

    document.body.addEventListener('click', function(e) {
        const addBtn = e.target.closest('.btn-add-to-class');
        if (addBtn) {
            e.preventDefault();
            const siswaId = addBtn.getAttribute('data-siswa-id');
            const siswaName = addBtn.getAttribute('data-siswa-name');
            const kelasId = document.getElementById('add_siswa_kelas_id').value;
            
            Swal.fire({
                title: 'Tambahkan Siswa?',
                html: `Tambahkan <strong>${siswaName}</strong> ke kelas ini?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Tambahkan!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Menambahkan siswa...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });
                    
                    fetch(`/admin/kelas/${kelasId}/add-siswa`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            siswa_ids: [siswaId]
                        })
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
                                text: data.message,
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
                }
            });
        }
    });

    document.body.addEventListener('click', function(e) {
        const addSelectedBtn = e.target.closest('.btn-add-selected-to-class');
        if (addSelectedBtn) {
            e.preventDefault();
            const kelasId = document.getElementById('add_siswa_kelas_id').value;
            const siswaIdsArray = Array.from(selectedSiswaIds);
            
            console.log('Add selected clicked. Selected IDs:', siswaIdsArray);
            
            if (siswaIdsArray.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan!',
                    text: 'Pilih minimal satu siswa',
                    confirmButtonColor: '#0d6efd'
                });
                return;
            }
            
            const selectedNames = allSiswaData
                .filter(siswa => siswaIdsArray.includes(siswa.id.toString()))
                .map(siswa => siswa.name);
            
            console.log('Selected names:', selectedNames);
            
            Swal.fire({
                title: 'Tambahkan Siswa?',
                html: `Tambahkan <strong>${siswaIdsArray.length} siswa</strong> ke kelas ini?<br><br>` +
                      `<small class="text-muted">${selectedNames.slice(0, 3).join(', ')}${selectedNames.length > 3 ? ` dan ${selectedNames.length - 3} lainnya` : ''}</small>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Tambahkan!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Menambahkan siswa...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });
                    
                    console.log('Sending request to add siswa:', siswaIdsArray);
                    
                    fetch(`/admin/kelas/${kelasId}/add-siswa`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            siswa_ids: siswaIdsArray
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Add siswa response:', data);
                        if (data.success) {
                            let message = data.message;
                            if (data.errors && data.errors.length > 0) {
                                message += `\n\nGagal menambahkan: ${data.errors.join(', ')}`;
                            }
                            
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: data.message,
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
                }
            });
        }
    });

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

    const createModal = document.getElementById('createKelasModal');
    if (createModal) {
        createModal.addEventListener('hidden.bs.modal', function() {
            if (createForm) createForm.reset();
        });
    }

    const editModal = document.getElementById('editKelasModal');
    if (editModal) {
        editModal.addEventListener('hidden.bs.modal', function() {
            const editForm = document.getElementById('editKelasForm');
            if (editForm) editForm.reset();
            document.getElementById('editKelasLoading').style.display = 'block';
            document.getElementById('editKelasFormContent').style.display = 'none';
            document.getElementById('editKelasSubmitBtn').style.display = 'none';
        });
    }

    const addSiswaModal = document.getElementById('addSiswaModal');
    if (addSiswaModal) {
        addSiswaModal.addEventListener('hidden.bs.modal', function() {
            selectedSiswaIds.clear();
            allSiswaData = [];
            currentKelasId = null;
            
            const searchInput = document.getElementById('search_siswa');
            if (searchInput) searchInput.value = '';
            
            setTimeout(() => {
                const backdrops = document.querySelectorAll('.modal-backdrop');
                backdrops.forEach(backdrop => backdrop.remove());
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            }, 300);
        });
    }

    document.body.addEventListener('input', function(e) {
        if (e.target.matches('input[name="kode_kelas"]')) {
            e.target.value = e.target.value.toUpperCase();
        }
    });

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

    console.log('Kelas JS fully loaded');
});


function loadAvailableSiswaList(kelasId) {
    console.log('Loading available siswa for kelas:', kelasId);
    
    const csrfToken = document.querySelector('input[name="_token"]').value;
    const container = document.getElementById('siswa_list_container');
    const countBadge = document.getElementById('available_siswa_count');
    
    container.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-secondary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-muted mt-2">Memuat daftar siswa...</p>
        </div>
    `;
    
    fetch(`/admin/kelas/${kelasId}/available-siswa`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        console.log('Available siswa response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Available siswa data:', data);
        
        if (data.success) {
            allSiswaData = data.siswa;
            countBadge.textContent = data.siswa.length;
            renderSiswaList(data.siswa);
        } else {
            throw new Error(data.message || 'Failed to load siswa');
        }
    })
    .catch(error => {
        console.error('Error loading siswa:', error);
        container.innerHTML = `
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Gagal memuat daftar siswa: ${error.message}
            </div>
        `;
        countBadge.textContent = '0';
    });
}

function renderSiswaList(siswaArray) {
    console.log('Rendering siswa list:', siswaArray.length, 'items');
    console.log('Current selected IDs:', Array.from(selectedSiswaIds));
    
    const container = document.getElementById('siswa_list_container');
    
    if (!siswaArray || siswaArray.length === 0) {
        container.innerHTML = `
            <div class="alert alert-info mb-0">
                <i class="bi bi-info-circle me-2"></i>
                Tidak ada siswa yang tersedia
            </div>
        `;
        updateAddSelectedButton();
        return;
    }
    
    const siswaHtml = siswaArray.map((siswa, index) => {
        const siswaIdStr = siswa.id.toString();
        const isSelected = selectedSiswaIds.has(siswaIdStr);
        console.log(`Siswa ${siswa.name} (ID: ${siswaIdStr}): isSelected = ${isSelected}`);
        
        return `
        <div class="card mb-2 border hover-shadow">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center flex-grow-1">
                        <input class="form-check-input me-3 siswa-checkbox-add" 
                               type="checkbox" 
                               value="${siswaIdStr}"
                               data-siswa-name="${siswa.name}"
                               ${isSelected ? 'checked' : ''}
                               id="siswa_checkbox_${siswaIdStr}">
                        <label for="siswa_checkbox_${siswaIdStr}" class="flex-grow-1 mb-0" style="cursor: pointer;">
                            <h6 class="mb-1">${siswa.name}</h6>
                            <small class="text-muted">
                                ${siswa.nis}
                            </small>
                        </label>
                    </div>
                    <button class="btn btn-sm btn-primary btn-add-to-class" 
                            data-siswa-id="${siswaIdStr}"
                            data-siswa-name="${siswa.name}"
                            title="Tambahkan ke kelas">
                        <i class="bi bi-plus-circle me-1"></i>Tambahkan
                    </button>
                </div>
            </div>
        </div>
    `;
    }).join('');
    
    container.innerHTML = siswaHtml;
    
    console.log('HTML rendered, initializing checkboxes...');
    
    initAddModalCheckboxes();
}

function updateAddSelectedButton() {
    const btn = document.querySelector('.btn-add-selected-to-class');
    const count = selectedSiswaIds.size;
    
    console.log('Updating button. Selected count:', count);
    
    if (btn) {
        const countSpan = btn.querySelector('.selected-count');
        if (countSpan) {
            countSpan.textContent = count;
        }
        
        if (count > 0) {
            btn.style.display = 'inline-block';
        } else {
            btn.style.display = 'none';
        }
    }
    
    const selectAllBtn = document.querySelector('.btn-select-all-add-siswa');
    if (selectAllBtn && allSiswaData.length > 0) {
        if (selectedSiswaIds.size === allSiswaData.length && allSiswaData.length > 0) {
            selectAllBtn.innerHTML = '<i class="bi bi-x-square me-1"></i>Batal Pilih Semua';
            selectAllBtn.classList.remove('btn-outline-primary');
            selectAllBtn.classList.add('btn-outline-danger');
        } else {
            selectAllBtn.innerHTML = '<i class="bi bi-check-square me-1"></i>Pilih Semua';
            selectAllBtn.classList.remove('btn-outline-danger');
            selectAllBtn.classList.add('btn-outline-primary');
        }
    }
}

function initAddModalCheckboxes() {
    const checkboxes = document.querySelectorAll('.siswa-checkbox-add');
    
    checkboxes.forEach(checkbox => {
        const newCheckbox = checkbox.cloneNode(true);
        checkbox.parentNode.replaceChild(newCheckbox, checkbox);
    });
    
    const newCheckboxes = document.querySelectorAll('.siswa-checkbox-add');
    newCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function(e) {
            const siswaId = this.value;
            
            console.log('Checkbox changed:', siswaId, 'checked:', this.checked);
            
            if (this.checked) {
                selectedSiswaIds.add(siswaId);
            } else {
                selectedSiswaIds.delete(siswaId);
            }
            
            console.log('Selected IDs:', Array.from(selectedSiswaIds));
            updateAddSelectedButton();
        });
    });
    
    updateAddSelectedButton();
}
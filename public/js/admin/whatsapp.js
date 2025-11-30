(function() {
    'use strict';

    // Global variables
    let devices = [];
    let routes = {};
    let csrfToken = '';

    /**
     * Initialize
     */
    document.addEventListener('DOMContentLoaded', function() {
        // Get data from window object (passed from Blade)
        if (window.WhatsAppSettings) {
            devices = window.WhatsAppSettings.devices || [];
            routes = window.WhatsAppSettings.routes || {};
            csrfToken = window.WhatsAppSettings.csrfToken || '';
        }

        // Initialize template preview
        initTemplatePreview();
        
        // Show session messages as SweetAlert
        showSessionMessages();
    });

    /**
     * Show session messages from Laravel as SweetAlert
     */
    function showSessionMessages() {
        // Success message
        const successAlert = document.querySelector('.alert-success');
        if (successAlert) {
            const message = successAlert.textContent.trim().replace(/×/g, '').replace(/\s+/g, ' ');
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: message,
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false,
                toast: true,
                position: 'top-end',
                customClass: {
                    popup: 'colored-toast'
                }
            });
            successAlert.remove();
        }

        // Error message
        const errorAlert = document.querySelector('.alert-danger');
        if (errorAlert) {
            const message = errorAlert.textContent
                .trim()
                .replace(/×/g, '')
                .replace('Terjadi kesalahan:', '')
                .replace(/\s+/g, ' ')
                .trim();
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: message,
                confirmButtonColor: '#dc3545'
            });
            errorAlert.remove();
        }

        // Warning message
        const warningAlert = document.querySelector('.alert-warning');
        if (warningAlert) {
            const message = warningAlert.textContent.trim().replace(/×/g, '').replace(/\s+/g, ' ');
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian!',
                text: message,
                confirmButtonColor: '#ffc107'
            });
            warningAlert.remove();
        }
    }

    /**
     * Template Preview Functions
     */
    function initTemplatePreview() {
        updatePreview();
        
        const checkinInput = document.getElementById('template_checkin');
        const checkoutInput = document.getElementById('template_checkout');
        
        if (checkinInput) {
            checkinInput.addEventListener('input', updatePreview);
        }
        
        if (checkoutInput) {
            checkoutInput.addEventListener('input', updatePreview);
        }
    }

    function updatePreview() {
        const checkinTemplate = document.getElementById('template_checkin')?.value || '';
        const checkoutTemplate = document.getElementById('template_checkout')?.value || '';
        
        const sampleData = {
            student_name: 'Ahmad Fauzi',
            nis: '2024001',
            class_name: 'XII RPL 1',
            checkin_time: '07:15',
            checkout_time: '15:30',
            date: new Date().toLocaleDateString('id-ID', { 
                day: 'numeric', 
                month: 'long', 
                year: 'numeric' 
            })
        };
        
        let checkinPreview = checkinTemplate;
        let checkoutPreview = checkoutTemplate;
        
        Object.keys(sampleData).forEach(key => {
            const regex = new RegExp(`{${key}}`, 'g');
            checkinPreview = checkinPreview.replace(regex, sampleData[key]);
            checkoutPreview = checkoutPreview.replace(regex, sampleData[key]);
        });
        
        const previewCheckin = document.getElementById('preview_checkin');
        const previewCheckout = document.getElementById('preview_checkout');
        
        if (previewCheckin) {
            previewCheckin.textContent = checkinPreview;
        }
        
        if (previewCheckout) {
            previewCheckout.textContent = checkoutPreview;
        }
    }

    /**
     * Device Management Functions
     */
    window.testDevice = function(deviceId) {
        Swal.fire({
            title: 'Testing...',
            text: 'Mengecek koneksi device',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });
        
        fetch(routes.testConnection, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ device_id: deviceId })
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
                    title: 'Koneksi Berhasil!',
                    text: data.message,
                    confirmButtonColor: '#198754',
                    timer: 2000,
                    timerProgressBar: true
                }).then(() => location.reload());
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Koneksi Gagal',
                    text: data.message,
                    confirmButtonColor: '#dc3545'
                });
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Terjadi kesalahan: ' + error.message,
                confirmButtonColor: '#dc3545'
            });
        });
    };

    window.testAllDevices = function() {
        Swal.fire({
            title: 'Testing All Devices...',
            text: 'Mohon tunggu',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });
        
        fetch(routes.testAll, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            Swal.fire({
                icon: 'info',
                title: 'Test Selesai',
                text: data.message,
                confirmButtonColor: '#0d6efd',
                timer: 2000,
                timerProgressBar: true
            }).then(() => location.reload());
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Terjadi kesalahan: ' + error.message,
                confirmButtonColor: '#dc3545'
            });
        });
    };

    window.editDevice = function(deviceId) {
        const device = devices.find(d => d.id === deviceId);
        if (!device) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Device tidak ditemukan',
                confirmButtonColor: '#dc3545'
            });
            return;
        }
        
        // Populate form
        document.getElementById('edit_device_id').value = device.id;
        document.getElementById('edit_name').value = device.name;
        document.getElementById('edit_api_key').value = device.api_key;
        document.getElementById('edit_phone').value = device.phone_number;
        document.getElementById('edit_device_id_field').value = device.device_id || '';
        document.getElementById('edit_priority').value = device.priority;
        document.getElementById('edit_is_active').checked = device.is_active;
        
        // Set form action
        const form = document.getElementById('editDeviceForm');
        form.action = `${routes.updateDevice}${deviceId}`;
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('editDeviceModal'));
        modal.show();
    };

    window.deleteDevice = function(deviceId, deviceName) {
        Swal.fire({
            title: 'Hapus Device?',
            html: `Apakah Anda yakin ingin menghapus<br><strong>${deviceName}</strong>?`,
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
                    text: 'Mohon tunggu',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                fetch(`${routes.deleteDevice}${deviceId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => {
                            throw new Error(err.message || `HTTP error! status: ${response.status}`);
                        }).catch(() => {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message,
                            timer: 2000,
                            timerProgressBar: true,
                            showConfirmButton: false,
                            toast: true,
                            position: 'top-end',
                            customClass: {
                                popup: 'colored-toast'
                            }
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: data.message || 'Gagal menghapus device',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Terjadi kesalahan: ' + error.message,
                        confirmButtonColor: '#dc3545'
                    });
                });
            }
        });
    };

    /**
     * Test Message Functions
     */
    window.showTestMessageModal = function() {
        const modal = new bootstrap.Modal(document.getElementById('testMessageModal'));
        modal.show();
    };

    window.sendTestMessage = function() {
        const phoneNumber = document.getElementById('testPhoneNumber').value.trim();
        
        if (!phoneNumber) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian!',
                text: 'Nomor WhatsApp harus diisi!',
                confirmButtonColor: '#ffc107'
            });
            return;
        }
        
        // Validate phone number format
        const phoneRegex = /^(08|628)[0-9]{8,13}$/;
        if (!phoneRegex.test(phoneNumber)) {
            Swal.fire({
                icon: 'warning',
                title: 'Format Salah!',
                text: 'Nomor WhatsApp harus dimulai dengan 08 atau 628 dan 10-15 digit',
                confirmButtonColor: '#ffc107'
            });
            return;
        }
        
        // Close modal
        const modalElement = document.getElementById('testMessageModal');
        const modal = bootstrap.Modal.getInstance(modalElement);
        if (modal) {
            modal.hide();
        }
        
        // Show loading
        Swal.fire({
            title: 'Mengirim...',
            text: 'Mohon tunggu sebentar',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });
        
        fetch(routes.testMessage, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ phone_number: phoneNumber })
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
                    html: data.message,
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end',
                    customClass: {
                        popup: 'colored-toast'
                    }
                }).then(() => {
                    document.getElementById('testPhoneNumber').value = '';
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
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Terjadi kesalahan: ' + error.message,
                confirmButtonColor: '#dc3545'
            });
        });
    };

    /**
     * Reset form on modal close
     */
    const addDeviceModal = document.getElementById('addDeviceModal');
    if (addDeviceModal) {
        addDeviceModal.addEventListener('hidden.bs.modal', function() {
            document.getElementById('addDeviceForm').reset();
        });
    }

    const editDeviceModal = document.getElementById('editDeviceModal');
    if (editDeviceModal) {
        editDeviceModal.addEventListener('hidden.bs.modal', function() {
            document.getElementById('editDeviceForm').reset();
        });
    }

    const testMessageModal = document.getElementById('testMessageModal');
    if (testMessageModal) {
        testMessageModal.addEventListener('hidden.bs.modal', function() {
            document.getElementById('testPhoneNumber').value = '';
        });
    }

})();
@extends('layouts.admin')

@section('title', 'Pengaturan WhatsApp Notification')

@section('content')
<div class="bg-primary pt-10 pb-21"></div>
<div class="container-fluid mt-n22 px-6">
    <div class="row">
        <div class="col-lg-12 col-md-12 col-12">
            <div class="mb-4">
                <h3 class="mb-0 text-white">Pengaturan Notifikasi WhatsApp</h3>
                <p class="text-white-50 mb-0">Konfigurasi Fonnte API untuk notifikasi otomatis ke orang tua</p>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="row mt-6">
        <div class="col-12">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="row mt-6">
        <div class="col-12">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    </div>
    @endif

    <div class="row mt-6">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h4 class="mb-0">
                        <i class="bi bi-whatsapp text-success me-2"></i>
                        Konfigurasi Fonnte API
                    </h4>
                </div>
                <form action="{{ route('admin.settings.whatsapp.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="card-body">
                        <!-- Status Toggle -->
                        <div class="mb-4 p-3 border rounded bg-light">
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="fonnte_enabled" 
                                       name="fonnte_enabled"
                                       {{ $settings['fonnte_enabled'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="fonnte_enabled">
                                    <strong>Aktifkan Notifikasi WhatsApp</strong>
                                    <br>
                                    <small class="text-muted">
                                        Kirim notifikasi otomatis ke orang tua saat siswa melakukan presensi
                                    </small>
                                </label>
                            </div>
                        </div>

                        <!-- API Key -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                API Key Fonnte
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-key"></i>
                                </span>
                                <input type="text" 
                                       class="form-control" 
                                       name="fonnte_api_key"
                                       id="fonnte_api_key"
                                       value="{{ $settings['fonnte_api_key'] }}"
                                       placeholder="Masukkan API Key dari Fonnte"
                                       required>
                            </div>
                            <small class="text-muted">
                                Dapatkan API Key dari dashboard Fonnte Anda di 
                                <a href="https://fonnte.com" target="_blank">fonnte.com</a>
                            </small>
                        </div>

                        <!-- Nomor Pengirim -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                Nomor WhatsApp Pengirim
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-phone"></i>
                                </span>
                                <input type="text" 
                                       class="form-control" 
                                       name="fonnte_sender_number"
                                       id="fonnte_sender_number"
                                       value="{{ $settings['fonnte_sender_number'] }}"
                                       placeholder="contoh: 628123456789"
                                       required>
                            </div>
                            <small class="text-muted">
                                Nomor WhatsApp yang terhubung di Fonnte (format: 628xxx atau 08xxx)
                            </small>
                        </div>

                        <hr class="my-4">

                        <!-- Tab Navigation untuk 2 Template -->
                        <ul class="nav nav-tabs mb-3" id="templateTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="checkin-tab" data-bs-toggle="tab" 
                                        data-bs-target="#checkin-template" type="button">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>
                                    Template Check-In
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="checkout-tab" data-bs-toggle="tab" 
                                        data-bs-target="#checkout-template" type="button">
                                    <i class="bi bi-box-arrow-right me-2"></i>
                                    Template Check-Out
                                </button>
                            </li>
                        </ul>

                        <!-- Tab Content -->
                        <div class="tab-content" id="templateTabsContent">
                            <!-- Check-In Template -->
                            <div class="tab-pane fade show active" id="checkin-template" role="tabpanel">
                                <div class="mb-4">
                                    <label class="form-label fw-semibold">
                                        Template Pesan Check-In
                                        <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control font-monospace" 
                                              name="fonnte_message_template"
                                              id="fonnte_message_template_checkin"
                                              rows="12"
                                              required>{{ $settings['fonnte_message_template'] ?? '' }}</textarea>
                                    
                                    <div class="alert alert-info mt-2 mb-0">
                                        <strong>
                                            <i class="bi bi-info-circle me-1"></i>
                                            Variabel untuk Check-In:
                                        </strong>
                                        <div class="row mt-2">
                                            <div class="col-md-6">
                                                <ul class="mb-0 small">
                                                    <li><code>{student_name}</code> - Nama siswa</li>
                                                    <li><code>{nis}</code> - NIS siswa</li>
                                                    <li><code>{class_name}</code> - Nama kelas</li>
                                                </ul>
                                            </div>
                                            <div class="col-md-6">
                                                <ul class="mb-0 small">
                                                    <li><code>{checkin_time}</code> - Waktu check-in</li>
                                                    <li><code>{status}</code> - Status (MASUK)</li>
                                                    <li><code>{date}</code> - Tanggal</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Preview Check-In -->
                                <div class="mb-0">
                                    <label class="form-label fw-semibold">Preview Pesan Check-In</label>
                                    <div class="card bg-success-soft">
                                        <div class="card-body">
                                            <div class="d-flex align-items-start">
                                                <i class="bi bi-whatsapp text-success fs-3 me-3"></i>
                                                <div class="flex-grow-1">
                                                    <div class="mb-2">
                                                        <small class="text-muted">Dari: <strong id="previewSenderCheckin">Loading...</strong></small>
                                                    </div>
                                                    <pre class="mb-0 small" id="messagePreviewCheckin" style="white-space: pre-wrap; word-wrap: break-word;"></pre>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Check-Out Template -->
                            <div class="tab-pane fade" id="checkout-template" role="tabpanel">
                                <div class="mb-4">
                                    <label class="form-label fw-semibold">
                                        Template Pesan Check-Out
                                        <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control font-monospace" 
                                              name="fonnte_message_template_checkout"
                                              id="fonnte_message_template_checkout"
                                              rows="12"
                                              required>{{ $settings['fonnte_message_template_checkout'] ?? '' }}</textarea>
                                    
                                    <div class="alert alert-info mt-2 mb-0">
                                        <strong>
                                            <i class="bi bi-info-circle me-1"></i>
                                            Variabel untuk Check-Out:
                                        </strong>
                                        <div class="row mt-2">
                                            <div class="col-md-6">
                                                <ul class="mb-0 small">
                                                    <li><code>{student_name}</code> - Nama siswa</li>
                                                    <li><code>{nis}</code> - NIS siswa</li>
                                                    <li><code>{class_name}</code> - Nama kelas</li>
                                                </ul>
                                            </div>
                                            <div class="col-md-6">
                                                <ul class="mb-0 small">
                                                    <li><code>{checkin_time}</code> - Waktu check-in</li>
                                                    <li><code>{checkout_time}</code> - Waktu check-out</li>
                                                    <li><code>{date}</code> - Tanggal</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Preview Check-Out -->
                                <div class="mb-0">
                                    <label class="form-label fw-semibold">Preview Pesan Check-Out</label>
                                    <div class="card bg-warning-soft">
                                        <div class="card-body">
                                            <div class="d-flex align-items-start">
                                                <i class="bi bi-whatsapp text-success fs-3 me-3"></i>
                                                <div class="flex-grow-1">
                                                    <div class="mb-2">
                                                        <small class="text-muted">Dari: <strong id="previewSenderCheckout">Loading...</strong></small>
                                                    </div>
                                                    <pre class="mb-0 small" id="messagePreviewCheckout" style="white-space: pre-wrap; word-wrap: break-word;"></pre>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer bg-white d-flex justify-content-between">
                        <div>
                            <button type="button" class="btn btn-outline-primary" id="btnTestConnection">
                                <i class="bi bi-cloud-check me-2"></i>Test Koneksi
                            </button>
                            <button type="button" class="btn btn-outline-success" id="btnTestMessage">
                                <i class="bi bi-send me-2"></i>Kirim Test Pesan
                            </button>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Simpan Pengaturan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="col-lg-4">
            <!-- Cara Setup -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-book me-2"></i>
                        Cara Setup Fonnte
                    </h5>
                </div>
                <div class="card-body">
                    <ol class="mb-0 ps-3">
                        <li class="mb-3">
                            Buka <a href="https://fonnte.com" target="_blank">fonnte.com</a> dan daftar akun
                        </li>
                        <li class="mb-3">
                            Hubungkan nomor WhatsApp Anda dengan scan QR Code
                        </li>
                        <li class="mb-3">
                            Salin API Key dari dashboard Fonnte
                        </li>
                        <li class="mb-3">
                            Salin nomor WhatsApp yang terhubung
                        </li>
                        <li class="mb-3">
                            Paste API Key dan nomor di form
                        </li>
                        <li class="mb-3">
                            Sesuaikan template pesan check-in dan check-out
                        </li>
                        <li class="mb-0">
                            Klik "Test Koneksi" untuk validasi
                        </li>
                    </ol>
                </div>
            </div>

            <!-- Status Connection -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-activity me-2"></i>
                        Status Koneksi
                    </h5>
                </div>
                <div class="card-body">
                    <div id="connectionStatus" class="text-center py-3">
                        <i class="bi bi-question-circle text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-3 mb-0">
                            Klik "Test Koneksi" untuk mengecek status
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Test Message -->
<div class="modal fade" id="testMessageModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-send me-2"></i>
                    Kirim Test Pesan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Tipe Template
                        <span class="text-danger">*</span>
                    </label>
                    <select class="form-select" id="testTemplateType">
                        <option value="checkin">Check-In</option>
                        <option value="checkout">Check-Out</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Nomor WhatsApp Tujuan
                        <span class="text-danger">*</span>
                    </label>
                    <input type="text" 
                           class="form-control" 
                           id="testPhoneNumber"
                           placeholder="contoh: 08123456789"
                           required>
                    <small class="text-muted">
                        Format: 08xxx atau 628xxx
                    </small>
                </div>
                <div class="alert alert-info mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    Pesan test akan dikirim ke nomor ini untuk memastikan konfigurasi bekerja dengan baik.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="btnSendTestMessage">
                    <i class="bi bi-send me-2"></i>
                    Kirim Test
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function() {
    document.addEventListener('DOMContentLoaded', function() {
        const enabledCheckbox = document.getElementById('fonnte_enabled');
        const currentEnabledStatus = {{ $settings['fonnte_enabled'] ? 'true' : 'false' }};
        
        if (enabledCheckbox && currentEnabledStatus) {
            enabledCheckbox.checked = true;
        }
    });

    const templateCheckinTextarea = document.getElementById('fonnte_message_template_checkin');
    const templateCheckoutTextarea = document.getElementById('fonnte_message_template_checkout');
    const previewCheckinDiv = document.getElementById('messagePreviewCheckin');
    const previewCheckoutDiv = document.getElementById('messagePreviewCheckout');
    const previewSenderCheckin = document.getElementById('previewSenderCheckin');
    const previewSenderCheckout = document.getElementById('previewSenderCheckout');
    const senderInput = document.getElementById('fonnte_sender_number');
    const testMessageModal = new bootstrap.Modal(document.getElementById('testMessageModal'));

    function updateCheckinPreview() {
        let template = templateCheckinTextarea.value;
        const preview = template
            .replace(/{student_name}/g, 'Ahmad Fauzi')
            .replace(/{nis}/g, '2024001')
            .replace(/{class_name}/g, 'XII RPL 1')
            .replace(/{status}/g, '✅ MASUK')
            .replace(/{checkin_time}/g, '07:15')
            .replace(/{date}/g, new Date().toLocaleDateString('id-ID', { 
                day: 'numeric', 
                month: 'long', 
                year: 'numeric' 
            }));
        previewCheckinDiv.textContent = preview;
        const senderNumber = senderInput.value || 'Belum diisi';
        previewSenderCheckin.textContent = senderNumber;
    }

    function updateCheckoutPreview() {
        let template = templateCheckoutTextarea.value;
        const preview = template
            .replace(/{student_name}/g, 'Ahmad Fauzi')
            .replace(/{nis}/g, '2024001')
            .replace(/{class_name}/g, 'XII RPL 1')
            .replace(/{checkin_time}/g, '07:15')
            .replace(/{checkout_time}/g, '15:30')
            .replace(/{date}/g, new Date().toLocaleDateString('id-ID', { 
                day: 'numeric', 
                month: 'long', 
                year: 'numeric' 
            }));
        previewCheckoutDiv.textContent = preview;
        const senderNumber = senderInput.value || 'Belum diisi';
        previewSenderCheckout.textContent = senderNumber;
    }

    updateCheckinPreview();
    updateCheckoutPreview();
    templateCheckinTextarea.addEventListener('input', updateCheckinPreview);
    templateCheckoutTextarea.addEventListener('input', updateCheckoutPreview);
    senderInput.addEventListener('input', function() {
        updateCheckinPreview();
        updateCheckoutPreview();
    });

    document.getElementById('btnTestConnection').addEventListener('click', function() {
        const btn = this;
        const originalHTML = btn.innerHTML;
        const apiKey = document.getElementById('fonnte_api_key').value;
        const senderNumber = document.getElementById('fonnte_sender_number').value;

        if (!apiKey) {
            Swal.fire({
                icon: 'warning', 
                title: 'API Key Kosong', 
                text: 'Silakan masukkan API Key terlebih dahulu'
            });
            return;
        }
        
        if (!senderNumber) {
            Swal.fire({
                icon: 'warning', 
                title: 'Nomor Pengirim Kosong', 
                text: 'Silakan masukkan nomor WhatsApp pengirim terlebih dahulu'
            });
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Testing...';

        fetch('{{ route("admin.settings.whatsapp.test-connection") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json', 
                'X-CSRF-TOKEN': '{{ csrf_token() }}', 
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                api_key: apiKey,
                sender_number: senderNumber
            })
        })
        .then(response => response.json())
        .then(data => {
            const statusDiv = document.getElementById('connectionStatus');
            
            if (data.success) {
                statusDiv.innerHTML = `
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                    <p class="text-success fw-semibold mt-3 mb-2">Koneksi Berhasil!</p>
                    <small class="text-muted">API Key valid dan siap digunakan</small>
                    <div class="mt-3 text-start">
                        <small class="text-muted">
                            <strong>Device:</strong> ${data.device_name || 'N/A'}<br>
                            <strong>Nomor:</strong> ${senderNumber}
                        </small>
                    </div>
                `;
                
                Swal.fire({
                    icon: 'success', 
                    title: 'Koneksi Berhasil!', 
                    html: `
                        <p>API Key valid dan Fonnte siap digunakan</p>
                        <div class="alert alert-info mt-3 mb-0 text-start">
                            ${data.device_name ? '<div><strong>Device:</strong> ' + data.device_name + '</div>' : ''}
                            <div><strong>Nomor Pengirim:</strong> ${senderNumber}</div>
                        </div>
                    `, 
                    confirmButtonColor: '#198754'
                });
            } else {
                statusDiv.innerHTML = `
                    <i class="bi bi-x-circle-fill text-danger" style="font-size: 3rem;"></i>
                    <p class="text-danger fw-semibold mt-3 mb-0">Koneksi Gagal</p>
                    <small class="text-muted">${data.message}</small>
                `;
                
                Swal.fire({
                    icon: 'error', 
                    title: 'Koneksi Gagal', 
                    html: `
                        <p class="mb-3">${data.message || 'Gagal terhubung ke Fonnte'}</p>
                        <div class="alert alert-warning text-start mb-0">
                            <strong>Tips:</strong>
                            <ul class="mb-0 mt-2 small">
                                <li>Pastikan API Key benar</li>
                                <li>Pastikan nomor WhatsApp sudah terhubung di Fonnte</li>
                                <li>Cek status device di dashboard Fonnte</li>
                            </ul>
                        </div>
                    `, 
                    confirmButtonColor: '#dc3545'
                });
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error', 
                title: 'Error', 
                text: 'Terjadi kesalahan: ' + error.message, 
                confirmButtonColor: '#dc3545'
            });
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalHTML;
        });
    });

    document.getElementById('btnTestMessage').addEventListener('click', function() {
        const apiKey = document.getElementById('fonnte_api_key').value;
        const senderNumber = document.getElementById('fonnte_sender_number').value;
        
        if (!apiKey) {
            Swal.fire({
                icon: 'warning', 
                title: 'API Key Kosong', 
                text: 'Silakan masukkan API Key terlebih dahulu'
            });
            return;
        }
        
        if (!senderNumber) {
            Swal.fire({
                icon: 'warning', 
                title: 'Nomor Pengirim Kosong', 
                text: 'Silakan masukkan nomor WhatsApp pengirim'
            });
            return;
        }
        
        testMessageModal.show();
    });

    document.getElementById('btnSendTestMessage').addEventListener('click', function() {
        const btn = this;
        const originalHTML = btn.innerHTML;
        const phoneNumber = document.getElementById('testPhoneNumber').value.trim();
        const templateType = document.getElementById('testTemplateType').value;

        if (!phoneNumber) {
            Swal.fire({
                icon: 'warning', 
                title: 'Nomor Kosong', 
                text: 'Silakan masukkan nomor WhatsApp tujuan'
            });
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';

        fetch('{{ route("admin.settings.whatsapp.test-message") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json', 
                'X-CSRF-TOKEN': '{{ csrf_token() }}', 
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                phone_number: phoneNumber,
                template_type: templateType
            })
        })
        .then(response => response.json())
        .then(data => {
            testMessageModal.hide();
            
            if (data.success) {
                Swal.fire({
                    icon: 'success', 
                    title: 'Pesan Terkirim!', 
                    html: `Pesan test <strong>${templateType === 'checkin' ? 'Check-In' : 'Check-Out'}</strong> berhasil dikirim ke <strong>${phoneNumber}</strong>`, 
                    confirmButtonColor: '#198754'
                });
                document.getElementById('testPhoneNumber').value = '';
            } else {
                Swal.fire({
                    icon: 'error', 
                    title: 'Gagal Mengirim', 
                    text: data.message || 'Gagal mengirim pesan test', 
                    confirmButtonColor: '#dc3545'
                });
            }
        })
        .catch(error => {
            testMessageModal.hide();
            Swal.fire({
                icon: 'error', 
                title: 'Error', 
                text: 'Terjadi kesalahan: ' + error.message, 
                confirmButtonColor: '#dc3545'
            });
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalHTML;
        });
    });
})();
</script>
@endpush
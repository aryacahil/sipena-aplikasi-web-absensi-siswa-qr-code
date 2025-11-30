@extends('layouts.admin')

@section('title', 'Pengaturan WhatsApp Notification')

@section('content')
<div class="bg-primary pt-10 pb-21"></div>
<div class="container-fluid mt-n22 px-6">
    <div class="row">
        <div class="col-lg-12 col-md-12 col-12">
            <div class="mb-4">
                <h3 class="mb-0 text-white">Pengaturan Notifikasi WhatsApp</h3>
                <p class="text-white-50 mb-0">Kelola device, template pesan, dan pengaturan notifikasi</p>
            </div>
        </div>
    </div>

    {{-- Session Messages (will be shown as SweetAlert by JS) --}}
    @if(session('success'))
    <div class="alert alert-success d-none">{{ session('success') }}</div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger d-none">{{ session('error') }}</div>
    @endif

    @if(session('warning'))
    <div class="alert alert-warning d-none">{{ session('warning') }}</div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger d-none">
        @foreach($errors->all() as $error)
            {{ $error }}
        @endforeach
    </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-xl-4 col-lg-4 col-md-6 col-12">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Device</h6>
                            <h2 class="mb-0 fw-bold text-primary">{{ $stats['total_devices'] }}</h2>
                        </div>
                        <div class="icon-shape icon-md bg-primary-soft text-primary rounded-circle">
                            <i class="bi bi-phone fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-4 col-md-6 col-12">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Device Aktif</h6>
                            <h2 class="mb-0 fw-bold text-success">{{ $stats['active_devices'] }}</h2>
                        </div>
                        <div class="icon-shape icon-md bg-success-soft text-success rounded-circle">
                            <i class="bi bi-check-circle fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-4 col-md-6 col-12">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Terhubung</h6>
                            <h2 class="mb-0 fw-bold text-info">{{ $stats['connected_devices'] }}</h2>
                        </div>
                        <div class="icon-shape icon-md bg-info-soft text-info rounded-circle">
                            <i class="bi bi-wifi fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Device Management Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">
                        <i class="bi bi-phone-fill text-primary me-2"></i>
                        Kelola Device WhatsApp
                    </h4>
                    <p class="text-muted small mb-0 mt-1">Klik device untuk mengelola</p>
                </div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDeviceModal">
                    <i class="bi bi-plus-circle me-2"></i>Tambah Device
                </button>
            </div>
        </div>
        
        <div class="card-body">
            @if($devices->isEmpty())
            <div class="text-center py-5">
                <i class="bi bi-phone-x text-muted" style="font-size: 4rem;"></i>
                <p class="text-muted mt-3 mb-2 fw-semibold">Belum ada device</p>
                <p class="text-muted small">Klik tombol "Tambah Device" untuk menambahkan device WhatsApp</p>
            </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 25%;">Nama Device</th>
                            <th style="width: 15%;">Nomor WA</th>
                            <th style="width: 10%;" class="text-center">Priority</th>
                            <th style="width: 15%;" class="text-center">Status</th>
                            <th style="width: 20%;">Terakhir Digunakan</th>
                            <th style="width: 15%;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($devices as $device)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="icon-shape icon-sm bg-primary-soft text-primary rounded me-2">
                                        <i class="bi bi-phone"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-semibold">{{ $device->name }}</h6>
                                        <small class="text-muted">{{ substr($device->api_key, 0, 25) }}...</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="text-dark">{{ $device->formatted_phone }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary">{{ $device->priority }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $device->status_badge_color }}">
                                    {{ $device->status_label }}
                                </span>
                            </td>
                            <td>
                                @if($device->last_used_at)
                                    <small class="text-muted">{{ $device->last_used_at->diffForHumans() }}</small>
                                @else
                                    <small class="text-muted fst-italic">Belum digunakan</small>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-inline-flex gap-1">
                                    <button type="button" 
                                            class="btn btn-sm btn-info text-white" 
                                            onclick="testDevice({{ $device->id }})"
                                            title="Test Connection">
                                        <i class="bi bi-wifi"></i>
                                    </button>
                                    <button type="button" 
                                            class="btn btn-sm btn-primary" 
                                            onclick="editDevice({{ $device->id }})"
                                            title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" 
                                            class="btn btn-sm btn-danger" 
                                            onclick="deleteDevice({{ $device->id }}, '{{ $device->name }}')"
                                            title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3 border-top pt-3">
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="testAllDevices()">
                    <i class="bi bi-arrow-repeat me-2"></i>Test Semua Device
                </button>
            </div>
            @endif
        </div>
    </div>

    <!-- Template Messages Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white border-bottom">
            <h4 class="mb-0">
                <i class="bi bi-chat-text-fill text-success me-2"></i>
                Template Pesan
            </h4>
            <p class="text-muted small mb-0 mt-1">Atur template pesan notifikasi WhatsApp</p>
        </div>
        
        <form action="{{ route('admin.settings.whatsapp.update') }}" method="POST" id="settingsForm">
            @csrf
            @method('PUT')
            
            <div class="card-body">
                <!-- Tab Navigation -->
                <ul class="nav nav-tabs mb-4" id="templateTabs" role="tablist">
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
                        <div class="row">
                            <div class="col-lg-6">
                                <label class="form-label fw-semibold">Template Pesan Check-In</label>
                                <textarea class="form-control font-monospace" 
                                          name="fonnte_message_template_checkin"
                                          id="template_checkin"
                                          rows="12"
                                          required>{{ old('fonnte_message_template_checkin', $settings['fonnte_message_template_checkin']) }}</textarea>
                                <small class="text-muted mt-2 d-block">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Gunakan variabel: {student_name}, {nis}, {class_name}, {checkin_time}, {date}
                                </small>
                            </div>
                            <div class="col-lg-6">
                                <label class="form-label fw-semibold">Preview</label>
                                <div class="card bg-light border">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start">
                                            <i class="bi bi-whatsapp text-success fs-3 me-3"></i>
                                            <div class="flex-grow-1">
                                                <pre class="mb-0 small" id="preview_checkin" style="white-space: pre-wrap; word-wrap: break-word; font-family: inherit;"></pre>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Check-Out Template -->
                    <div class="tab-pane fade" id="checkout-template" role="tabpanel">
                        <div class="row">
                            <div class="col-lg-6">
                                <label class="form-label fw-semibold">Template Pesan Check-Out</label>
                                <textarea class="form-control font-monospace" 
                                          name="fonnte_message_template_checkout"
                                          id="template_checkout"
                                          rows="12"
                                          required>{{ old('fonnte_message_template_checkout', $settings['fonnte_message_template_checkout']) }}</textarea>
                                <small class="text-muted mt-2 d-block">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Gunakan variabel: {student_name}, {nis}, {class_name}, {checkin_time}, {checkout_time}, {date}
                                </small>
                            </div>
                            <div class="col-lg-6">
                                <label class="form-label fw-semibold">Preview</label>
                                <div class="card bg-light border">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start">
                                            <i class="bi bi-whatsapp text-success fs-3 me-3"></i>
                                            <div class="flex-grow-1">
                                                <pre class="mb-0 small" id="preview_checkout" style="white-space: pre-wrap; word-wrap: break-word; font-family: inherit;"></pre>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Global Settings Footer -->
            <div class="card-footer bg-white border-top">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="fonnte_enabled" 
                                   name="fonnte_enabled"
                                   value="1"
                                   {{ old('fonnte_enabled', $settings['fonnte_enabled']) ? 'checked' : '' }}>
                            <label class="form-check-label" for="fonnte_enabled">
                                <strong>Aktifkan Notifikasi WhatsApp</strong>
                                <br>
                                <small class="text-muted">Kirim notifikasi otomatis saat check-in/check-out</small>
                            </label>
                        </div>
                    </div>
                    <div class="col-lg-6 text-lg-end mt-3 mt-lg-0">
                        <button type="button" class="btn btn-outline-success" onclick="showTestMessageModal()">
                            <i class="bi bi-send me-2"></i>Kirim Test Pesan
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Simpan Pengaturan
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Add Device -->
<div class="modal fade" id="addDeviceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('admin.settings.whatsapp.devices.store') }}" method="POST" id="addDeviceForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle text-primary me-2"></i>
                        Tambah Device Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Device <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" placeholder="Contoh: Device 1, HP Admin" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">API Key Fonnte <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="api_key" placeholder="Masukkan API Key dari Fonnte" required>
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Dapatkan dari <a href="https://fonnte.com" target="_blank" class="text-primary">fonnte.com</a>
                        </small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nomor WhatsApp <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="phone_number" placeholder="628123456789" required>
                        <small class="text-muted">Format: 628xxx atau 08xxx</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Device ID (Opsional)</label>
                        <input type="text" class="form-control" name="device_id" placeholder="Device ID dari Fonnte (jika ada)">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Priority <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="priority" value="1" min="1" max="100" required>
                        <small class="text-muted">Angka lebih kecil = prioritas lebih tinggi (1 = tertinggi)</small>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active_add" value="1" checked>
                        <label class="form-check-label" for="is_active_add">
                            Aktifkan device ini
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Tambah Device
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit Device -->
<div class="modal fade" id="editDeviceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="editDeviceForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square text-primary me-2"></i>
                        Edit Device
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit_device_id">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Device <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="edit_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">API Key Fonnte <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="api_key" id="edit_api_key" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nomor WhatsApp <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="phone_number" id="edit_phone" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Device ID (Opsional)</label>
                        <input type="text" class="form-control" name="device_id" id="edit_device_id_field">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Priority <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="priority" id="edit_priority" min="1" max="100" required>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="edit_is_active" value="1">
                        <label class="form-check-label" for="edit_is_active">
                            Aktifkan device ini
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-2"></i>Update Device
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Test Message -->
<div class="modal fade" id="testMessageModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-send text-success me-2"></i>
                    Kirim Test Pesan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nomor WhatsApp Tujuan</label>
                    <input type="text" 
                           class="form-control" 
                           id="testPhoneNumber"
                           placeholder="Contoh: 08123456789"
                           required>
                    <small class="text-muted">Format: 08xxx atau 628xxx</small>
                </div>
                <div class="alert alert-info mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    <small>Pesan akan dikirim menggunakan device yang tersedia</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-2"></i>Batal
                </button>
                <button type="button" class="btn btn-success" onclick="sendTestMessage()">
                    <i class="bi bi-send me-2"></i>Kirim Test
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/admin/whatsapp.js') }}"></script>
<script>
    // Pass data dari Laravel ke JavaScript
    window.WhatsAppSettings = {
        devices: @json($devices),
        routes: {
            testConnection: '{{ route("admin.settings.whatsapp.devices.test-connection") }}',
            testAll: '{{ route("admin.settings.whatsapp.devices.test-all") }}',
            testMessage: '{{ route("admin.settings.whatsapp.test-message") }}',
            toggleDevice: '/admin/settings/whatsapp/devices/',
            deleteDevice: '/admin/settings/whatsapp/devices/',
            updateDevice: '/admin/settings/whatsapp/devices/'
        },
        csrfToken: '{{ csrf_token() }}'
    };
</script>
@endpush
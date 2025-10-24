@extends('layouts.guru')

@section('content')
<div class="bg-primary pt-10 pb-21"></div>
<div class="container-fluid mt-n22 px-6">
    <div class="row">
        <div class="col-lg-12 col-md-12 col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div class="mb-2 mb-lg-0">
                    <h3 class="mb-0 text-white">Detail Session Presensi</h3>
                </div>
                <div>
                    <a href="{{ route('guru.presensi.index') }}" class="btn btn-white">
                        <i class="bi bi-arrow-left me-2"></i>Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-6">
        <!-- QR Code Card -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-qr-code me-2"></i>QR Code Presensi
                    </h5>
                </div>
                <div class="card-body text-center">
                    <div class="qr-code-container p-3 bg-white rounded shadow-sm d-inline-block">
                        {!! $qrCode !!}
                    </div>
                    
                    <div class="mt-3">
                        <span class="badge bg-{{ $session->status == 'active' ? 'success' : 'secondary' }} fs-6">
                            {{ $session->status == 'active' ? 'AKTIF' : 'DITUTUP' }}
                        </span>
                    </div>
                    
                    <div class="mt-4">
                        <a href="{{ route('guru.presensi.download-qr', $session->id) }}" 
                           class="btn btn-primary w-100 mb-2">
                            <i class="bi bi-download me-2"></i>Download QR Code
                        </a>
                        
                        @if($session->status == 'active')
                        <form action="{{ route('guru.presensi.close', $session->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-warning w-100 mb-2" 
                                    onclick="return confirm('Tutup session presensi ini?')">
                                <i class="bi bi-lock me-2"></i>Tutup Session
                            </button>
                        </form>
                        @else
                        <form action="{{ route('guru.presensi.reopen', $session->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100 mb-2" 
                                    onclick="return confirm('Buka kembali session ini?')">
                                <i class="bi bi-unlock me-2"></i>Buka Kembali
                            </button>
                        </form>
                        @endif
                        
                        <button type="button" class="btn btn-info w-100" 
                                data-bs-toggle="modal" data-bs-target="#absenManualModal">
                            <i class="bi bi-pencil-square me-2"></i>Absen Manual
                        </button>
                    </div>
                </div>
            </div>

            <!-- Info Session -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0">Informasi Session</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Kelas:</strong></p>
                    <p class="text-muted">
                        <span class="badge bg-primary">{{ $session->kelas->nama_kelas }}</span><br>
                        {{ $session->kelas->jurusan->nama_jurusan }}
                    </p>
                    
                    <p class="mb-2 mt-3"><strong>Tanggal:</strong></p>
                    <p class="text-muted">{{ $session->tanggal->format('d F Y') }}</p>
                    
                    <p class="mb-2 mt-3"><strong>Waktu:</strong></p>
                    <p class="text-muted">{{ $session->jam_mulai }} - {{ $session->jam_selesai }}</p>
                    
                    <p class="mb-2 mt-3"><strong>Radius:</strong></p>
                    <p class="text-muted">{{ $session->radius }} meter</p>
                    
                    @if($session->keterangan)
                    <p class="mb-2 mt-3"><strong>Keterangan:</strong></p>
                    <p class="text-muted">{{ $session->keterangan }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Statistik & Daftar -->
        <div class="col-md-8">
            <!-- Statistik -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Statistik Kehadiran</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h3 class="mb-0">{{ $totalSiswa }}</h3>
                                    <small>Total Siswa</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h3 class="mb-0">{{ $totalHadir }}</h3>
                                    <small>Hadir</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h3 class="mb-0">{{ $totalIzin + $totalSakit }}</h3>
                                    <small>Izin/Sakit</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <h3 class="mb-0">{{ $belumAbsen }}</h3>
                                    <small>Belum Absen</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="progress mt-3" style="height: 30px;">
                        @php
                            $persenHadir = $totalSiswa > 0 ? ($totalHadir / $totalSiswa) * 100 : 0;
                            $persenIzin = $totalSiswa > 0 ? ($totalIzin / $totalSiswa) * 100 : 0;
                            $persenSakit = $totalSiswa > 0 ? ($totalSakit / $totalSiswa) * 100 : 0;
                            $persenBelum = $totalSiswa > 0 ? ($belumAbsen / $totalSiswa) * 100 : 0;
                        @endphp
                        <div class="progress-bar bg-success" style="width: {{ $persenHadir }}%" 
                             title="Hadir: {{ $totalHadir }}">
                            {{ $totalHadir }}
                        </div>
                        <div class="progress-bar bg-warning" style="width: {{ $persenIzin }}%" 
                             title="Izin: {{ $totalIzin }}">
                            {{ $totalIzin }}
                        </div>
                        <div class="progress-bar bg-info" style="width: {{ $persenSakit }}%" 
                             title="Sakit: {{ $totalSakit }}">
                            {{ $totalSakit }}
                        </div>
                        <div class="progress-bar bg-danger" style="width: {{ $persenBelum }}%" 
                             title="Belum Absen: {{ $belumAbsen }}">
                            {{ $belumAbsen }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daftar Siswa yang Sudah Absen -->
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#sudahAbsen">
                                <i class="bi bi-check-circle me-1"></i>Sudah Absen ({{ $totalHadir + $totalIzin + $totalSakit }})
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#belumAbsen">
                                <i class="bi bi-exclamation-circle me-1"></i>Belum Absen ({{ $belumAbsen }})
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <!-- Tab Sudah Absen -->
                        <div class="tab-pane fade show active" id="sudahAbsen">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Siswa</th>
                                            <th>Waktu</th>
                                            <th>Status</th>
                                            <th>Tipe</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($session->presensis as $index => $presensi)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="{{ Avatar::create($presensi->siswa->name)->toBase64() }}" 
                                                         class="rounded-circle me-2" 
                                                         width="32" height="32">
                                                    <div>
                                                        <strong>{{ $presensi->siswa->name }}</strong><br>
                                                        <small class="text-muted">{{ $presensi->siswa->email }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <small>{{ $presensi->waktu_absen->format('H:i:s') }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $presensi->status_badge }}">
                                                    {{ strtoupper($presensi->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $presensi->tipe_absen == 'qr' ? 'primary' : 'secondary' }}">
                                                    {{ $presensi->tipe_absen == 'qr' ? 'QR' : 'Manual' }}
                                                </span>
                                            </td>
                                            <td>
                                                <button type="button" 
                                                        class="btn btn-sm btn-warning btn-edit-presensi" 
                                                        data-presensi-id="{{ $presensi->id }}"
                                                        data-siswa-name="{{ $presensi->siswa->name }}"
                                                        data-status="{{ $presensi->status }}"
                                                        data-keterangan="{{ $presensi->keterangan }}">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <form action="{{ route('guru.presensi.destroy', [$session->id, $presensi->id]) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger"
                                                            onclick="return confirm('Hapus presensi ini?')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-3">
                                                <i class="bi bi-inbox fs-3 text-muted"></i>
                                                <p class="text-muted mb-0">Belum ada siswa yang absen</p>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tab Belum Absen -->
                        <div class="tab-pane fade" id="belumAbsen">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Siswa</th>
                                            <th>Email</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($siswaBelumAbsen as $index => $siswa)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="{{ Avatar::create($siswa->name)->toBase64() }}" 
                                                         class="rounded-circle me-2" 
                                                         width="32" height="32">
                                                    {{ $siswa->name }}
                                                </div>
                                            </td>
                                            <td>{{ $siswa->email }}</td>
                                            <td>
                                                <button type="button" 
                                                        class="btn btn-sm btn-primary btn-absen-cepat"
                                                        data-siswa-id="{{ $siswa->id }}"
                                                        data-siswa-name="{{ $siswa->name }}">
                                                    <i class="bi bi-check-circle me-1"></i>Absen Cepat
                                                </button>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-3">
                                                <i class="bi bi-check-circle fs-3 text-success"></i>
                                                <p class="text-muted mb-0">Semua siswa sudah absen</p>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Absen Manual -->
<div class="modal fade" id="absenManualModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-pencil-square me-2"></i>Absen Manual
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('guru.presensi.absen-manual', $session->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="siswa_id" class="form-label">Pilih Siswa <span class="text-danger">*</span></label>
                        <select class="form-select" name="siswa_id" id="siswa_id" required>
                            <option value="">Pilih Siswa</option>
                            @foreach($siswaBelumAbsen as $siswa)
                                <option value="{{ $siswa->id }}">{{ $siswa->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select" name="status" id="status" required>
                            <option value="hadir">Hadir</option>
                            <option value="izin">Izin</option>
                            <option value="sakit">Sakit</option>
                            <option value="alpha">Alpha</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="keterangan" class="form-label">Keterangan</label>
                        <textarea class="form-control" name="keterangan" id="keterangan" 
                                  rows="3" placeholder="Keterangan tambahan (opsional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Presensi -->
<div class="modal fade" id="editPresensiModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-pencil-square me-2"></i>Edit Presensi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editPresensiForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Siswa</label>
                        <input type="text" class="form-control" id="edit_siswa_name" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select" name="status" id="edit_status" required>
                            <option value="hadir">Hadir</option>
                            <option value="izin">Izin</option>
                            <option value="sakit">Sakit</option>
                            <option value="alpha">Alpha</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_keterangan" class="form-label">Keterangan</label>
                        <textarea class="form-control" name="keterangan" id="edit_keterangan" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Auto refresh setiap 30 detik untuk update real-time
setInterval(function() {
    location.reload();
}, 30000);

// Absen Cepat (langsung hadir)
document.querySelectorAll('.btn-absen-cepat').forEach(btn => {
    btn.addEventListener('click', function() {
        const siswaId = this.getAttribute('data-siswa-id');
        const siswaName = this.getAttribute('data-siswa-name');
        
        Swal.fire({
            title: 'Absen Cepat',
            html: `Tandai <strong>${siswaName}</strong> sebagai <strong>HADIR</strong>?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hadir',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#28a745'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("guru.presensi.absen-manual", $session->id) }}';
                
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);
                
                const siswaInput = document.createElement('input');
                siswaInput.type = 'hidden';
                siswaInput.name = 'siswa_id';
                siswaInput.value = siswaId;
                form.appendChild(siswaInput);
                
                const statusInput = document.createElement('input');
                statusInput.type = 'hidden';
                statusInput.name = 'status';
                statusInput.value = 'hadir';
                form.appendChild(statusInput);
                
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
});

// Edit Presensi
document.querySelectorAll('.btn-edit-presensi').forEach(btn => {
    btn.addEventListener('click', function() {
        const presensiId = this.getAttribute('data-presensi-id');
        const siswaName = this.getAttribute('data-siswa-name');
        const status = this.getAttribute('data-status');
        const keterangan = this.getAttribute('data-keterangan');
        
        document.getElementById('edit_siswa_name').value = siswaName;
        document.getElementById('edit_status').value = status;
        document.getElementById('edit_keterangan').value = keterangan || '';
        
        const form = document.getElementById('editPresensiForm');
        form.action = `/guru/presensi/{{ $session->id }}/update/${presensiId}`;
        
        new bootstrap.Modal(document.getElementById('editPresensiModal')).show();
    });
});

@if(session('success'))
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '{{ session("success") }}',
        showConfirmButton: false,
        timer: 2000,
        toast: true,
        position: 'top-end'
    });
@endif

@if(session('error'))
    Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: '{{ session("error") }}',
        confirmButtonColor: '#dc3545'
    });
@endif
</script>

<style>
.qr-code-container svg {
    width: 100%;
    height: auto;
    max-width: 300px;
}
</style>
@endpush
@endsection
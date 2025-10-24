@extends('layouts.guru')

@section('content')
<div class="bg-primary pt-10 pb-21"></div>
<div class="container-fluid mt-n22 px-6">
    <div class="row">
        <div class="col-lg-12 col-md-12 col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div class="mb-2 mb-lg-0">
                    <h3 class="mb-0 text-white">Manajemen Presensi</h3>
                </div>
                <div>
                    <a href="{{ route('guru.presensi.create') }}" class="btn btn-white">
                        <i class="bi bi-plus-circle me-2"></i>Buat Session Baru
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="row mt-6">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filter</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('guru.presensi.index') }}" method="GET">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">Semua Status</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                                    <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Ditutup</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tanggal</label>
                                <input type="date" name="tanggal" class="form-control" value="{{ request('tanggal') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Kelas</label>
                                <select name="kelas_id" class="form-select">
                                    <option value="">Semua Kelas</option>
                                    <!-- Dynamic kelas options would go here -->
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-search"></i> Filter
                                    </button>
                                    <a href="{{ route('guru.presensi.index') }}" class="btn btn-secondary">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Sessions List -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Daftar Session Presensi</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Kelas</th>
                                    <th>Waktu</th>
                                    <th>Status</th>
                                    <th>Kehadiran</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sessions as $index => $session)
                                <tr>
                                    <td>{{ $sessions->firstItem() + $index }}</td>
                                    <td>{{ $session->tanggal->format('d/m/Y') }}</td>
                                    <td>
                                        <strong>{{ $session->kelas->nama_kelas }}</strong><br>
                                        <small class="text-muted">{{ $session->kelas->jurusan->nama_jurusan }}</small>
                                    </td>
                                    <td>
                                        <small>{{ $session->jam_mulai }}</small><br>
                                        <small>{{ $session->jam_selesai }}</small>
                                    </td>
                                    <td>
                                        @if($session->status == 'active')
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle me-1"></i>Aktif
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="bi bi-x-circle me-1"></i>Ditutup
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $totalSiswa = $session->kelas->siswa->count();
                                            $hadir = $session->presensis->count();
                                            $percentage = $totalSiswa > 0 ? round(($hadir / $totalSiswa) * 100) : 0;
                                        @endphp
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-primary me-2">{{ $hadir }}/{{ $totalSiswa }}</span>
                                            <small class="text-muted">({{ $percentage }}%)</small>
                                        </div>
                                        <div class="progress mt-1" style="height: 5px;">
                                            <div class="progress-bar bg-success" style="width: {{ $percentage }}%"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('guru.presensi.show', $session->id) }}" 
                                               class="btn btn-sm btn-info" title="Detail">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            
                                            @if($session->status == 'active')
                                            <form action="{{ route('guru.presensi.close', $session->id) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-warning" 
                                                        title="Tutup Session"
                                                        onclick="return confirm('Tutup session presensi ini?')">
                                                    <i class="bi bi-lock"></i>
                                                </button>
                                            </form>
                                            @else
                                            <form action="{{ route('guru.presensi.reopen', $session->id) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" 
                                                        title="Buka Kembali"
                                                        onclick="return confirm('Buka kembali session ini?')">
                                                    <i class="bi bi-unlock"></i>
                                                </button>
                                            </form>
                                            @endif
                                            
                                            <a href="{{ route('guru.presensi.download-qr', $session->id) }}" 
                                               class="btn btn-sm btn-primary" title="Download QR">
                                                <i class="bi bi-download"></i>
                                            </a>
                                            
                                            <form action="{{ route('guru.presensi.destroy', $session->id) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" 
                                                        title="Hapus"
                                                        onclick="return confirm('Hapus session ini?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="bi bi-inbox fs-1 text-muted"></i>
                                        <p class="text-muted mt-2">Belum ada session presensi</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $sessions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
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
@endpush
@endsection
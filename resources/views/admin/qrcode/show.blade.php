@extends('layouts.admin')

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
                    <a href="{{ route('admin.qrcode.index') }}" class="btn btn-white">
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
                            {{ $session->status == 'active' ? 'AKTIF' : 'NONAKTIF' }}
                        </span>
                    </div>
                    
                    <div class="mt-4">
                        <a href="{{ route('admin.qrcode.index') }}" class="btn btn-primary w-100 mb-2">
                            <i class="bi bi-download me-2"></i>Download QR Code
                        </a>
                        
                        @if($session->status == 'active')
                        <form action="{{ route('admin.qrcode.destroy', $session->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100" 
                                    onclick="return confirm('Nonaktifkan session ini?')">
                                <i class="bi bi-x-circle me-2"></i>Nonaktifkan Session
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Info & Stats -->
        <div class="col-md-8">
            <!-- Info Session -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Informasi Session</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Kelas:</strong><br>
                            <span class="badge bg-primary">{{ $session->kelas->nama_kelas }}</span>
                            {{ $session->kelas->jurusan->nama_jurusan }}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Tanggal:</strong><br>
                            {{ $session->tanggal->format('d F Y') }}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Waktu:</strong><br>
                            {{ $session->jam_mulai }} - {{ $session->jam_selesai }}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Radius:</strong><br>
                            {{ $session->radius }} meter
                        </div>
                        <div class="col-md-12">
                            <strong>Dibuat oleh:</strong><br>
                            {{ $session->creator->name }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistik -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Statistik Kehadiran</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h3 class="text-primary mb-0">{{ $stats['total_siswa'] }}</h3>
                                    <small class="text-muted">Total Siswa</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h3 class="text-success mb-0">{{ $stats['hadir'] }}</h3>
                                    <small class="text-muted">Hadir</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h3 class="text-warning mb-0">{{ $stats['izin'] }}</h3>
                                    <small class="text-muted">Izin</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h3 class="text-info mb-0">{{ $stats['sakit'] }}</h3>
                                    <small class="text-muted">Sakit</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="progress mt-3" style="height: 30px;">
                        @php
                            $totalPresent = $stats['hadir'] + $stats['izin'] + $stats['sakit'];
                            $percentPresent = $stats['total_siswa'] > 0 ? ($totalPresent / $stats['total_siswa']) * 100 : 0;
                        @endphp
                        <div class="progress-bar bg-success" style="width: {{ ($stats['hadir'] / $stats['total_siswa']) * 100 }}%">
                            {{ $stats['hadir'] }}
                        </div>
                        <div class="progress-bar bg-warning" style="width: {{ ($stats['izin'] / $stats['total_siswa']) * 100 }}%">
                            {{ $stats['izin'] }}
                        </div>
                        <div class="progress-bar bg-info" style="width: {{ ($stats['sakit'] / $stats['total_siswa']) * 100 }}%">
                            {{ $stats['sakit'] }}
                        </div>
                        <div class="progress-bar bg-danger" style="width: {{ ($stats['alpha'] / $stats['total_siswa']) * 100 }}%">
                            {{ $stats['alpha'] }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Daftar Siswa yang Sudah Absen -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Daftar Siswa yang Sudah Absen</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Siswa</th>
                                    <th>Waktu Absen</th>
                                    <th>Status</th>
                                    <th>Jarak</th>
                                    <th>Tipe</th>
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
                                            {{ $presensi->siswa->name }}
                                        </div>
                                    </td>
                                    <td>{{ $presensi->waktu_absen->format('H:i:s') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $presensi->status_badge }}">
                                            {{ strtoupper($presensi->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($presensi->latitude && $presensi->longitude)
                                            @php
                                                $distance = \App\Models\Presensi::calculateDistance(
                                                    $session->latitude,
                                                    $session->longitude,
                                                    $presensi->latitude,
                                                    $presensi->longitude
                                                );
                                            @endphp
                                            {{ round($distance) }} m
                                            @if($distance <= $session->radius)
                                                <i class="bi bi-check-circle text-success"></i>
                                            @else
                                                <i class="bi bi-exclamation-circle text-danger"></i>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $presensi->tipe_absen == 'qr' ? 'primary' : 'secondary' }}">
                                            {{ $presensi->tipe_absen == 'qr' ? 'QR Code' : 'Manual' }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <i class="bi bi-inbox fs-1 text-muted"></i>
                                        <p class="text-muted mt-2">Belum ada siswa yang absen</p>
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

<style>
.qr-code-container svg {
    width: 100%;
    height: auto;
    max-width: 300px;
}
</style>
@endsection
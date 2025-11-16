@extends('layouts.siswa')
@section('title', 'Dashboard')

@section('content')
    <div class="bg-primary pt-10 pb-21"></div>
    <div class="container-fluid mt-n22 px-6">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-12">
                <!-- Page header -->
                <div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="mb-2 mb-lg-0">
                            <h3 class="mb-0 text-white">Selamat Datang, {{ Auth::user()->name }}</h3>
                            <p class="text-white-50 mb-0">Dashboard Siswa</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cards -->
        <div class="row mt-6">
            <!-- Profile Card -->
            <div class="col-xl-4 col-lg-6 col-md-12 col-12">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <img src="{{ Avatar::create(Auth::user()->name)->toBase64() }}" 
                                 alt="Avatar" 
                                 class="rounded-circle" 
                                 width="120" 
                                 height="120">
                        </div>
                        <h4 class="mb-1">{{ Auth::user()->name }}</h4>
                        <p class="text-muted mb-2">{{ Auth::user()->email }}</p>
                        
                        @if(Auth::user()->kelas)
                            <div class="mb-3">
                                <span class="badge bg-primary-soft text-primary fs-6 px-3 py-2">
                                    {{ Auth::user()->kelas->nama_kelas }}
                                </span>
                                <br>
                                <small class="text-muted mt-1">
                                    {{ Auth::user()->kelas->jurusan->nama_jurusan }}
                                </small>
                            </div>
                        @else
                            <div class="alert alert-warning mb-0">
                                <small>
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    Anda belum terdaftar di kelas
                                </small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="col-xl-8 col-lg-6 col-md-12 col-12">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="bi bi-lightning-fill text-warning me-2"></i>
                            Aksi Cepat
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Scan QR Code -->
                            <div class="col-md-6">
                                <a href="{{ route('siswa.presensi.index') }}" 
                                   class="btn btn-primary btn-lg w-100 d-flex align-items-center justify-content-center">
                                    <i class="bi bi-qr-code-scan me-2 fs-4"></i>
                                    <div class="text-start">
                                        <div class="fw-bold">Scan QR Code</div>
                                        <small>Presensi Sekarang</small>
                                    </div>
                                </a>
                            </div>

                            <!-- View History -->
                            <div class="col-md-6">
                                <a href="#" 
                                   class="btn btn-outline-primary btn-lg w-100 d-flex align-items-center justify-content-center">
                                    <i class="bi bi-clock-history me-2 fs-4"></i>
                                    <div class="text-start">
                                        <div class="fw-bold">Riwayat</div>
                                        <small>Lihat Presensi</small>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Today's Attendance Status -->
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="bi bi-calendar-check text-success me-2"></i>
                            Status Presensi Hari Ini
                        </h5>
                    </div>
                    <div class="card-body">
                        @php
                            $todayPresensi = \App\Models\Presensi::where('siswa_id', Auth::id())
                                ->whereDate('tanggal_presensi', now())
                                ->first();
                        @endphp

                        @if($todayPresensi)
                            <div class="alert alert-success mb-0">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-check-circle-fill fs-2 me-3"></i>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-bold">Sudah Presensi</h6>
                                        <div class="mb-2">
                                            <span class="badge bg-success me-2">
                                                {{ ucfirst($todayPresensi->status) }}
                                            </span>
                                            <span class="badge bg-info">
                                                {{ $todayPresensi->metode == 'qr' ? 'QR Code' : 'Manual' }}
                                            </span>
                                        </div>
                                        <p class="mb-0 small">
                                            <i class="bi bi-clock me-1"></i>
                                            <strong>Waktu:</strong> 
                                            {{ $todayPresensi->created_at->format('H:i:s') }}
                                            <br>
                                            <i class="bi bi-calendar me-1"></i>
                                            <strong>Tanggal:</strong> 
                                            {{ $todayPresensi->tanggal_presensi->format('d M Y') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning mb-0">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-exclamation-triangle-fill fs-2 me-3"></i>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-bold">Belum Presensi</h6>
                                        <p class="mb-2">
                                            Anda belum melakukan presensi hari ini ({{ now()->format('d M Y') }})
                                        </p>
                                        <a href="{{ route('siswa.presensi.index') }}" 
                                           class="btn btn-warning btn-sm">
                                            <i class="bi bi-qr-code-scan me-1"></i>
                                            Presensi Sekarang
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Attendance -->
        @if(Auth::user()->kelas)
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="bi bi-list-check text-primary me-2"></i>
                            Riwayat Presensi Terakhir
                        </h5>
                    </div>
                    <div class="card-body">
                        @php
                            $recentPresensis = \App\Models\Presensi::where('siswa_id', Auth::id())
                                ->orderBy('tanggal_presensi', 'desc')
                                ->take(5)
                                ->get();
                        @endphp

                        @if($recentPresensis->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Waktu</th>
                                            <th>Status</th>
                                            <th>Metode</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recentPresensis as $presensi)
                                        <tr>
                                            <td>{{ $presensi->tanggal_presensi->format('d M Y') }}</td>
                                            <td>{{ $presensi->created_at->format('H:i:s') }}</td>
                                            <td>
                                                @if($presensi->status == 'hadir')
                                                    <span class="badge bg-success">Hadir</span>
                                                @elseif($presensi->status == 'izin')
                                                    <span class="badge bg-warning">Izin</span>
                                                @elseif($presensi->status == 'sakit')
                                                    <span class="badge bg-info">Sakit</span>
                                                @else
                                                    <span class="badge bg-danger">Alpha</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge {{ $presensi->metode == 'qr' ? 'bg-primary' : 'bg-secondary' }}">
                                                    {{ $presensi->metode == 'qr' ? 'QR Code' : 'Manual' }}
                                                </span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                <p class="text-muted mt-2 mb-0">Belum ada riwayat presensi</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    @if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session("success") }}',
                timer: 3000,
                showConfirmButton: false
            });
        });
    </script>
    @endif
@endsection
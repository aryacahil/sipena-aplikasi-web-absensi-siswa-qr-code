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
                            <p class="text-white-50 mb-0">Dashboard Siswa - {{ now()->format('d F Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cards -->
        <div class="row mt-6">
            <!-- Profile Card -->
            <div class="col-xl-4 col-lg-6 col-md-12 col-12 mb-4 mb-xl-0">
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <img src="{{ Avatar::create(Auth::user()->name)->toBase64() }}" 
                                 alt="Avatar" 
                                 class="rounded-circle border border-3 border-primary" 
                                 width="100" 
                                 height="100">
                        </div>
                        <h4 class="mb-1 fw-bold">{{ Auth::user()->name }}</h4>
                        <p class="text-muted mb-3 small">{{ Auth::user()->email }}</p>
                        
                        @if(Auth::user()->kelas)
                            <div class="bg-light rounded-3 p-3">
                                <div class="mb-2">
                                    <i class="bi bi-door-open text-primary me-2"></i>
                                    <span class="fw-semibold">{{ Auth::user()->kelas->nama_kelas }}</span>
                                </div>
                                <div class="small text-muted">
                                    <i class="bi bi-building me-2"></i>
                                    {{ Auth::user()->kelas->jurusan->nama_jurusan }}
                                </div>
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

            <!-- Today's Attendance Status -->
            <div class="col-xl-8 col-lg-6 col-md-12 col-12">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0 fw-bold">
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
                                    <i class="bi bi-check-circle-fill fs-2 me-3 d-none d-md-block"></i>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-bold">
                                            <i class="bi bi-check-circle-fill me-1 d-md-none"></i>
                                            Sudah Presensi
                                        </h6>
                                        <div class="mb-2">
                                            <span class="badge bg-success me-2 px-3 py-2">
                                                <i class="bi bi-check-circle me-1"></i>
                                                {{ ucfirst($todayPresensi->status) }}
                                            </span>
                                            <span class="badge bg-info px-3 py-2">
                                                <i class="bi bi-{{ $todayPresensi->metode == 'qr' ? 'qr-code' : 'pencil' }} me-1"></i>
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
                                    <i class="bi bi-exclamation-triangle-fill fs-2 me-3 d-none d-md-block"></i>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-bold">
                                            <i class="bi bi-exclamation-triangle-fill me-1 d-md-none"></i>
                                            Belum Presensi
                                        </h6>
                                        <p class="mb-2">
                                            Anda belum melakukan presensi hari ini ({{ now()->format('d M Y') }})
                                        </p>
                                        <a href="{{ route('siswa.presensi.index') }}" 
                                           class="btn btn-warning">
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

        <!-- Attendance Statistics -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0 fw-bold">
                            <i class="bi bi-graph-up text-primary me-2"></i>
                            Statistik Presensi
                        </h5>
                    </div>
                    <div class="card-body">
                        @php
                            $stats = [
                                'hadir' => \App\Models\Presensi::where('siswa_id', Auth::id())->where('status', 'hadir')->count(),
                                'izin' => \App\Models\Presensi::where('siswa_id', Auth::id())->where('status', 'izin')->count(),
                                'sakit' => \App\Models\Presensi::where('siswa_id', Auth::id())->where('status', 'sakit')->count(),
                                'alpha' => \App\Models\Presensi::where('siswa_id', Auth::id())->where('status', 'alpha')->count(),
                            ];
                            $total = array_sum($stats);
                        @endphp

                        <div class="row g-3">
                            <!-- Hadir -->
                            <div class="col-6 col-md-3">
                                <div class="text-center p-3 bg-success bg-opacity-10 rounded-3">
                                    <i class="bi bi-check-circle-fill text-success fs-2"></i>
                                    <h3 class="mb-0 mt-2 fw-bold">{{ $stats['hadir'] }}</h3>
                                    <small class="text-muted">Hadir</small>
                                </div>
                            </div>

                            <!-- Izin -->
                            <div class="col-6 col-md-3">
                                <div class="text-center p-3 bg-warning bg-opacity-10 rounded-3">
                                    <i class="bi bi-envelope-fill text-warning fs-2"></i>
                                    <h3 class="mb-0 mt-2 fw-bold">{{ $stats['izin'] }}</h3>
                                    <small class="text-muted">Izin</small>
                                </div>
                            </div>

                            <!-- Sakit -->
                            <div class="col-6 col-md-3">
                                <div class="text-center p-3 bg-info bg-opacity-10 rounded-3">
                                    <i class="bi bi-heart-pulse-fill text-info fs-2"></i>
                                    <h3 class="mb-0 mt-2 fw-bold">{{ $stats['sakit'] }}</h3>
                                    <small class="text-muted">Sakit</small>
                                </div>
                            </div>

                            <!-- Alpha -->
                            <div class="col-6 col-md-3">
                                <div class="text-center p-3 bg-danger bg-opacity-10 rounded-3">
                                    <i class="bi bi-x-circle-fill text-danger fs-2"></i>
                                    <h3 class="mb-0 mt-2 fw-bold">{{ $stats['alpha'] }}</h3>
                                    <small class="text-muted">Alpha</small>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 text-center">
                            <p class="mb-0 text-muted small">
                                <i class="bi bi-calendar-range me-1"></i>
                                Total Presensi: <strong>{{ $total }}</strong> hari
                            </p>
                        </div>
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
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold">
                                <i class="bi bi-clock-history text-primary me-2"></i>
                                Riwayat Presensi Terakhir
                            </h5>
                            <span class="badge bg-primary">5 Terakhir</span>
                        </div>
                    </div>
                    <div class="card-body">
                        @php
                            $recentPresensis = \App\Models\Presensi::where('siswa_id', Auth::id())
                                ->orderBy('tanggal_presensi', 'desc')
                                ->take(5)
                                ->get();
                        @endphp

                        @if($recentPresensis->count() > 0)
                            <!-- Desktop Table View -->
                            <div class="table-responsive d-none d-md-block">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Hari</th>
                                            <th>Waktu</th>
                                            <th>Status</th>
                                            <th>Metode</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recentPresensis as $presensi)
                                        <tr>
                                            <td>{{ $presensi->tanggal_presensi->format('d M Y') }}</td>
                                            <td>{{ $presensi->tanggal_presensi->locale('id')->dayName }}</td>
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

                            <!-- Mobile Card View -->
                            <div class="d-md-none">
                                @foreach($recentPresensis as $presensi)
                                <div class="card mb-3 shadow-sm">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h6 class="mb-1 fw-bold">
                                                    {{ $presensi->tanggal_presensi->format('d M Y') }}
                                                </h6>
                                                <small class="text-muted">
                                                    {{ $presensi->tanggal_presensi->locale('id')->dayName }}
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                @if($presensi->status == 'hadir')
                                                    <span class="badge bg-success">Hadir</span>
                                                @elseif($presensi->status == 'izin')
                                                    <span class="badge bg-warning">Izin</span>
                                                @elseif($presensi->status == 'sakit')
                                                    <span class="badge bg-info">Sakit</span>
                                                @else
                                                    <span class="badge bg-danger">Alpha</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                                            <small class="text-muted">
                                                <i class="bi bi-clock me-1"></i>
                                                {{ $presensi->created_at->format('H:i:s') }}
                                            </small>
                                            <span class="badge {{ $presensi->metode == 'qr' ? 'bg-primary' : 'bg-secondary' }}">
                                                {{ $presensi->metode == 'qr' ? 'QR' : 'Manual' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
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

@push('styles')
<style>
    .bg-success.bg-opacity-10 {
        background-color: rgba(25, 135, 84, 0.1) !important;
    }

    .bg-warning.bg-opacity-10 {
        background-color: rgba(255, 193, 7, 0.1) !important;
    }

    .bg-info.bg-opacity-10 {
        background-color: rgba(13, 202, 240, 0.1) !important;
    }

    .bg-danger.bg-opacity-10 {
        background-color: rgba(220, 53, 69, 0.1) !important;
    }

    /* Mobile optimizations */
    @media (max-width: 768px) {
        .container-fluid {
            padding-left: 15px !important;
            padding-right: 15px !important;
        }

        h3 {
            font-size: 1.25rem;
        }

        .fs-2 {
            font-size: 1.5rem !important;
        }

        .badge {
            font-size: 0.75rem;
            padding: 0.35em 0.65em;
        }
    }

    @media (max-width: 576px) {
        h3 {
            font-size: 1.1rem;
        }

        h4 {
            font-size: 1.15rem;
        }

        h5 {
            font-size: 1rem;
        }
    }
</style>
@endpush
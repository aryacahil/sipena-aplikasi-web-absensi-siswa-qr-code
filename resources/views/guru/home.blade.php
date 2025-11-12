@extends('layouts.guru')
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
                            <h3 class="mb-0 text-white">Halo {{ ucfirst(Auth::user()->role) }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kartu Statistik -->
            <div class="col-xl-3 col-lg-6 col-md-12 col-12 mt-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h4 class="mb-0">Jumlah Siswa</h4>
                            </div>
                            <div class="icon-shape icon-md bg-light-primary text-primary rounded-2">
                                <i class="bi bi-people-fill fs-4"></i>
                            </div>
                        </div>
                        <div>
                            <h1 class="fw-bold">{{ $stats['total_siswa'] }}</h1>
                            <p class="mb-0"><span class="text-dark me-2">{{ $stats['siswa_completed'] }}</span>Aktif</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-12 col-12 mt-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h4 class="mb-0">Jumlah Guru</h4>
                            </div>
                            <div class="icon-shape icon-md bg-light-primary text-primary rounded-2">
                                <i class="bi bi-person-fill fs-4"></i>
                            </div>
                        </div>
                        <div>
                            <h1 class="fw-bold">{{ $stats['total_guru'] }}</h1>
                            <p class="mb-0"><span class="text-dark me-2">{{ $stats['guru_completed'] }}</span>Aktif</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-12 col-12 mt-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h4 class="mb-0">Jumlah Kelas</h4>
                            </div>
                            <div class="icon-shape icon-md bg-light-primary text-primary rounded-2">
                                <i class="bi bi-easel-fill fs-4"></i>
                            </div>
                        </div>
                        <div>
                            <h1 class="fw-bold">{{ $stats['total_kelas'] }}</h1>
                            <p class="mb-0"><span class="text-dark me-2">{{ $stats['kelas_completed'] }}</span>Aktif</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-12 col-12 mt-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h4 class="mb-0">Jumlah Admin</h4>
                            </div>
                            <div class="icon-shape icon-md bg-light-primary text-primary rounded-2">
                                <i class="bi bi-person-fill-lock fs-4"></i>
                            </div>
                        </div>
                        <div>
                            <h1 class="fw-bold">{{ $stats['total_admin'] }}</h1>
                            <p class="mb-0"><span class="text-success me-2">{{ $stats['admin_completed'] }}</span>Aktif</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grafik Kehadiran -->
        <div class="row mt-6">
            <div class="col-md-12 col-12">
                <div class="card">
                    <div class="card-header bg-white py-4">
                        <h4 class="mb-0">Grafik Kehadiran Siswa</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="attendanceChart" style="max-height: 400px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Jurusan dan Kelas -->
        <div class="row mt-6">
            <div class="col-md-12 col-12">
                <div class="card">
                    <div class="card-header bg-white py-4 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Data Jurusan dan Kelas</h4>
                        <a href="#" class="btn btn-primary btn-sm">Lihat Semua</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Jurusan</th>
                                        <th>Kode Jurusan</th>
                                        <th>Jumlah Kelas</th>
                                        <th>Kelas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($jurusans as $index => $jurusan)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $jurusan->nama_jurusan }}</td>
                                        <td>{{ $jurusan->kode_jurusan }}</td>
                                        <td>{{ $jurusan->kelas_count }}</td>
                                        <td>
                                            @if($jurusan->kelas->count() > 0)
                                                @foreach($jurusan->kelas as $kelas)
                                                    <span class="badge bg-primary">{{ $kelas->nama_kelas }}</span>
                                                @endforeach
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center">Belum ada data jurusan</td>
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

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('attendanceChart').getContext('2d');
            
            const attendanceData = {
                labels: @json($chartData['labels']),
                datasets: [
                    {
                        label: 'Hadir',
                        data: @json($chartData['hadir']),
                        backgroundColor: 'rgba(25,135,84,0.7)',
                        borderColor: 'rgba(25,135,84,1)',
                        borderWidth: 2
                    },
                    {
                        label: 'Izin',
                        data: @json($chartData['izin']),
                        backgroundColor: 'rgba(255,193,7,0.7)',
                        borderColor: 'rgba(255,193,7,1)',
                        borderWidth: 2
                    },
                    {
                        label: 'Sakit',
                        data: @json($chartData['sakit']),
                        backgroundColor: 'rgba(13,110,253,0.7)',
                        borderColor: 'rgba(13,110,253,1)',
                        borderWidth: 2
                    },
                    {
                        label: 'Alpha',
                        data: @json($chartData['alpha']),
                        backgroundColor: 'rgba(220,53,69,0.7)',
                        borderColor: 'rgba(220,53,69,1)',
                        borderWidth: 2
                    }
                ]
            };

            new Chart(ctx, {
                type: 'bar',
                data: attendanceData,
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { position: 'top' },
                        title: {
                            display: true,
                            text: 'Statistik Kehadiran Siswa (5 Hari Terakhir)'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 10 },
                            title: { display: true, text: 'Jumlah Siswa' }
                        },
                        x: {
                            title: { display: true, text: 'Hari' }
                        }
                    }
                }
            });
        });
    </script>
@endsection

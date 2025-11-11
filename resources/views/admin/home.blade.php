@extends('layouts.admin')
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
                            <h3 class="mb-0  text-white">Halo {{ ucfirst(Auth::user()->role) }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kartu Statistik -->
            <div class="col-xl-3 col-lg-6 col-md-12 col-12 mt-6">
                <div class="card ">
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
                <div class="card ">
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
                <div class="card ">
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
                            <p class="mb-0"><span class="text-dark me-2">{{ $stats['kelas_completed'] }}</span>Berisi Siswa</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-12 col-12 mt-6">
                <div class="card ">
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

        <!-- Grafik Kehadiran dengan Filter -->
        <div class="row mt-6">
            <div class="col-md-12 col-12">
                <div class="card">
                    <div class="card-header bg-white py-4">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <h4 class="mb-0">Grafik Kehadiran Siswa</h4>
                            
                            <!-- Filter Grafik -->
                            <div class="d-flex gap-2 mt-2 mt-md-0">
                                <!-- Filter Periode -->
                                <select class="form-select form-select-sm" id="filterPeriode" style="width: auto;">
                                    <option value="week">Minggu Ini</option>
                                    <option value="month">Bulan Ini</option>
                                    <option value="year">Tahun Ini</option>
                                </select>

                                <!-- Filter Kelas -->
                                <select class="form-select form-select-sm" id="filterKelas" style="width: auto;">
                                    <option value="all">Semua Kelas</option>
                                    @foreach($jurusans as $jurusan)
                                        @foreach($jurusan->kelas as $kelas)
                                            <option value="{{ $kelas->id }}">{{ $kelas->nama_kelas }}</option>
                                        @endforeach
                                    @endforeach
                                </select>

                                <!-- Filter Jurusan -->
                                <select class="form-select form-select-sm" id="filterJurusan" style="width: auto;">
                                    <option value="all">Semua Jurusan</option>
                                    @foreach($jurusans as $jurusan)
                                        <option value="{{ $jurusan->id }}">{{ $jurusan->nama_jurusan }}</option>
                                    @endforeach
                                </select>

                                <!-- Tombol Reset -->
                                <button class="btn btn-sm btn-outline-secondary" id="resetFilter">
                                    <i class="bi bi-arrow-clockwise"></i> Reset
                                </button>
                            </div>
                        </div>
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
                        <a href="{{ route('admin.jurusan.index') }}" class="btn btn-primary btn-sm">Lihat Semua</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">No</th>
                                        <th scope="col">Nama Jurusan</th>
                                        <th scope="col">Kode Jurusan</th>
                                        <th scope="col">Jumlah Kelas</th>
                                        <th scope="col">Kelas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($jurusans as $index => $jurusan)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $jurusan->nama_jurusan }}</td>
                                        <td><span class="badge bg-primary">{{ $jurusan->kode_jurusan }}</span></td>
                                        <td class="text-center">{{ $jurusan->kelas_count }}</td>
                                        <td>
                                            @if($jurusan->kelas->count() > 0)
                                                @foreach($jurusan->kelas as $kelas)
                                                    <span class="badge bg-info me-1 mb-1">
                                                        {{ $kelas->nama_kelas }} ({{ $kelas->siswa_count }} siswa)
                                                    </span>
                                                @endforeach
                                            @else
                                                <span class="text-muted">-</span>
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

    <!-- Chart.js Script -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('attendanceChart').getContext('2d');
            
            // Data dari backend
            const chartDataFromBackend = {
                week: {
                    labels: @json($chartData['labels']),
                    hadir: @json($chartData['hadir']),
                    izin: @json($chartData['izin']),
                    sakit: @json($chartData['sakit']),
                    alpha: @json($chartData['alpha'])
                }
            };

            let currentChart;

            function createChart(period = 'week', data = null) {
                const chartData = data || chartDataFromBackend[period] || chartDataFromBackend.week;
                
                if (currentChart) {
                    currentChart.destroy();
                }

                const attendanceData = {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'Hadir',
                        data: chartData.hadir,
                        backgroundColor: 'rgba(25, 135, 84, 0.7)',
                        borderColor: 'rgba(25, 135, 84, 1)',
                        borderWidth: 2
                    }, {
                        label: 'Izin',
                        data: chartData.izin,
                        backgroundColor: 'rgba(255, 193, 7, 0.7)',
                        borderColor: 'rgba(255, 193, 7, 1)',
                        borderWidth: 2
                    }, {
                        label: 'Sakit',
                        data: chartData.sakit,
                        backgroundColor: 'rgba(13, 110, 253, 0.7)',
                        borderColor: 'rgba(13, 110, 253, 1)',
                        borderWidth: 2
                    }, {
                        label: 'Alpha',
                        data: chartData.alpha,
                        backgroundColor: 'rgba(220, 53, 69, 0.7)',
                        borderColor: 'rgba(220, 53, 69, 1)',
                        borderWidth: 2
                    }]
                };

                const config = {
                    type: 'bar',
                    data: attendanceData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            title: {
                                display: true,
                                text: `Statistik Kehadiran Siswa (${
                                    period === 'week' ? 'Minggu Ini' : 
                                    period === 'month' ? 'Bulan Ini' : 
                                    'Tahun Ini'
                                })`
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) label += ': ';
                                        label += context.parsed.y + ' siswa';
                                        return label;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: Math.max(...chartData.hadir, ...chartData.izin, ...chartData.sakit, ...chartData.alpha) > 100 ? 20 : 5
                                },
                                title: {
                                    display: true,
                                    text: 'Jumlah Siswa'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: period === 'week' ? 'Hari' : 
                                          period === 'month' ? 'Minggu' : 
                                          'Bulan'
                                }
                            }
                        }
                    }
                };

                currentChart = new Chart(ctx, config);
            }

            // Inisialisasi chart dengan data dari backend
            createChart('week', chartDataFromBackend.week);

            // Event listener untuk filter periode
            document.getElementById('filterPeriode').addEventListener('change', function() {
                const period = this.value;
                const kelasId = document.getElementById('filterKelas').value;
                const jurusanId = document.getElementById('filterJurusan').value;
                
                // Fetch data dari server
                fetch(`/admin/chart-data?period=${period}&kelas_id=${kelasId}&jurusan_id=${jurusanId}`)
                    .then(response => response.json())
                    .then(data => {
                        createChart(period, data);
                    })
                    .catch(error => {
                        console.error('Error fetching chart data:', error);
                        // Fallback ke data default
                        createChart(period);
                    });
            });

            // Event listener untuk filter kelas
            document.getElementById('filterKelas').addEventListener('change', function() {
                const period = document.getElementById('filterPeriode').value;
                const kelasId = this.value;
                const jurusanId = document.getElementById('filterJurusan').value;
                
                fetch(`/admin/chart-data?period=${period}&kelas_id=${kelasId}&jurusan_id=${jurusanId}`)
                    .then(response => response.json())
                    .then(data => {
                        createChart(period, data);
                    });
            });

            // Event listener untuk filter jurusan
            document.getElementById('filterJurusan').addEventListener('change', function() {
                const period = document.getElementById('filterPeriode').value;
                const kelasId = document.getElementById('filterKelas').value;
                const jurusanId = this.value;
                
                fetch(`/admin/chart-data?period=${period}&kelas_id=${kelasId}&jurusan_id=${jurusanId}`)
                    .then(response => response.json())
                    .then(data => {
                        createChart(period, data);
                    });
            });

            // Event listener untuk reset filter
            document.getElementById('resetFilter').addEventListener('click', function() {
                document.getElementById('filterPeriode').value = 'week';
                document.getElementById('filterKelas').value = 'all';
                document.getElementById('filterJurusan').value = 'all';
                createChart('week', chartDataFromBackend.week);
            });
        });
    </script>
@endsection
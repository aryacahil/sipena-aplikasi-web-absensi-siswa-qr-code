@extends('layouts.admin')

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
                            <h1 class="fw-bold">1</h1>
                            <p class="mb-0"><span class="text-dark me-2">2</span>Completed</p>
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
                            <h1 class="fw-bold">1</h1>
                            <p class="mb-0"><span class="text-dark me-2">28</span>Completed</p>
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
                            <h1 class="fw-bold">12</h1>
                            <p class="mb-0"><span class="text-dark me-2">1</span>Completed</p>
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
                            <h1 class="fw-bold">2</h1>
                            <p class="mb-0"><span class="text-success me-2">5</span>Completed</p>
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
                                    <option value="X-TKJ-1">X TKJ 1</option>
                                    <option value="X-TKJ-2">X TKJ 2</option>
                                    <option value="XI-TKJ-1">XI TKJ 1</option>
                                    <option value="XI-TKJ-2">XI TKJ 2</option>
                                </select>

                                <!-- Filter Jurusan -->
                                <select class="form-select form-select-sm" id="filterJurusan" style="width: auto;">
                                    <option value="all">Semua Jurusan</option>
                                    <option value="TKJ">TKJ</option>
                                    <option value="RPL">RPL</option>
                                    <option value="MM">Multimedia</option>
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

        <!-- Tambahan: Tabel Jurusan dan Kelas -->
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
                                        <th scope="col">No</th>
                                        <th scope="col">Nama Jurusan</th>
                                        <th scope="col">Kode Jurusan</th>
                                        <th scope="col">Jumlah Kelas</th>
                                        <th scope="col">Kelas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
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
            
            // Data set untuk berbagai filter
            const dataByPeriod = {
                week: {
                    labels: ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'],
                    hadir: [85, 92, 88, 90, 87],
                    izin: [8, 5, 7, 6, 8],
                    sakit: [5, 2, 3, 3, 4],
                    alpha: [2, 1, 2, 1, 1]
                },
                month: {
                    labels: ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4'],
                    hadir: [430, 445, 425, 440],
                    izin: [34, 28, 32, 30],
                    sakit: [17, 15, 19, 16],
                    alpha: [9, 7, 8, 6]
                },
                year: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                    hadir: [1720, 1680, 1750, 1700, 1730, 1690, 1710, 1740, 1720, 1700, 1680, 1650],
                    izin: [135, 140, 130, 145, 138, 142, 136, 133, 139, 141, 144, 148],
                    sakit: [68, 72, 65, 70, 67, 71, 69, 66, 68, 70, 73, 75],
                    alpha: [32, 35, 30, 33, 31, 34, 33, 32, 31, 30, 34, 36]
                }
            };

            let currentChart;

            function createChart(period = 'week') {
                const data = dataByPeriod[period];
                
                if (currentChart) {
                    currentChart.destroy();
                }

                const attendanceData = {
                    labels: data.labels,
                    datasets: [{
                        label: 'Hadir',
                        data: data.hadir,
                        backgroundColor: 'rgba(25, 135, 84, 0.7)',
                        borderColor: 'rgba(25, 135, 84, 1)',
                        borderWidth: 2
                    }, {
                        label: 'Izin',
                        data: data.izin,
                        backgroundColor: 'rgba(255, 193, 7, 0.7)',
                        borderColor: 'rgba(255, 193, 7, 1)',
                        borderWidth: 2
                    }, {
                        label: 'Sakit',
                        data: data.sakit,
                        backgroundColor: 'rgba(13, 110, 253, 0.7)',
                        borderColor: 'rgba(13, 110, 253, 1)',
                        borderWidth: 2
                    }, {
                        label: 'Alpha',
                        data: data.alpha,
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
                                    stepSize: period === 'year' ? 200 : period === 'month' ? 50 : 10
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

            // Inisialisasi chart
            createChart('week');

            // Event listener untuk filter periode
            document.getElementById('filterPeriode').addEventListener('change', function() {
                createChart(this.value);
            });

            // Event listener untuk filter kelas
            document.getElementById('filterKelas').addEventListener('change', function() {
                console.log('Filter Kelas:', this.value);
                // Implementasi filter kelas (bisa dikembangkan dengan AJAX)
            });

            // Event listener untuk filter jurusan
            document.getElementById('filterJurusan').addEventListener('change', function() {
                console.log('Filter Jurusan:', this.value);
                // Implementasi filter jurusan (bisa dikembangkan dengan AJAX)
            });

            // Event listener untuk reset filter
            document.getElementById('resetFilter').addEventListener('click', function() {
                document.getElementById('filterPeriode').value = 'week';
                document.getElementById('filterKelas').value = 'all';
                document.getElementById('filterJurusan').value = 'all';
                createChart('week');
            });
        });
    </script>
@endsection
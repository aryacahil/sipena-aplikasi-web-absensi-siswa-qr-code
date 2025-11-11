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
                            <h1 class="fw-bold">{{ $totalSiswa }}</h1>
                            <p class="mb-0"><span class="text-dark me-2">{{ $siswaAktif }}</span>Aktif</p>
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
                            <h1 class="fw-bold">{{ $totalGuru }}</h1>
                            <p class="mb-0"><span class="text-dark me-2">{{ $guruAktif }}</span>Aktif</p>
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
                            <h1 class="fw-bold">{{ $totalKelas }}</h1>
                            <p class="mb-0"><span class="text-dark me-2">{{ $kelasAktif }}</span>Aktif</p>
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
                            <h1 class="fw-bold">{{ $totalAdmin }}</h1>
                            <p class="mb-0"><span class="text-success me-2">{{ $adminAktif }}</span>Aktif</p>
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
                                    @forelse($jurusanData as $index => $jurusan)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $jurusan->nama_jurusan }}</td>
                                        <td>{{ $jurusan->kode_jurusan }}</td>
                                        <td>{{ $jurusan->jumlah_kelas }}</td>
                                        <td>
                                            @if($jurusan->kelas_list)
                                                <span class="badge bg-primary">{{ $jurusan->kelas_list }}</span>
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

    <!-- Chart.js Script -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('attendanceChart').getContext('2d');
            
            // Data dinamis dari controller
            const attendanceData = {
                labels: @json($chartLabels),
                datasets: [{
                    label: 'Hadir',
                    data: @json($hadirData),
                    backgroundColor: 'rgba(25, 135, 84, 0.7)',
                    borderColor: 'rgba(25, 135, 84, 1)',
                    borderWidth: 2
                }, {
                    label: 'Izin',
                    data: @json($izinData),
                    backgroundColor: 'rgba(255, 193, 7, 0.7)',
                    borderColor: 'rgba(255, 193, 7, 1)',
                    borderWidth: 2
                }, {
                    label: 'Sakit',
                    data: @json($sakitData),
                    backgroundColor: 'rgba(13, 110, 253, 0.7)',
                    borderColor: 'rgba(13, 110, 253, 1)',
                    borderWidth: 2
                }, {
                    label: 'Alpha',
                    data: @json($alphaData),
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
                            text: 'Statistik Kehadiran Siswa (Minggu Ini)'
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
                                stepSize: 10
                            },
                            title: {
                                display: true,
                                text: 'Jumlah Siswa'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Hari'
                            }
                        }
                    }
                }
            };

            new Chart(ctx, config);
        });
    </script>
@endsection
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
                        <h4 class="mb-3 mb-md-0">Grafik Kehadiran Siswa</h4>
                        
                        <!-- Filter Grafik - Mobile Friendly -->
                        <div class="row g-2">
                            <!-- Filter Periode -->
                            <div class="col-12 col-sm-6 col-md-3">
                                <select class="form-select form-select-sm" id="filterPeriode">
                                    <option value="week">Minggu Ini</option>
                                    <option value="month">Bulan Ini</option>
                                    <option value="year">Tahun Ini</option>
                                </select>
                            </div>

                            <!-- Filter Kelas -->
                            <div class="col-12 col-sm-6 col-md-3">
                                <select class="form-select form-select-sm" id="filterKelas">
                                    <option value="all">Semua Kelas</option>
                                    @foreach($jurusans as $jurusan)
                                        @foreach($jurusan->kelas as $kelas)
                                            <option value="{{ $kelas->id }}">{{ $kelas->nama_kelas }}</option>
                                        @endforeach
                                    @endforeach
                                </select>
                            </div>

                            <!-- Filter Jurusan -->
                            <div class="col-12 col-sm-6 col-md-3">
                                <select class="form-select form-select-sm" id="filterJurusan">
                                    <option value="all">Semua Jurusan</option>
                                    @foreach($jurusans as $jurusan)
                                        <option value="{{ $jurusan->id }}">{{ $jurusan->nama_jurusan }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Tombol Reset -->
                            <div class="col-12 col-sm-6 col-md-3">
                                <button class="btn btn-sm btn-outline-secondary w-100" id="resetFilter">
                                    <i class="bi bi-arrow-clockwise"></i> Reset Filter
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="attendanceChart" style="min-height: 350px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Jurusan dan Kelas -->
        <div class="row mt-6">
            <div class="col-md-12 col-12">
                <div class="card">
                    <div class="card-header bg-white py-4">
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
                            <h4 class="mb-0">Data Jurusan dan Kelas</h4>
                            <a href="{{ route('admin.jurusan.index') }}" class="btn btn-primary btn-sm">Lihat Semua</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Desktop Table View -->
                        <div class="table-responsive d-none d-md-block">
                            <table class="table table-bordered table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">No</th>
                                        <th scope="col">Nama Jurusan</th>
                                        <th scope="col">Kode</th>
                                        <th scope="col" class="text-center">Jml Kelas</th>
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
                                                <div class="d-flex flex-wrap gap-1">
                                                    @foreach($jurusan->kelas as $kelas)
                                                        <span class="badge bg-info">
                                                            {{ $kelas->nama_kelas }} ({{ $kelas->siswa_count }})
                                                        </span>
                                                    @endforeach
                                                </div>
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

                        <!-- Mobile Card View -->
                        <div class="d-md-none">
                            @forelse($jurusans as $index => $jurusan)
                            <div class="card mb-3 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h5 class="card-title mb-1">{{ $jurusan->nama_jurusan }}</h5>
                                            <span class="badge bg-primary">{{ $jurusan->kode_jurusan }}</span>
                                        </div>
                                        <span class="badge bg-secondary">{{ $jurusan->kelas_count }} Kelas</span>
                                    </div>
                                    
                                    @if($jurusan->kelas->count() > 0)
                                        <div class="mt-3">
                                            <h6 class="text-muted mb-2" style="font-size: 0.875rem;">Daftar Kelas:</h6>
                                            <div class="d-flex flex-wrap gap-2">
                                                @foreach($jurusan->kelas as $kelas)
                                                    <span class="badge bg-info">
                                                        {{ $kelas->nama_kelas }} <span class="text-white">({{ $kelas->siswa_count }} siswa)</span>
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @else
                                        <div class="mt-3 text-muted small">
                                            Belum ada kelas
                                        </div>
                                    @endif
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-4 text-muted">
                                Belum ada data jurusan
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('styles')
<style>
    /* Mobile Optimization */
    @media (max-width: 768px) {
        .container-fluid {
            padding-left: 15px !important;
            padding-right: 15px !important;
        }
        
        /* Card spacing on mobile */
        .card {
            margin-bottom: 1rem;
        }
        
        /* Chart title on mobile */
        #attendanceChart {
            min-height: 300px !important;
        }
        
        /* Mobile card view for jurusan */
        .card .card-body {
            padding: 1rem;
        }
        
        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        /* Badge styling */
        .badge {
            font-size: 0.75rem;
            padding: 0.35em 0.65em;
            white-space: normal;
            word-wrap: break-word;
        }
    }
    
    @media (max-width: 576px) {
        /* Smaller card titles on mobile */
        h4.mb-0 {
            font-size: 1rem;
        }
        
        h1.fw-bold {
            font-size: 1.75rem;
        }
        
        /* Filter selects full width on mobile */
        .form-select-sm {
            font-size: 0.875rem;
        }
        
        /* Mobile card adjustments */
        .card .card-body {
            padding: 0.75rem;
        }
        
        .card-title {
            font-size: 1rem;
        }
        
        .badge {
            font-size: 0.7rem;
            padding: 0.3em 0.6em;
        }
    }
</style>
@endpush

@push('scripts')
    <script src="{{ asset('admin_assets/vendor/apexcharts/dist/apexcharts.min.js') }}"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Data dari backend
            const chartDataFromBackend = {
                week: {
                    labels: @json($chartData['labels'] ?? []),
                    hadir: @json($chartData['hadir'] ?? []),
                    izin: @json($chartData['izin'] ?? []),
                    sakit: @json($chartData['sakit'] ?? []),
                    alpha: @json($chartData['alpha'] ?? [])
                }
            };

            // Deteksi role dari URL
            const isAdmin = window.location.pathname.includes('/admin/');
            const baseUrl = isAdmin ? '/admin' : '/guru';

            let currentChart;

            function createChart(period = 'week', data = null) {
                const chartData = data || chartDataFromBackend[period] || chartDataFromBackend.week;
                
                // Validasi dan sanitasi data
                const sanitizeArray = (arr) => {
                    if (!Array.isArray(arr)) return [];
                    return arr.map(val => {
                        const num = Number(val);
                        return isNaN(num) || !isFinite(num) ? 0 : Math.max(0, num);
                    });
                };

                const hadirData = sanitizeArray(chartData.hadir);
                const izinData = sanitizeArray(chartData.izin);
                const sakitData = sanitizeArray(chartData.sakit);
                const alphaData = sanitizeArray(chartData.alpha);
                const labelsData = Array.isArray(chartData.labels) ? chartData.labels : [];

                // Validasi panjang data
                if (labelsData.length === 0) {
                    document.querySelector("#attendanceChart").innerHTML = '<div class="text-center p-5"><p class="text-muted">Tidak ada data untuk ditampilkan</p></div>';
                    return;
                }
                
                if (currentChart) {
                    currentChart.destroy();
                }

                // Hitung max value untuk Y-axis
                const allValues = [...hadirData, ...izinData, ...sakitData, ...alphaData];
                const maxValue = allValues.length > 0 ? Math.max(...allValues) : 10;
                
                // Deteksi ukuran layar
                const isMobile = window.innerWidth < 768;
                
                const options = {
                    series: [{
                        name: 'Hadir',
                        data: hadirData,
                        color: '#198754'
                    }, {
                        name: 'Izin',
                        data: izinData,
                        color: '#ffc107'
                    }, {
                        name: 'Sakit',
                        data: sakitData,
                        color: '#0d6efd'
                    }, {
                        name: 'Alpha',
                        data: alphaData,
                        color: '#dc3545'
                    }],
                    chart: {
                        type: 'bar',
                        height: isMobile ? 300 : 400,
                        toolbar: {
                            show: !isMobile,
                            tools: {
                                download: true,
                                selection: false,
                                zoom: false,
                                zoomin: false,
                                zoomout: false,
                                pan: false,
                                reset: false
                            }
                        },
                        animations: {
                            enabled: true,
                            easing: 'easeinout',
                            speed: 800
                        }
                    },
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: isMobile ? '70%' : '55%',
                            borderRadius: isMobile ? 3 : 5,
                            dataLabels: {
                                position: 'top'
                            }
                        },
                    },
                    dataLabels: {
                        enabled: !isMobile,
                        offsetY: -20,
                        style: {
                            fontSize: '10px',
                            colors: ["#304758"]
                        }
                    },
                    stroke: {
                        show: true,
                        width: 2,
                        colors: ['transparent']
                    },
                    xaxis: {
                        categories: labelsData,
                        title: {
                            text: period === 'week' ? 'Hari' : 
                                  period === 'month' ? 'Minggu' : 'Bulan',
                            style: {
                                fontSize: isMobile ? '10px' : '12px',
                                fontWeight: 600
                            }
                        },
                        labels: {
                            style: {
                                fontSize: isMobile ? '9px' : '12px'
                            },
                            rotate: isMobile ? -45 : 0,
                            rotateAlways: isMobile
                        }
                    },
                    yaxis: {
                        min: 0,
                        max: maxValue > 0 ? Math.ceil(maxValue * 1.2) : 10,
                        tickAmount: isMobile ? 5 : Math.min(10, maxValue),
                        forceNiceScale: true,
                        title: {
                            text: 'Jumlah Siswa',
                            style: {
                                fontSize: isMobile ? '10px' : '12px',
                                fontWeight: 600
                            }
                        },
                        labels: {
                            style: {
                                fontSize: isMobile ? '9px' : '11px'
                            },
                            formatter: function (val) {
                                return Math.floor(val);
                            }
                        }
                    },
                    fill: {
                        opacity: 0.9
                    },
                    tooltip: {
                        enabled: true,
                        y: {
                            formatter: function (val) {
                                return val + " siswa";
                            }
                        }
                    },
                    legend: {
                        position: isMobile ? 'bottom' : 'top',
                        horizontalAlign: 'center',
                        fontSize: isMobile ? '11px' : '13px',
                        markers: {
                            width: isMobile ? 10 : 12,
                            height: isMobile ? 10 : 12,
                            radius: 2
                        },
                        itemMargin: {
                            horizontal: isMobile ? 5 : 10,
                            vertical: 5
                        }
                    },
                    title: {
                        text: isMobile ? '' : `Statistik Kehadiran Siswa (${
                            period === 'week' ? 'Minggu Ini' : 
                            period === 'month' ? 'Bulan Ini' : 
                            'Tahun Ini'
                        })`,
                        align: 'left',
                        style: {
                            fontSize: '14px',
                            fontWeight: 600,
                            color: '#495057'
                        }
                    },
                    grid: {
                        borderColor: '#f1f1f1',
                        strokeDashArray: 4,
                        padding: {
                            left: isMobile ? 5 : 10,
                            right: isMobile ? 5 : 10
                        }
                    }
                };

                currentChart = new ApexCharts(document.querySelector("#attendanceChart"), options);
                currentChart.render();
            }

            // Inisialisasi chart dengan data dari backend
            createChart('week', chartDataFromBackend.week);

            // Redraw chart on window resize
            let resizeTimeout;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimeout);
                resizeTimeout = setTimeout(function() {
                    if (currentChart) {
                        const period = document.getElementById('filterPeriode').value;
                        createChart(period, chartDataFromBackend[period] || chartDataFromBackend.week);
                    }
                }, 250);
            });

            // Function untuk fetch data dari server
            function fetchChartData() {
                const period = document.getElementById('filterPeriode').value;
                const kelasId = document.getElementById('filterKelas').value;
                const jurusanId = document.getElementById('filterJurusan').value;
                
                const chartContainer = document.querySelector("#attendanceChart");
                if (chartContainer) {
                    chartContainer.style.opacity = '0.5';
                }
                
                fetch(`${baseUrl}/chart-data?period=${period}&kelas_id=${kelasId}&jurusan_id=${jurusanId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        createChart(period, data);
                        if (chartContainer) {
                            chartContainer.style.opacity = '1';
                        }
                    })
                    .catch(error => {
                        createChart(period, chartDataFromBackend.week);
                        if (chartContainer) {
                            chartContainer.style.opacity = '1';
                        }
                        alert('Gagal memuat data grafik. Silakan coba lagi.');
                    });
            }

            // Event listeners
            document.getElementById('filterPeriode').addEventListener('change', fetchChartData);
            document.getElementById('filterKelas').addEventListener('change', fetchChartData);
            document.getElementById('filterJurusan').addEventListener('change', fetchChartData);
            
            document.getElementById('resetFilter').addEventListener('click', function() {
                document.getElementById('filterPeriode').value = 'week';
                document.getElementById('filterKelas').value = 'all';
                document.getElementById('filterJurusan').value = 'all';
                createChart('week', chartDataFromBackend.week);
            });
        });
    </script>
@endpush
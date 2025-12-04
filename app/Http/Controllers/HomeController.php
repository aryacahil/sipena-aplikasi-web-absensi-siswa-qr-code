<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Kelas;
use App\Models\Jurusan;
use App\Models\Presensi;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function adminHome()
    {
        $stats = [
            'total_siswa' => User::whereRaw('role = ?', [2])->count(),
            'total_guru' => User::whereRaw('role = ?', [0])->count(),
            'total_kelas' => Kelas::count(),
            'total_admin' => User::whereRaw('role = ?', [1])->count(),
            
            'siswa_completed' => User::whereRaw('role = ?', [2])->where('status', 'active')->count(),
            'guru_completed' => User::whereRaw('role = ?', [0])->where('status', 'active')->count(),
            'kelas_completed' => Kelas::withCount('siswa')->get()->filter(function($kelas) {
                return $kelas->siswa_count > 0;
            })->count(),
            'admin_completed' => User::whereRaw('role = ?', [1])->where('status', 'active')->count(),
        ];

        $jurusans = Jurusan::withCount('kelas')
            ->with(['kelas' => function($query) {
                $query->withCount('siswa');
            }])
            ->get();

        $chartData = $this->getChartData('week');

        return view('admin.home', compact('stats', 'jurusans', 'chartData'));
    }

    public function guruHome() 
    {
        $stats = [
            'total_siswa' => User::whereRaw('role = ?', [2])->count(),
            'total_guru' => User::whereRaw('role = ?', [0])->count(),
            'total_kelas' => Kelas::count(),
            'total_admin' => User::whereRaw('role = ?', [1])->count(),
            
            'siswa_completed' => User::whereRaw('role = ?', [2])->where('status', 'active')->count(),
            'guru_completed' => User::whereRaw('role = ?', [0])->where('status', 'active')->count(),
            'kelas_completed' => Kelas::withCount('siswa')->get()->filter(function($kelas) {
                return $kelas->siswa_count > 0;
            })->count(),
            'admin_completed' => User::whereRaw('role = ?', [1])->where('status', 'active')->count(),
        ];

        $jurusans = Jurusan::withCount('kelas')
            ->with(['kelas' => function($query) {
                $query->withCount('siswa');
            }])
            ->get();

        $chartData = $this->getChartData('week');

        return view('guru.home', compact('stats', 'jurusans', 'chartData')); 
    }

    public function siswaHome()
    {
        return view('siswa.home');
    }

    public function getChartDataAjax(Request $request)
    {
        $period = $request->get('period', 'week');
        $kelasId = $request->get('kelas_id', 'all');
        $jurusanId = $request->get('jurusan_id', 'all');

        $chartData = $this->getChartData($period, $kelasId, $jurusanId);

        return response()->json($chartData);
    }

    private function getChartData($period = 'week', $kelasId = 'all', $jurusanId = 'all')
    {
        $dates = [];
        $labels = [];
        
        switch ($period) {
            case 'week':
                $today = Carbon::now();
                
                if ($today->dayOfWeek === 0) {
                    $startDate = Carbon::now()->subWeek()->startOfWeek(Carbon::MONDAY);
                } else {
                    $startDate = Carbon::now()->startOfWeek(Carbon::MONDAY);
                }
                
                $endDate = $startDate->copy()->addDays(4); 
                
                for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
                    $dates[] = $date->format('Y-m-d');
                    $labels[] = $this->getDayName($date->dayOfWeek);
                }
                break;
                
            case 'month':
                $endDate = Carbon::now()->endOfWeek();
                $startDate = Carbon::now()->subWeeks(3)->startOfWeek();
                
                $weekNumber = 1;
                for ($date = $startDate->copy(); $date <= $endDate; $date->addWeek()) {
                    $weekStart = $date->copy();
                    $weekEnd = $date->copy()->endOfWeek();
                    $dates[] = ['start' => $weekStart->format('Y-m-d'), 'end' => $weekEnd->format('Y-m-d')];
                    $labels[] = 'Minggu ' . $weekNumber;
                    $weekNumber++;
                }
                break;
                
            case 'year':
                $endDate = Carbon::now();
                $startDate = Carbon::now()->subMonths(11)->startOfMonth();
                
                for ($date = $startDate->copy(); $date <= $endDate; $date->addMonth()) {
                    $dates[] = $date->format('Y-m');
                    $labels[] = $this->getMonthName($date->month);
                }
                break;
        }

        $chartData = [
            'labels' => $labels,
            'hadir' => [],
            'izin' => [],
            'sakit' => [],
            'alpha' => []
        ];

        foreach ($dates as $date) {
            $baseQuery = Presensi::query();

            if ($period === 'month' && is_array($date)) {
                $baseQuery->whereBetween('tanggal_presensi', [$date['start'], $date['end']]);
            } elseif ($period === 'year') {
                $year = substr($date, 0, 4);
                $month = substr($date, 5, 2);
                $baseQuery->whereYear('tanggal_presensi', $year)
                          ->whereMonth('tanggal_presensi', $month);
            } else {
                $baseQuery->whereDate('tanggal_presensi', $date);
            }

            if ($kelasId !== 'all' && !empty($kelasId)) {
                $baseQuery->where('kelas_id', $kelasId);
            }

            if ($jurusanId !== 'all' && !empty($jurusanId)) {
                $baseQuery->whereHas('kelas', function($q) use ($jurusanId) {
                    $q->where('jurusan_id', $jurusanId);
                });
            }

            $chartData['hadir'][] = (int) (clone $baseQuery)->where('status', 'hadir')->count();
            $chartData['izin'][] = (int) (clone $baseQuery)->where('status', 'izin')->count();
            $chartData['sakit'][] = (int) (clone $baseQuery)->where('status', 'sakit')->count();
            $chartData['alpha'][] = (int) (clone $baseQuery)->where('status', 'alpha')->count();
        }

        $labelCount = count($chartData['labels']);
        foreach (['hadir', 'izin', 'sakit', 'alpha'] as $key) {
            while (count($chartData[$key]) < $labelCount) {
                $chartData[$key][] = 0;
            }
            $chartData[$key] = array_slice($chartData[$key], 0, $labelCount);
        }

        return $chartData;
    }

    private function getDayName($dayOfWeek)
    {
        $days = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu'
        ];
        
        return $days[$dayOfWeek];
    }

    private function getMonthName($month)
    {
        $months = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agt',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
        ];
        
        return $months[$month];
    }
}
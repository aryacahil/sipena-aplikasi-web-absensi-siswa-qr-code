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
        // Statistik Cards
        $stats = [
            'total_siswa' => User::where('role', 2)->count(),
            'total_guru' => User::where('role', 0)->count(),
            'total_kelas' => Kelas::count(),
            'total_admin' => User::where('role', 1)->count(),
            
            // Completed (bisa disesuaikan dengan logika Anda)
            'siswa_completed' => User::where('role', 2)->where('status', 'active')->count(),
            'guru_completed' => User::where('role', 0)->where('status', 'active')->count(),
            'kelas_completed' => Kelas::withCount('siswa')->get()->filter(function($kelas) {
                return $kelas->siswa_count > 0;
            })->count(),
            'admin_completed' => User::where('role', 1)->where('status', 'active')->count(),
        ];

        // Data Jurusan dan Kelas
        $jurusans = Jurusan::withCount('kelas')
            ->with(['kelas' => function($query) {
                $query->withCount('siswa');
            }])
            ->get();

        // Data Grafik Kehadiran (5 hari terakhir)
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays(4);
        
        $dates = [];
        $labels = [];
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dates[] = $date->format('Y-m-d');
            $labels[] = $this->getDayName($date->dayOfWeek);
        }

        $chartData = [
            'labels' => $labels,
            'hadir' => [],
            'izin' => [],
            'sakit' => [],
            'alpha' => []
        ];

        foreach ($dates as $date) {
            $chartData['hadir'][] = Presensi::whereDate('tanggal_presensi', $date)
                ->where('status', 'hadir')->count();
            $chartData['izin'][] = Presensi::whereDate('tanggal_presensi', $date)
                ->where('status', 'izin')->count();
            $chartData['sakit'][] = Presensi::whereDate('tanggal_presensi', $date)
                ->where('status', 'sakit')->count();
            $chartData['alpha'][] = Presensi::whereDate('tanggal_presensi', $date)
                ->where('status', 'alpha')->count();
        }

        return view('admin.home', compact('stats', 'jurusans', 'chartData'));
    }

    public function guruHome() 
    {
        // Statistik Cards untuk Guru
        $stats = [
            'total_siswa' => User::where('role', 2)->count(),
            'total_guru' => User::where('role', 0)->count(),
            'total_kelas' => Kelas::count(),
            'total_admin' => User::where('role', 1)->count(),
            
            'siswa_completed' => User::where('role', 2)->where('status', 'active')->count(),
            'guru_completed' => User::where('role', 0)->where('status', 'active')->count(),
            'kelas_completed' => Kelas::withCount('siswa')->get()->filter(function($kelas) {
                return $kelas->siswa_count > 0;
            })->count(),
            'admin_completed' => User::where('role', 1)->where('status', 'active')->count(),
        ];

        // Data Jurusan dan Kelas
        $jurusans = Jurusan::withCount('kelas')
            ->with(['kelas' => function($query) {
                $query->withCount('siswa');
            }])
            ->get();

        // Data Grafik Kehadiran
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays(4);
        
        $dates = [];
        $labels = [];
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dates[] = $date->format('Y-m-d');
            $labels[] = $this->getDayName($date->dayOfWeek);
        }

        $chartData = [
            'labels' => $labels,
            'hadir' => [],
            'izin' => [],
            'sakit' => [],
            'alpha' => []
        ];

        foreach ($dates as $date) {
            $chartData['hadir'][] = Presensi::whereDate('tanggal_presensi', $date)
                ->where('status', 'hadir')->count();
            $chartData['izin'][] = Presensi::whereDate('tanggal_presensi', $date)
                ->where('status', 'izin')->count();
            $chartData['sakit'][] = Presensi::whereDate('tanggal_presensi', $date)
                ->where('status', 'sakit')->count();
            $chartData['alpha'][] = Presensi::whereDate('tanggal_presensi', $date)
                ->where('status', 'alpha')->count();
        }

        return view('guru.home', compact('stats', 'jurusans', 'chartData')); 
    }

    public function siswaHome()
    {
        return view('siswa.home');
    }

    /**
     * Helper function untuk mendapatkan nama hari dalam Bahasa Indonesia
     */
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
}
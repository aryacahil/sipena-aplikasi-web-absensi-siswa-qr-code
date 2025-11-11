<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\QrCleanupCommand; // <-- tambahkan ini

class Kernel extends ConsoleKernel
{
    /**
     * Daftar command custom yang tersedia untuk Artisan.
     */
    protected $commands = [
        QrCleanupCommand::class, // <-- tambahkan ini
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Jalankan command ini setiap jam (bisa diubah ke daily, hourly, dst)
        $schedule->command('qr:cleanup')->hourly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\QrCleanupCommand;

class Kernel extends ConsoleKernel
{
    /**
     * Daftar command custom yang tersedia untuk Artisan.
     */
    protected $commands = [
        QrCleanupCommand::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // ====================================================
        // CLEANUP QR CODE EXPIRED - OTOMATIS HAPUS
        // ====================================================
        // Jalankan setiap jam untuk cleanup QR expired
        $schedule->command('qr:cleanup')
            ->hourly()
            ->withoutOverlapping() // Prevent multiple instances
            ->runInBackground(); // Don't block other scheduled tasks
        
        // Alternative schedules (pilih salah satu):
        // $schedule->command('qr:cleanup')->everyMinute(); // Testing only!
        // $schedule->command('qr:cleanup')->everyFiveMinutes();
        // $schedule->command('qr:cleanup')->everyTenMinutes();
        // $schedule->command('qr:cleanup')->everyThirtyMinutes();
        // $schedule->command('qr:cleanup')->daily(); // Run once per day
        // $schedule->command('qr:cleanup')->dailyAt('23:00'); // Run at 11 PM
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
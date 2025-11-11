<?php

namespace App\Console\Commands;

use App\Models\PresensiSession;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class QrCleanupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qr:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup expired QR codes and update session status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting QR code cleanup...');
        
        $now = Carbon::now();
        $currentDate = $now->format('Y-m-d');
        $currentTime = $now->format('H:i:s');
        
        // Cari semua sesi yang sudah expired tapi masih berstatus active
        $expiredSessions = PresensiSession::where('status', 'active')
            ->where(function($query) use ($currentDate, $currentTime) {
                // Sesi yang tanggalnya sudah lewat
                $query->where('tanggal', '<', $currentDate)
                    // Atau sesi hari ini tapi jam selesainya sudah lewat
                    ->orWhere(function($q) use ($currentDate, $currentTime) {
                        $q->where('tanggal', '=', $currentDate)
                          ->whereRaw("TIME(jam_selesai) < ?", [$currentTime]);
                    });
            })
            ->get();
        
        $count = 0;
        $errors = [];
        
        foreach ($expiredSessions as $session) {
            try {
                // Hapus file QR code jika ada
                $qrPath = 'qrcodes/' . $session->qr_code . '.png';
                if (Storage::disk('public')->exists($qrPath)) {
                    Storage::disk('public')->delete($qrPath);
                    $this->info("Deleted QR file: {$qrPath}");
                }
                
                // Update status menjadi expired
                $session->update(['status' => 'expired']);
                $count++;
                
                $this->info("Session #{$session->id} ({$session->kelas->nama_kelas}) marked as expired");
                
            } catch (\Exception $e) {
                $error = "Error processing session #{$session->id}: " . $e->getMessage();
                $errors[] = $error;
                $this->error($error);
                Log::error($error);
            }
        }
        
        // Summary
        $this->info("===========================================");
        $this->info("QR Cleanup Summary:");
        $this->info("Total sessions processed: {$count}");
        if (count($errors) > 0) {
            $this->warn("Errors encountered: " . count($errors));
        }
        $this->info("===========================================");
        
        Log::info("QR Cleanup completed. Processed: {$count}, Errors: " . count($errors));
        
        return Command::SUCCESS;
    }
}
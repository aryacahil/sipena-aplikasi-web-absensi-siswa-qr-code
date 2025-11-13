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
    protected $description = 'Auto-delete expired QR codes and their files';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting QR code cleanup...');
        
        $now = Carbon::now();
        $currentDate = $now->format('Y-m-d');
        $currentTime = $now->format('H:i:s');
        
        // =================================================
        // STEP 1: Cari semua sesi yang sudah expired
        // =================================================
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
        $deleted = 0;
        $errors = [];
        
        foreach ($expiredSessions as $session) {
            try {
                // =================================================
                // STEP 2: Hapus file QR code jika ada
                // =================================================
                $qrPath = 'qrcodes/' . $session->qr_code . '.svg';
                if (Storage::disk('public')->exists($qrPath)) {
                    Storage::disk('public')->delete($qrPath);
                    $this->info("✓ Deleted QR file: {$qrPath}");
                }
                
                // =================================================
                // STEP 3: HAPUS RECORD DARI DATABASE (bukan update status!)
                // =================================================
                $sessionInfo = "Session #{$session->id} ({$session->kelas->nama_kelas} - {$session->tanggal->format('Y-m-d')})";
                
                $session->delete(); // HARD DELETE!
                $deleted++;
                
                $this->info("✓ DELETED {$sessionInfo}");
                
                Log::info('Expired QR Session DELETED', [
                    'session_id' => $session->id,
                    'kelas' => $session->kelas->nama_kelas,
                    'tanggal' => $session->tanggal->format('Y-m-d'),
                    'deleted_at' => now()
                ]);
                
            } catch (\Exception $e) {
                $error = "Error processing session #{$session->id}: " . $e->getMessage();
                $errors[] = $error;
                $this->error($error);
                Log::error($error, [
                    'session_id' => $session->id,
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
        
        // =================================================
        // STEP 4: Clean orphaned QR files (files tanpa database record)
        // =================================================
        $this->info('Checking for orphaned QR files...');
        $orphanedCount = 0;
        
        try {
            $allQrFiles = Storage::disk('public')->files('qrcodes');
            $validQrCodes = PresensiSession::pluck('qr_code')->toArray();
            
            foreach ($allQrFiles as $file) {
                $filename = basename($file, '.svg');
                
                if (!in_array($filename, $validQrCodes)) {
                    Storage::disk('public')->delete($file);
                    $orphanedCount++;
                    $this->info("✓ Deleted orphaned file: {$file}");
                }
            }
        } catch (\Exception $e) {
            $this->warn("Could not clean orphaned files: " . $e->getMessage());
        }
        
        // =================================================
        // Summary
        // =================================================
        $this->info("===========================================");
        $this->info("QR Cleanup Summary:");
        $this->info("✓ Expired sessions found: " . $expiredSessions->count());
        $this->info("✓ Sessions DELETED: {$deleted}");
        $this->info("✓ Orphaned files cleaned: {$orphanedCount}");
        if (count($errors) > 0) {
            $this->warn("⚠ Errors encountered: " . count($errors));
        }
        $this->info("===========================================");
        
        Log::info("QR Cleanup completed", [
            'expired_sessions' => $expiredSessions->count(),
            'deleted' => $deleted,
            'orphaned_files' => $orphanedCount,
            'errors' => count($errors)
        ]);
        
        return Command::SUCCESS;
    }
}
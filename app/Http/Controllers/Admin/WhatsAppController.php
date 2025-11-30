<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\FonnteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class WhatsAppController extends Controller
{
    protected $fonnteService;

    public function __construct(FonnteService $fonnteService)
    {
        $this->fonnteService = $fonnteService;
    }

    /**
     * Display WhatsApp settings page
     */
    public function index()
    {
        $settings = [
            'fonnte_api_key' => Setting::get('fonnte_api_key', ''),
            'fonnte_enabled' => Setting::get('fonnte_enabled', false),
            'fonnte_sender_number' => Setting::get('fonnte_sender_number', ''),
            'fonnte_device_id' => Setting::get('fonnte_device_id', ''),
            'fonnte_message_template' => Setting::get('fonnte_message_template_checkin', $this->getDefaultTemplate('checkin')),
            'fonnte_message_template_checkout' => Setting::get('fonnte_message_template_checkout', $this->getDefaultTemplate('checkout')),
        ];

        return view('admin.settings.whatsapp', compact('settings'));
    }

    /**
     * Update WhatsApp settings
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'fonnte_api_key' => 'required|string|max:255',
            'fonnte_sender_number' => 'required|string|max:20',
            'fonnte_device_id' => 'nullable|string|max:100',
            'fonnte_enabled' => 'nullable',
            'fonnte_message_template' => 'required|string', // Check-in template
            'fonnte_message_template_checkout' => 'required|string', // Check-out template
        ]);

        try {
            // Update all settings
            Setting::set('fonnte_api_key', $validated['fonnte_api_key']);
            Setting::set('fonnte_sender_number', $validated['fonnte_sender_number']);
            Setting::set('fonnte_device_id', $validated['fonnte_device_id'] ?? '');
            Setting::set('fonnte_message_template_checkin', $validated['fonnte_message_template']);
            Setting::set('fonnte_message_template_checkout', $validated['fonnte_message_template_checkout']);
            
            // Clear cache
            Setting::clearCache();
            
            // Handle enabled status
            $wantsToEnable = $request->has('fonnte_enabled') && 
                            in_array($request->input('fonnte_enabled'), ['on', '1', 'true', true], true);
            
            if ($wantsToEnable) {
                $testService = new FonnteService();
                $testResult = $testService->testConnection();
                
                if (!$testResult['success']) {
                    Setting::set('fonnte_enabled', false);
                    Setting::clearCache();
                    
                    return redirect()
                        ->back()
                        ->with('error', 'Pengaturan disimpan, tetapi notifikasi TIDAK DIAKTIFKAN. Alasan: ' . $testResult['message'])
                        ->withInput();
                }
                
                Setting::set('fonnte_enabled', true);
                Setting::clearCache();
                
                return redirect()
                    ->route('admin.settings.whatsapp')
                    ->with('success', 'Pengaturan berhasil disimpan dan notifikasi WhatsApp DIAKTIFKAN! ✅');
            }
            
            Setting::set('fonnte_enabled', false);
            Setting::clearCache();

            return redirect()
                ->route('admin.settings.whatsapp')
                ->with('success', 'Pengaturan berhasil disimpan. Notifikasi WhatsApp tidak aktif.');

        } catch (\Exception $e) {
            Log::error('Failed to update WhatsApp settings', [
                'error' => $e->getMessage()
            ]);

            return redirect()
                ->back()
                ->with('error', 'Gagal menyimpan pengaturan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Test Fonnte connection
     */
    public function testConnection(Request $request)
    {
        try {
            // Get API key dan sender number dari form
            $apiKey = $request->input('api_key') ?? Setting::get('fonnte_api_key');
            $senderNumber = $request->input('sender_number') ?? Setting::get('fonnte_sender_number');
            
            if (empty($apiKey)) {
                return response()->json([
                    'success' => false,
                    'message' => 'API Key tidak boleh kosong'
                ], 400);
            }

            if (empty($senderNumber)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nomor WhatsApp pengirim tidak boleh kosong'
                ], 400);
            }

            // Normalize phone number
            $targetNumber = $this->normalizePhoneNumber($senderNumber);

            Log::info('Testing Fonnte connection', [
                'api_key_length' => strlen($apiKey),
                'api_key_preview' => substr($apiKey, 0, 10) . '...',
                'target_number' => $targetNumber
            ]);

            // Test dengan endpoint /validate dan target parameter
            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => $apiKey,
                ])
                ->post('https://api.fonnte.com/validate', [
                    'target' => $targetNumber
                ]);

            Log::info('Fonnte validate response', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            if ($response->successful()) {
                $result = $response->json();
                
                // Check status
                if (isset($result['status']) && $result['status'] === true) {
                    $deviceName = $result['device'] ?? $result['name'] ?? 'Connected Device';
                    
                    return response()->json([
                        'success' => true,
                        'message' => 'Koneksi berhasil! API Key valid.',
                        'device_name' => $deviceName,
                        'data' => $result
                    ]);
                }

                // Check if there's an error message
                if (isset($result['reason'])) {
                    return response()->json([
                        'success' => false,
                        'message' => $result['reason']
                    ], 400);
                }

                // Status false
                if (isset($result['status']) && $result['status'] === false) {
                    return response()->json([
                        'success' => false,
                        'message' => $result['reason'] ?? 'Validasi gagal'
                    ], 400);
                }

                // No explicit status but successful response
                return response()->json([
                    'success' => true,
                    'message' => 'API Key valid',
                    'device_name' => 'Connected',
                    'data' => $result
                ]);
            }

            // HTTP Error
            $errorBody = $response->json();
            $errorMessage = $errorBody['reason'] ?? 'Invalid API Key atau koneksi gagal';
            
            Log::warning('Fonnte test connection failed', [
                'status' => $response->status(),
                'error' => $errorMessage
            ]);

            return response()->json([
                'success' => false,
                'message' => $errorMessage
            ], 400);

        } catch (\Exception $e) {
            Log::error('Test connection error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send test message
     */
    public function testMessage(Request $request)
    {
        $validated = $request->validate([
            'phone_number' => 'required|string|min:10|max:15',
        ]);

        try {
            // Clear cache dulu untuk get latest settings
            Setting::clearCache();
            
            // Recreate service dengan setting terbaru
            $fonnteService = new FonnteService();
            
            // Check if service is ready
            if (!$fonnteService->isEnabled()) {
                Log::warning('Fonnte service not enabled for test message', [
                    'api_key_exists' => !empty(Setting::get('fonnte_api_key')),
                    'enabled' => Setting::get('fonnte_enabled', false)
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Notifikasi WhatsApp belum diaktifkan. Silakan centang "Aktifkan Notifikasi WhatsApp" dan simpan terlebih dahulu.'
                ], 400);
            }

            // Normalize phone number
            $phoneNumber = $this->normalizePhoneNumber($validated['phone_number']);

            Log::info('Sending test message', [
                'to' => $phoneNumber,
                'api_key_exists' => !empty(Setting::get('fonnte_api_key')),
                'enabled' => Setting::get('fonnte_enabled', false)
            ]);

            $testMessage = "📱 *Test Notifikasi*\n\n";
            $testMessage .= "Halo! Ini adalah pesan test dari Sistem Presensi Sekolah.\n\n";
            $testMessage .= "Jika Anda menerima pesan ini, berarti konfigurasi WhatsApp API sudah benar.\n\n";
            $testMessage .= "Waktu: " . now()->format('d F Y, H:i:s') . "\n\n";
            $testMessage .= "_Pesan otomatis dari Sistem Presensi_";

            $result = $fonnteService->sendMessage(
                $phoneNumber,
                $testMessage
            );

            if ($result['success']) {
                Log::info('Test message sent successfully', [
                    'to' => $phoneNumber
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Pesan test berhasil dikirim ke ' . $phoneNumber
                ]);
            }

            Log::warning('Test message failed', [
                'to' => $phoneNumber,
                'reason' => $result['message'] ?? 'Unknown'
            ]);

            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Gagal mengirim pesan'
            ], 400);

        } catch (\Exception $e) {
            Log::error('Test message error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'phone' => $validated['phone_number'] ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Normalize phone number format
     */
    protected function normalizePhoneNumber($phone)
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Convert 08xxx to 628xxx
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }
        
        // Add 62 if not present
        if (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone;
        }
        
        return $phone;
    }

    /**
     * Get default message template
     */
    protected function getDefaultTemplate($type = 'checkin')
    {
        if ($type === 'checkin') {
            return "Assalamualaikum Bapak/Ibu\n\n" .
                   "Kami informasikan bahwa:\n\n" .
                   "*Nama:* {student_name}\n" .
                   "*NIS:* {nis}\n" .
                   "*Kelas:* {class_name}\n" .
                   "*Status:* ✅ MASUK\n" .
                   "*Waktu:* {checkin_time}\n" .
                   "*Tanggal:* {date}\n\n" .
                   "Terima kasih.\n\n" .
                   "_Sistem Presensi Sekolah_";
        } else {
            return "Assalamualaikum Bapak/Ibu\n\n" .
                   "Kami informasikan bahwa:\n\n" .
                   "*Nama:* {student_name}\n" .
                   "*NIS:* {nis}\n" .
                   "*Kelas:* {class_name}\n" .
                   "*Status:* 🏠 PULANG\n" .
                   "*Check-in:* {checkin_time}\n" .
                   "*Check-out:* {checkout_time}\n" .
                   "*Tanggal:* {date}\n\n" .
                   "Terima kasih.\n\n" .
                   "_Sistem Presensi Sekolah_";
        }
    }
}
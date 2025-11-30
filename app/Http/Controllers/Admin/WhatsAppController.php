<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\FonnteDevice;
use App\Services\FonnteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppController extends Controller
{
    protected $fonnteService;

    public function __construct(FonnteService $fonnteService)
    {
        $this->fonnteService = $fonnteService;
    }

    /**
     * Display WhatsApp settings page with all devices
     */
    public function index()
    {
        $settings = [
            'fonnte_enabled' => Setting::get('fonnte_enabled', false),
            'fonnte_message_template_checkin' => Setting::get('fonnte_message_template_checkin', $this->getDefaultTemplate('checkin')),
            'fonnte_message_template_checkout' => Setting::get('fonnte_message_template_checkout', $this->getDefaultTemplate('checkout')),
        ];

        // Get all devices
        $devices = FonnteDevice::orderBy('priority', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get statistics
        $stats = $this->fonnteService->getDeviceStatistics();

        return view('admin.settings.whatsapp', compact('settings', 'devices', 'stats'));
    }

    /**
     * Update WhatsApp settings (Global settings & templates)
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'fonnte_message_template_checkin' => 'required|string',
            'fonnte_message_template_checkout' => 'required|string',
        ]);

        try {
            // Update templates
            Setting::set('fonnte_message_template_checkin', $validated['fonnte_message_template_checkin']);
            Setting::set('fonnte_message_template_checkout', $validated['fonnte_message_template_checkout']);
            
            // Handle enabled status (checkbox)
            $wantsToEnable = $request->has('fonnte_enabled') && 
                            $request->input('fonnte_enabled') == '1';
            
            if ($wantsToEnable) {
                // Check if there are available devices
                if (!$this->fonnteService->hasAvailableDevices()) {
                    Setting::set('fonnte_enabled', false);
                    Setting::clearCache();
                    
                    return redirect()
                        ->back()
                        ->with('error', 'Tidak ada device yang tersedia. Silakan tambahkan device terlebih dahulu.')
                        ->withInput();
                }
                
                Setting::set('fonnte_enabled', true);
                Setting::clearCache();
                
                return redirect()
                    ->route('admin.settings.whatsapp.index')
                    ->with('success', 'Pengaturan berhasil disimpan dan notifikasi WhatsApp DIAKTIFKAN! ✅');
            }
            
            Setting::set('fonnte_enabled', false);
            Setting::clearCache();

            return redirect()
                ->route('admin.settings.whatsapp.index')
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
     * Store new device
     */
    public function storeDevice(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'api_key' => 'required|string|max:255|unique:fonnte_devices,api_key',
            'phone_number' => 'required|string|max:20',
            'device_id' => 'nullable|string|max:100',
            'priority' => 'required|integer|min:1|max:100',
        ]);

        try {
            // Normalize phone number
            $validated['phone_number'] = $this->normalizePhoneNumber($validated['phone_number']);
            
            // Handle is_active checkbox
            $validated['is_active'] = $request->input('is_active') == '1';

            Log::info('Creating new device', [
                'name' => $validated['name'],
                'phone' => $validated['phone_number'],
                'is_active' => $validated['is_active']
            ]);

            // Create device
            $device = FonnteDevice::create($validated);

            Log::info('Device created successfully', [
                'device_id' => $device->id,
                'name' => $device->name
            ]);

            // Test connection
            $testResult = $this->fonnteService->testDevice($device);

            if ($testResult['success']) {
                return redirect()
                    ->route('admin.settings.whatsapp.index')
                    ->with('success', 'Device berhasil ditambahkan dan terhubung! ✅');
            } else {
                return redirect()
                    ->route('admin.settings.whatsapp.index')
                    ->with('warning', 'Device berhasil ditambahkan, tapi koneksi gagal: ' . $testResult['message']);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed for device creation', [
                'errors' => $e->errors()
            ]);

            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->with('error', 'Validasi gagal. Periksa data yang diinput.')
                ->withInput();

        } catch (\Exception $e) {
            Log::error('Failed to create device', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->back()
                ->with('error', 'Gagal menambahkan device: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update device
     */
    public function updateDevice(Request $request, FonnteDevice $device)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'api_key' => 'required|string|max:255|unique:fonnte_devices,api_key,' . $device->id,
            'phone_number' => 'required|string|max:20',
            'device_id' => 'nullable|string|max:100',
            'priority' => 'required|integer|min:1|max:100',
        ]);

        try {
            // Normalize phone number
            $validated['phone_number'] = $this->normalizePhoneNumber($validated['phone_number']);
            
            // Handle is_active checkbox
            $validated['is_active'] = $request->input('is_active') == '1';

            // Update device
            $device->update($validated);

            // Return JSON for AJAX request
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Device berhasil diupdate!'
                ]);
            }

            return redirect()
                ->route('admin.settings.whatsapp.index')
                ->with('success', 'Device berhasil diupdate!');

        } catch (\Exception $e) {
            Log::error('Failed to update device', [
                'device_id' => $device->id,
                'error' => $e->getMessage()
            ]);

            // Return JSON for AJAX request
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal update device: ' . $e->getMessage()
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', 'Gagal update device: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
 * Delete device - Enhanced with debugging
 */
public function deleteDevice(Request $request, $deviceId)
{
    try {
        // Log incoming request
        Log::info('Delete device request received', [
            'device_id_param' => $deviceId,
            'request_method' => $request->method(),
            'is_ajax' => $request->ajax(),
            'wants_json' => $request->wantsJson(),
            'headers' => $request->headers->all()
        ]);

        // Find device manually by ID
        $device = FonnteDevice::find($deviceId);
        
        if (!$device) {
            Log::warning('Device not found', ['device_id' => $deviceId]);
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Device tidak ditemukan'
                ], 404);
            }

            return redirect()
                ->back()
                ->with('error', 'Device tidak ditemukan');
        }

        $deviceName = $device->name;
        
        Log::info('Deleting device', [
            'device_id' => $device->id,
            'name' => $deviceName
        ]);

        $device->delete();

        Log::info('Device deleted successfully', [
            'device_id' => $device->id,
            'name' => $deviceName
        ]);

        // Always return JSON for AJAX request
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Device '$deviceName' berhasil dihapus!"
            ], 200);
        }

        return redirect()
            ->route('admin.settings.whatsapp.index')
            ->with('success', "Device '$deviceName' berhasil dihapus!");

    } catch (\Exception $e) {
        Log::error('Failed to delete device', [
            'device_id' => $deviceId ?? null,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus device: ' . $e->getMessage()
            ], 500);
        }

        return redirect()
            ->back()
            ->with('error', 'Gagal menghapus device: ' . $e->getMessage());
    }
}

    /**
     * Test device connection
     */
    public function testDeviceConnection(Request $request)
    {
        try {
            $validated = $request->validate([
                'device_id' => 'required|exists:fonnte_devices,id'
            ]);

            $device = FonnteDevice::findOrFail($validated['device_id']);

            $result = $this->fonnteService->testDevice($device);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Koneksi berhasil!',
                    'device_name' => $result['device_name'] ?? 'Connected',
                    'status' => $device->fresh()->status
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Koneksi gagal'
            ], 400);

        } catch (\Exception $e) {
            Log::error('Test device connection error', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test all devices
     */
    public function testAllDevices()
    {
        try {
            $results = $this->fonnteService->testAllDevices();

            $successCount = collect($results)->where('success', true)->count();
            $totalCount = count($results);

            return response()->json([
                'success' => true,
                'message' => "$successCount dari $totalCount device berhasil terhubung",
                'results' => $results
            ]);

        } catch (\Exception $e) {
            Log::error('Test all devices error', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle device active status
     */
    public function toggleDevice(FonnteDevice $device)
    {
        try {
            $device->update([
                'is_active' => !$device->is_active
            ]);

            $status = $device->is_active ? 'diaktifkan' : 'dinonaktifkan';

            return response()->json([
                'success' => true,
                'message' => "Device berhasil $status",
                'is_active' => $device->is_active
            ]);

        } catch (\Exception $e) {
            Log::error('Toggle device active error', [
                'device_id' => $device->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status: ' . $e->getMessage()
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
            // Clear cache
            Setting::clearCache();
            
            // Recreate service
            $fonnteService = new FonnteService();
            
            // Check if service is ready
            if (!$fonnteService->isEnabled()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notifikasi WhatsApp belum diaktifkan atau tidak ada device tersedia.'
                ], 400);
            }

            // Normalize phone number
            $phoneNumber = $this->normalizePhoneNumber($validated['phone_number']);

            Log::info('Sending test message', [
                'to' => $phoneNumber
            ]);

            $testMessage = "📱 *Test Notifikasi*\n\n";
            $testMessage .= "Halo! Ini adalah pesan test dari Sistem Presensi Sekolah.\n\n";
            $testMessage .= "Jika Anda menerima pesan ini, berarti konfigurasi WhatsApp API sudah benar.\n\n";
            $testMessage .= "Waktu: " . now()->format('d F Y, H:i:s') . "\n\n";
            $testMessage .= "_Pesan otomatis dari Sistem Presensi_";

            $result = $fonnteService->sendMessage($phoneNumber, $testMessage);

            if ($result['success']) {
                Log::info('Test message sent successfully', [
                    'to' => $phoneNumber,
                    'device_id' => $result['device_id'] ?? null,
                    'device_name' => $result['device_name'] ?? null
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Pesan test berhasil dikirim ke ' . $phoneNumber . ' via ' . ($result['device_name'] ?? 'device'),
                    'device_name' => $result['device_name'] ?? 'Unknown'
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
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }
        
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
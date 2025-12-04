<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\FonnteDevice;
use App\Services\FonnteService;
use Illuminate\Http\Request;

class WhatsAppController extends Controller
{
    protected $fonnteService;

    public function __construct(FonnteService $fonnteService)
    {
        $this->fonnteService = $fonnteService;
    }

    public function index()
    {
        $settings = [
            'fonnte_enabled' => Setting::get('fonnte_enabled', false),
            'fonnte_message_template_checkin' => Setting::get('fonnte_message_template_checkin', $this->getDefaultTemplate('checkin')),
            'fonnte_message_template_checkout' => Setting::get('fonnte_message_template_checkout', $this->getDefaultTemplate('checkout')),
        ];

        $devices = FonnteDevice::orderBy('priority', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = $this->fonnteService->getDeviceStatistics();

        return view('admin.settings.whatsapp', compact('settings', 'devices', 'stats'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'fonnte_message_template_checkin' => 'required|string',
            'fonnte_message_template_checkout' => 'required|string',
        ]);

        try {
            Setting::set('fonnte_message_template_checkin', $validated['fonnte_message_template_checkin']);
            Setting::set('fonnte_message_template_checkout', $validated['fonnte_message_template_checkout']);
            
            $wantsToEnable = $request->has('fonnte_enabled') && 
                            $request->input('fonnte_enabled') == '1';
            
            if ($wantsToEnable) {
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

            return redirect()
                ->back()
                ->with('error', 'Gagal menyimpan pengaturan: ' . $e->getMessage())
                ->withInput();
        }
    }

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
            $validated['phone_number'] = $this->normalizePhoneNumber($validated['phone_number']);
            
            $validated['is_active'] = $request->input('is_active') == '1';

            $device = FonnteDevice::create($validated);

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

            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->with('error', 'Validasi gagal. Periksa data yang diinput.')
                ->withInput();

        } catch (\Exception $e) {

            return redirect()
                ->back()
                ->with('error', 'Gagal menambahkan device: ' . $e->getMessage())
                ->withInput();
        }
    }

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
            $validated['phone_number'] = $this->normalizePhoneNumber($validated['phone_number']);
            
            $validated['is_active'] = $request->input('is_active') == '1';

            $device->update($validated);

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

public function deleteDevice(Request $request, $deviceId)
{
    try {

        $device = FonnteDevice::find($deviceId);
        
        if (!$device) {
            
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

        $device->delete();

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

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

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

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

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

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function testMessage(Request $request)
    {
        $validated = $request->validate([
            'phone_number' => 'required|string|min:10|max:15',
        ]);

        try {
            Setting::clearCache();
            
            
            $fonnteService = new FonnteService();
            
            if (!$fonnteService->isEnabled()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notifikasi WhatsApp belum diaktifkan atau tidak ada device tersedia.'
                ], 400);
            }

            $phoneNumber = $this->normalizePhoneNumber($validated['phone_number']);

            $testMessage = "📱 *Test Notifikasi*\n\n";
            $testMessage .= "Halo! Ini adalah pesan test dari Sistem Presensi Sekolah.\n\n";
            $testMessage .= "Jika Anda menerima pesan ini, berarti konfigurasi WhatsApp API sudah benar.\n\n";
            $testMessage .= "Waktu: " . now()->format('d F Y, H:i:s') . "\n\n";
            $testMessage .= "_Pesan otomatis dari Sistem Presensi_";

            $result = $fonnteService->sendMessage($phoneNumber, $testMessage);

            if ($result['success']) {
                
                return response()->json([
                    'success' => true,
                    'message' => 'Pesan test berhasil dikirim ke ' . $phoneNumber . ' via ' . ($result['device_name'] ?? 'device'),
                    'device_name' => $result['device_name'] ?? 'Unknown'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Gagal mengirim pesan'
            ], 400);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

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
<?php

namespace App\Services;

use App\Models\FonnteDevice;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;

class FonnteService
{
    protected $apiUrl = 'https://api.fonnte.com';

    public function isEnabled(): bool
    {
        return Setting::get('fonnte_enabled', false) && $this->hasAvailableDevices();
    }

    public function hasAvailableDevices(): bool
    {
        return FonnteDevice::available()->exists();
    }

    public function getNextDevice(): ?FonnteDevice
    {
        $device = FonnteDevice::available()
            ->byPriority()
            ->first();

        if (!$device) {
            return null;
        }

        return $device;
    }

    /**
     * Send WhatsApp message via Fonnte with auto device selection
     * 
     * @param string $phoneNumber Target phone number
     * @param string $message Message to send
     * @param FonnteDevice|null $device Specific device to use
     * @param int|null $kelasId Filter device by class
     */
    public function sendMessage(string $phoneNumber, string $message, ?FonnteDevice $device = null, ?int $kelasId = null): array
    {
        if (!$device) {
            $device = $this->getNextDevice($kelasId);
        }

                    if (!$device) {
            return [
                'success' => false,
                'message' => 'No available device. All devices are offline or in error state.'
            ];
        }

        if ($kelasId !== null && !$device->canSendToClass($kelasId)) {
            
            $device = $this->getNextDevice($kelasId);
            
            if (!$device) {
                return [
                    'success' => false,
                    'message' => 'No device available for this class'
                ];
            }
        }

        try {
            $delay = rand(2, 5);
            sleep($delay);

            $phoneNumber = $this->normalizePhoneNumber($phoneNumber);

            $data = [
                'target' => $phoneNumber,
                'message' => $message,
                'countryCode' => '62',
            ];

            if (!empty($device->device_id)) {
                $data['device'] = $device->device_id;
            }

            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => $device->api_key,
                ])
                ->post($this->apiUrl . '/send', $data);

            if ($response->successful()) {
                $result = $response->json();
                
                if (isset($result['status']) && $result['status'] === true) {
                    $device->incrementSentCount();
                    
                    $device->updateStatus('connected', 'Message sent successfully');
                    
                    return [
                        'success' => true,
                        'message' => 'Message sent successfully',
                        'device_id' => $device->id,
                        'device_name' => $device->name,
                        'data' => $result
                    ];
                }

                $device->updateStatus('error', $result['reason'] ?? 'Failed to send message');
                
                $nextDevice = $this->getNextDevice();
                if ($nextDevice && $nextDevice->id !== $device->id) {
                    return $this->sendMessage($phoneNumber, $message, $nextDevice);
                }

                return [
                    'success' => false,
                    'message' => $result['reason'] ?? 'Failed to send message'
                ];
            }

            $errorBody = $response->json();
            $errorMessage = $errorBody['reason'] ?? 'HTTP Error: ' . $response->status();
            
            $device->updateStatus('error', $errorMessage);

            $nextDevice = $this->getNextDevice();
            if ($nextDevice && $nextDevice->id !== $device->id) {
                return $this->sendMessage($phoneNumber, $message, $nextDevice);
            }

            return [
                'success' => false,
                'message' => $errorMessage
            ];

        } catch (\Exception $e) {

            if ($device) {
                $device->updateStatus('error', $e->getMessage());
            }

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function testDevice(FonnteDevice $device): array
    {
        if (empty($device->api_key)) {
            return [
                'success' => false,
                'message' => 'API Key is missing'
            ];
        }

        if (empty($device->phone_number)) {
            return [
                'success' => false,
                'message' => 'Phone number is missing'
            ];
        }

        try {
            $targetNumber = $this->normalizePhoneNumber($device->phone_number);

            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => $device->api_key,
                ])
                ->post($this->apiUrl . '/validate', [
                    'target' => $targetNumber
                ]);

            if ($response->successful()) {
                $result = $response->json();
                
                if (isset($result['status']) && $result['status'] === true) {
                    $deviceName = $result['device'] ?? $result['name'] ?? 'Unknown Device';
                    
                    $device->updateStatus('connected', 'Device connected successfully');
                    
                    return [
                        'success' => true,
                        'message' => 'Connection successful',
                        'device_name' => $deviceName,
                        'data' => $result
                    ];
                }

                if (isset($result['reason'])) {
                    $device->updateStatus('disconnected', $result['reason']);
                    return [
                        'success' => false,
                        'message' => $result['reason']
                    ];
                }

                if (isset($result['status']) && $result['status'] === false) {
                    $device->updateStatus('disconnected', $result['reason'] ?? 'Validation failed');
                    return [
                        'success' => false,
                        'message' => $result['reason'] ?? 'Validation failed'
                    ];
                }

                $device->updateStatus('connected', 'API Key valid');
                return [
                    'success' => true,
                    'message' => 'API Key valid',
                    'device_name' => 'Connected',
                    'data' => $result
                ];
            }

            $errorBody = $response->json();
            $errorMessage = $errorBody['reason'] ?? 'Invalid API Key or connection failed';
            
            $device->updateStatus('error', $errorMessage);
            
            return [
                'success' => false,
                'message' => $errorMessage
            ];

        } catch (\Exception $e) {

            $device->updateStatus('error', $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function testAllDevices(): array
    {
        $devices = FonnteDevice::active()->get();
        $results = [];

        foreach ($devices as $device) {
            $results[$device->id] = $this->testDevice($device);
            
            sleep(1);
        }

        return $results;
    }

    public function sendPresensiNotification($presensi, string $type = 'checkin'): array
    {
        if (!$this->isEnabled()) {
            return [
                'success' => false,
                'message' => 'Fonnte service is not enabled',
                'skipped' => true
            ];
        }

        try {
            $presensi->load(['siswa', 'kelas.jurusan']);
            
            $siswa = $presensi->siswa;
            $kelas = $presensi->kelas;
            
            if (!$siswa) {
                
                return [
                    'success' => false,
                    'message' => 'Student not found',
                    'skipped' => true
                ];
            }
            
            if (empty($siswa->parent_phone)) {
                
                return [
                    'success' => false,
                    'message' => 'No parent phone number available',
                    'skipped' => true
                ];
            }

            $templateKey = $type === 'checkin' 
                ? 'fonnte_message_template_checkin' 
                : 'fonnte_message_template_checkout';
            
            $template = Setting::get($templateKey, $this->getDefaultTemplate($type));

            $parentName = $this->getParentName($siswa->name);
            $className = $kelas ? $kelas->nama_kelas : '-';

            $variables = [
                'parent_name' => $parentName,
                'student_name' => $siswa->name,
                'nis' => $siswa->nis ?? '-',
                'class_name' => $className,
                'date' => $presensi->tanggal_presensi->format('d F Y'),
            ];

            if ($type === 'checkin') {
                $variables['checkin_time'] = $presensi->waktu_checkin 
                    ? $presensi->waktu_checkin->format('H:i') 
                    : '-';
                $variables['status'] = '✅ MASUK';
            } else {
                $variables['checkin_time'] = $presensi->waktu_checkin 
                    ? $presensi->waktu_checkin->format('H:i') 
                    : '-';
                $variables['checkout_time'] = $presensi->waktu_checkout 
                    ? $presensi->waktu_checkout->format('H:i') 
                    : '-';
                $variables['status'] = '🏠 PULANG';
            }

            $message = $this->replaceVariables($template, $variables);

            return $this->sendMessage($siswa->parent_phone, $message);

        } catch (\Exception $e) {

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    protected function replaceVariables(string $template, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }
        
        return $template;
    }

    protected function getParentName(string $studentName): string
    {
        $firstName = explode(' ', $studentName)[0];
        return "Orang Tua " . $firstName;
    }

    protected function normalizePhoneNumber(string $phone): string
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

    protected function getDefaultTemplate(string $type = 'checkin'): string
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

    public function getDeviceStatistics(): array
    {
        return [
            'total_devices' => FonnteDevice::count(),
            'active_devices' => FonnteDevice::active()->count(),
            'available_devices' => FonnteDevice::available()->count(),
            'connected_devices' => FonnteDevice::where('status', 'connected')->count(),
            'total_sent' => FonnteDevice::sum('sent_count'),
        ];
    }
}
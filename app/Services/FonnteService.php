<?php

namespace App\Services;

use App\Models\FonnteDevice;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    protected $apiUrl = 'https://api.fonnte.com';

    /**
     * Check if Fonnte service is enabled globally
     */
    public function isEnabled(): bool
    {
        return Setting::get('fonnte_enabled', false) && $this->hasAvailableDevices();
    }

    /**
     * Check if there are available devices
     */
    public function hasAvailableDevices(): bool
    {
        return FonnteDevice::available()->exists();
    }

    /**
     * Get next available device using round-robin with priority
     */
    public function getNextDevice(): ?FonnteDevice
    {
        $device = FonnteDevice::available()
            ->byPriority()
            ->first();

        if (!$device) {
            Log::warning('No available Fonnte device found');
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
        // If no device specified, get next available (with class filter if provided)
        if (!$device) {
            $device = $this->getNextDevice($kelasId);
        }

                    if (!$device) {
            Log::warning('No available device to send message', [
                'kelas_id' => $kelasId
            ]);
            return [
                'success' => false,
                'message' => 'No available device. All devices are offline or in error state.'
            ];
        }

        // Double check: jika ada kelasId, pastikan device bisa kirim ke kelas ini
        if ($kelasId !== null && !$device->canSendToClass($kelasId)) {
            Log::warning('Device cannot send to this class', [
                'device_id' => $device->id,
                'device_name' => $device->name,
                'kelas_id' => $kelasId
            ]);
            
            // Try get another device
            $device = $this->getNextDevice($kelasId);
            
            if (!$device) {
                return [
                    'success' => false,
                    'message' => 'No device available for this class'
                ];
            }
        }

        try {
            // Add random delay between 2-5 seconds to avoid spam
            $delay = rand(2, 5);
            Log::info('Adding random delay before sending message', [
                'delay_seconds' => $delay,
                'device_id' => $device->id,
                'device_name' => $device->name
            ]);
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

            Log::info('Sending WhatsApp message', [
                'phone' => $phoneNumber,
                'message_length' => strlen($message),
                'device_id' => $device->id,
                'device_name' => $device->name,
                'sent_count' => $device->sent_count,
            ]);

            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => $device->api_key,
                ])
                ->post($this->apiUrl . '/send', $data);

            Log::info('Fonnte send message response', [
                'status' => $response->status(),
                'body' => $response->json(),
                'device_id' => $device->id
            ]);

            if ($response->successful()) {
                $result = $response->json();
                
                if (isset($result['status']) && $result['status'] === true) {
                    // Increment sent counter
                    $device->incrementSentCount();
                    
                    // Update device status to connected
                    $device->updateStatus('connected', 'Message sent successfully');
                    
                    return [
                        'success' => true,
                        'message' => 'Message sent successfully',
                        'device_id' => $device->id,
                        'device_name' => $device->name,
                        'data' => $result
                    ];
                }

                // If failed, try next device
                $device->updateStatus('error', $result['reason'] ?? 'Failed to send message');
                
                // Recursive call with next device
                $nextDevice = $this->getNextDevice();
                if ($nextDevice && $nextDevice->id !== $device->id) {
                    Log::info('Retrying with next device', [
                        'failed_device' => $device->name,
                        'next_device' => $nextDevice->name
                    ]);
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

            // Try next device
            $nextDevice = $this->getNextDevice();
            if ($nextDevice && $nextDevice->id !== $device->id) {
                Log::info('Retrying with next device after error', [
                    'failed_device' => $device->name,
                    'next_device' => $nextDevice->name,
                    'error' => $errorMessage
                ]);
                return $this->sendMessage($phoneNumber, $message, $nextDevice);
            }

            return [
                'success' => false,
                'message' => $errorMessage
            ];

        } catch (\Exception $e) {
            Log::error('Fonnte send message error', [
                'error' => $e->getMessage(),
                'phone' => $phoneNumber,
                'device_id' => $device->id ?? null
            ]);

            if ($device) {
                $device->updateStatus('error', $e->getMessage());
            }

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Test Fonnte API connection for specific device
     */
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

            Log::info('Testing Fonnte device connection', [
                'device_id' => $device->id,
                'device_name' => $device->name,
                'target_number' => $targetNumber
            ]);

            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => $device->api_key,
                ])
                ->post($this->apiUrl . '/validate', [
                    'target' => $targetNumber
                ]);

            Log::info('Fonnte device test response', [
                'device_id' => $device->id,
                'status' => $response->status(),
                'body' => $response->json()
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
            Log::error('Fonnte device test error', [
                'device_id' => $device->id,
                'error' => $e->getMessage()
            ]);

            $device->updateStatus('error', $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Test all active devices
     */
    public function testAllDevices(): array
    {
        $devices = FonnteDevice::active()->get();
        $results = [];

        foreach ($devices as $device) {
            $results[$device->id] = $this->testDevice($device);
            
            // Add small delay between tests
            sleep(1);
        }

        return $results;
    }

    /**
     * Send presensi notification to parent
     */
    public function sendPresensiNotification($presensi, string $type = 'checkin'): array
    {
        if (!$this->isEnabled()) {
            Log::info('Fonnte service is disabled, skipping notification');
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
                Log::warning('Student not found for presensi', [
                    'presensi_id' => $presensi->id,
                    'siswa_id' => $presensi->siswa_id
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Student not found',
                    'skipped' => true
                ];
            }
            
            if (empty($siswa->parent_phone)) {
                Log::warning('Student has no parent phone number', [
                    'student_id' => $siswa->id,
                    'student_name' => $siswa->name
                ]);
                
                return [
                    'success' => false,
                    'message' => 'No parent phone number available',
                    'skipped' => true
                ];
            }

            // Get template berdasarkan tipe
            $templateKey = $type === 'checkin' 
                ? 'fonnte_message_template_checkin' 
                : 'fonnte_message_template_checkout';
            
            $template = Setting::get($templateKey, $this->getDefaultTemplate($type));

            $parentName = $this->getParentName($siswa->name);
            $className = $kelas ? $kelas->nama_kelas : '-';

            // Build variables untuk template
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

            Log::info('Sending presensi notification', [
                'presensi_id' => $presensi->id,
                'type' => $type,
                'siswa_name' => $siswa->name,
                'parent_phone' => $siswa->parent_phone,
            ]);

            // Send message with auto device rotation
            return $this->sendMessage($siswa->parent_phone, $message);

        } catch (\Exception $e) {
            Log::error('Failed to send attendance notification', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'presensi_id' => $presensi->id ?? null,
                'type' => $type ?? null
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Replace variables in message template
     */
    protected function replaceVariables(string $template, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }
        
        return $template;
    }

    /**
     * Get parent name from student name
     */
    protected function getParentName(string $studentName): string
    {
        $firstName = explode(' ', $studentName)[0];
        return "Orang Tua " . $firstName;
    }

    /**
     * Normalize phone number to international format
     */
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

    /**
     * Get default message template
     */
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

    /**
     * Get statistics for all devices
     */
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
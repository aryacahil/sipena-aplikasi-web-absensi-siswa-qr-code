<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    protected $apiUrl = 'https://api.fonnte.com';
    protected $apiKey;
    protected $enabled;

    public function __construct()
    {
        $this->apiKey = Setting::get('fonnte_api_key');
        $this->enabled = Setting::get('fonnte_enabled', false);
    }

    /**
     * Check if Fonnte service is enabled and configured
     */
    public function isEnabled(): bool
    {
        return $this->enabled && !empty($this->apiKey);
    }

    /**
     * Send WhatsApp message via Fonnte
     */
    public function sendMessage(string $phoneNumber, string $message): array
    {
        if (!$this->isEnabled()) {
            Log::warning('Fonnte service is not enabled or API key is missing');
            return [
                'success' => false,
                'message' => 'Fonnte service is not enabled'
            ];
        }

        try {
            $phoneNumber = $this->normalizePhoneNumber($phoneNumber);

            $data = [
                'target' => $phoneNumber,
                'message' => $message,
                'countryCode' => '62',
            ];

            $deviceId = Setting::get('fonnte_device_id');
            if (!empty($deviceId)) {
                $data['device'] = $deviceId;
            }

            Log::info('Sending WhatsApp message', [
                'phone' => $phoneNumber,
                'message_length' => strlen($message),
            ]);

            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => $this->apiKey,
                ])
                ->post($this->apiUrl . '/send', $data);

            Log::info('Fonnte send message response', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            if ($response->successful()) {
                $result = $response->json();
                
                if (isset($result['status']) && $result['status'] === true) {
                    return [
                        'success' => true,
                        'message' => 'Message sent successfully',
                        'data' => $result
                    ];
                }

                return [
                    'success' => false,
                    'message' => $result['reason'] ?? 'Failed to send message'
                ];
            }

            $errorBody = $response->json();
            return [
                'success' => false,
                'message' => $errorBody['reason'] ?? 'HTTP Error: ' . $response->status()
            ];

        } catch (\Exception $e) {
            Log::error('Fonnte send message error', [
                'error' => $e->getMessage(),
                'phone' => $phoneNumber
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Test Fonnte API connection
     */
    public function testConnection(): array
    {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'message' => 'API Key is missing'
            ];
        }

        $senderNumber = Setting::get('fonnte_sender_number');
        if (empty($senderNumber)) {
            return [
                'success' => false,
                'message' => 'Sender number is missing'
            ];
        }

        try {
            $targetNumber = $this->normalizePhoneNumber($senderNumber);

            Log::info('Testing Fonnte API connection', [
                'target_number' => $targetNumber
            ]);

            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => $this->apiKey,
                ])
                ->post($this->apiUrl . '/validate', [
                    'target' => $targetNumber
                ]);

            Log::info('Fonnte test connection response', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            if ($response->successful()) {
                $result = $response->json();
                
                if (isset($result['status']) && $result['status'] === true) {
                    $deviceName = $result['device'] ?? $result['name'] ?? 'Unknown Device';
                    
                    return [
                        'success' => true,
                        'message' => 'Connection successful',
                        'device_name' => $deviceName,
                        'data' => $result
                    ];
                }

                if (isset($result['reason'])) {
                    return [
                        'success' => false,
                        'message' => $result['reason']
                    ];
                }

                if (isset($result['status']) && $result['status'] === false) {
                    return [
                        'success' => false,
                        'message' => $result['reason'] ?? 'Validation failed'
                    ];
                }

                return [
                    'success' => true,
                    'message' => 'API Key valid',
                    'device_name' => 'Connected',
                    'data' => $result
                ];
            }

            $errorBody = $response->json();
            return [
                'success' => false,
                'message' => $errorBody['reason'] ?? 'Invalid API Key or connection failed'
            ];

        } catch (\Exception $e) {
            Log::error('Fonnte test connection error', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Send presensi notification to parent
     * 
     * @param mixed $presensi Presensi model instance
     * @param string $type 'checkin' or 'checkout'
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
}
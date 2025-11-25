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
        // Check if service is enabled
        if (!$this->isEnabled()) {
            Log::warning('Fonnte service is not enabled or API key is missing');
            return [
                'success' => false,
                'message' => 'Fonnte service is not enabled'
            ];
        }

        try {
            // Normalize phone number
            $phoneNumber = $this->normalizePhoneNumber($phoneNumber);

            // Prepare request data
            $data = [
                'target' => $phoneNumber,
                'message' => $message,
                'countryCode' => '62', // Indonesia
            ];

            // Add device ID if configured
            $deviceId = Setting::get('fonnte_device_id');
            if (!empty($deviceId)) {
                $data['device'] = $deviceId;
            }

            Log::info('Sending WhatsApp message', [
                'phone' => $phoneNumber,
                'message_length' => strlen($message),
                'has_device_id' => !empty($deviceId)
            ]);

            // Send request to Fonnte API
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => $this->apiKey,
                ])
                ->post($this->apiUrl . '/send', $data);

            // Log the response for debugging
            Log::info('Fonnte send message response', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            // Check response
            if ($response->successful()) {
                $result = $response->json();
                
                // ✅ Fonnte returns status:true when successful
                if (isset($result['status']) && $result['status'] === true) {
                    return [
                        'success' => true,
                        'message' => 'Message sent successfully',
                        'data' => $result
                    ];
                }

                // ❌ API returned error
                return [
                    'success' => false,
                    'message' => $result['reason'] ?? 'Failed to send message'
                ];
            }

            // ❌ HTTP Error
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
     * FIXED VERSION - Gunakan sender number sebagai target
     */
    public function testConnection(): array
    {
        // Check if service is configured
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'message' => 'API Key is missing'
            ];
        }

        // Get sender number for test
        $senderNumber = Setting::get('fonnte_sender_number');
        if (empty($senderNumber)) {
            return [
                'success' => false,
                'message' => 'Sender number is missing'
            ];
        }

        try {
            // Normalize phone number
            $targetNumber = $this->normalizePhoneNumber($senderNumber);

            Log::info('Testing Fonnte API connection', [
                'target_number' => $targetNumber
            ]);

            // ✅ Use /validate endpoint dengan target parameter
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
                
                // ✅ Check status true
                if (isset($result['status']) && $result['status'] === true) {
                    $deviceName = $result['device'] ?? $result['name'] ?? 'Unknown Device';
                    
                    return [
                        'success' => true,
                        'message' => 'Connection successful',
                        'device_name' => $deviceName,
                        'data' => $result
                    ];
                }

                // ❌ Check if there's an error message
                if (isset($result['reason'])) {
                    return [
                        'success' => false,
                        'message' => $result['reason']
                    ];
                }

                // ❌ Status false
                if (isset($result['status']) && $result['status'] === false) {
                    return [
                        'success' => false,
                        'message' => $result['reason'] ?? 'Validation failed'
                    ];
                }

                // ✅ No explicit status but successful response
                return [
                    'success' => true,
                    'message' => 'API Key valid',
                    'device_name' => 'Connected',
                    'data' => $result
                ];
            }

            // ❌ HTTP Error
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
     */
    public function sendPresensiNotification($presensi): array
    {
        // Check if service is enabled
        if (!$this->isEnabled()) {
            Log::info('Fonnte service is disabled, skipping notification');
            return [
                'success' => false,
                'message' => 'Fonnte service is not enabled',
                'skipped' => true
            ];
        }

        try {
            // Load relationships
            $presensi->load(['siswa', 'kelas.jurusan']);
            
            $siswa = $presensi->siswa;
            $kelas = $presensi->kelas;
            
            // Validate student exists
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
            
            // Validate parent phone number exists
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

            // Get message template
            $template = Setting::get('fonnte_message_template', $this->getDefaultTemplate());

            // Get parent name from student name
            $parentName = $this->getParentName($siswa->name);
            
            // Get class name
            $className = $kelas ? $kelas->nama_kelas : '-';

            // Replace variables in template
            $message = $this->replaceVariables($template, [
                'parent_name' => $parentName,
                'student_name' => $siswa->name,
                'class_name' => $className,
                'status' => $this->getStatusEmoji($presensi->status) . ' ' . strtoupper($presensi->status),
                'time' => $presensi->waktu_absen ? $presensi->waktu_absen->format('H:i:s') : $presensi->created_at->format('H:i:s'),
                'date' => $presensi->tanggal_presensi->format('d F Y'),
            ]);

            Log::info('Sending presensi notification', [
                'presensi_id' => $presensi->id,
                'siswa_name' => $siswa->name,
                'parent_phone' => $siswa->parent_phone,
                'class_name' => $className
            ]);

            // Send message
            return $this->sendMessage($siswa->parent_phone, $message);

        } catch (\Exception $e) {
            Log::error('Failed to send attendance notification', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'presensi_id' => $presensi->id ?? null
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
     * Get emoji for attendance status
     */
    protected function getStatusEmoji(string $status): string
    {
        $emojis = [
            'hadir' => '✅',
            'izin' => '📝',
            'sakit' => '🏥',
            'alpha' => '❌',
        ];

        return $emojis[strtolower($status)] ?? '📌';
    }

    /**
     * Get parent name from student name
     */
    protected function getParentName(string $studentName): string
    {
        // Simple logic: use "Orang Tua [First Name]"
        $firstName = explode(' ', $studentName)[0];
        return "Orang Tua " . $firstName;
    }

    /**
     * Normalize phone number to international format
     */
    protected function normalizePhoneNumber(string $phone): string
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
    protected function getDefaultTemplate(): string
    {
        return "Assalamualaikum Bapak/Ibu\n\n" .
               "Kami informasikan bahwa putra/putri Anda:\n\n" .
               "*Nama:* {student_name}\n" .
               "*Kelas:* {class_name}\n" .
               "*Status:* {status}\n" .
               "*Waktu:* {time}\n" .
               "*Tanggal:* {date}\n\n" .
               "Terima kasih atas perhatiannya.\n\n" .
               "_Pesan otomatis dari Sistem Presensi Sekolah_";
    }
}
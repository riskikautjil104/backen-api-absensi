<?php

// awal batas suci yang kamu ubah

namespace App\Services;

use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Firebase Cloud Messaging (FCM) HTTP v1 API Service for Absensi Sekolah
 * 
 * Menggunakan Service Account (firebase-credentials.json) untuk autentikasi,
 * lalu mengirim push notification ke HP Guru & Siswa melalui FCM HTTP v1 API.
 * 
 * File credentials diletakkan di: storage/app/firebase-credentials.json
 */
class FcmService
{
    protected string $projectId;
    protected string $credentialsPath;

    public function __construct()
    {
        $this->credentialsPath = storage_path('app/firebase-credentials.json');

        $this->projectId = 'moro5smart-cbt';
        if (file_exists($this->credentialsPath)) {
            $json = json_decode(file_get_contents($this->credentialsPath), true);
            $this->projectId = $json['project_id'] ?? $this->projectId;
        }
    }

    /**
     * Ambil OAuth2 Access Token dari Service Account Key
     */
    protected function getAccessToken(): ?string
    {
        try {
            if (!file_exists($this->credentialsPath)) {
                Log::error('[FCM Absensi] Service account credentials file tidak ditemukan: ' . $this->credentialsPath);
                return null;
            }

            $client = new GoogleClient();
            $client->setAuthConfig($this->credentialsPath);
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            $client->fetchAccessTokenWithAssertion();
            $token = $client->getAccessToken();

            return $token['access_token'] ?? null;
        } catch (\Exception $e) {
            Log::error('[FCM Absensi] Gagal mendapatkan Access Token: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Kirim notifikasi ke satu device (token tunggal)
     */
    public function sendToDevice(string $fcmToken, string $title, string $body, array $data = []): array
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return [
                'success' => false,
                'error'   => 'Gagal mendapatkan OAuth2 Access Token. Pastikan file storage/app/firebase-credentials.json ada dan valid.',
            ];
        }

        try {
            $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type'  => 'application/json',
            ])->post($url, [
                'message' => [
                    'token' => $fcmToken,
                    'notification' => [
                        'title' => $title,
                        'body'  => $body,
                    ],
                    'android' => [
                        'priority' => 'HIGH',
                        'notification' => [
                            'channel_id'            => 'moro5smart_high_importance_channel',
                            'sound'                 => 'default',
                            'notification_priority' => 'PRIORITY_HIGH',
                        ],
                    ],
                    'apns' => [
                        'payload' => [
                            'aps' => [
                                'alert' => [
                                    'title' => $title,
                                    'body'  => $body,
                                ],
                                'sound' => 'default',
                                'badge' => 1,
                            ],
                        ],
                    ],
                    'data' => collect($data)->map(fn($v) => (string) $v)->toArray(),
                ],
            ]);

            if ($response->successful()) {
                return ['success' => true, 'error' => null];
            }

            $errorDetail = "HTTP {$response->status()}: " . $response->body();
            Log::warning('[FCM Absensi] Gagal kirim ke device: ' . $errorDetail);

            return [
                'success' => false,
                'error'   => $errorDetail,
            ];

        } catch (\Exception $e) {
            Log::error('[FCM Absensi] Exception saat kirim notification: ' . $e->getMessage());
            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Kirim notifikasi ke banyak device sekaligus (bulk)
     */
    public function sendToMultiple(array $fcmTokens, string $title, string $body, array $data = []): array
    {
        $success = 0;
        $failed  = 0;
        $errors  = [];

        foreach ($fcmTokens as $token) {
            if (empty($token)) continue;
            $res = $this->sendToDevice($token, $title, $body, $data);
            if ($res['success']) {
                $success++;
            } else {
                $failed++;
                if (!empty($res['error'])) {
                    $errors[] = $res['error'];
                }
            }
        }

        return ['success' => $success, 'failed' => $failed, 'errors' => $errors];
    }

    /**
     * Kirim notifikasi ke Topic FCM (misal: 'guru', 'siswa', 'pengumuman_sekolah')
     */
    public function sendToTopic(string $topic, string $title, string $body, array $data = []): bool
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return false;
        }

        try {
            $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type'  => 'application/json',
            ])->post($url, [
                'message' => [
                    'topic' => $topic,
                    'notification' => [
                        'title' => $title,
                        'body'  => $body,
                    ],
                    'android' => [
                        'priority' => 'HIGH',
                        'notification' => [
                            'channel_id'            => 'moro5smart_high_importance_channel',
                            'sound'                 => 'default',
                            'notification_priority' => 'PRIORITY_HIGH',
                        ],
                    ],
                    'data' => collect($data)->map(fn($v) => (string) $v)->toArray(),
                ],
            ]);

            if ($response->successful()) {
                Log::info("[FCM Absensi] Topic '{$topic}' berhasil dikirim.");
                return true;
            }

            Log::warning("[FCM Absensi] Gagal kirim ke topic '{$topic}'.", [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('[FCM Absensi] Exception sendToTopic: ' . $e->getMessage());
            return false;
        }
    }
}

// akhir batas suci yang kamu ubah

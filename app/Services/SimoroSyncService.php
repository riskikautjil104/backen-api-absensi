<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\KartuSiswa;

class SimoroSyncService
{
    protected $baseUrl;
    protected $token;

    public function __construct()
    {
        $this->baseUrl = config('services.simoro.url', 'https://simoro.sma-n5-morotai.id/api');
        $this->token = config('services.simoro.token');
    }

    protected function getClient()
    {
        @set_time_limit(240); // Perpanjang batas waktu eksekusi PHP lokal
        $client = Http::timeout(120); // Naikkan batas waktu cURL ke 120 detik
        if ($this->token) {
            $client = $client->withToken($this->token);
        }
        return $client;
    }

    protected function checkResponse($response, $url)
    {
        if ($response->status() === 401) {
            throw new \Exception("Akses Ditolak (401 Unauthorized) untuk {$url}. Pastikan SIMORO_API_TOKEN di file .env sudah diatur dan sesuai.");
        }
        if ($response->status() === 404) {
            throw new \Exception("Endpoint tidak ditemukan (404 Not Found) untuk {$url}. Pastikan API URL sudah benar.");
        }
        if (!$response->successful()) {
            throw new \Exception("Gagal menghubungi server SIMORO (Status: " . $response->status() . ") untuk {$url}.");
        }
    }

    public function syncKelas()
    {
        $url = $this->baseUrl . '/kelas';
        try {
            $response = $this->getClient()->get($url);
            if ($response->status() === 404 || !$response->successful()) {
                $urlPublic = $this->baseUrl . '/public/kelas';
                $responseAlt = $this->getClient()->get($urlPublic);
                if ($responseAlt->successful()) {
                    $response = $responseAlt;
                }
            }

            $this->checkResponse($response, $url);

            $classes = $response->json('data') ?? [];
            foreach ($classes as $c) {
                Kelas::updateOrCreate(
                    ['id' => $c['id']],
                    [
                        'nama_kelas' => $c['name'] ?? $c['nama_kelas'],
                        'tahun_ajaran' => $c['tahun_ajaran'] ?? '2025/2026',
                    ]
                );
            }
            return count($classes);
        } catch (\Exception $e) {
            Log::error('Kelas Sync failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function syncGuru()
    {
        $url = $this->baseUrl . '/guru';
        try {
            $response = $this->getClient()->get($url);
            if ($response->status() === 404 || !$response->successful()) {
                $urlPublic = $this->baseUrl . '/public/guru';
                $responseAlt = $this->getClient()->get($urlPublic);
                if ($responseAlt->successful()) {
                    $response = $responseAlt;
                }
            }

            $this->checkResponse($response, $url);

            $teachers = $response->json('data') ?? [];
            foreach ($teachers as $t) {
                $nip = $t['nip'] ?? null;
                $userQuery = User::where('email', $t['email']);
                if ($nip) {
                    $userQuery = $userQuery->orWhere('nip', $nip);
                }
                $localUser = $userQuery->first();

                if ($localUser) {
                    $localUser->update([
                        'name' => $t['name'],
                        'email' => $t['email'],
                        'nip' => $nip,
                        'role' => 'guru',
                    ]);
                } else {
                    User::create([
                        'name' => $t['name'],
                        'email' => $t['email'],
                        'nip' => $nip,
                        'role' => 'guru',
                        'password' => Hash::make(Str::random(16)),
                    ]);
                }
            }
            return count($teachers);
        } catch (\Exception $e) {
            Log::error('Guru Sync failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function syncMapel()
    {
        $url = $this->baseUrl . '/mapel';
        try {
            $response = $this->getClient()->get($url);
            if ($response->status() === 404 || !$response->successful()) {
                $urlPublic = $this->baseUrl . '/public/mapel';
                $responseAlt = $this->getClient()->get($urlPublic);
                if ($responseAlt->successful()) {
                    $response = $responseAlt;
                }
            }

            $this->checkResponse($response, $url);

            $mapels = $response->json('data') ?? [];
            foreach ($mapels as $m) {
                // 1. Create/update MataPelajaran record with duplicate code prevention
                $code = $m['code'] ?? $m['kode_mapel'] ?? 'MP-' . $m['id'];
                $existingMapel = MataPelajaran::where('kode_mapel', $code)
                    ->where('id', '!=', $m['id'])
                    ->first();

                if ($existingMapel) {
                    $code = $code . '-' . $m['id'];
                }

                $mapel = MataPelajaran::updateOrCreate(
                    ['id' => $m['id']],
                    [
                        'nama_mapel' => $m['name'] ?? $m['nama_mapel'],
                        'kode_mapel' => $code,
                    ]
                );

                // 2. Find Teacher
                $guru = null;
                if (!empty($m['teacher_email'])) {
                    $guru = User::where('email', $m['teacher_email'])->first();
                }

                // 3. Link classes in guru_mapel and generate dummy schedules
                if (isset($m['classes']) && is_array($m['classes'])) {
                    foreach ($m['classes'] as $c) {
                        $kelas = Kelas::where('id', $c['id'])
                            ->orWhere('nama_kelas', $c['name'])
                            ->first();

                        if ($kelas && $guru) {
                            // Link in guru_mapel
                            \DB::table('guru_mapel')->updateOrInsert(
                                [
                                    'guru_id' => $guru->id,
                                    'mapel_id' => $mapel->id,
                                    'kelas_id' => $kelas->id,
                                ]
                            );
                        }
                    }
                }
            }
            return count($mapels);
        } catch (\Exception $e) {
            Log::error('Mapel Sync failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function syncSiswa()
    {
        $page = 1;
        $totalSynced = 0;
        $hasMore = true;

        while ($hasMore) {
            $url = $this->baseUrl . '/siswa?paginate=true&per_page=50&page=' . $page;
            try {
                $response = $this->getClient()->get($url);
                if ($response->status() === 404 || !$response->successful()) {
                    $urlPublic = $this->baseUrl . '/students?paginate=true&per_page=50&page=' . $page;
                    $responseAlt = $this->getClient()->get($urlPublic);
                    if ($responseAlt->successful()) {
                        $response = $responseAlt;
                    }
                }

                $this->checkResponse($response, $url);

                $apiResponse = $response->json();
                
                $students = [];
                if (isset($apiResponse['data']['data'])) {
                    $students = $apiResponse['data']['data'];
                    $nextPageUrl = $apiResponse['data']['next_page_url'] ?? null;
                    $hasMore = !empty($nextPageUrl);
                } else {
                    $students = $apiResponse['data'] ?? [];
                    $hasMore = false;
                }

                if (empty($students)) {
                    $hasMore = false;
                    break;
                }

                foreach ($students as $s) {
                    $classId = null;
                    if (isset($s['class']['name'])) {
                        $kelas = Kelas::firstOrCreate(
                            ['nama_kelas' => $s['class']['name']],
                            ['tahun_ajaran' => $s['class']['tahun_ajaran'] ?? '2025/2026']
                        );
                        $classId = $kelas->id;
                    } elseif (isset($s['class_name'])) {
                        $kelas = Kelas::firstOrCreate(
                            ['nama_kelas' => $s['class_name']],
                            ['tahun_ajaran' => $s['angkatan'] ?? '2025/2026']
                        );
                        $classId = $kelas->id;
                    } elseif (isset($s['class_id'])) {
                        $classId = $s['class_id'];
                    }

                    $nis = $s['nis'] ?? null;
                    $userQuery = User::where('email', $s['email']);
                    if ($nis) {
                        $userQuery = $userQuery->orWhere('nis', $nis);
                    }
                    $user = $userQuery->first();

                    if ($user) {
                        $user->update([
                            'name' => $s['name'],
                            'email' => $s['email'],
                            'nis' => $nis,
                            'role' => 'siswa',
                        ]);
                    } else {
                        $user = User::create([
                            'name' => $s['name'],
                            'email' => $s['email'],
                            'nis' => $nis,
                            'role' => 'siswa',
                            'password' => Hash::make(Str::random(16)),
                        ]);
                    }

                    $siswa = Siswa::updateOrCreate(
                        ['user_id' => $user->id],
                        [
                            'kelas_id' => $classId,
                            'nomor_hp' => $s['phone'] ?? null,
                        ]
                    );

                    if (!$siswa->kartu) {
                        KartuSiswa::create([
                            'siswa_id' => $siswa->id,
                            'token' => 'TK-' . strtoupper(Str::random(8)),
                            'status' => 'aktif',
                        ]);
                    }
                }

                $totalSynced += count($students);
                $page++;

            } catch (\Exception $e) {
                Log::error("Siswa Sync failed on page {$page}: " . $e->getMessage());
                throw $e;
            }
        }

        return $totalSynced;
    }
}

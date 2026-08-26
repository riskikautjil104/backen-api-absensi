<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $loginValue = $this->email;
        $password = $this->password;
        
        $authenticated = false;
        $localUser = null;

        // Try SIMORO API SSO
        try {
            $apiUrl = config('services.simoro.url', 'https://simoro.sma-n5-morotai.id/api');
            $response = null;

            if (filter_var($loginValue, FILTER_VALIDATE_EMAIL)) {
                // It is an email, try general login API (admin/teacher/student)
                $response = \Illuminate\Support\Facades\Http::timeout(5)->post($apiUrl . '/login', [
                    'email' => $loginValue,
                    'password' => $password,
                ]);

                if ($response->successful() && $response->json('user')) {
                    $apiUser = $response->json('user');
                    $role = $apiUser['role'] ?? 'student';

                    // Normalize role string
                    if ($role === 'teacher') $role = 'guru';
                    elseif ($role === 'student') $role = 'siswa';

                    // Sync the User
                    $nip = $apiUser['nip'] ?? null;
                    $nis = $apiUser['nis'] ?? null;
                    
                    $userQuery = \App\Models\User::where('email', $apiUser['email']);
                    if ($nip) $userQuery = $userQuery->orWhere('nip', $nip);
                    if ($nis) $userQuery = $userQuery->orWhere('nis', $nis);
                    $localUser = $userQuery->first();

                    if (!$localUser) {
                        $localUser = \App\Models\User::create([
                            'name' => $apiUser['name'],
                            'email' => $apiUser['email'],
                            'nip' => $nip,
                            'nis' => $nis,
                            'role' => $role,
                            'password' => \Illuminate\Support\Facades\Hash::make($password),
                        ]);
                    } else {
                        $localUser->update([
                            'name' => $apiUser['name'],
                            'email' => $apiUser['email'],
                            'nip' => $nip,
                            'nis' => $nis,
                            'role' => $role,
                            'password' => \Illuminate\Support\Facades\Hash::make($password),
                        ]);
                    }

                    // If they are a student, sync Kelas and detail
                    if ($role === 'siswa') {
                        $kelasName = $apiUser['class_name'] ?? 'Umum';
                        $kelas = \App\Models\Kelas::firstOrCreate(
                            ['nama_kelas' => $kelasName],
                            ['tahun_ajaran' => $apiUser['angkatan'] ?? '2025/2026']
                        );

                        $siswa = \App\Models\Siswa::updateOrCreate(
                            ['user_id' => $localUser->id],
                            [
                                'kelas_id' => $kelas->id,
                                'nomor_hp' => $apiUser['phone'] ?? null,
                            ]
                        );

                        if (!$siswa->kartu) {
                            \App\Models\KartuSiswa::create([
                                'siswa_id' => $siswa->id,
                                'token' => 'TK-' . strtoupper(\Illuminate\Support\Str::random(8)),
                                'status' => 'aktif',
                            ]);
                        }
                    }

                    Auth::login($localUser, $this->boolean('remember'));
                    $authenticated = true;
                }
            } else {
                // It is not an email (likely NIS/NIP), try siswa login API
                $response = \Illuminate\Support\Facades\Http::timeout(5)->post($apiUrl . '/siswa/login', [
                    'login' => $loginValue,
                    'password' => $password,
                ]);

                if ($response->successful() && $response->json('success')) {
                    $apiData = $response->json('data');
                    $apiUser = $apiData['user'];

                    $kelasName = $apiUser['class_name'] ?? 'Umum';
                    $kelas = \App\Models\Kelas::firstOrCreate(
                        ['nama_kelas' => $kelasName],
                        ['tahun_ajaran' => $apiUser['angkatan'] ?? '2025/2026']
                    );

                    $localUser = \App\Models\User::where('email', $apiUser['email'])
                        ->orWhere('nis', $apiUser['nis'])
                        ->first();

                    if (!$localUser) {
                        $localUser = \App\Models\User::create([
                            'name' => $apiUser['name'],
                            'email' => $apiUser['email'],
                            'nis' => $apiUser['nis'],
                            'role' => 'siswa',
                            'password' => \Illuminate\Support\Facades\Hash::make($password),
                        ]);
                    } else {
                        $localUser->update([
                            'name' => $apiUser['name'],
                            'email' => $apiUser['email'],
                            'nis' => $apiUser['nis'],
                            'password' => \Illuminate\Support\Facades\Hash::make($password),
                        ]);
                    }

                    $siswa = \App\Models\Siswa::updateOrCreate(
                        ['user_id' => $localUser->id],
                        [
                            'kelas_id' => $kelas->id,
                            'nomor_hp' => $apiUser['phone'] ?? null,
                        ]
                    );

                    if (!$siswa->kartu) {
                        \App\Models\KartuSiswa::create([
                            'siswa_id' => $siswa->id,
                            'token' => 'TK-' . strtoupper(\Illuminate\Support\Str::random(8)),
                            'status' => 'aktif',
                        ]);
                    }

                    Auth::login($localUser, $this->boolean('remember'));
                    $authenticated = true;
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('SIMORO API Login SSO failed: ' . $e->getMessage());
        }

        // Local Auth Fallback (for local Siswa, Admin, and Guru)
        if (!$authenticated) {
            $field = filter_var($loginValue, FILTER_VALIDATE_EMAIL) ? 'email' : 'nis';
            
            if ($field === 'nis') {
                $userCheck = \App\Models\User::where('nis', $loginValue)->orWhere('nip', $loginValue)->first();
                if ($userCheck) {
                    $field = $userCheck->nip ? 'nip' : 'nis';
                }
            }

            if (! Auth::attempt([$field => $loginValue, 'password' => $password], $this->boolean('remember'))) {
                RateLimiter::hit($this->throttleKey());

                throw ValidationException::withMessages([
                    'email' => trans('auth.failed'),
                ]);
            }
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}

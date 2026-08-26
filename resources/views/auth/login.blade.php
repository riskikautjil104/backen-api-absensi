<x-guest-layout>
    <div class="min-h-screen flex flex-col items-center justify-center p-4">
        <div class="w-full max-w-[400px] space-y-8">
            <div class="text-center">
                <h2 class="text-[32px] font-semibold tracking-tightest">Masuk ke SMA 5.</h2>
                <p class="text-apple-gray-muted-48 mt-2">Gunakan akun Anda untuk mengakses sistem.</p>
            </div>

            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <!-- Email Address -->
                <div class="space-y-1">
                    <label for="email" class="text-[12px] font-semibold text-apple-gray-muted-48 ml-4">EMAIL</label>
                    <input id="email" class="block w-full px-4 py-3 bg-apple-parchment border-none rounded-apple-lg focus:ring-2 focus:ring-apple-blue-focus transition-shadow" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div class="space-y-1">
                    <label for="password" class="text-[12px] font-semibold text-apple-gray-muted-48 ml-4">PASSWORD</label>
                    <input id="password" class="block w-full px-4 py-3 bg-apple-parchment border-none rounded-apple-lg focus:ring-2 focus:ring-apple-blue-focus transition-shadow" type="password" name="password" required autocomplete="current-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Remember Me -->
                <div class="flex items-center px-4">
                    <input id="remember_me" type="checkbox" class="rounded border-apple-gray-muted text-apple-blue shadow-sm focus:ring-apple-blue" name="remember">
                    <span class="ml-2 text-[14px] text-apple-gray-muted-48">Ingat saya</span>
                </div>

                <div class="pt-4">
                    <button type="submit" class="w-full apple-button-primary !py-4 !text-[17px]">
                        Masuk
                    </button>
                </div>

                @if (Route::has('password.request'))
                    <div class="text-center">
                        <a class="text-[14px] text-apple-blue hover:underline" href="{{ route('password.request') }}">
                            Lupa password?
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>
</x-guest-layout>

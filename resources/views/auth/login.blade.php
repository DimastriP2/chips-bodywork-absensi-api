```php
<x-guest-layout>

    <div class="w-full max-w-md">

        <div class="bg-white rounded-3xl shadow-xl p-8">

            <div class="text-center mb-8">

                <img src="{{ asset('images/logo-chips.png') }}"
                     alt="Logo"
                     class="h-20 mx-auto mb-4">

                <h1 class="text-3xl font-bold text-gray-900">
                    Chips Bodywork
                </h1>

                <p class="text-gray-500 mt-2">
                    Sistem Rekap Absensi Karyawan
                </p>

            </div>

            <x-auth-session-status
                class="mb-4"
                :status="session('status')" />

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div>
                    <x-input-label
                        for="email"
                        :value="__('Email')" />

                    <x-text-input
                        id="email"
                        class="block mt-1 w-full rounded-xl"
                        type="email"
                        name="email"
                        :value="old('email')"
                        required
                        autofocus />

                    <x-input-error
                        :messages="$errors->get('email')"
                        class="mt-2" />
                </div>

                <div class="mt-5">

                    <x-input-label
                        for="password"
                        :value="__('Password')" />

                    <x-text-input
                        id="password"
                        class="block mt-1 w-full rounded-xl"
                        type="password"
                        name="password"
                        required />

                    <x-input-error
                        :messages="$errors->get('password')"
                        class="mt-2" />

                </div>

                <div class="flex items-center justify-between mt-5">

                    <label class="inline-flex items-center">
                        <input
                            type="checkbox"
                            name="remember"
                            class="rounded border-gray-300">

                        <span class="ml-2 text-sm text-gray-600">
                            Remember Me
                        </span>
                    </label>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}"
                           class="text-sm text-red-600 hover:text-red-700">
                            Lupa Password?
                        </a>
                    @endif

                </div>

                <button
                    type="submit"
                    class="w-full mt-6 bg-red-600 hover:bg-red-700 text-white py-3 rounded-xl font-semibold transition">

                    Login

                </button>

            </form>

        </div>

        <div class="text-center mt-6 text-sm text-gray-500">
            © {{ date('Y') }} Chips Motor Bodywork
        </div>

    </div>

</x-guest-layout>
```

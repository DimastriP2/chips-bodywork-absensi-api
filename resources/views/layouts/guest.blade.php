<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Chips Bodywork Admin</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans text-gray-900 antialiased">

    <div class="min-h-screen bg-gray-100 flex items-center justify-center px-4 py-10">

        <div class="w-full max-w-5xl bg-white rounded-3xl shadow-2xl overflow-hidden grid grid-cols-1 lg:grid-cols-2">

            <div class="hidden lg:flex flex-col justify-between bg-gray-950 text-white p-10 relative overflow-hidden">

                <div class="absolute -right-20 -top-20 w-64 h-64 bg-red-600 rounded-full opacity-30"></div>
                <div class="absolute -left-20 -bottom-20 w-64 h-64 bg-red-600 rounded-full opacity-20"></div>

                <div class="relative z-10">
                    <img src="{{ asset('images/logo-chips.png') }}"
                         alt="Chips Bodywork"
                         class="h-20 mb-8 bg-white rounded-xl p-3">

                    <h1 class="text-4xl font-extrabold leading-tight">
                        Sistem Rekap Absensi<br>
                        Chips Bodywork
                    </h1>

                    <p class="mt-5 text-gray-300 leading-relaxed">
                        Website admin digunakan untuk mengelola data karyawan,
                        lokasi kantor, dan rekap absensi yang dikirim melalui aplikasi mobile.
                    </p>
                </div>

                <div class="relative z-10 grid grid-cols-3 gap-4 mt-10">
                    <div class="bg-white/10 rounded-2xl p-4">
                        <div class="text-2xl font-bold text-red-400">GPS</div>
                        <div class="text-sm text-gray-300">Validasi Lokasi</div>
                    </div>

                    <div class="bg-white/10 rounded-2xl p-4">
                        <div class="text-2xl font-bold text-red-400">100m</div>
                        <div class="text-sm text-gray-300">Radius Absen</div>
                    </div>

                    <div class="bg-white/10 rounded-2xl p-4">
                        <div class="text-2xl font-bold text-red-400">Admin</div>
                        <div class="text-sm text-gray-300">Rekap Data</div>
                    </div>
                </div>

            </div>

            <div class="flex items-center justify-center p-8 sm:p-12 bg-white">

                <div class="w-full max-w-md">
                    {{ $slot }}
                </div>

            </div>

        </div>

    </div>

</body>

</html>
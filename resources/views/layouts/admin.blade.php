<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Chips Bodywork Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background: #f4f6f9;
            font-family: 'Segoe UI', sans-serif;
        }

        .sidebar {
            width: 260px;
            min-height: 100vh;
            background: #111827;
            position: fixed;
            left: 0;
            top: 0;
            color: white;
        }

        .sidebar-logo {
            background: white;
            padding: 20px;
            text-align: center;
            border-bottom: 4px solid #dc2626;
        }

        .sidebar-logo img {
            max-width: 170px;
        }

        .sidebar a {
            color: #d1d5db;
            text-decoration: none;
            padding: 14px 24px;
            display: block;
            font-weight: 500;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #dc2626;
            color: white;
        }

        .content {
            margin-left: 260px;
            padding: 28px;
        }

        .topbar {
            background: white;
            border-radius: 16px;
            padding: 18px 24px;
            margin-bottom: 24px;
            box-shadow: 0 8px 25px rgba(0,0,0,.06);
        }

        .card-custom {
            border: none;
            border-radius: 18px;
            box-shadow: 0 8px 25px rgba(0,0,0,.06);
        }

        .btn-red {
            background: #dc2626;
            color: white;
            border-radius: 10px;
        }

        .btn-red:hover {
            background: #b91c1c;
            color: white;
        }

        .sidebar { overflow-y: auto; height: 100vh; }
        a:focus-visible, button:focus-visible { outline: 3px solid #f59e0b; outline-offset: 3px; }
        @media (max-width: 991.98px) {
            .sidebar { position: static; width: 100%; min-height: 0; height: auto; }
            .sidebar-logo { padding: 12px; }
            .sidebar-logo img { max-width: 120px; }
            .sidebar a { display: inline-block; padding: 12px 16px; }
            .sidebar form { margin-top: 8px !important; padding-bottom: 16px; }
            .content { margin-left: 0; padding: 16px; }
            .topbar { padding: 16px; gap: 12px; flex-wrap: wrap; }
        }
    </style>
</head>
<body>

<nav class="sidebar" aria-label="Navigasi admin">
    <div class="sidebar-logo">
        <img src="{{ asset('images/logo-chips.png') }}" alt="Chips Bodywork">
    </div>

    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif>
        <i class="bi bi-speedometer2 me-2"></i> Dashboard
    </a>

    <a href="{{ route('employees.index') }}" class="{{ request()->routeIs('employees.*') ? 'active' : '' }}" @if(request()->routeIs('employees.*')) aria-current="page" @endif>
        <i class="bi bi-people me-2"></i> Data Karyawan
    </a>

    <a href="{{ route('attendances.index') }}" class="{{ request()->routeIs('attendances.*') ? 'active' : '' }}" @if(request()->routeIs('attendances.*')) aria-current="page" @endif>
        <i class="bi bi-calendar-check me-2"></i> Rekap Absensi
    </a>

    <a href="{{ route('office.index') }}" class="{{ request()->routeIs('office.*') ? 'active' : '' }}" @if(request()->routeIs('office.*')) aria-current="page" @endif>
        <i class="bi bi-geo-alt me-2"></i> Lokasi Kantor
    </a>

    <form method="POST" action="{{ route('logout') }}" class="mt-4 px-3">
        @csrf
        <button class="btn btn-outline-light w-100">
            <i class="bi bi-box-arrow-right me-2"></i> Logout
        </button>
    </form>
</nav>

<main class="content">
    <div class="topbar d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 fw-bold">Chips Bodywork</h4>
            <small class="text-muted">Sistem Rekap Absensi Karyawan</small>
        </div>

        <div class="text-end">
            <strong>{{ Auth::user()->name }}</strong><br>
            <small class="text-muted">{{ Auth::user()->role }}</small>
        </div>
    </div>

    @yield('content')
</main>

</body>
</html>
@extends('layouts.admin')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <p class="text-muted small mb-1">{{ $todayLabel }} · {{ config('app.timezone') }}</p>
        <h1 class="h3 fw-bold mb-1">Ringkasan kehadiran</h1>
        <p class="text-muted mb-0">Pantau absensi karyawan dan kelengkapan jam pulang hari ini.</p>
    </div>
    <a href="{{ route('attendances.index', ['date' => now()->toDateString()]) }}" class="btn btn-red">
        <i class="bi bi-calendar-check me-2" aria-hidden="true"></i>Lihat rekap hari ini
    </a>
</div>

@if(!$office)
    <div class="alert alert-warning" role="alert">
        Lokasi kantor belum diatur. <a href="{{ route('office.index') }}" class="alert-link">Atur lokasi</a>
        agar karyawan dapat melakukan absensi.
    </div>
@endif

<div class="row g-3 mb-4">
    @foreach([
        ['label' => 'Total karyawan', 'value' => $totalEmployees, 'note' => 'Akun dengan profil karyawan', 'icon' => 'people'],
        ['label' => 'Sudah check in', 'value' => $todayAttendances, 'note' => $attendanceRate.'% dari total karyawan', 'icon' => 'calendar-check'],
        ['label' => 'Belum check in', 'value' => $pendingEmployees, 'note' => 'Belum ada catatan masuk hari ini', 'icon' => 'clock'],
        ['label' => 'Sudah check out', 'value' => $completedAttendances, 'note' => $onsiteEmployees.' catatan belum check out', 'icon' => 'box-arrow-right'],
    ] as $metric)
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-custom p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">{{ $metric['label'] }}</span>
                    <i class="bi bi-{{ $metric['icon'] }} text-danger fs-5" aria-hidden="true"></i>
                </div>
                <div class="display-6 fw-bold mb-1">{{ $metric['value'] }}</div>
                <small class="text-muted">{{ $metric['note'] }}</small>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-4">
    <div class="col-xl-8">
        <div class="card card-custom p-4 h-100">
            <h2 class="h5 fw-bold">Check in terbaru</h2>
            <p class="small text-muted">Maksimal delapan catatan masuk hari ini. Muat ulang halaman untuk pembaruan.</p>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <caption class="visually-hidden">Catatan absensi terbaru hari ini</caption>
                    <thead><tr>
                        <th scope="col">Karyawan</th><th scope="col">Masuk</th>
                        <th scope="col">Pulang</th><th scope="col">Kelengkapan</th>
                    </tr></thead>
                    <tbody>
                        @forelse($recentAttendances as $attendance)
                            <tr>
                                <td class="fw-semibold">{{ $attendance->user->name ?? 'Akun tidak tersedia' }}</td>
                                <td>{{ $attendance->check_in_time }}</td>
                                <td>{{ $attendance->check_out_time ?? '—' }}</td>
                                <td>
                                    @if($attendance->check_out_time)
                                        <span class="badge text-bg-success">Lengkap</span>
                                    @else
                                        <span class="badge text-bg-warning">Belum check out</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-5">
                                Belum ada check in hari ini. Catatan karyawan akan muncul di sini.
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card card-custom p-4 h-100">
            <h2 class="h5 fw-bold">Kehadiran tujuh hari</h2>
            <p class="small text-muted mb-4">Jumlah karyawan yang tercatat check in per hari.</p>
            @foreach($trend as $day)
                <div class="mb-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span>{{ $day['label'] }}</span><strong>{{ $day['total'] }} orang</strong>
                    </div>
                    <div class="progress" style="height: 7px" aria-hidden="true">
                        <div class="progress-bar bg-danger"
                            style="width: {{ $totalEmployees ? min(100, round($day['total'] / $totalEmployees * 100)) : 0 }}%"></div>
                    </div>
                </div>
            @endforeach
            <p class="small text-muted mb-0">Belum check in tidak otomatis berarti alfa. Jadwal kerja dan cuti belum dihitung.</p>
        </div>
    </div>
</div>

<div class="card card-custom p-4 mt-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h2 class="h6 fw-bold mb-1">{{ $office->office_name ?? 'Lokasi belum tersedia' }}</h2>
            <p class="text-muted small mb-0">
                @if($office)
                    Radius absensi {{ number_format($office->radius, 0, ',', '.') }} meter · Waktu mengikuti server {{ config('app.timezone') }}.
                @else
                    Tentukan koordinat dan radius kantor untuk memulai.
                @endif
            </p>
        </div>
        <a class="btn btn-outline-secondary btn-sm" href="{{ route('office.index') }}">Kelola lokasi</a>
    </div>
</div>
@endsection

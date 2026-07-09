@extends('layouts.admin')

@section('content')
<div class="row g-4">
    <div class="col-md-4">
        <div class="card card-custom p-4">
            <h6 class="text-muted">Total Karyawan</h6>
            <h2 class="fw-bold">{{ $totalEmployees }}</h2>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-custom p-4">
            <h6 class="text-muted">Absen Hari Ini</h6>
            <h2 class="fw-bold">{{ $todayAttendances }}</h2>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-custom p-4">
            <h6 class="text-muted">Lokasi Kantor</h6>
            <h2 class="fw-bold">{{ $officeCount }}</h2>
        </div>
    </div>
</div>
@endsection
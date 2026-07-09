@extends('layouts.admin')

@section('content')

<div class="card card-custom p-4">

    <h4 class="fw-bold mb-1">Lokasi Kantor</h4>
    <p class="text-muted mb-4">
        Atur titik lokasi Chips Bodywork dan radius maksimal absensi.
    </p>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('office.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label">Nama Kantor</label>
            <input type="text" name="office_name" class="form-control"
                   value="{{ $office->office_name ?? 'Chips Bodywork' }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Latitude</label>
            <input type="text" name="latitude" class="form-control"
                   value="{{ $office->latitude ?? '' }}"
                   placeholder="-6.2000000" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Longitude</label>
            <input type="text" name="longitude" class="form-control"
                   value="{{ $office->longitude ?? '' }}"
                   placeholder="106.8166660" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Radius Absensi / Meter</label>
            <input type="number" name="radius" class="form-control"
                   value="{{ $office->radius ?? 100 }}" required>
        </div>

        <button class="btn btn-red">
            Simpan Lokasi Kantor
        </button>
    </form>

</div>

@endsection
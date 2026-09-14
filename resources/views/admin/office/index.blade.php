@extends('layouts.admin')

@section('content')
<div class="card card-custom p-4">
    <h4 class="fw-bold mb-1">Lokasi Kantor</h4>
    <p class="text-muted mb-4">Atur titik kantor dan radius maksimal absensi dalam meter.</p>

    @if(session('success'))
        <div class="alert alert-success" role="status">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger" role="alert">
            <strong>Periksa kembali data lokasi.</strong>
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ route('office.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="office_name" class="form-label">Nama kantor</label>
            <input id="office_name" type="text" name="office_name" class="form-control"
                value="{{ old('office_name', $office->office_name ?? 'Chips Bodywork') }}"
                maxlength="255" required>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="latitude" class="form-label">Latitude</label>
                <input id="latitude" type="number" step="any" min="-90" max="90"
                    name="latitude" class="form-control"
                    value="{{ old('latitude', $office->latitude ?? '') }}"
                    placeholder="-6.2000000" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="longitude" class="form-label">Longitude</label>
                <input id="longitude" type="number" step="any" min="-180" max="180"
                    name="longitude" class="form-control"
                    value="{{ old('longitude', $office->longitude ?? '') }}"
                    placeholder="106.8166660" required>
            </div>
        </div>
        <div class="mb-4">
            <label for="radius" class="form-label">Radius absensi (meter)</label>
            <input id="radius" type="number" min="1" max="10000" step="1" name="radius"
                class="form-control" value="{{ old('radius', $office->radius ?? 100) }}"
                aria-describedby="radius-help" required>
            <div id="radius-help" class="form-text">Gunakan radius yang sesuai dengan area kerja, antara 1–10.000 meter.</div>
        </div>
        <button class="btn btn-red">Simpan lokasi kantor</button>
    </form>
</div>
@endsection

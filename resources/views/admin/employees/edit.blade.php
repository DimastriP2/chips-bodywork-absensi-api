@extends('layouts.admin')

@section('content')

<div class="card card-custom p-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Edit Karyawan</h4>
            <small class="text-muted">Ubah data karyawan Chips Bodywork</small>
        </div>

        <a href="{{ route('employees.index') }}" class="btn btn-secondary">
            Kembali
        </a>
    </div>

    <form action="{{ route('employees.update', $employee->id) }}" method="POST">

        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Nama Karyawan</label>
            <input type="text" name="name" class="form-control"
                   value="{{ $employee->name }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">NIK / Nomor Karyawan</label>
            <input type="text" name="employee_number" class="form-control"
                   value="{{ $employee->employee_number }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Posisi</label>
            <input type="text" name="position" class="form-control"
                   value="{{ $employee->position }}">
        </div>

        <div class="mb-3">
            <label class="form-label">No HP</label>
            <input type="text" name="phone" class="form-control"
                   value="{{ $employee->phone }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Alamat</label>
            <textarea name="address" class="form-control" rows="4">{{ $employee->address }}</textarea>
        </div>

        <button type="submit" class="btn btn-red">
            Update Data
        </button>

    </form>

</div>

@endsection
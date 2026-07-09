@extends('layouts.admin')

@section('content')

<div class="card card-custom p-4">

    <h4 class="fw-bold mb-4">Tambah Karyawan</h4>
    @if ($errors->any())
    <div class="alert alert-danger">
        <strong>Data gagal disimpan:</strong>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

    <form action="{{ route('employees.store') }}" method="POST">

        @csrf

        <div class="mb-3">
            <label>Nama Karyawan</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Email Login Aplikasi</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Password Login</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Konfirmasi Password</label>
            <input type="password" name="password_confirmation" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>NIK</label>
            <input type="text" name="employee_number" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Posisi</label>
            <input type="text" name="position" class="form-control">
        </div>

        <div class="mb-3">
            <label>No HP</label>
            <input type="text" name="phone" class="form-control">
        </div>

        <div class="mb-3">
            <label>Alamat</label>
            <textarea name="address" class="form-control"></textarea>
        </div>

        <button type="submit" class="btn btn-red">
            Simpan Karyawan
        </button>

    </form>

</div>

@endsection
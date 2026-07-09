@extends('layouts.admin')

@section('content')

<div class="card card-custom p-4">

    <div class="d-flex justify-content-between mb-4">
        <div>
            <h4 class="fw-bold">Data Karyawan</h4>
            <small class="text-muted">
                Chips Bodywork Employee Management
            </small>
        </div>

        <a href="{{ route('employees.create') }}" class="btn btn-red">
            <i class="bi bi-plus-circle"></i> Tambah Karyawan
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Email Login</th>
                    <th>NIK</th>
                    <th>Posisi</th>
                    <th>No HP</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($employees as $employee)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $employee->name }}</td>
                    <td>{{ $employee->user->email ?? '-' }}</td>
                    <td>{{ $employee->employee_number }}</td>
                    <td>{{ $employee->position ?? '-' }}</td>
                    <td>{{ $employee->phone ?? '-' }}</td>

                    <td class="d-flex gap-2">

                        <a href="{{ route('employees.edit', $employee->id) }}"
                            class="btn btn-warning btn-sm">
                            Edit
                        </a>

                        <form action="{{ route('employees.destroy', $employee->id) }}"
                            method="POST"
                            onsubmit="return confirm('Yakin ingin menghapus karyawan ini? Akun login aplikasi juga akan ikut terhapus.')">

                            @csrf
                            @method('DELETE')

                            <button class="btn btn-danger btn-sm">
                                Hapus
                            </button>

                        </form>

                    </td>
                </tr>

                @empty

                <tr>
                    <td colspan="7" class="text-center">
                        Belum ada data karyawan
                    </td>
                </tr>

                @endforelse
            </tbody>
        </table>
    </div>

</div>

@endsection
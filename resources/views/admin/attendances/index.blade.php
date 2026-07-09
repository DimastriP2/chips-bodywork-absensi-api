@extends('layouts.admin')

@section('content')

<div class="card shadow-sm border-0">

    <div class="card-header bg-white">

        <div class="d-flex justify-content-between align-items-center">

            <div>
                <h4 class="fw-bold mb-1">
                    Rekap Absensi Karyawan
                </h4>

                <small class="text-muted">
                    Chips Bodywork Employee Attendance
                </small>
            </div>

        </div>

    </div>

    <div class="card-body">

        <form method="GET"
              action="{{ route('attendances.index') }}">

            <div class="row">

                {{-- Filter Harian --}}
                <div class="col-md-3">

                    <label class="form-label">
                        Filter Tanggal
                    </label>

                    <input
                        type="date"
                        name="date"
                        class="form-control"
                        value="{{ request('date') }}">

                </div>

                {{-- Filter Mingguan --}}
                <div class="col-md-3">

                    <label class="form-label">
                        Filter Minggu
                    </label>

                    <input
                        type="date"
                        name="week"
                        class="form-control"
                        value="{{ request('week') }}">

                    <small class="text-muted">
                        Pilih salah satu tanggal pada minggu tersebut
                    </small>

                </div>

                {{-- Filter Bulanan --}}
                <div class="col-md-3">

                    <label class="form-label">
                        Filter Bulan
                    </label>

                    <input
                        type="month"
                        name="month"
                        class="form-control"
                        value="{{ request('month') }}">

                </div>

                <div class="col-md-3 d-flex align-items-end">

                    <button
                        class="btn btn-primary me-2">

                        Filter

                    </button>

                    <a href="{{ route('attendances.index') }}"
                       class="btn btn-secondary">

                        Reset

                    </a>

                </div>

            </div>

        </form>

        <hr>

        <div class="mb-3">

            <a
                href="{{ route('attendance.export.csv', request()->query()) }}"
                class="btn btn-success">

                📄 Download CSV

            </a>

            <a
                href="{{ route('attendance.export.pdf', request()->query()) }}"
                class="btn btn-danger">

                📕 Download PDF

            </a>

        </div>

        <div class="table-responsive">

            <table class="table table-bordered table-hover align-middle">

                <thead class="table-dark">

                    <tr>

                        <th width="60">
                            No
                        </th>

                        <th>
                            Nama
                        </th>

                        <th>
                            Tanggal
                        </th>

                        <th>
                            Jam Masuk
                        </th>

                        <th>
                            Jam Pulang
                        </th>

                        <th>
                            Jarak
                        </th>

                        <th>
                            Status
                        </th>

                    </tr>

                </thead>

                <tbody>

                @forelse($attendances as $attendance)

                    <tr>

                        <td>

                            {{ $loop->iteration }}

                        </td>

                        <td>

                            {{ $attendance->user->name ?? '-' }}

                        </td>

                        <td>

                            {{ \Carbon\Carbon::parse($attendance->date)->format('d-m-Y') }}

                        </td>

                        <td>

                            {{ $attendance->check_in_time ?? '-' }}

                        </td>

                        <td>

                            {{ $attendance->check_out_time ?? '-' }}

                        </td>

                        <td>

                            @if($attendance->distance)

                                {{ $attendance->distance }} Meter

                            @else

                                -

                            @endif

                        </td>

                        <td>

                            @if($attendance->status=='hadir')

                                <span class="badge bg-success">

                                    Hadir

                                </span>

                            @else

                                <span class="badge bg-secondary">

                                    {{ ucfirst($attendance->status) }}

                                </span>

                            @endif

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="7"
                            class="text-center">

                            Belum ada data absensi.

                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection
<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">

    <title>Laporan Rekap Absensi</title>

    <style>

        body{

            font-family: DejaVu Sans, sans-serif;
            font-size:12px;
            color:#222;

        }

        h2{

            margin:0;
            text-align:center;

        }

        h4{

            margin:3px 0;
            text-align:center;

        }

        .subtitle{

            text-align:center;
            margin-bottom:20px;
            color:#666;

        }

        .info{

            margin-bottom:15px;

        }

        table{

            width:100%;
            border-collapse:collapse;

        }

        table th{

            background:#b71c1c;
            color:white;
            border:1px solid #000;
            padding:8px;

        }

        table td{

            border:1px solid #000;
            padding:6px;

            text-align:center;

        }

        .footer{

            margin-top:30px;

            font-size:11px;

            text-align:right;

        }

        .summary{

            margin-top:15px;

            font-weight:bold;

        }

    </style>

</head>

<body>

<h2>
    CHIPS BODYWORK
</h2>

<h4>
    Laporan Rekap Absensi Karyawan
</h4>

<div class="subtitle">

    Dicetak pada :

    {{ now()->format('d F Y H:i') }}

</div>

<div class="info">

    <strong>Total Data :</strong>

    {{ $attendances->count() }}

</div>

<table>

    <thead>

        <tr>

            <th>No</th>

            <th>Nama</th>

            <th>Tanggal</th>

            <th>Jam Masuk</th>

            <th>Jam Pulang</th>

            <th>Jarak</th>

            <th>Status</th>

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

                {{ ucfirst($attendance->status) }}

            </td>

        </tr>

    @empty

        <tr>

            <td colspan="7">

                Tidak ada data absensi

            </td>

        </tr>

    @endforelse

    </tbody>

</table>

<div class="summary">

Jumlah Rekap :

{{ $attendances->count() }}

Data

</div>

<div class="footer">

Dicetak otomatis oleh Sistem Absensi Karyawan Chips Bodywork

</div>

</body>

</html>
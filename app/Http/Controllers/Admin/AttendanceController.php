<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Menampilkan data absensi
     */
    public function index(Request $request)
    {
        $attendances = $this->filterAttendance($request);

        return view('admin.attendances.index', compact('attendances'));
    }

    /**
     * Filter data absensi
     */
    private function filterAttendance(Request $request)
    {
        $query = Attendance::with('user');

        // ==========================
        // FILTER HARIAN
        // ==========================
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        // ==========================
        // FILTER MINGGUAN
        // format : 2026-07-06
        // ==========================
        if ($request->filled('week')) {

            $start = Carbon::parse($request->week)->startOfWeek(Carbon::MONDAY);
            $end = Carbon::parse($request->week)->endOfWeek(Carbon::SUNDAY);

            $query->whereBetween('date', [
                $start->toDateString(),
                $end->toDateString()
            ]);
        }

        // ==========================
        // FILTER BULANAN
        // format : 2026-07
        // ==========================
        if ($request->filled('month')) {

            $date = Carbon::parse($request->month . "-01");

            $query->whereMonth('date', $date->month)
                ->whereYear('date', $date->year);
        }

        return $query
            ->latest()
            ->get();
    }

    /**
     * EXPORT CSV
     */
    public function exportCsv(Request $request)
    {
        $attendances = $this->filterAttendance($request);

        $filename = 'Rekap_Absensi_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($attendances) {

            $file = fopen('php://output', 'w');

            // UTF-8 BOM supaya Excel tidak rusak
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'No',
                'Nama',
                'Tanggal',
                'Jam Masuk',
                'Jam Pulang',
                'Jarak',
                'Status'
            ]);

            $no = 1;

            foreach ($attendances as $attendance) {

                fputcsv($file, [

                    $no++,

                    $attendance->user->name ?? '-',

                    $attendance->date,

                    $attendance->check_in_time ?? '-',

                    $attendance->check_out_time ?? '-',

                    $attendance->distance
                        ? $attendance->distance . ' Meter'
                        : '-',

                    ucfirst($attendance->status)

                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * EXPORT PDF
     */
    public function exportPdf(Request $request)
    {
        $attendances = $this->filterAttendance($request);

        $pdf = Pdf::loadView(
            'admin.attendances.pdf',
            compact('attendances')
        );

        $pdf->setPaper('A4', 'landscape');

        return $pdf->download(
            'Rekap_Absensi_' . date('Ymd_His') . '.pdf'
        );
    }
}
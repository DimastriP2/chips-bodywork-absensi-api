<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OfficeLocation;
use Illuminate\Http\Request;

class OfficeLocationController extends Controller
{
    public function index()
    {
        $office = OfficeLocation::query()->orderBy('id')->first();

        return view('admin.office.index', compact('office'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'office_name' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius' => ['required', 'integer', 'between:1,10000'],
        ]);

        // Match the office selected by the attendance API, including imported data.
        $office = OfficeLocation::query()->orderBy('id')->first();
        OfficeLocation::updateOrCreate(['id' => $office?->id ?? 1], $data);

        return back()->with('success', 'Lokasi kantor berhasil disimpan');
    }
}

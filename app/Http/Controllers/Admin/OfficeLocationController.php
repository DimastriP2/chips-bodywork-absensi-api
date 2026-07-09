<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OfficeLocation;
use Illuminate\Http\Request;

class OfficeLocationController extends Controller
{
    public function index()
    {
        $office = OfficeLocation::first();

        return view('admin.office.index', compact('office'));
    }

    public function store(Request $request)
    {
        OfficeLocation::updateOrCreate(
            ['id' => 1],
            [
                'office_name' => $request->office_name,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'radius' => $request->radius,
            ]
        );

        return back()->with('success', 'Lokasi kantor berhasil disimpan');
    }
}
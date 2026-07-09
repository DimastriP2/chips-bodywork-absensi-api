<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::with('user')->latest()->get();

        return view('admin.employees.index', compact('employees'));
    }

    public function create()
    {
        return view('admin.employees.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'employee_number' => 'required|unique:employees,employee_number',
            'position' => 'nullable',
            'phone' => 'nullable',
            'address' => 'nullable',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => strtolower($request->email),
            'password' => Hash::make($request->password),
            'role' => 'employee',
        ]);

        Employee::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'employee_number' => $request->employee_number,
            'position' => $request->position,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        return redirect()->route('employees.index')
            ->with('success', 'Karyawan dan akun login aplikasi berhasil dibuat');
    }

    public function edit(Employee $employee)
    {
        return view('admin.employees.edit', compact('employee'));
    }

    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'name' => 'required',
            'employee_number' => 'required|unique:employees,employee_number,' . $employee->id,
            'position' => 'nullable',
            'phone' => 'nullable',
            'address' => 'nullable',
        ]);

        $employee->update([
            'name' => $request->name,
            'employee_number' => $request->employee_number,
            'position' => $request->position,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        if ($employee->user) {
            $employee->user->update([
                'name' => $request->name,
            ]);
        }

        return redirect()->route('employees.index')
            ->with('success', 'Data karyawan berhasil diupdate');
    }

    public function destroy(Employee $employee)
    {
        if ($employee->user) {
            $employee->user->delete();
        }

        $employee->delete();

        return back()->with('success', 'Karyawan dan akun login berhasil dihapus');
    }
}
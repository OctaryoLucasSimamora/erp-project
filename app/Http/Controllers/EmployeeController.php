<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Department;
use App\Models\JobPosition;

class EmployeeController extends Controller
{
    // DEPARTMENT METHODS
    public function departmentIndex()
    {
        $departments = Department::orderBy('department_name')->get();
        return view('employee.department.index', compact('departments'));
    }

    public function departmentCreate()
    {
        return view('employee.department.create');
    }

    public function departmentStore(Request $request)
    {
        $request->validate([
            'department_name' => 'required|unique:departments,department_name',
            'company' => 'required'
        ]);

        Department::create($request->only(['department_name', 'company']));

        return redirect()->route('employee.department.index')
            ->with('success', 'Department berhasil ditambahkan!');
    }

    public function departmentEdit($id)
    {
        $department = Department::findOrFail($id);
        return view('employee.department.edit', compact('department'));
    }

    public function departmentUpdate(Request $request, $id)
    {
        $request->validate([
            'department_name' => 'required|unique:departments,department_name,' . $id,
            'company' => 'required'
        ]);

        $department = Department::findOrFail($id);
        $department->update($request->only(['department_name', 'company']));

        return redirect()->route('employee.department.index')
            ->with('success', 'Department berhasil diperbarui!');
    }

    public function departmentDestroy($id)
    {
        $department = Department::findOrFail($id);
        $department->delete();

        return redirect()->route('employee.department.index')
            ->with('success', 'Department berhasil dihapus!');
    }

    // JOB POSITION METHODS
    public function jobPositionIndex()
    {
        $jobPositions = JobPosition::orderBy('position')->get();
        $departments = Department::orderBy('department_name')->get();
        $companies = Department::distinct()->pluck('company');
        
        return view('employee.job_position.index', compact('jobPositions', 'departments', 'companies'));
    }

    public function jobPositionCreate()
    {
        $departments = Department::orderBy('department_name')->get();
        $companies = Department::distinct()->pluck('company');
        
        return view('employee.job_position.create', compact('departments', 'companies'));
    }

    public function jobPositionStore(Request $request)
    {
        $request->validate([
            'position' => 'required',
            'department_name' => 'required',
            'company' => 'required'
        ]);

        JobPosition::create($request->all());

        return redirect()->route('employee.job_position.index')
            ->with('success', 'Job Position berhasil ditambahkan!');
    }

    public function jobPositionEdit($id)
    {
        $jobPosition = JobPosition::findOrFail($id);
        $departments = Department::orderBy('department_name')->get();
        $companies = Department::distinct()->pluck('company');
        
        return view('employee.job_position.edit', compact('jobPosition', 'departments', 'companies'));
    }

    public function jobPositionUpdate(Request $request, $id)
    {
        $request->validate([
            'position' => 'required',
            'department_name' => 'required',
            'company' => 'required'
        ]);

        $jobPosition = JobPosition::findOrFail($id);
        $jobPosition->update($request->all());

        return redirect()->route('employee.job_position.index')
            ->with('success', 'Job Position berhasil diperbarui!');
    }

    public function jobPositionDestroy($id)
    {
        $jobPosition = JobPosition::findOrFail($id);
        $jobPosition->delete();

        return redirect()->route('employee.job_position.index')
            ->with('success', 'Job Position berhasil dihapus!');
    }

    // EMPLOYEE METHODS
    public function employeeIndex()
    {
        $employees = Employee::orderBy('name')->get();
        $departments = Department::orderBy('department_name')->get();
        $jobPositions = JobPosition::orderBy('position')->get();
        $companies = Department::distinct()->pluck('company');
        
        return view('employee.employee.index', compact('employees', 'departments', 'jobPositions', 'companies'));
    }

    public function employeeCreate()
    {
        $departments = Department::orderBy('department_name')->get();
        $jobPositions = JobPosition::orderBy('position')->get();
        $companies = Department::distinct()->pluck('company');
        
        return view('employee.employee.create', compact('departments', 'jobPositions', 'companies'));
    }

    public function employeeStore(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'department_name' => 'required',
            'position' => 'required',
            'company' => 'required'
        ]);

        Employee::create($request->all());

        return redirect()->route('employee.employee.index')
            ->with('success', 'Employee berhasil ditambahkan!');
    }

    public function employeeEdit($id)
    {
        $employee = Employee::findOrFail($id);
        $departments = Department::orderBy('department_name')->get();
        $jobPositions = JobPosition::orderBy('position')->get();
        $companies = Department::distinct()->pluck('company');
        
        return view('employee.employee.edit', compact('employee', 'departments', 'jobPositions', 'companies'));
    }

    public function employeeUpdate(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'department_name' => 'required',
            'position' => 'required',
            'company' => 'required'
        ]);

        $employee = Employee::findOrFail($id);
        $employee->update($request->all());

        return redirect()->route('employee.employee.index')
            ->with('success', 'Employee berhasil diperbarui!');
    }

    public function employeeDestroy($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();

        return redirect()->route('employee.employee.index')
            ->with('success', 'Employee berhasil dihapus!');
    }
}
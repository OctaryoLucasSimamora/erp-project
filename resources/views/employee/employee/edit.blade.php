@extends('layouts.app')
@section('title', 'Edit Employee')

@section('content')
<div class="card shadow">
    <div class="card-header">
        <h5 class="mb-0">Edit Employee</h5>
    </div>
    
    <div class="card-body">
        <form action="{{ route('employee.employee.update', $employee->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" 
                               class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $employee->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Position</label>
                        <select name="position" class="form-control @error('position') is-invalid @enderror" required>
                            <option value="">Select Position</option>
                            @foreach($jobPositions as $job)
                                <option value="{{ $job->position }}" 
                                        {{ old('position', $employee->position) == $job->position ? 'selected' : '' }}>
                                    {{ $job->position }}
                                </option>
                            @endforeach
                        </select>
                        @error('position')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Department</label>
                        <select name="department_name" class="form-control @error('department_name') is-invalid @enderror" required>
                            <option value="">Select Department</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->department_name }}" 
                                        {{ old('department_name', $employee->department_name) == $dept->department_name ? 'selected' : '' }}>
                                    {{ $dept->department_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('department_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Company</label>
                        <select name="company" class="form-control @error('company') is-invalid @enderror" required>
                            <option value="">Select Company</option>
                            @foreach($companies as $company)
                                <option value="{{ $company }}" 
                                        {{ old('company', $employee->company) == $company ? 'selected' : '' }}>
                                    {{ $company }}
                                </option>
                            @endforeach
                        </select>
                        @error('company')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Manager</label>
                        <input type="text" name="manager" 
                               class="form-control @error('manager') is-invalid @enderror" 
                               value="{{ old('manager', $employee->manager) }}">
                        @error('manager')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Telephone</label>
                        <input type="text" name="telephone" 
                               class="form-control @error('telephone') is-invalid @enderror" 
                               value="{{ old('telephone', $employee->telephone) }}">
                        @error('telephone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               value="{{ old('email', $employee->email) }}">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Work Location</label>
                        <input type="text" name="work_location" 
                               class="form-control @error('work_location') is-invalid @enderror" 
                               value="{{ old('work_location', $employee->work_location) }}">
                        @error('work_location')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-warning">Update</button>
                <a href="{{ route('employee.employee.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
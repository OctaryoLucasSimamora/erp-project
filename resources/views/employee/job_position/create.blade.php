@extends('layouts.app')
@section('title', 'Create Job Position')

@section('content')
<div class="card shadow">
    <div class="card-header">
        <h5 class="mb-0">Create Job Position</h5>
    </div>
    
    <div class="card-body">
        <form action="{{ route('employee.job_position.store') }}" method="POST">
            @csrf
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Job Position</label>
                        <input type="text" name="position" 
                               class="form-control @error('position') is-invalid @enderror" 
                               value="{{ old('position') }}" required>
                        @error('position')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Job Location</label>
                        <input type="text" name="job_location" 
                               class="form-control @error('job_location') is-invalid @enderror" 
                               value="{{ old('job_location') }}">
                        @error('job_location')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Company</label>
                        <select name="company" class="form-control @error('company') is-invalid @enderror" required>
                            <option value="">Select Company</option>
                            @foreach($companies as $company)
                                <option value="{{ $company }}" {{ old('company') == $company ? 'selected' : '' }}>
                                    {{ $company }}
                                </option>
                            @endforeach
                        </select>
                        @error('company')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Expected New Employee</label>
                        <input type="number" name="expected_new_employees" 
                               class="form-control @error('expected_new_employees') is-invalid @enderror" 
                               value="{{ old('expected_new_employees', 0) }}">
                        @error('expected_new_employees')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Department</label>
                <select name="department_name" class="form-control @error('department_name') is-invalid @enderror" required>
                    <option value="">Select Department</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->department_name }}" 
                                {{ old('department_name') == $dept->department_name ? 'selected' : '' }}>
                            {{ $dept->department_name }}
                        </option>
                    @endforeach
                </select>
                @error('department_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label class="form-label">Job Description</label>
                <textarea name="job_description" class="form-control @error('job_description') is-invalid @enderror" 
                          rows="3">{{ old('job_description') }}</textarea>
                @error('job_description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('employee.job_position.index') }}" class="btn btn-secondary">Discard</a>
            </div>
        </form>
    </div>
</div>
@endsection
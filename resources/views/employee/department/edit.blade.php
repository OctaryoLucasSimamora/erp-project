@extends('layouts.app')
@section('title', 'Edit Department')

@section('content')
<div class="card shadow">
    <div class="card-header">
        <h5 class="mb-0">Edit Department</h5>
    </div>
    
    <div class="card-body">
        <form action="{{ route('employee.department.update', $department->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="mb-3">
                <label for="department_name" class="form-label">Department Name</label>
                <input type="text" class="form-control @error('department_name') is-invalid @enderror" 
                       id="department_name" name="department_name" 
                       value="{{ old('department_name', $department->department_name) }}" required>
                @error('department_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label for="company" class="form-label">Company</label>
                <input type="text" class="form-control @error('company') is-invalid @enderror" 
                       id="company" name="company" 
                       value="{{ old('company', $department->company) }}" required>
                @error('company')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-warning">Update</button>
                <a href="{{ route('employee.department.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
@extends('layouts.app')
@section('title', 'Department')

@section('content')
<div class="card shadow">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Department</h5>
        <a href="{{ route('employee.department.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> New
        </a>
    </div>
    
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th width="50">No</th>
                    <th>Department</th>
                    <th>Company</th>
                    <th width="150" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($departments as $index => $dept)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $dept->department_name }}</td>
                    <td>{{ $dept->company }}</td>
                    <td class="text-center">
                        <a href="{{ route('employee.department.edit', $dept->id) }}" 
                           class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        
                        <form action="{{ route('employee.department.destroy', $dept->id) }}" 
                              method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" 
                                    onclick="return confirm('Delete this department?')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center">No departments found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
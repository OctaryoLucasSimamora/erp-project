@extends('layouts.app')
@section('title', 'Job Position')

@section('content')
<div class="card shadow">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Job Position</h5>
        <div class="d-flex">
            <input type="text" class="form-control me-2" placeholder="Search..." style="width: 200px;">
            <a href="{{ route('employee.job_position.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> New
            </a>
        </div>
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
                    <th>Position</th>
                    <th>Department</th>
                    <th>Company</th>
                    <th width="150" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jobPositions as $index => $job)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $job->position }}</td>
                    <td>{{ $job->department_name }}</td>
                    <td>{{ $job->company }}</td>
                    <td class="text-center">
                        <a href="{{ route('employee.job_position.edit', $job->id) }}" 
                           class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        
                        <form action="{{ route('employee.job_position.destroy', $job->id) }}" 
                              method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" 
                                    onclick="return confirm('Delete this job position?')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">No job positions found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
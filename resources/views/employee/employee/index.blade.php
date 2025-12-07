@extends('layouts.app')
@section('title', 'Employee')

@section('content')
<div class="card shadow">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Employee</h5>
        <div class="d-flex">
            <input type="text" id="employeeSearch" class="form-control me-2" placeholder="Name Search..." style="width: 200px;">
            <a href="{{ route('employee.employee.create') }}" class="btn btn-primary">
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
                    <th>Name</th>
                    <th>Department</th>
                    <th>Position</th>
                    <th>Company</th>
                    <th width="150" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody id="employeeTableBody">
                @forelse($employees as $index => $emp)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $emp->name }}</td>
                    <td>{{ $emp->department_name }}</td>
                    <td>{{ $emp->position }}</td>
                    <td>{{ $emp->company }}</td>
                    <td class="text-center">
                        <a href="{{ route('employee.employee.edit', $emp->id) }}" 
                           class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        
                        <form action="{{ route('employee.employee.destroy', $emp->id) }}" 
                              method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" 
                                    onclick="return confirm('Delete this employee?')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">No employees found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
// Search functionality
document.getElementById('employeeSearch').addEventListener('keyup', function() {
    const searchValue = this.value.toLowerCase();
    const rows = document.querySelectorAll('#employeeTableBody tr');
    
    rows.forEach(row => {
        const name = row.cells[1].textContent.toLowerCase();
        const dept = row.cells[2].textContent.toLowerCase();
        const position = row.cells[3].textContent.toLowerCase();
        
        if (name.includes(searchValue) || dept.includes(searchValue) || position.includes(searchValue)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>
@endsection
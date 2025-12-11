@extends('layouts.app')
@section('title', 'Vendor Management')

@section('content')
<div class="card shadow">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Vendor List</h5>
        <a href="{{ route('purchase.vendor.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> New Vendor
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
                    <th>Name</th>
                    <th>Company Type</th>
                    <th>Contact Phone</th>
                    <th>Email</th>
                    <th width="150" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vendors as $index => $vendor)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $vendor->name }}</td>
                    <td>
                        <span class="badge bg-{{ $vendor->company_type == 'company' ? 'info' : 'secondary' }}">
                            {{ ucfirst($vendor->company_type) }}
                        </span>
                    </td>
                    <td>{{ $vendor->contact_phone }}</td>
                    <td>{{ $vendor->email }}</td>
                    <td class="text-center">
                        <a href="{{ route('purchase.vendor.edit', $vendor->id) }}" 
                           class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        
                        <form action="{{ route('purchase.vendor.destroy', $vendor->id) }}" 
                              method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" 
                                    onclick="return confirm('Delete this vendor?')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">No vendors found</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{ $vendors->links() }}
    </div>
</div>
@endsection
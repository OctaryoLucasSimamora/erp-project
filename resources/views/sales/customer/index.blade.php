@extends('layouts.app')

@section('content')
    <h3>Customers</h3>

    <div class="row mb-3">
        <div class="col-md-6">
            <a href="{{ route('sales.customer.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create Customer
            </a>
        </div>
        <div class="col-md-6 text-right">
            <form action="{{ route('sales.customer.index') }}" method="GET" class="form-inline float-right">
                <div class="input-group" style="width: 300px;">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by name, email, phone, or company" 
                           value="{{ request('search') }}" style="border-right: none;">
                    <div class="input-group-append">
                        <button type="submit" class="input-group-text" style="background: white; border-left: none; cursor: pointer;">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="thead-light">
                <tr>
                    <th width="5%">No</th>
                    <th width="15%">Customer</th>
                    <th width="20%">Contact Info</th>
                    <th width="15%">Company</th>
                    <th width="15%">Address</th>
                    <th width="10%">Quotations</th>
                    <th width="20%" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $index => $customer)
                    <tr>
                        <td>{{ $customers->firstItem() + $index }}</td>
                        <td>
                            @if($customer->image)
                                <img src="{{ Storage::url($customer->image) }}" alt="{{ $customer->name }}" 
                                     class="rounded-circle mr-2" width="30" height="30">
                            @else
                                <div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center mr-2" 
                                     style="width: 30px; height: 30px;">
                                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                                </div>
                            @endif
                            <strong>{{ $customer->name }}</strong>
                            @if($customer->title)
                                <br><small class="text-muted">{{ $customer->title }}</small>
                            @endif
                        </td>
                        <td>
                            @if($customer->email)
                                <div><i class="fas fa-envelope text-muted mr-1"></i> {{ $customer->email }}</div>
                            @endif
                            @if($customer->phone)
                                <div><i class="fas fa-phone text-muted mr-1"></i> {{ $customer->phone }}</div>
                            @endif
                            @if($customer->mobile)
                                <div><i class="fas fa-mobile-alt text-muted mr-1"></i> {{ $customer->mobile }}</div>
                            @endif
                        </td>
                        <td>
                            {{ $customer->company ?? '-' }}
                            @if($customer->position)
                                <br><small class="text-muted">{{ $customer->position }}</small>
                            @endif
                        </td>
                        <td>
                            @if($customer->address)
                                <small>{{ Str::limit($customer->address, 50) }}</small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-info">{{ $customer->quotations()->count() }}</span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group" role="group">
                                <a href="{{ route('sales.customer.show', $customer->id) }}" 
                                   class="btn btn-sm btn-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>

                                <a href="{{ route('sales.customer.edit', $customer->id) }}" 
                                   class="btn btn-sm btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <button type="button" class="btn btn-sm btn-danger" 
                                        onclick="deleteCustomer({{ $customer->id }})" 
                                        title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">No customers found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($customers->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
                Showing {{ $customers->firstItem() }} to {{ $customers->lastItem() }} of {{ $customers->total() }} entries
            </div>
            <div>
                {{ $customers->appends(request()->query())->links() }}
            </div>
        </div>
    @endif

    <!-- Delete Form -->
    <form id="delete-form" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@endsection

@push('scripts')
<script>
function deleteCustomer(id) {
    if (confirm('Are you sure you want to delete this customer?')) {
        const form = document.getElementById('delete-form');
        form.action = '{{ url("sales/customer") }}/' + id;
        form.submit();
    }
}
</script>
@endpush

@push('styles')
<style>
    .table td {
        vertical-align: middle;
    }
    
    .btn-group .btn {
        margin: 0 2px;
    }
    
    .rounded-circle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 14px;
    }
</style>
@endpush
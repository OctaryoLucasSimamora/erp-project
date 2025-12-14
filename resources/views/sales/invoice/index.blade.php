@extends('layouts.app')

@section('content')
    <h3>Customer Invoices</h3>

    <div class="row mb-3">
        <div class="col-md-6">
            <a href="{{ route('sales.invoice.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create Invoice
            </a>
        </div>
        <div class="col-md-6 text-right">
            <form action="{{ route('sales.invoice.index') }}" method="GET" class="form-inline float-right">
                <div class="form-group mr-2">
                    <select name="status" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="posted" {{ request('status') == 'posted' ? 'selected' : '' }}>Posted</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                    </select>
                </div>
                <div class="form-group mr-2">
                    <select name="customer_id" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">All Customers</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="input-group" style="width: 200px;">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search" 
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
                    <th width="10%">Invoice Number</th>
                    <th width="15%">Customer</th>
                    <th width="10%">Invoice Date</th>
                    <th width="10%">Due Date</th>
                    <th width="10%">Source</th>
                    <th width="12%" class="text-right">Total Amount</th>
                    <th width="10%">Status</th>
                    <th width="18%" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $index => $invoice)
                    <tr>
                        <td>{{ $invoices->firstItem() + $index }}</td>
                        <td>
                            <strong>{{ $invoice->invoice_number }}</strong>
                            @if($invoice->journal)
                                <br><small class="text-muted">Journal: {{ $invoice->journal }}</small>
                            @endif
                        </td>
                        <td>{{ $invoice->customer->name ?? '-' }}</td>
                        <td>{{ $invoice->invoice_date->format('d M Y') }}</td>
                        <td>{{ $invoice->due_date->format('d M Y') }}</td>
                        <td>
                            @if($invoice->salesOrder)
                                <small>SO: {{ $invoice->salesOrder->order_number }}</small>
                            @endif
                            @if($invoice->delivery_order_id)
                                <br><small>DO: {{ $invoice->delivery_order_id }}</small>
                            @endif
                        </td>
                        <td class="text-right">
                            <strong>Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</strong>
                        </td>
                        <td>
                            <span class="badge badge-{{ $invoice->status_badge }}">
                                {{ ucfirst($invoice->status) }}
                            </span>
                            @if($invoice->paid_at)
                                <br><small class="text-muted">{{ $invoice->paid_at->format('d M Y') }}</small>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="btn-group" role="group">
                                <a href="{{ route('sales.invoice.show', $invoice->id) }}" 
                                   class="btn btn-sm btn-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>

                                @if($invoice->isDraft())
                                    <a href="{{ route('sales.invoice.edit', $invoice->id) }}" 
                                       class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-success" 
                                            onclick="postInvoice({{ $invoice->id }})" 
                                            title="Post Invoice">
                                        <i class="fas fa-check"></i>
                                    </button>
                                @endif

                                @if($invoice->isPosted())
                                    <button type="button" class="btn btn-sm btn-primary" 
                                            onclick="payInvoice({{ $invoice->id }})" 
                                            title="Mark as Paid">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </button>
                                @endif

                                @if($invoice->isDraft())
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            onclick="deleteInvoice({{ $invoice->id }})" 
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">No invoices found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($invoices->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
                Showing {{ $invoices->firstItem() }} to {{ $invoices->lastItem() }} of {{ $invoices->total() }} entries
            </div>
            <div>
                {{ $invoices->appends(request()->query())->links() }}
            </div>
        </div>
    @endif

    <!-- Delete Form -->
    <form id="delete-form" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <!-- Post Form -->
    <form id="post-form" method="POST" style="display: none;">
        @csrf
    </form>

    <!-- Pay Form -->
    <form id="pay-form" method="POST" style="display: none;">
        @csrf
    </form>
@endsection

@push('scripts')
<script>
function deleteInvoice(id) {
    if (confirm('Are you sure you want to delete this invoice?')) {
        const form = document.getElementById('delete-form');
        form.action = '{{ url("sales/invoice") }}/' + id;
        form.submit();
    }
}

function postInvoice(id) {
    if (confirm('Are you sure you want to post this invoice? This will create journal entries.')) {
        const form = document.getElementById('post-form');
        form.action = '{{ url("sales/invoice") }}/' + id + '/post';
        form.submit();
    }
}

function payInvoice(id) {
    if (confirm('Are you sure you want to mark this invoice as paid?')) {
        const form = document.getElementById('pay-form');
        form.action = '{{ url("sales/invoice") }}/' + id + '/pay';
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
</style>
@endpush
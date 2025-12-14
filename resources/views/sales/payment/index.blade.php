@extends('layouts.app')

@section('content')
    <h3>Customer Payments</h3>

    <div class="row mb-3">
        <div class="col-md-6">
            <a href="{{ route('sales.payment.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create Payment
            </a>
        </div>
        <div class="col-md-6 text-right">
            <form action="{{ route('sales.payment.index') }}" method="GET" class="form-inline float-right">
                <div class="form-group mr-2">
                    <select name="status" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="posted" {{ request('status') == 'posted' ? 'selected' : '' }}>Posted</option>
                        <option value="reconciled" {{ request('status') == 'reconciled' ? 'selected' : '' }}>Reconciled</option>
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
                <div class="form-group mr-2">
                    <select name="payment_method" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">All Methods</option>
                        <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="bank_transfer" {{ request('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="credit_card" {{ request('payment_method') == 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                        <option value="check" {{ request('payment_method') == 'check' ? 'selected' : '' }}>Check</option>
                        <option value="other" {{ request('payment_method') == 'other' ? 'selected' : '' }}>Other</option>
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
                    <th width="10%">Payment Number</th>
                    <th width="15%">Customer</th>
                    <th width="10%">Payment Date</th>
                    <th width="12%" class="text-right">Amount</th>
                    <th width="12%">Payment Method</th>
                    <th width="10%">Status</th>
                    <th width="16%" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $index => $payment)
                    <tr>
                        <td>{{ $payments->firstItem() + $index }}</td>
                        <td>
                            <strong>{{ $payment->payment_number }}</strong>
                            @if($payment->memo)
                                <br><small class="text-muted">{{ Str::limit($payment->memo, 30) }}</small>
                            @endif
                        </td>
                        <td>{{ $payment->customer->name ?? '-' }}</td>
                        <td>{{ $payment->payment_date->format('d M Y') }}</td>
                        <td class="text-right">
                            <strong>Rp {{ number_format($payment->amount, 0, ',', '.') }}</strong>
                            @if($payment->allocated_amount > 0)
                                <br>
                                <small class="text-success">Allocated: Rp {{ number_format($payment->allocated_amount, 0, ',', '.') }}</small>
                                <br>
                                <small class="text-info">Remaining: Rp {{ number_format($payment->remaining_amount, 0, ',', '.') }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-info">{{ $payment->payment_method_label }}</span>
                        </td>
                        <td>
                            <span class="badge badge-{{ $payment->status_badge }}">
                                {{ ucfirst($payment->status) }}
                            </span>
                            @if($payment->posted_at)
                                <br><small class="text-muted">Posted: {{ $payment->posted_at->format('d M Y') }}</small>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="btn-group" role="group">
                                <a href="{{ route('sales.payment.show', $payment->id) }}" 
                                   class="btn btn-sm btn-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>

                                @if($payment->isDraft())
                                    <a href="{{ route('sales.payment.edit', $payment->id) }}" 
                                       class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-success" 
                                            onclick="postPayment({{ $payment->id }})" 
                                            title="Post Payment">
                                        <i class="fas fa-check"></i>
                                    </button>
                                @endif

                                @if($payment->isPosted())
                                    <a href="{{ route('sales.payment.allocate', $payment->id) }}" 
                                       class="btn btn-sm btn-primary" title="Allocate to Invoices">
                                        <i class="fas fa-link"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-secondary" 
                                            onclick="reconcilePayment({{ $payment->id }})" 
                                            title="Mark as Reconciled">
                                        <i class="fas fa-exchange-alt"></i>
                                    </button>
                                @endif

                                @if($payment->isDraft())
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            onclick="deletePayment({{ $payment->id }})" 
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">No payments found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($payments->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
                Showing {{ $payments->firstItem() }} to {{ $payments->lastItem() }} of {{ $payments->total() }} entries
            </div>
            <div>
                {{ $payments->appends(request()->query())->links() }}
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

    <!-- Reconcile Form -->
    <form id="reconcile-form" method="POST" style="display: none;">
        @csrf
    </form>
@endsection

@push('scripts')
<script>
function deletePayment(id) {
    if (confirm('Are you sure you want to delete this payment?')) {
        const form = document.getElementById('delete-form');
        form.action = '{{ url("sales/payment") }}/' + id;
        form.submit();
    }
}

function postPayment(id) {
    if (confirm('Are you sure you want to post this payment? This will create journal entries.')) {
        const form = document.getElementById('post-form');
        form.action = '{{ url("sales/payment") }}/' + id + '/post';
        form.submit();
    }
}

function reconcilePayment(id) {
    if (confirm('Are you sure you want to mark this payment as reconciled?')) {
        const form = document.getElementById('reconcile-form');
        form.action = '{{ url("sales/payment") }}/' + id + '/reconcile';
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
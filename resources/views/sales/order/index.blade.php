@extends('layouts.app')

@section('content')
    <h3>Sales Order</h3>

    <div class="row mb-3">
        <div class="col-md-6">
            <a href="{{ route('sales.order.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create Sales Order
            </a>
        </div>
        <div class="col-md-6 text-right">
            <form action="{{ route('sales.order.index') }}" method="GET" class="form-inline float-right">
                <div class="form-group mr-2">
                    <select name="status" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="quotation" {{ request('status') == 'quotation' ? 'selected' : '' }}>Quotation</option>
                        <option value="sales_order" {{ request('status') == 'sales_order' ? 'selected' : '' }}>Sales Order</option>
                        <option value="locked" {{ request('status') == 'locked' ? 'selected' : '' }}>Locked</option>
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
                    <th width="12%">Order Number</th>
                    <th width="15%">Customer</th>
                    <th width="10%">Order Date</th>
                    <th width="10%">Commitment Date</th>
                    <th width="12%">Salesperson</th>
                    <th width="12%" class="text-right">Total Amount</th>
                    <th width="10%">Status</th>
                    <th width="14%" class="text-center">Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($salesOrders as $index => $order)
                    <tr>
                        <td>{{ $salesOrders->firstItem() + $index }}</td>
                        <td>
                            <strong>{{ $order->order_number }}</strong>
                            @if($order->quotation)
                                <br><small class="text-muted">From: {{ $order->quotation->quotation_number }}</small>
                            @endif
                            @if($order->tags)
                                <br>
                                @foreach($order->tags as $tag)
                                    <span class="badge badge-secondary badge-sm">{{ $tag }}</span>
                                @endforeach
                            @endif
                        </td>
                        <td>{{ $order->customer->name ?? '-' }}</td>
                        <td>{{ $order->order_date->format('d M Y') }}</td>
                        <td>
                            @if($order->commitment_date)
                                {{ $order->commitment_date->format('d M Y') }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $order->salesperson->name ?? '-' }}</td>
                        <td class="text-right">
                            <strong>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong>
                            <br>
                            <small class="text-muted">{{ $order->items->count() }} item(s)</small>
                        </td>
                        <td>
                            @if($order->status == 'quotation')
                                <span class="badge badge-warning">Quotation</span>
                            @elseif($order->status == 'sales_order')
                                <span class="badge badge-success">Sales Order</span>
                                @if($order->confirmed_at)
                                    <br><small class="text-muted">{{ $order->confirmed_at->format('d M Y') }}</small>
                                @endif
                            @elseif($order->status == 'locked')
                                <span class="badge badge-secondary">Locked</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="btn-group" role="group">
                                @if($order->canEdit())
                                    <a href="{{ route('sales.order.edit', $order->id) }}" 
                                       class="btn btn-sm btn-info" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif

                                @if($order->canConfirm())
                                    <button type="button" class="btn btn-sm btn-success" 
                                            onclick="confirmOrder({{ $order->id }})" 
                                            title="Confirm Order">
                                        <i class="fas fa-check"></i>
                                    </button>
                                @endif

                                @if($order->canLock())
                                    <button type="button" class="btn btn-sm btn-warning" 
                                            onclick="lockOrder({{ $order->id }})" 
                                            title="Lock Order">
                                        <i class="fas fa-lock"></i>
                                    </button>
                                @endif

                                @if($order->canDelete())
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            onclick="deleteOrder({{ $order->id }})" 
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">Tidak ada data sales order</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($salesOrders->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
                Showing {{ $salesOrders->firstItem() }} to {{ $salesOrders->lastItem() }} of {{ $salesOrders->total() }} entries
            </div>
            <div>
                {{ $salesOrders->appends(request()->query())->links() }}
            </div>
        </div>
    @endif

    <!-- Delete Form (Hidden) -->
    <form id="delete-form" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <!-- Confirm Form (Hidden) -->
    <form id="confirm-form" method="POST" style="display: none;">
        @csrf
    </form>

    <!-- Lock Form (Hidden) -->
    <form id="lock-form" method="POST" style="display: none;">
        @csrf
    </form>
@endsection

@push('scripts')
<script>
    function deleteOrder(id) {
        if (confirm('Are you sure you want to delete this sales order?')) {
            const form = document.getElementById('delete-form');
            form.action = '{{ url("sales/order") }}/' + id;
            form.submit();
        }
    }

    function confirmOrder(id) {
        if (confirm('Are you sure you want to confirm this sales order?')) {
            const form = document.getElementById('confirm-form');
            form.action = '{{ url("sales/order") }}/' + id + '/confirm';
            form.submit();
        }
    }

    function lockOrder(id) {
        if (confirm('Are you sure you want to lock this sales order? This action is typically done after delivery or invoice.')) {
            const form = document.getElementById('lock-form');
            form.action = '{{ url("sales/order") }}/' + id + '/lock';
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
    
    .badge-sm {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
    
    .btn-group .btn {
        margin: 0 2px;
    }
</style>
@endpush
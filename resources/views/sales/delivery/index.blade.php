@extends('layouts.app')

@section('content')
    <h3>Delivery Orders</h3>

    <div class="row mb-3">
        <div class="col-md-6">
            <a href="{{ route('sales.delivery.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create Delivery Order
            </a>
        </div>
        <div class="col-md-6 text-right">
            <form action="{{ route('sales.delivery.index') }}" method="GET" class="form-inline float-right">
                <div class="form-group mr-2">
                    <select name="status" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="waiting" {{ request('status') == 'waiting' ? 'selected' : '' }}>Waiting</option>
                        <option value="ready" {{ request('status') == 'ready' ? 'selected' : '' }}>Ready</option>
                        <option value="done" {{ request('status') == 'done' ? 'selected' : '' }}>Done</option>
                    </select>
                </div>
                <div class="form-group mr-2">
                    <select name="sales_order_id" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">All Sales Orders</option>
                        @foreach($salesOrders as $so)
                            <option value="{{ $so->id }}" {{ request('sales_order_id') == $so->id ? 'selected' : '' }}>
                                {{ $so->order_number }}
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
                    <th width="10%">DO Number</th>
                    <th width="10%">SO Number</th>
                    <th width="15%">Customer</th>
                    <th width="10%">Delivery Date</th>
                    <th width="10%">Scheduled Date</th>
                    <th width="10%">Carrier</th>
                    <th width="10%">Tracking</th>
                    <th width="10%">Status</th>
                    <th width="10%" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($deliveryOrders as $index => $do)
                    <tr>
                        <td>{{ $deliveryOrders->firstItem() + $index }}</td>
                        <td>
                            <strong>{{ $do->delivery_number }}</strong>
                            <br><small class="text-muted">{{ $do->items->count() }} item(s)</small>
                        </td>
                        <td>
                            <a href="{{ route('sales.order.edit', $do->sales_order_id) }}">
                                {{ $do->salesOrder->order_number }}
                            </a>
                        </td>
                        <td>{{ $do->salesOrder->customer->name ?? '-' }}</td>
                        <td>{{ $do->delivery_date->format('d M Y') }}</td>
                        <td>{{ $do->scheduled_date->format('d M Y') }}</td>
                        <td>{{ $do->carrier ?? '-' }}</td>
                        <td>
                            @if($do->tracking_number)
                                <span class="badge badge-info">{{ $do->tracking_number }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-{{ $do->status_badge }}">
                                {{ ucfirst($do->status) }}
                            </span>
                            @if($do->delivered_at)
                                <br><small class="text-muted">{{ $do->delivered_at->format('d M Y H:i') }}</small>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="btn-group" role="group">
                                <a href="{{ route('sales.delivery.show', $do->id) }}" 
                                   class="btn btn-sm btn-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>

                                @if($do->isWaiting())
                                    <a href="{{ route('sales.delivery.edit', $do->id) }}" 
                                       class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif

                                @if($do->isWaiting() || $do->isReady())
                                    <button type="button" class="btn btn-sm btn-success update-status-btn" 
                                            data-id="{{ $do->id }}" 
                                            data-status="{{ $do->isWaiting() ? 'ready' : 'done' }}"
                                            title="{{ $do->isWaiting() ? 'Mark as Ready' : 'Mark as Done' }}">
                                        <i class="fas fa-check"></i>
                                    </button>
                                @endif

                                @if($do->isWaiting())
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            onclick="deleteDeliveryOrder({{ $do->id }})" 
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center">No delivery orders found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($deliveryOrders->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
                Showing {{ $deliveryOrders->firstItem() }} to {{ $deliveryOrders->lastItem() }} of {{ $deliveryOrders->total() }} entries
            </div>
            <div>
                {{ $deliveryOrders->appends(request()->query())->links() }}
            </div>
        </div>
    @endif

    <!-- Delete Form -->
    <form id="delete-form" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <!-- Status Update Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="status-form" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Update Delivery Status</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="delivered-quantities-container">
                            <!-- Delivered quantities inputs will be added here for "done" status -->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function deleteDeliveryOrder(id) {
    if (confirm('Are you sure you want to delete this delivery order?')) {
        const form = document.getElementById('delete-form');
        form.action = '{{ url("sales/delivery") }}/' + id;
        form.submit();
    }
}

$(document).ready(function() {
    $('.update-status-btn').click(function() {
        const id = $(this).data('id');
        const status = $(this).data('status');
        const form = $('#status-form');
        
        form.attr('action', '{{ url("sales/delivery") }}/' + id + '/status');
        form.append('<input type="hidden" name="status" value="' + status + '">');
        
        if (status === 'done') {
            // Load items for delivered quantities
            $.get('{{ url("sales/delivery") }}/' + id + '/show', function(data) {
                let html = '<p>Enter delivered quantities:</p>';
                data.items.forEach(function(item) {
                    html += '<div class="form-group">';
                    html += '<label>' + item.product_name + ' (Ordered: ' + item.quantity + ')</label>';
                    html += '<input type="number" name="delivered_quantities[' + item.id + ']" ';
                    html += 'class="form-control" step="0.01" min="0" max="' + item.quantity + '" ';
                    html += 'value="' + (item.delivered_quantity || 0) + '" required>';
                    html += '</div>';
                });
                $('#delivered-quantities-container').html(html);
            });
        } else {
            $('#delivered-quantities-container').html(
                '<p>Mark this delivery order as ready for processing?</p>'
            );
        }
        
        $('#statusModal').modal('show');
    });
});
</script>
@endpush
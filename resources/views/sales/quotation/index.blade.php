@extends('layouts.app')

@section('content')
    <h3>Quotation</h3>

    <div class="row mb-3">
        <div class="col-md-6">
            <a href="{{ route('sales.quotation.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create Quotation
            </a>
        </div>
        <div class="col-md-6 text-right">
            <form action="{{ route('sales.quotation.index') }}" method="GET" class="form-inline float-right">
                <div class="form-group mr-2">
                    <select name="status" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="quotation" {{ request('status') == 'quotation' ? 'selected' : '' }}>Quotation</option>
                        <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
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
                    <th width="12%">Quotation</th>
                    <th width="15%">Customer</th>
                    <th width="10%">Order Date</th>
                    <th width="10%">Expiration Date</th>
                    <th width="12%">Salesperson</th>
                    <th width="12%" class="text-right">Total Amount</th>
                    <th width="10%">Status</th>
                    <th width="14%" class="text-center">Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($quotations as $index => $quotation)
                    <tr>
                        <td>{{ $quotations->firstItem() + $index }}</td>
                        <td>
                            <strong>{{ $quotation->quotation_number }}</strong>
                            @if($quotation->tags)
                                <br>
                                @foreach($quotation->tags as $tag)
                                    <span class="badge badge-secondary badge-sm">{{ $tag }}</span>
                                @endforeach
                            @endif
                        </td>
                        <td>{{ $quotation->customer->name ?? '-' }}</td>
                        <td>{{ $quotation->order_date->format('d M Y') }}</td>
                        <td>
                            {{ $quotation->expiration_date->format('d M Y') }}
                            @if($quotation->expiration_date->isPast() && $quotation->status == 'quotation')
                                <br><span class="badge badge-danger badge-sm">Expired</span>
                            @endif
                        </td>
                        <td>{{ $quotation->salesperson->name ?? '-' }}</td>
                        <td class="text-right">
                            <strong>Rp {{ number_format($quotation->total_amount, 0, ',', '.') }}</strong>
                            <br>
                            <small class="text-muted">{{ $quotation->items->count() }} item(s)</small>
                        </td>
                        <td>
                            @if($quotation->status == 'quotation')
                                <span class="badge badge-warning">Quotation</span>
                            @elseif($quotation->status == 'sent')
                                <span class="badge badge-success">Sent</span>
                                <br><small class="text-muted">{{ $quotation->sent_at->format('d M Y') }}</small>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="btn-group" role="group">
                                @if($quotation->canEdit())
                                    <a href="{{ route('sales.quotation.edit', $quotation->id) }}" 
                                       class="btn btn-sm btn-info" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif

                                @if($quotation->status == 'quotation')
                                    <button type="button" class="btn btn-sm btn-success" 
                                            onclick="updateStatus({{ $quotation->id }}, 'sent')" 
                                            title="Mark as Sent">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                @endif

                                @if($quotation->status == 'sent')
                                    <a href="{{ route('sales.quotation.convert', $quotation->id) }}" 
                                       class="btn btn-sm btn-primary" title="Convert to Sales Order">
                                        <i class="fas fa-exchange-alt"></i>
                                    </a>
                                @endif

                                @if($quotation->canDelete())
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            onclick="deleteQuotation({{ $quotation->id }})" 
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">Tidak ada data quotation</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($quotations->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
                Showing {{ $quotations->firstItem() }} to {{ $quotations->lastItem() }} of {{ $quotations->total() }} entries
            </div>
            <div>
                {{ $quotations->appends(request()->query())->links() }}
            </div>
        </div>
    @endif

    <!-- Delete Form (Hidden) -->
    <form id="delete-form" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <!-- Update Status Form (Hidden) -->
    <form id="status-form" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="status" id="status-input">
    </form>
@endsection

@push('scripts')
<script>
    function deleteQuotation(id) {
        if (confirm('Are you sure you want to delete this quotation?')) {
            const form = document.getElementById('delete-form');
            form.action = '{{ url("sales/quotation") }}/' + id;
            form.submit();
        }
    }

    function updateStatus(id, status) {
        const message = status === 'sent' 
            ? 'Are you sure you want to mark this quotation as sent?' 
            : 'Update quotation status?';
        
        if (confirm(message)) {
            const form = document.getElementById('status-form');
            const statusInput = document.getElementById('status-input');
            
            form.action = '{{ url("sales/quotation") }}/' + id + '/status';
            statusInput.value = status;
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
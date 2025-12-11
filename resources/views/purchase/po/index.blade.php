@extends('layouts.app')
@section('title', 'Purchase Order Management')

@section('content')
<div class="card shadow">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Purchase Order (PO)</h5>
        <a href="{{ route('purchase.po.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> New PO
        </a>
    </div>
    
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th width="50">No</th>
                    <th>PO Number</th>
                    <th>Vendor</th>
                    <th>Order Date</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                    <th width="250" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchaseOrders as $index => $po)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $po->po_number }}</strong>
                        @if($po->rfq)
                            <br><small class="text-muted">From: {{ $po->rfq->rfq_number }}</small>
                        @endif
                        @if($po->vendorBills()->count() > 0)
                            <br><small class="text-success">
                                <i class="fas fa-file-invoice"></i> Has Bill
                            </small>
                        @endif
                    </td>
                    <td>{{ $po->vendor->name }}</td>
                    <td>{{ date('d/m/Y', strtotime($po->order_date)) }}</td>
                    <td>Rp {{ number_format($po->total_amount, 2) }}</td>
                    <td>
                        @php
                            $statusColors = [
                                'draft' => 'secondary',
                                'sent' => 'info',
                                'confirmed' => 'warning',
                                'received' => 'success',
                                'cancelled' => 'danger'
                            ];
                            $color = $statusColors[$po->status] ?? 'secondary';
                        @endphp
                        <span class="badge bg-{{ $color }}">{{ strtoupper($po->status) }}</span>
                    </td>
                    <td class="text-center">
                        <!-- Status Update Buttons -->
                        <div class="btn-group btn-group-sm" role="group">
                            @if($po->status == 'draft')
                                <form action="{{ route('purchase.po.status', $po->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="status" value="sent">
                                    <button type="submit" class="btn btn-info btn-sm" 
                                            onclick="return confirm('Mark PO as SENT?')">
                                        <i class="fas fa-paper-plane"></i> Send
                                    </button>
                                </form>
                            @endif
                            
                            @if($po->status == 'sent')
                                <form action="{{ route('purchase.po.status', $po->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="status" value="confirmed">
                                    <button type="submit" class="btn btn-warning btn-sm" 
                                            onclick="return confirm('Confirm this PO?')">
                                        <i class="fas fa-check-circle"></i> Confirm
                                    </button>
                                </form>
                            @endif
                            
                            @if($po->status == 'confirmed')
                                <form action="{{ route('purchase.po.status', $po->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="status" value="received">
                                    <button type="submit" class="btn btn-success btn-sm" 
                                            onclick="return confirm('Mark PO as RECEIVED?')">
                                        <i class="fas fa-box"></i> Received
                                    </button>
                                </form>
                            @endif
                            
                            @if($po->status == 'received' && $po->vendorBills()->count() == 0)
                                <a href="{{ route('purchase.po.convert', $po->id) }}" 
                                   class="btn btn-warning btn-sm"
                                   onclick="return confirm('Convert this PO to Vendor Bill?')">
                                    <i class="fas fa-file-invoice-dollar"></i> Create Bill
                                </a>
                            @endif
                        </div>
                        
                        <!-- Edit, Delete and View Bills -->
                        <div class="mt-1">
                            <a href="{{ route('purchase.po.edit', $po->id) }}" 
                               class="btn btn-sm btn-outline-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            
                            <form action="{{ route('purchase.po.destroy', $po->id) }}" 
                                  method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                        onclick="return confirm('Delete this PO?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            
                            @if($po->rfq)
                                <a href="{{ route('purchase.rfq.edit', $po->rfq->id) }}" 
                                   class="btn btn-sm btn-outline-info" title="View RFQ">
                                    <i class="fas fa-eye"></i> RFQ
                                </a>
                            @endif
                            
                            @if($po->vendorBills()->count() > 0)
                                <a href="{{ route('purchase.vendor-bill.edit', $po->vendorBills()->first()->id) }}" 
                                   class="btn btn-sm btn-outline-success" title="View Bill">
                                    <i class="fas fa-file-invoice"></i> Bill
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">No Purchase Orders found</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{ $purchaseOrders->links() }}
    </div>
</div>
@endsection
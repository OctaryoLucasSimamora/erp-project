@extends('layouts.app')
@section('title', 'RFQ Management')

@section('content')
<div class="card shadow">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Request for Quotation (RFQ)</h5>
        <a href="{{ route('purchase.rfq.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> New RFQ
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
                    <th>RFQ Number</th>
                    <th>Vendor</th>
                    <th>Deadline</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                    <th width="220" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rfqs as $index => $rfq)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $rfq->rfq_number }}</strong>
                        @if($rfq->purchaseOrder)
                            <br><small class="text-success">
                                <i class="fas fa-check-circle"></i> Converted to PO
                            </small>
                        @endif
                    </td>
                    <td>{{ $rfq->vendor->name }}</td>
                    <td>{{ $rfq->deadline ? date('d/m/Y', strtotime($rfq->deadline)) : '-' }}</td>
                    <td>Rp {{ number_format($rfq->total_amount, 2) }}</td>
                    <td>
                        @php
                            $statusColors = [
                                'draft' => 'secondary',
                                'sent' => 'info',
                                'cancelled' => 'danger',
                                'done' => 'success'
                            ];
                            $color = $statusColors[$rfq->status] ?? 'secondary';
                        @endphp
                        <span class="badge bg-{{ $color }}">{{ strtoupper($rfq->status) }}</span>
                    </td>
                    <td class="text-center">
                        <!-- Status Update Buttons -->
                        <div class="btn-group btn-group-sm" role="group">
                            @if($rfq->status == 'draft')
                                <form action="{{ route('purchase.rfq.status', $rfq->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="status" value="sent">
                                    <button type="submit" class="btn btn-info btn-sm" 
                                            onclick="return confirm('Mark RFQ as SENT?')">
                                        <i class="fas fa-paper-plane"></i> Send
                                    </button>
                                </form>
                            @endif
                            
                            @if($rfq->status == 'sent')
                                <form action="{{ route('purchase.rfq.status', $rfq->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="status" value="done">
                                    <button type="submit" class="btn btn-success btn-sm" 
                                            onclick="return confirm('Mark RFQ as DONE?')">
                                        <i class="fas fa-check"></i> Done
                                    </button>
                                </form>
                            @endif
                            
                            @if($rfq->status == 'done' && !$rfq->purchaseOrder)
                                <a href="{{ route('purchase.rfq.convert', $rfq->id) }}" 
                                   class="btn btn-warning btn-sm"
                                   onclick="return confirm('Convert this RFQ to Purchase Order?')">
                                    <i class="fas fa-exchange-alt"></i> Convert to PO
                                </a>
                            @endif
                        </div>
                        
                        <!-- Edit and Delete -->
                        <div class="mt-1">
                            <a href="{{ route('purchase.rfq.edit', $rfq->id) }}" 
                               class="btn btn-sm btn-outline-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            
                            <form action="{{ route('purchase.rfq.destroy', $rfq->id) }}" 
                                  method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                        onclick="return confirm('Delete this RFQ?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            
                            @if($rfq->purchaseOrder)
                                <a href="{{ route('purchase.po.edit', $rfq->purchaseOrder->id) }}" 
                                   class="btn btn-sm btn-outline-info" title="View PO">
                                    <i class="fas fa-eye"></i> PO
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">No RFQs found</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{ $rfqs->links() }}
    </div>
</div>
@endsection
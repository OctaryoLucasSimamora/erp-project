@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Delivery Order - {{ $deliveryOrder->delivery_number }}</h3>
        <div>
            <a href="{{ route('sales.delivery.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
            @if($deliveryOrder->isWaiting())
                <a href="{{ route('sales.delivery.edit', $deliveryOrder->id) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit
                </a>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Delivery Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Delivery Number:</th>
                                    <td>{{ $deliveryOrder->delivery_number }}</td>
                                </tr>
                                <tr>
                                    <th>Sales Order:</th>
                                    <td>
                                        <a href="{{ route('sales.order.edit', $deliveryOrder->sales_order_id) }}">
                                            {{ $deliveryOrder->salesOrder->order_number }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Customer:</th>
                                    <td>{{ $deliveryOrder->salesOrder->customer->name }}</td>
                                </tr>
                                <tr>
                                    <th>Delivery Address:</th>
                                    <td>{{ nl2br($deliveryOrder->delivery_address) }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Status:</th>
                                    <td>
                                        <span class="badge badge-{{ $deliveryOrder->status_badge }}">
                                            {{ ucfirst($deliveryOrder->status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Delivery Date:</th>
                                    <td>{{ $deliveryOrder->delivery_date->format('d M Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Scheduled Date:</th>
                                    <td>{{ $deliveryOrder->scheduled_date->format('d M Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Carrier:</th>
                                    <td>{{ $deliveryOrder->carrier ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Tracking Number:</th>
                                    <td>{{ $deliveryOrder->tracking_number ?? '-' }}</td>
                                </tr>
                                @if($deliveryOrder->delivered_at)
                                    <tr>
                                        <th>Delivered At:</th>
                                        <td>{{ $deliveryOrder->delivered_at->format('d M Y H:i') }}</td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                    
                    @if($deliveryOrder->notes)
                        <div class="row mt-3">
                            <div class="col-12">
                                <h6>Notes:</h6>
                                <p>{{ $deliveryOrder->notes }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Summary</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th>Total Items:</th>
                            <td class="text-right">{{ $deliveryOrder->total_items }}</td>
                        </tr>
                        <tr>
                            <th>Total Delivered:</th>
                            <td class="text-right">{{ $deliveryOrder->total_delivered }}</td>
                        </tr>
                        <tr>
                            <th>Created By:</th>
                            <td class="text-right">{{ $deliveryOrder->createdBy->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Created At:</th>
                            <td class="text-right">{{ $deliveryOrder->created_at->format('d M Y H:i') }}</td>
                        </tr>
                        @if($deliveryOrder->updated_by)
                            <tr>
                                <th>Last Updated By:</th>
                                <td class="text-right">{{ $deliveryOrder->updatedBy->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Updated At:</th>
                                <td class="text-right">{{ $deliveryOrder->updated_at->format('d M Y H:i') }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">Delivery Items</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Description</th>
                            <th class="text-right">Quantity</th>
                            <th class="text-right">Delivered</th>
                            <th class="text-right">Remaining</th>
                            <th class="text-right">Unit Price</th>
                            <th class="text-right">Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($deliveryOrder->items as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item->product->name }}</td>
                                <td>{{ $item->description }}</td>
                                <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                                <td class="text-right">{{ number_format($item->delivered_quantity, 2) }}</td>
                                <td class="text-right">{{ number_format($item->remaining_quantity, 2) }}</td>
                                <td class="text-right">Rp {{ number_format($item->unit_price, 0) }}</td>
                                <td class="text-right">Rp {{ number_format($item->quantity * $item->unit_price, 0) }}</td>
                                <td>
                                    @if($item->isFullyDelivered())
                                        <span class="badge badge-success">Fully Delivered</span>
                                    @elseif($item->delivered_quantity > 0)
                                        <span class="badge badge-warning">Partially Delivered</span>
                                    @else
                                        <span class="badge badge-secondary">Pending</span>
                                    @endif
                                </td>
                            </tr>
                            @if($item->notes)
                                <tr>
                                    <td colspan="9" class="text-muted">
                                        <small><strong>Notes:</strong> {{ $item->notes }}</small>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
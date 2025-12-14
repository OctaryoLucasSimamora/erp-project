@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Customer - {{ $customer->name }}</h3>
        <div>
            <a href="{{ route('sales.customer.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
            <a href="{{ route('sales.customer.edit', $customer->id) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-body text-center">
                    @if($customer->image)
                        <img src="{{ Storage::url($customer->image) }}" alt="{{ $customer->name }}" 
                             class="rounded-circle mb-3" width="150" height="150" style="object-fit: cover;">
                    @else
                        <div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center mb-3" 
                             style="width: 150px; height: 150px; font-size: 48px;">
                            {{ strtoupper(substr($customer->name, 0, 1)) }}
                        </div>
                    @endif
                    <h4>{{ $customer->name }}</h4>
                    @if($customer->title)
                        <p class="text-muted">{{ $customer->title }}</p>
                    @endif
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Contact Information</h5>
                </div>
                <div class="card-body">
                    @if($customer->email || $customer->phone || $customer->mobile || $customer->address)
                        <table class="table table-sm table-borderless">
                            @if($customer->email)
                                <tr>
                                    <th width="30%"><i class="fas fa-envelope text-muted mr-1"></i> Email:</th>
                                    <td>{{ $customer->email }}</td>
                                </tr>
                            @endif
                            @if($customer->phone)
                                <tr>
                                    <th><i class="fas fa-phone text-muted mr-1"></i> Phone:</th>
                                    <td>{{ $customer->phone }}</td>
                                </tr>
                            @endif
                            @if($customer->mobile)
                                <tr>
                                    <th><i class="fas fa-mobile-alt text-muted mr-1"></i> Mobile:</th>
                                    <td>{{ $customer->mobile }}</td>
                                </tr>
                            @endif
                            @if($customer->address)
                                <tr>
                                    <th><i class="fas fa-map-marker-alt text-muted mr-1"></i> Address:</th>
                                    <td>{{ $customer->address }}</td>
                                </tr>
                            @endif
                        </table>
                    @else
                        <p class="text-muted text-center mb-0">No contact information available</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Company Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        @if($customer->company)
                            <tr>
                                <th width="30%">Company:</th>
                                <td>{{ $customer->company }}</td>
                            </tr>
                        @endif
                        @if($customer->position)
                            <tr>
                                <th>Position:</th>
                                <td>{{ $customer->position }}</td>
                            </tr>
                        @endif
                        <tr>
                            <th>Created At:</th>
                            <td>{{ $customer->created_at ? $customer->created_at->format('d M Y H:i') : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Updated At:</th>
                            <td>{{ $customer->updated_at ? $customer->updated_at->format('d M Y H:i') : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Total Quotations:</th>
                            <td><span class="badge badge-info">{{ $customer->quotations->count() }}</span></td>
                        </tr>
                    </table>
                </div>
            </div>

            @if($customer->quotations->count() > 0)
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">Recent Quotations (Last 10)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Quotation #</th>
                                        <th>Date</th>
                                        <th>Total Amount</th>
                                        <th>Status</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($customer->quotations as $quotation)
                                        <tr>
                                            <td>{{ $quotation->quotation_number ?? '-' }}</td>
                                            <td>
                                                @if($quotation->quotation_date)
                                                    {{ \Carbon\Carbon::parse($quotation->quotation_date)->format('d M Y') }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>Rp {{ number_format($quotation->total_amount ?? 0, 0, ',', '.') }}</td>
                                            <td>
                                                @if($quotation->status == 'draft')
                                                    <span class="badge badge-warning">Draft</span>
                                                @elseif($quotation->status == 'sent')
                                                    <span class="badge badge-info">Sent</span>
                                                @elseif($quotation->status == 'confirmed')
                                                    <span class="badge badge-success">Confirmed</span>
                                                @elseif($quotation->status == 'cancelled')
                                                    <span class="badge badge-danger">Cancelled</span>
                                                @else
                                                    <span class="badge badge-secondary">{{ ucfirst($quotation->status) }}</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('sales.quotation.edit', $quotation->id) }}" 
                                                   class="btn btn-sm btn-info" title="View Quotation">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        @if($customer->quotations->count() >= 10)
                            <div class="text-center mt-2">
                                <small class="text-muted">Showing last 10 quotations only</small>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">Quotations</h5>
                    </div>
                    <div class="card-body text-center text-muted">
                        <i class="fas fa-file-invoice fa-3x mb-3"></i>
                        <p>No quotations found for this customer</p>
                        <a href="{{ route('sales.quotation.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Create Quotation
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('styles')
<style>
    .table-borderless th,
    .table-borderless td {
        border: none;
        padding: 0.5rem 0;
    }
    
    .rounded-circle {
        border: 3px solid #f0f0f0;
    }
</style>
@endpush
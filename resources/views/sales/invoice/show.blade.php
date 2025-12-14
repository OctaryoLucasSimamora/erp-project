@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Invoice - {{ $invoice->invoice_number }}</h3>
        <div>
            <a href="{{ route('sales.invoice.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
            @if($invoice->isDraft())
                <a href="{{ route('sales.invoice.edit', $invoice->id) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit
                </a>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Invoice Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Invoice Number:</th>
                                    <td>{{ $invoice->invoice_number }}</td>
                                </tr>
                                <tr>
                                    <th>Customer:</th>
                                    <td>{{ $invoice->customer->name }}</td>
                                </tr>
                                <tr>
                                    <th>Invoice Date:</th>
                                    <td>{{ $invoice->invoice_date->format('d M Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Due Date:</th>
                                    <td>{{ $invoice->due_date->format('d M Y') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Status:</th>
                                    <td>
                                        <span class="badge badge-{{ $invoice->status_badge }}">
                                            {{ ucfirst($invoice->status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Sales Order:</th>
                                    <td>
                                        @if($invoice->salesOrder)
                                            <a href="{{ route('sales.order.edit', $invoice->sales_order_id) }}">
                                                {{ $invoice->salesOrder->order_number }}
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Delivery Order:</th>
                                    <td>
                                        @if($invoice->deliveryOrder)
                                            <a href="{{ route('sales.delivery.show', $invoice->delivery_order_id) }}">
                                                {{ $invoice->deliveryOrder->delivery_number }}
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Journal:</th>
                                    <td>{{ $invoice->journal ?? '-' }}</td>
                                </tr>
                                @if($invoice->posted_at)
                                    <tr>
                                        <th>Posted At:</th>
                                        <td>{{ $invoice->posted_at->format('d M Y H:i') }}</td>
                                    </tr>
                                @endif
                                @if($invoice->paid_at)
                                    <tr>
                                        <th>Paid At:</th>
                                        <td>{{ $invoice->paid_at->format('d M Y H:i') }}</td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                    
                    @if($invoice->payment_terms)
                        <div class="row mt-3">
                            <div class="col-12">
                                <h6>Payment Terms:</h6>
                                <p>{{ $invoice->payment_terms }}</p>
                            </div>
                        </div>
                    @endif
                    
                    @if($invoice->notes)
                        <div class="row mt-3">
                            <div class="col-12">
                                <h6>Notes:</h6>
                                <p>{{ $invoice->notes }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Invoice Summary</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th>Subtotal:</th>
                            <td class="text-right">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th>Discount:</th>
                            <td class="text-right">Rp {{ number_format($invoice->discount_amount, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th>Tax:</th>
                            <td class="text-right">Rp {{ number_format($invoice->tax_amount, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="table-primary">
                            <th>Total Amount:</th>
                            <th class="text-right">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</th>
                        </tr>
                        <tr>
                            <th>Created By:</th>
                            <td class="text-right">{{ $invoice->createdBy->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Created At:</th>
                            <td class="text-right">{{ $invoice->created_at->format('d M Y H:i') }}</td>
                        </tr>
                        @if($invoice->updated_by)
                            <tr>
                                <th>Last Updated By:</th>
                                <td class="text-right">{{ $invoice->updatedBy->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Updated At:</th>
                                <td class="text-right">{{ $invoice->updated_at->format('d M Y H:i') }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">Invoice Items</h5>
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
                            <th class="text-right">Unit Price</th>
                            <th class="text-right">Discount %</th>
                            <th class="text-right">Tax %</th>
                            <th class="text-right">Subtotal</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item->product->name }}</td>
                                <td>{{ $item->description }}</td>
                                <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                                <td class="text-right">Rp {{ number_format($item->unit_price, 0) }}</td>
                                <td class="text-right">{{ number_format($item->discount_percent, 2) }}%</td>
                                <td class="text-right">{{ number_format($item->tax_percent, 2) }}%</td>
                                <td class="text-right">Rp {{ number_format($item->subtotal, 0) }}</td>
                                <td class="text-right">Rp {{ number_format($item->total, 0) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($invoice->journalItems->count() > 0)
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Journal Entries</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>Account Code</th>
                                <th>Account Name</th>
                                <th class="text-right">Debit</th>
                                <th class="text-right">Credit</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->journalItems as $index => $journal)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $journal->account_code }}</td>
                                    <td>{{ $journal->account_name }}</td>
                                    <td class="text-right">@if($journal->debit > 0) Rp {{ number_format($journal->debit, 0) }} @endif</td>
                                    <td class="text-right">@if($journal->credit > 0) Rp {{ number_format($journal->credit, 0) }} @endif</td>
                                    <td>{{ $journal->description }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-primary">
                            <tr>
                                <th colspan="3" class="text-right">Total:</th>
                                <th class="text-right">Rp {{ number_format($invoice->journalItems->sum('debit'), 0) }}</th>
                                <th class="text-right">Rp {{ number_format($invoice->journalItems->sum('credit'), 0) }}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    @endif
@endsection
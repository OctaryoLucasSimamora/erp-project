@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Payment - {{ $payment->payment_number }}</h3>
        <div>
            <a href="{{ route('sales.payment.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
            @if($payment->isDraft())
                <a href="{{ route('sales.payment.edit', $payment->id) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit
                </a>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Payment Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Payment Number:</th>
                                    <td>{{ $payment->payment_number }}</td>
                                </tr>
                                <tr>
                                    <th>Customer:</th>
                                    <td>{{ $payment->customer->name }}</td>
                                </tr>
                                <tr>
                                    <th>Payment Date:</th>
                                    <td>{{ $payment->payment_date->format('d M Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Amount:</th>
                                    <td><strong>Rp {{ number_format($payment->amount, 0, ',', '.') }}</strong></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Status:</th>
                                    <td>
                                        <span class="badge badge-{{ $payment->status_badge }}">
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Payment Method:</th>
                                    <td>{{ $payment->payment_method_label }}</td>
                                </tr>
                                <tr>
                                    <th>Allocated Amount:</th>
                                    <td>Rp {{ number_format($payment->allocated_amount, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <th>Remaining Amount:</th>
                                    <td>Rp {{ number_format($payment->remaining_amount, 0, ',', '.') }}</td>
                                </tr>
                                @if($payment->posted_at)
                                    <tr>
                                        <th>Posted At:</th>
                                        <td>{{ $payment->posted_at->format('d M Y H:i') }}</td>
                                    </tr>
                                @endif
                                @if($payment->reconciled_at)
                                    <tr>
                                        <th>Reconciled At:</th>
                                        <td>{{ $payment->reconciled_at->format('d M Y H:i') }}</td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                    
                    @if($payment->memo)
                        <div class="row mt-3">
                            <div class="col-12">
                                <h6>Memo:</h6>
                                <p>{{ $payment->memo }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Payment Summary</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th>Total Amount:</th>
                            <td class="text-right">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th>Allocated:</th>
                            <td class="text-right">Rp {{ number_format($payment->allocated_amount, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th>Remaining:</th>
                            <td class="text-right">Rp {{ number_format($payment->remaining_amount, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th>Created By:</th>
                            <td class="text-right">{{ $payment->createdBy->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Created At:</th>
                            <td class="text-right">{{ $payment->created_at->format('d M Y H:i') }}</td>
                        </tr>
                        @if($payment->updated_by)
                            <tr>
                                <th>Last Updated By:</th>
                                <td class="text-right">{{ $payment->updatedBy->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Updated At:</th>
                                <td class="text-right">{{ $payment->updated_at->format('d M Y H:i') }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    @if($payment->paymentInvoices->count() > 0)
        <div class="card mb-3">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">Allocated Invoices</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>Invoice Number</th>
                                <th>Invoice Date</th>
                                <th>Due Date</th>
                                <th class="text-right">Invoice Amount</th>
                                <th class="text-right">Allocated Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payment->paymentInvoices as $index => $paymentInvoice)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <a href="{{ route('sales.invoice.show', $paymentInvoice->customerInvoice->id) }}">
                                            {{ $paymentInvoice->customerInvoice->invoice_number }}
                                        </a>
                                    </td>
                                    <td>{{ $paymentInvoice->customerInvoice->invoice_date->format('d M Y') }}</td>
                                    <td>{{ $paymentInvoice->customerInvoice->due_date->format('d M Y') }}</td>
                                    <td class="text-right">Rp {{ number_format($paymentInvoice->customerInvoice->total_amount, 0) }}</td>
                                    <td class="text-right">Rp {{ number_format($paymentInvoice->amount, 0) }}</td>
                                    <td>
                                        <span class="badge badge-{{ $paymentInvoice->customerInvoice->status_badge }}">
                                            {{ ucfirst($paymentInvoice->customerInvoice->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-primary">
                            <tr>
                                <th colspan="5" class="text-right">Total Allocated:</th>
                                <th class="text-right">Rp {{ number_format($payment->allocated_amount, 0) }}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @if($payment->journalItems->count() > 0)
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
                            @foreach($payment->journalItems as $index => $journal)
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
                                <th class="text-right">Rp {{ number_format($payment->journalItems->sum('debit'), 0) }}</th>
                                <th class="text-right">Rp {{ number_format($payment->journalItems->sum('credit'), 0) }}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    @endif
@endsection
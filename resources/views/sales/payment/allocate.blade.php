@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Allocate Payment - {{ $payment->payment_number }}</h3>
        <div>
            <a href="{{ route('sales.payment.show', $payment->id) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Payment
            </a>
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

    <div class="card mb-3">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Payment Summary</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <strong>Customer:</strong> {{ $payment->customer->name }}
                </div>
                <div class="col-md-4">
                    <strong>Payment Date:</strong> {{ $payment->payment_date->format('d M Y') }}
                </div>
                <div class="col-md-4">
                    <strong>Payment Method:</strong> {{ $payment->payment_method_label }}
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-4">
                    <strong>Total Amount:</strong> Rp {{ number_format($payment->amount, 0, ',', '.') }}
                </div>
                <div class="col-md-4">
                    <strong>Already Allocated:</strong> Rp {{ number_format($payment->allocated_amount, 0, ',', '.') }}
                </div>
                <div class="col-md-4">
                    <strong>Available for Allocation:</strong> 
                    <span class="text-success">Rp {{ number_format($payment->remaining_amount, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('sales.payment.allocate.store', $payment->id) }}" method="POST" id="allocate-form">
        @csrf

        <div class="card mb-3">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">Allocate to Invoices</h5>
            </div>
            <div class="card-body">
                @if($invoices->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="15%">Invoice Number</th>
                                    <th width="10%">Invoice Date</th>
                                    <th width="10%">Due Date</th>
                                    <th width="12%" class="text-right">Total Amount</th>
                                    <th width="12%" class="text-right">Already Paid</th>
                                    <th width="12%" class="text-right">Remaining</th>
                                    <th width="12%" class="text-right">Allocate Amount</th>
                                    <th width="12%" class="text-right">New Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoices as $index => $invoice)
                                    @php
                                        $allocatedAmount = $payment->paymentInvoices->where('customer_invoice_id', $invoice->id)->first()->amount ?? 0;
                                        $totalAllocated = \App\Models\PaymentInvoice::where('customer_invoice_id', $invoice->id)->sum('amount');
                                        $remainingAmount = $invoice->total_amount - $totalAllocated;
                                    @endphp
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <a href="{{ route('sales.invoice.show', $invoice->id) }}">
                                                {{ $invoice->invoice_number }}
                                            </a>
                                        </td>
                                        <td>{{ $invoice->invoice_date->format('d M Y') }}</td>
                                        <td>{{ $invoice->due_date->format('d M Y') }}</td>
                                        <td class="text-right">Rp {{ number_format($invoice->total_amount, 0) }}</td>
                                        <td class="text-right">Rp {{ number_format($totalAllocated, 0) }}</td>
                                        <td class="text-right">
                                            <span class="text-info">Rp {{ number_format($remainingAmount, 0) }}</span>
                                        </td>
                                        <td>
                                            <input type="hidden" name="allocations[{{ $index }}][invoice_id]" value="{{ $invoice->id }}">
                                            <input type="number" name="allocations[{{ $index }}][amount]" 
                                                   class="form-control form-control-sm allocate-amount" 
                                                   step="0.01" min="0" max="{{ $remainingAmount }}"
                                                   value="{{ old('allocations.' . $index . '.amount', $allocatedAmount) }}">
                                        </td>
                                        <td class="text-right new-balance">
                                            Rp 0
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-primary">
                                <tr>
                                    <th colspan="7" class="text-right">Total Allocation:</th>
                                    <th class="text-right" id="total-allocation-display">Rp 0</th>
                                    <th class="text-right" id="remaining-allocation-display">Rp 0</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="alert alert-info mt-3">
                        <strong>Note:</strong> Total allocation cannot exceed available amount: 
                        <strong>Rp {{ number_format($payment->remaining_amount, 0, ',', '.') }}</strong>
                    </div>
                @else
                    <div class="alert alert-warning">
                        No unpaid invoices found for this customer.
                    </div>
                @endif
            </div>
        </div>

        <div class="text-right">
            <a href="{{ route('sales.payment.show', $payment->id) }}" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
            @if($invoices->count() > 0)
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Allocation
                </button>
            @endif
        </div>
    </form>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    calculateAllocation();

    $('.allocate-amount').on('input', function() {
        calculateAllocation();
    });

    function calculateAllocation() {
        let totalAllocation = 0;
        
        $('.allocate-amount').each(function() {
            const amount = parseFloat($(this).val()) || 0;
            const max = parseFloat($(this).attr('max')) || 0;
            
            // Validate input
            if (amount > max) {
                $(this).val(max);
                amount = max;
            }
            
            totalAllocation += amount;
            
            // Calculate new balance
            const row = $(this).closest('tr');
            const totalAmount = parseFloat(row.find('td:eq(4)').text().replace(/[^0-9.-]+/g, "")) || 0;
            const alreadyPaid = parseFloat(row.find('td:eq(5)').text().replace(/[^0-9.-]+/g, "")) || 0;
            const newBalance = totalAmount - (alreadyPaid + amount);
            
            row.find('.new-balance').text('Rp ' + formatNumber(newBalance));
        });
        
        const availableAmount = {{ $payment->remaining_amount }};
        const remainingAmount = availableAmount - totalAllocation;
        
        $('#total-allocation-display').text('Rp ' + formatNumber(totalAllocation));
        $('#remaining-allocation-display').text('Rp ' + formatNumber(remainingAmount));
        
        // Highlight if over-allocated
        if (totalAllocation > availableAmount) {
            $('#total-allocation-display').addClass('text-danger');
            $('#remaining-allocation-display').addClass('text-danger');
        } else {
            $('#total-allocation-display').removeClass('text-danger');
            $('#remaining-allocation-display').removeClass('text-danger');
        }
    }

    function formatNumber(num) {
        return Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    $('#allocate-form').submit(function(e) {
        const availableAmount = {{ $payment->remaining_amount }};
        let totalAllocation = 0;
        
        $('.allocate-amount').each(function() {
            totalAllocation += parseFloat($(this).val()) || 0;
        });
        
        if (totalAllocation > availableAmount) {
            e.preventDefault();
            alert('Total allocation cannot exceed available amount.');
            return false;
        }
        
        if (totalAllocation <= 0) {
            e.preventDefault();
            alert('Please allocate at least some amount.');
            return false;
        }
    });
});
</script>
@endpush
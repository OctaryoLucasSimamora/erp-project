@extends('layouts.app')
@section('title', 'Process Payment')

@section('content')
<div class="card shadow">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">
            <i class="fas fa-money-bill-wave"></i> Process Payment - {{ $vendorBill->bill_number }}
        </h5>
    </div>
    
    <div class="card-body">
        @if($vendorBill->status == 'paid')
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> This bill is already PAID.
        </div>
        @endif
        
        @if($vendorBill->status == 'cancelled')
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> This bill is CANCELLED and cannot be paid.
        </div>
        @endif
        
        <form action="{{ route('purchase.vendor-bill.payment.process', $vendorBill->id) }}" method="POST" id="paymentForm">
            @csrf
            
            <!-- Bill Information -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-file-invoice"></i> Bill Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="40%"><strong>Bill Number:</strong></td>
                                    <td>{{ $vendorBill->bill_number }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Vendor:</strong></td>
                                    <td>{{ $vendorBill->vendor->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Bill Date:</strong></td>
                                    <td>{{ date('d/m/Y', strtotime($vendorBill->bill_date)) }}</td>
                                </tr>
                                @if($vendorBill->due_date)
                                <tr>
                                    <td><strong>Due Date:</strong></td>
                                    <td>
                                        {{ date('d/m/Y', strtotime($vendorBill->due_date)) }}
                                        @if($vendorBill->due_date < date('Y-m-d'))
                                            <span class="badge bg-danger ms-2">OVERDUE</span>
                                        @endif
                                    </td>
                                </tr>
                                @endif
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="40%"><strong>Total Amount:</strong></td>
                                    <td class="text-end">Rp {{ number_format($vendorBill->total_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Paid Amount:</strong></td>
                                    <td class="text-end">Rp {{ number_format($vendorBill->paid_amount, 2) }}</td>
                                </tr>
                                <tr class="table-active">
                                    <td><strong>Balance Due:</strong></td>
                                    <td class="text-end">
                                        <h4 class="text-success mb-0">Rp {{ number_format($vendorBill->balance, 2) }}</h4>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    @if($vendorBill->purchaseOrder)
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="fas fa-link"></i> Related to PO: 
                            <a href="{{ route('purchase.po.edit', $vendorBill->purchaseOrder->id) }}">
                                {{ $vendorBill->purchaseOrder->po_number }}
                            </a>
                        </small>
                    </div>
                    @endif
                </div>
            </div>
            
            <!-- Payment Details -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-credit-card"></i> Payment Details</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Payment Method *</label>
                                <select name="payment_method" class="form-control @error('payment_method') is-invalid @enderror" required
                                        {{ $vendorBill->status == 'paid' || $vendorBill->status == 'cancelled' ? 'disabled' : '' }}>
                                    <option value="">Select Payment Method</option>
                                    <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="check" {{ old('payment_method') == 'check' ? 'selected' : '' }}>Check</option>
                                    <option value="credit_card" {{ old('payment_method') == 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                                    <option value="debit_card" {{ old('payment_method') == 'debit_card' ? 'selected' : '' }}>Debit Card</option>
                                    <option value="other" {{ old('payment_method') == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('payment_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Payment Date *</label>
                                <input type="date" name="payment_date" 
                                       class="form-control @error('payment_date') is-invalid @enderror" 
                                       value="{{ old('payment_date', date('Y-m-d')) }}" required
                                       {{ $vendorBill->status == 'paid' || $vendorBill->status == 'cancelled' ? 'disabled' : '' }}>
                                @error('payment_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Amount *</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="amount" 
                                           class="form-control @error('amount') is-invalid @enderror" 
                                           step="0.01" min="0.01" max="{{ $vendorBill->balance }}" 
                                           value="{{ old('amount', $vendorBill->balance) }}" required
                                           {{ $vendorBill->status == 'paid' || $vendorBill->status == 'cancelled' ? 'disabled' : '' }}
                                           id="amountInput">
                                </div>
                                <div class="form-text">
                                    Maximum: Rp {{ number_format($vendorBill->balance, 2) }}
                                    @if($vendorBill->balance < $vendorBill->total_amount)
                                        <span class="text-warning ms-2">
                                            <i class="fas fa-exclamation-circle"></i> Partial payment
                                        </span>
                                    @endif
                                </div>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Reference Number</label>
                                <input type="text" name="reference" 
                                       class="form-control @error('reference') is-invalid @enderror" 
                                       value="{{ old('reference') }}" 
                                       placeholder="TRX-001, Check No., etc."
                                       {{ $vendorBill->status == 'paid' || $vendorBill->status == 'cancelled' ? 'disabled' : '' }}>
                                @error('reference')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Memo / Notes</label>
                        <textarea name="memo" class="form-control @error('memo') is-invalid @enderror" 
                                  rows="3" placeholder="Payment description or notes"
                                  {{ $vendorBill->status == 'paid' || $vendorBill->status == 'cancelled' ? 'disabled' : '' }}>{{ old('memo') }}</textarea>
                        @error('memo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Payment Type Options -->
                    <div class="mb-3">
                        <label class="form-label">Payment Type</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_type" 
                                   id="fullPayment" value="full" checked
                                   {{ $vendorBill->status == 'paid' || $vendorBill->status == 'cancelled' ? 'disabled' : '' }}>
                            <label class="form-check-label" for="fullPayment">
                                Full Payment (Rp {{ number_format($vendorBill->balance, 2) }})
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_type" 
                                   id="partialPayment" value="partial"
                                   {{ $vendorBill->status == 'paid' || $vendorBill->status == 'cancelled' ? 'disabled' : '' }}>
                            <label class="form-check-label" for="partialPayment">
                                Partial Payment
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="d-flex justify-content-between">
                <div>
                    <a href="{{ route('purchase.vendor-bill.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Bills
                    </a>
                    <a href="{{ route('purchase.vendor-bill.edit', $vendorBill->id) }}" class="btn btn-outline-warning ms-2">
                        <i class="fas fa-edit"></i> Edit Bill
                    </a>
                </div>
                
                @if($vendorBill->status != 'paid' && $vendorBill->status != 'cancelled')
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-check-circle"></i> Process Payment
                </button>
                @else
                <button type="button" class="btn btn-secondary" disabled>
                    <i class="fas fa-ban"></i> Cannot Process Payment
                </button>
                @endif
            </div>
        </form>
        
        <!-- Payment History -->
        @if($vendorBill->payments()->count() > 0)
        <div class="card mt-4">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fas fa-history"></i> Payment History</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Method</th>
                                <th>Amount</th>
                                <th>Reference</th>
                                <th>Memo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vendorBill->payments as $payment)
                            <tr>
                                <td>{{ date('d/m/Y', strtotime($payment->payment_date)) }}</td>
                                <td>
                                    <span class="badge bg-info">
                                        {{ ucwords(str_replace('_', ' ', $payment->payment_method)) }}
                                    </span>
                                </td>
                                <td class="text-end">Rp {{ number_format($payment->amount, 2) }}</td>
                                <td>{{ $payment->reference ?? '-' }}</td>
                                <td>{{ $payment->memo ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const amountInput = document.getElementById('amountInput');
    const fullPaymentRadio = document.getElementById('fullPayment');
    const partialPaymentRadio = document.getElementById('partialPayment');
    const maxAmount = parseFloat('{{ $vendorBill->balance }}');
    
    // Set full payment amount when full payment radio is selected
    fullPaymentRadio.addEventListener('change', function() {
        if (this.checked) {
            amountInput.value = maxAmount;
        }
    });
    
    // Clear amount when partial payment is selected
    partialPaymentRadio.addEventListener('change', function() {
        if (this.checked) {
            amountInput.value = '';
            amountInput.focus();
        }
    });
    
    // Validate amount input
    amountInput.addEventListener('blur', function() {
        const inputAmount = parseFloat(this.value) || 0;
        
        if (inputAmount > maxAmount) {
            alert('Amount cannot exceed balance due: Rp ' + maxAmount.toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
            this.value = maxAmount;
        }
        
        if (inputAmount <= 0) {
            alert('Amount must be greater than 0');
            this.value = 0.01;
        }
        
        // Auto-select partial payment if amount is less than balance
        if (inputAmount > 0 && inputAmount < maxAmount) {
            partialPaymentRadio.checked = true;
        } else if (inputAmount == maxAmount) {
            fullPaymentRadio.checked = true;
        }
    });
    
    // Form validation
    document.getElementById('paymentForm').addEventListener('submit', function(e) {
        const amount = parseFloat(amountInput.value) || 0;
        
        if (amount <= 0) {
            e.preventDefault();
            alert('Please enter a valid payment amount.');
            amountInput.focus();
            return false;
        }
        
        if (amount > maxAmount) {
            e.preventDefault();
            alert('Payment amount cannot exceed balance due.');
            amountInput.focus();
            return false;
        }
        
        if (!confirm('Process payment of Rp ' + amount.toLocaleString('id-ID') + '?')) {
            e.preventDefault();
            return false;
        }
    });
});
</script>
@endsection
@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Edit Payment - {{ $payment->payment_number }}</h3>
        <a href="{{ route('sales.payment.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>

    @if(!$payment->isDraft())
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong>Warning!</strong> Only draft payments can be edited.
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error!</strong> Please check the form below.
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <form action="{{ route('sales.payment.update', $payment->id) }}" method="POST" id="payment-form" {{ !$payment->isDraft() ? 'onsubmit="return false;"' : '' }}>
        @csrf
        @method('PUT')

        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Payment Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="customer_id">Customer <span class="text-danger">*</span></label>
                            <select name="customer_id" id="customer_id" 
                                    class="form-control @error('customer_id') is-invalid @enderror" 
                                    {{ !$payment->isDraft() ? 'disabled' : '' }} required>
                                <option value="">-- Select Customer --</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" 
                                        {{ old('customer_id', $payment->customer_id) == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="payment_date">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" id="payment_date" 
                                   class="form-control @error('payment_date') is-invalid @enderror" 
                                   value="{{ old('payment_date', $payment->payment_date->format('Y-m-d')) }}" 
                                   {{ !$payment->isDraft() ? 'readonly' : '' }} required>
                            @error('payment_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="amount">Amount <span class="text-danger">*</span></label>
                            <input type="number" name="amount" id="amount" 
                                   class="form-control @error('amount') is-invalid @enderror" 
                                   step="0.01" min="0.01" 
                                   value="{{ old('amount', $payment->amount) }}" 
                                   {{ !$payment->isDraft() ? 'readonly' : '' }} required>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="payment_method">Payment Method <span class="text-danger">*</span></label>
                            <select name="payment_method" id="payment_method" 
                                    class="form-control @error('payment_method') is-invalid @enderror" 
                                    {{ !$payment->isDraft() ? 'disabled' : '' }} required>
                                <option value="">-- Select Payment Method --</option>
                                <option value="cash" {{ old('payment_method', $payment->payment_method) == 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="bank_transfer" {{ old('payment_method', $payment->payment_method) == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                <option value="credit_card" {{ old('payment_method', $payment->payment_method) == 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                                <option value="check" {{ old('payment_method', $payment->payment_method) == 'check' ? 'selected' : '' }}>Check</option>
                                <option value="other" {{ old('payment_method', $payment->payment_method) == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('payment_method')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="memo">Memo</label>
                            <textarea name="memo" id="memo" rows="2" 
                                      class="form-control @error('memo') is-invalid @enderror"
                                      {{ !$payment->isDraft() ? 'readonly' : '' }}>{{ old('memo', $payment->memo) }}</textarea>
                            @error('memo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-right">
            <a href="{{ route('sales.payment.index') }}" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
            @if($payment->isDraft())
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Payment
                </button>
            @endif
        </div>
    </form>
@endsection

@push('styles')
<style>
    .table td, .table th {
        vertical-align: middle;
    }
    
    .form-control-sm {
        font-size: 0.875rem;
    }
</style>
@endpush
@extends('layouts.app')
@section('title', 'Edit Vendor')

@section('content')
<div class="card shadow">
    <div class="card-header">
        <h5 class="mb-0">Edit Vendor</h5>
    </div>
    
    <div class="card-body">
        <form action="{{ route('purchase.vendor.update', $vendor->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Vendor Name *</label>
                        <input type="text" name="name" 
                               class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $vendor->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Company Type *</label>
                        <div class="mt-2">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="company_type" 
                                       id="individual" value="individual" 
                                       {{ old('company_type', $vendor->company_type) == 'individual' ? 'checked' : '' }} required>
                                <label class="form-check-label" for="individual">Individual</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="company_type" 
                                       id="company" value="company" 
                                       {{ old('company_type', $vendor->company_type) == 'company' ? 'checked' : '' }}>
                                <label class="form-check-label" for="company">Company</label>
                            </div>
                        </div>
                        @error('company_type')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Street Address</label>
                        <input type="text" name="street" 
                               class="form-control @error('street') is-invalid @enderror" 
                               value="{{ old('street', $vendor->street) }}">
                        @error('street')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">State</label>
                        <input type="text" name="state" 
                               class="form-control @error('state') is-invalid @enderror" 
                               value="{{ old('state', $vendor->state) }}">
                        @error('state')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Country</label>
                        <input type="text" name="country" 
                               class="form-control @error('country') is-invalid @enderror" 
                               value="{{ old('country', $vendor->country) }}">
                        @error('country')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Contact Phone</label>
                        <input type="text" name="contact_phone" 
                               class="form-control @error('contact_phone') is-invalid @enderror" 
                               value="{{ old('contact_phone', $vendor->contact_phone) }}">
                        @error('contact_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               value="{{ old('email', $vendor->email) }}">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Bank Account</label>
                <input type="text" name="bank_account" 
                       class="form-control @error('bank_account') is-invalid @enderror" 
                       value="{{ old('bank_account', $vendor->bank_account) }}">
                @error('bank_account')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" 
                          rows="3">{{ old('notes', $vendor->notes) }}</textarea>
                @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-save"></i> Update Vendor
                </button>
                <a href="{{ route('purchase.vendor.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
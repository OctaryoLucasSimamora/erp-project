@extends('layouts.app')
@section('title', 'Create RFQ')

@section('content')
<div class="card shadow">
    <div class="card-header">
        <h5 class="mb-0">Create Request for Quotation</h5>
    </div>
    
    <div class="card-body">
        <form action="{{ route('purchase.rfq.store') }}" method="POST" id="rfqForm">
            @csrf
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Vendor *</label>
                        <select name="vendor_id" class="form-control @error('vendor_id') is-invalid @enderror" required>
                            <option value="">Select Vendor</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}" {{ old('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                    {{ $vendor->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('vendor_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Deadline</label>
                        <input type="date" name="deadline" 
                               class="form-control @error('deadline') is-invalid @enderror" 
                               value="{{ old('deadline') }}">
                        @error('deadline')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Arrival Date</label>
                        <input type="date" name="arrival_date" 
                               class="form-control @error('arrival_date') is-invalid @enderror" 
                               value="{{ old('arrival_date') }}">
                        @error('arrival_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Company</label>
                <input type="text" name="company" 
                       class="form-control @error('company') is-invalid @enderror" 
                       value="{{ old('company') }}">
                @error('company')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <!-- Product Lines -->
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6>Product Lines</h6>
                    <button type="button" class="btn btn-sm btn-success" onclick="addProductLine()">
                        <i class="fas fa-plus"></i> Add Product
                    </button>
                </div>
                
                <div id="productLines">
                    <!-- Product lines will be added here by JavaScript -->
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" 
                          rows="3">{{ old('notes') }}</textarea>
                @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save RFQ
                </button>
                <a href="{{ route('purchase.rfq.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Template for product line -->
<template id="productLineTemplate">
    <div class="product-line border p-3 mb-3">
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Product *</label>
                    <select name="product_id[]" class="form-control product-select" required>
                        <option value="">Select Product</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <div class="mb-3">
                    <label class="form-label">Quantity *</label>
                    <input type="number" name="quantity[]" class="form-control quantity" 
                           step="0.01" min="0.01" required>
                </div>
            </div>
            <div class="col-md-2">
                <div class="mb-3">
                    <label class="form-label">Price *</label>
                    <input type="number" name="price[]" class="form-control price" 
                           step="0.01" min="0" required>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <input type="text" name="description[]" class="form-control">
                </div>
            </div>
            <div class="col-md-1">
                <div class="mb-3">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-danger w-100" onclick="removeProductLine(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-text">Subtotal: <span class="subtotal">Rp 0</span></div>
            </div>
        </div>
    </div>
</template>

<script>
let productLineCount = 0;

function addProductLine() {
    const template = document.getElementById('productLineTemplate');
    const clone = template.content.cloneNode(true);
    const productLines = document.getElementById('productLines');
    
    productLines.appendChild(clone);
    productLineCount++;
    
    if (productLineCount === 1) {
        // Focus on first product select
        const firstSelect = productLines.querySelector('.product-select');
        if (firstSelect) firstSelect.focus();
    }
    
    // Add event listeners for calculation
    const newLine = productLines.lastElementChild;
    const quantityInput = newLine.querySelector('.quantity');
    const priceInput = newLine.querySelector('.price');
    
    const calculateSubtotal = () => {
        const quantity = parseFloat(quantityInput.value) || 0;
        const price = parseFloat(priceInput.value) || 0;
        const subtotal = quantity * price;
        newLine.querySelector('.subtotal').textContent = 'Rp ' + subtotal.toLocaleString('id-ID', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    };
    
    quantityInput.addEventListener('input', calculateSubtotal);
    priceInput.addEventListener('input', calculateSubtotal);
}

function removeProductLine(button) {
    const line = button.closest('.product-line');
    line.remove();
    productLineCount--;
}

// Add first product line on page load
document.addEventListener('DOMContentLoaded', function() {
    addProductLine();
});

// Form validation
document.getElementById('rfqForm').addEventListener('submit', function(e) {
    if (productLineCount === 0) {
        e.preventDefault();
        alert('Please add at least one product line.');
        return false;
    }
    
    // Validate each product line
    const productSelects = document.querySelectorAll('.product-select');
    let isValid = true;
    
    productSelects.forEach((select, index) => {
        if (!select.value) {
            isValid = false;
            select.classList.add('is-invalid');
        } else {
            select.classList.remove('is-invalid');
        }
    });
    
    if (!isValid) {
        e.preventDefault();
        alert('Please select a product for all lines.');
        return false;
    }
});
</script>
@endsection
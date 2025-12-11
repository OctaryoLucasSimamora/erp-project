@extends('layouts.app')
@section('title', 'Edit Purchase Order')

@section('content')
<div class="card shadow">
    <div class="card-header">
        <h5 class="mb-0">Edit Purchase Order</h5>
    </div>
    
    <div class="card-body">
        <form action="{{ route('purchase.po.update', $po->id) }}" method="POST" id="poForm">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Vendor *</label>
                        <select name="vendor_id" class="form-control @error('vendor_id') is-invalid @enderror" required>
                            <option value="">Select Vendor</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}" 
                                        {{ old('vendor_id', $po->vendor_id) == $vendor->id ? 'selected' : '' }}>
                                    {{ $vendor->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('vendor_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">RFQ (Optional)</label>
                        <select name="rfq_id" class="form-control @error('rfq_id') is-invalid @enderror">
                            <option value="">Select RFQ</option>
                            @foreach($rfqs as $rfq)
                                <option value="{{ $rfq->id }}" 
                                        {{ old('rfq_id', $po->rfq_id) == $rfq->id ? 'selected' : '' }}>
                                    {{ $rfq->rfq_number }} - {{ $rfq->vendor->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('rfq_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Order Date *</label>
                        <input type="date" name="order_date" 
                               class="form-control @error('order_date') is-invalid @enderror" 
                               value="{{ old('order_date', $po->order_date ? date('Y-m-d', strtotime($po->order_date)) : date('Y-m-d')) }}" required>
                        @error('order_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Expected Delivery</label>
                        <input type="date" name="expected_delivery_date" 
                               class="form-control @error('expected_delivery_date') is-invalid @enderror" 
                               value="{{ old('expected_delivery_date', $po->expected_delivery_date ? date('Y-m-d', strtotime($po->expected_delivery_date)) : '') }}">
                        @error('expected_delivery_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Ship To</label>
                        <input type="text" name="ship_to" 
                               class="form-control @error('ship_to') is-invalid @enderror" 
                               value="{{ old('ship_to', $po->ship_to) }}">
                        @error('ship_to')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Incoterm</label>
                        <input type="text" name="incoterm" 
                               class="form-control @error('incoterm') is-invalid @enderror" 
                               value="{{ old('incoterm', $po->incoterm) }}">
                        @error('incoterm')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Payment Term</label>
                        <input type="text" name="payment_term" 
                               class="form-control @error('payment_term') is-invalid @enderror" 
                               value="{{ old('payment_term', $po->payment_term) }}">
                        @error('payment_term')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
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
                    @foreach($po->lines as $index => $line)
                    <div class="product-line border p-3 mb-3">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Product *</label>
                                    <select name="product_id[]" class="form-control product-select" required>
                                        <option value="">Select Product</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" 
                                                    data-price="{{ $product->price ?? 0 }}"
                                                    {{ $line->product_id == $product->id ? 'selected' : '' }}>
                                                {{ $product->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label class="form-label">Quantity *</label>
                                    <input type="number" name="quantity[]" class="form-control quantity" 
                                           step="0.01" min="0.01" value="{{ $line->quantity }}" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label class="form-label">Price *</label>
                                    <input type="number" name="price[]" class="form-control price" 
                                           step="0.01" min="0" value="{{ $line->price }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <input type="text" name="description[]" class="form-control" 
                                           value="{{ $line->description }}">
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
                                <div class="form-text">Subtotal: <span class="subtotal">Rp {{ number_format($line->subtotal, 2) }}</span></div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <!-- Totals -->
                <div class="row justify-content-end mt-3">
                    <div class="col-md-4">
                        <table class="table table-bordered">
                            <tr>
                                <td><strong>Subtotal</strong></td>
                                <td class="text-end" id="subtotalDisplay">Rp {{ number_format($po->subtotal, 2) }}</td>
                                <input type="hidden" name="subtotal" id="subtotal" value="{{ $po->subtotal }}">
                            </tr>
                            <tr>
                                <td><strong>Tax (10%)</strong></td>
                                <td class="text-end" id="taxDisplay">Rp {{ number_format($po->tax_amount, 2) }}</td>
                                <input type="hidden" name="tax_amount" id="tax_amount" value="{{ $po->tax_amount }}">
                            </tr>
                            <tr class="table-active">
                                <td><strong>Total</strong></td>
                                <td class="text-end" id="totalDisplay">Rp {{ number_format($po->total_amount, 2) }}</td>
                                <input type="hidden" name="total_amount" id="total_amount" value="{{ $po->total_amount }}">
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" 
                          rows="3">{{ old('notes', $po->notes) }}</textarea>
                @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-save"></i> Update Purchase Order
                </button>
                <a href="{{ route('purchase.po.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Template for new product line -->
<template id="productLineTemplate">
    <div class="product-line border p-3 mb-3">
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Product *</label>
                    <select name="product_id[]" class="form-control product-select" required>
                        <option value="">Select Product</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" data-price="{{ $product->price ?? 0 }}">
                                {{ $product->name }}
                            </option>
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
let productLineCount = {{ count($po->lines) }};

function addProductLine() {
    const template = document.getElementById('productLineTemplate');
    const clone = template.content.cloneNode(true);
    const productLines = document.getElementById('productLines');
    
    productLines.appendChild(clone);
    productLineCount++;
    
    // Add event listeners for calculation
    const newLine = productLines.lastElementChild;
    const productSelect = newLine.querySelector('.product-select');
    const quantityInput = newLine.querySelector('.quantity');
    const priceInput = newLine.querySelector('.price');
    
    // Auto-fill price when product selected
    productSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const price = selectedOption.getAttribute('data-price');
        if (price && price > 0) {
            priceInput.value = price;
            calculateSubtotal();
            calculateTotals();
        }
    });
    
    const calculateSubtotal = () => {
        const quantity = parseFloat(quantityInput.value) || 0;
        const price = parseFloat(priceInput.value) || 0;
        const subtotal = quantity * price;
        newLine.querySelector('.subtotal').textContent = 'Rp ' + subtotal.toLocaleString('id-ID', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        calculateTotals();
    };
    
    quantityInput.addEventListener('input', calculateSubtotal);
    priceInput.addEventListener('input', calculateSubtotal);
}

function removeProductLine(button) {
    const line = button.closest('.product-line');
    line.remove();
    productLineCount--;
    calculateTotals();
}

function calculateTotals() {
    let subtotal = 0;
    const productLines = document.querySelectorAll('.product-line');
    
    productLines.forEach(line => {
        const quantity = parseFloat(line.querySelector('.quantity').value) || 0;
        const price = parseFloat(line.querySelector('.price').value) || 0;
        subtotal += quantity * price;
    });
    
    const tax = subtotal * 0.10;
    const total = subtotal + tax;
    
    // Update display
    document.getElementById('subtotalDisplay').textContent = 'Rp ' + subtotal.toLocaleString('id-ID', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
    document.getElementById('taxDisplay').textContent = 'Rp ' + tax.toLocaleString('id-ID', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
    document.getElementById('totalDisplay').textContent = 'Rp ' + total.toLocaleString('id-ID', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
    
    // Update hidden inputs
    document.getElementById('subtotal').value = subtotal;
    document.getElementById('tax_amount').value = tax;
    document.getElementById('total_amount').value = total;
}

// Add calculation for existing lines
document.addEventListener('DOMContentLoaded', function() {
    const productLines = document.querySelectorAll('.product-line');
    
    productLines.forEach(line => {
        const productSelect = line.querySelector('.product-select');
        const quantityInput = line.querySelector('.quantity');
        const priceInput = line.querySelector('.price');
        
        // Auto-fill price when product selected
        productSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const price = selectedOption.getAttribute('data-price');
            if (price && price > 0) {
                priceInput.value = price;
                calculateSubtotal();
                calculateTotals();
            }
        });
        
        const calculateSubtotal = () => {
            const quantity = parseFloat(quantityInput.value) || 0;
            const price = parseFloat(priceInput.value) || 0;
            const subtotal = quantity * price;
            line.querySelector('.subtotal').textContent = 'Rp ' + subtotal.toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            calculateTotals();
        };
        
        quantityInput.addEventListener('input', calculateSubtotal);
        priceInput.addEventListener('input', calculateSubtotal);
    });
});

// Form validation
document.getElementById('poForm').addEventListener('submit', function(e) {
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
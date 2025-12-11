@extends('layouts.app')
@section('title', 'Edit Vendor Bill')

@section('content')
<div class="card shadow">
    <div class="card-header">
        <h5 class="mb-0">Edit Vendor Bill</h5>
    </div>
    
    <div class="card-body">
        <form action="{{ route('purchase.vendor-bill.update', $vendorBill->id) }}" method="POST" id="vendorBillForm">
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
                                        {{ old('vendor_id', $vendorBill->vendor_id) == $vendor->id ? 'selected' : '' }}>
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
                        <label class="form-label">Purchase Order (Optional)</label>
                        <select name="purchase_order_id" class="form-control @error('purchase_order_id') is-invalid @enderror">
                            <option value="">Select PO</option>
                            @foreach($purchaseOrders as $po)
                                <option value="{{ $po->id }}" 
                                        {{ old('purchase_order_id', $vendorBill->purchase_order_id) == $po->id ? 'selected' : '' }}>
                                    {{ $po->po_number }} - {{ $po->vendor->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('purchase_order_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Bill Date *</label>
                        <input type="date" name="bill_date" 
                               class="form-control @error('bill_date') is-invalid @enderror" 
                               value="{{ old('bill_date', $vendorBill->bill_date ? date('Y-m-d', strtotime($vendorBill->bill_date)) : date('Y-m-d')) }}" required>
                        @error('bill_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Due Date</label>
                        <input type="date" name="due_date" 
                               class="form-control @error('due_date') is-invalid @enderror" 
                               value="{{ old('due_date', $vendorBill->due_date ? date('Y-m-d', strtotime($vendorBill->due_date)) : '') }}">
                        @error('due_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Payment Reference</label>
                        <input type="text" name="payment_reference" 
                               class="form-control @error('payment_reference') is-invalid @enderror" 
                               value="{{ old('payment_reference', $vendorBill->payment_reference) }}">
                        @error('payment_reference')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <!-- Product Lines -->
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6>Bill Lines</h6>
                    <button type="button" class="btn btn-sm btn-success" onclick="addProductLine()">
                        <i class="fas fa-plus"></i> Add Item
                    </button>
                </div>
                
                <div id="productLines">
                    @foreach($vendorBill->lines as $index => $line)
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
                            <tr class="table-active">
                                <td><strong>Total Amount</strong></td>
                                <td class="text-end" id="totalDisplay">Rp {{ number_format($vendorBill->total_amount, 2) }}</td>
                                <input type="hidden" name="total_amount" id="total_amount" value="{{ $vendorBill->total_amount }}">
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" 
                          rows="3">{{ old('notes', $vendorBill->notes) }}</textarea>
                @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-save"></i> Update Vendor Bill
                </button>
                <a href="{{ route('purchase.vendor-bill.index') }}" class="btn btn-secondary">
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
let productLineCount = {{ count($vendorBill->lines) }};

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
    let total = 0;
    const productLines = document.querySelectorAll('.product-line');
    
    productLines.forEach(line => {
        const quantity = parseFloat(line.querySelector('.quantity').value) || 0;
        const price = parseFloat(line.querySelector('.price').value) || 0;
        total += quantity * price;
    });
    
    // Update display
    document.getElementById('totalDisplay').textContent = 'Rp ' + total.toLocaleString('id-ID', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
    
    // Update hidden input
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
document.getElementById('vendorBillForm').addEventListener('submit', function(e) {
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
@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Edit Invoice - {{ $invoice->invoice_number }}</h3>
        <a href="{{ route('sales.invoice.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>

    @if(!$invoice->isDraft())
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong>Warning!</strong> Only draft invoices can be edited.
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

    <form action="{{ route('sales.invoice.update', $invoice->id) }}" method="POST" id="invoice-form" {{ !$invoice->isDraft() ? 'onsubmit="return false;"' : '' }}>
        @csrf
        @method('PUT')

        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Invoice Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="customer_id">Customer <span class="text-danger">*</span></label>
                            <select name="customer_id" id="customer_id" 
                                    class="form-control @error('customer_id') is-invalid @enderror" 
                                    {{ !$invoice->isDraft() ? 'disabled' : '' }} required>
                                <option value="">-- Select Customer --</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" 
                                        {{ old('customer_id', $invoice->customer_id) == $customer->id ? 'selected' : '' }}>
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
                            <label for="invoice_date">Invoice Date <span class="text-danger">*</span></label>
                            <input type="date" name="invoice_date" id="invoice_date" 
                                   class="form-control @error('invoice_date') is-invalid @enderror" 
                                   value="{{ old('invoice_date', $invoice->invoice_date->format('Y-m-d')) }}" 
                                   {{ !$invoice->isDraft() ? 'readonly' : '' }} required>
                            @error('invoice_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="due_date">Due Date <span class="text-danger">*</span></label>
                            <input type="date" name="due_date" id="due_date" 
                                   class="form-control @error('due_date') is-invalid @enderror" 
                                   value="{{ old('due_date', $invoice->due_date->format('Y-m-d')) }}" 
                                   {{ !$invoice->isDraft() ? 'readonly' : '' }} required>
                            @error('due_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="sales_order_id">Sales Order (Optional)</label>
                            <select name="sales_order_id" id="sales_order_id" 
                                    class="form-control @error('sales_order_id') is-invalid @enderror"
                                    {{ !$invoice->isDraft() ? 'disabled' : '' }}>
                                <option value="">-- Select Sales Order --</option>
                                @foreach($salesOrders as $so)
                                    <option value="{{ $so->id }}" 
                                        {{ old('sales_order_id', $invoice->sales_order_id) == $so->id ? 'selected' : '' }}>
                                        {{ $so->order_number }} - {{ $so->customer->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('sales_order_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="delivery_order_id">Delivery Order (Optional)</label>
                            <select name="delivery_order_id" id="delivery_order_id" 
                                    class="form-control @error('delivery_order_id') is-invalid @enderror"
                                    {{ !$invoice->isDraft() ? 'disabled' : '' }}>
                                <option value="">-- Select Delivery Order --</option>
                                @foreach($deliveryOrders as $do)
                                    <option value="{{ $do->id }}" 
                                        {{ old('delivery_order_id', $invoice->delivery_order_id) == $do->id ? 'selected' : '' }}>
                                        {{ $do->delivery_number }} - {{ $do->salesOrder->customer->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('delivery_order_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="journal">Journal</label>
                            <input type="text" name="journal" id="journal" 
                                   class="form-control @error('journal') is-invalid @enderror" 
                                   value="{{ old('journal', $invoice->journal) }}" 
                                   {{ !$invoice->isDraft() ? 'readonly' : '' }} 
                                   placeholder="e.g., Jurnal Penjualan">
                            @error('journal')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="payment_terms">Payment Terms</label>
                            <textarea name="payment_terms" id="payment_terms" rows="2" 
                                      class="form-control @error('payment_terms') is-invalid @enderror"
                                      {{ !$invoice->isDraft() ? 'readonly' : '' }}>{{ old('payment_terms', $invoice->payment_terms) }}</textarea>
                            @error('payment_terms')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="notes">Notes</label>
                            <textarea name="notes" id="notes" rows="2" 
                                      class="form-control @error('notes') is-invalid @enderror"
                                      {{ !$invoice->isDraft() ? 'readonly' : '' }}>{{ old('notes', $invoice->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Invoice Items</h5>
                @if($invoice->isDraft())
                    <button type="button" class="btn btn-sm btn-light" id="add-item">
                        <i class="fas fa-plus"></i> Add Item
                    </button>
                @endif
            </div>
            <div class="card-body">
                <div class="alert alert-info" id="no-items-alert" style="display: {{ $invoice->items->count() == 0 ? 'block' : 'none' }};">
                    No items in this invoice.
                </div>
                
                @if($invoice->items->count() > 0)
                    <div class="table-responsive" id="items-table-container">
                        <table class="table table-bordered" id="items-table">
                            <thead class="thead-light">
                                <tr>
                                    <th width="25%">Product</th>
                                    <th width="20%">Description</th>
                                    <th width="10%">Quantity</th>
                                    <th width="12%">Unit Price</th>
                                    <th width="8%">Discount %</th>
                                    <th width="8%">Tax %</th>
                                    <th width="12%">Total</th>
                                    @if($invoice->isDraft())
                                        <th width="5%"></th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody id="items-container">
                                @foreach($invoice->items as $index => $item)
                                    <tr class="item-row">
                                        <td>
                                            <select name="items[{{ $index }}][product_id]" 
                                                    class="form-control form-control-sm product-select" 
                                                    {{ !$invoice->isDraft() ? 'disabled' : '' }} required>
                                                <option value="">-- Select Product --</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}" 
                                                        data-price="{{ $product->price }}"
                                                        {{ $item->product_id == $product->id ? 'selected' : '' }}>
                                                        {{ $product->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <input type="hidden" name="items[{{ $index }}][sales_order_item_id]" value="{{ $item->sales_order_item_id }}">
                                            <input type="hidden" name="items[{{ $index }}][delivery_order_item_id]" value="{{ $item->delivery_order_item_id }}">
                                        </td>
                                        <td>
                                            <input type="text" name="items[{{ $index }}][description]" 
                                                   class="form-control form-control-sm" 
                                                   value="{{ $item->description }}" 
                                                   {{ !$invoice->isDraft() ? 'readonly' : '' }} 
                                                   placeholder="Description">
                                        </td>
                                        <td>
                                            <input type="number" name="items[{{ $index }}][quantity]" 
                                                   class="form-control form-control-sm quantity-input" 
                                                   step="0.01" min="0.01" 
                                                   value="{{ $item->quantity }}" 
                                                   {{ !$invoice->isDraft() ? 'readonly' : '' }} required>
                                        </td>
                                        <td>
                                            <input type="number" name="items[{{ $index }}][unit_price]" 
                                                   class="form-control form-control-sm price-input" 
                                                   step="0.01" min="0" 
                                                   value="{{ $item->unit_price }}" 
                                                   {{ !$invoice->isDraft() ? 'readonly' : '' }} required>
                                        </td>
                                        <td>
                                            <input type="number" name="items[{{ $index }}][discount_percent]" 
                                                   class="form-control form-control-sm discount-input" 
                                                   step="0.01" min="0" max="100" 
                                                   value="{{ $item->discount_percent }}" 
                                                   {{ !$invoice->isDraft() ? 'readonly' : '' }}>
                                        </td>
                                        <td>
                                            <input type="number" name="items[{{ $index }}][tax_percent]" 
                                                   class="form-control form-control-sm tax-input" 
                                                   step="0.01" min="0" max="100" 
                                                   value="{{ $item->tax_percent }}" 
                                                   {{ !$invoice->isDraft() ? 'readonly' : '' }}>
                                        </td>
                                        <td class="text-right">
                                            <strong class="item-total">Rp 0</strong>
                                        </td>
                                        @if($invoice->isDraft())
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-danger remove-item" title="Remove">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="row mt-3">
                            <div class="col-md-8"></div>
                            <div class="col-md-4">
                                <table class="table table-sm">
                                    <tr>
                                        <th>Subtotal:</th>
                                        <td class="text-right" id="subtotal-display">Rp 0</td>
                                    </tr>
                                    <tr>
                                        <th>Discount:</th>
                                        <td class="text-right" id="discount-display">Rp 0</td>
                                    </tr>
                                    <tr>
                                        <th>Tax:</th>
                                        <td class="text-right" id="tax-display">Rp 0</td>
                                    </tr>
                                    <tr class="table-primary">
                                        <th>Total:</th>
                                        <th class="text-right" id="total-display">Rp 0</th>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="text-right">
            <a href="{{ route('sales.invoice.index') }}" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
            @if($invoice->isDraft())
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Invoice
                </button>
            @endif
        </div>
    </form>

    @if($invoice->isDraft())
        <!-- Item Row Template -->
        <template id="item-row-template">
            <tr class="item-row">
                <td>
                    <select name="items[INDEX][product_id]" class="form-control form-control-sm product-select" required>
                        <option value="">-- Select Product --</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" data-price="{{ $product->price }}">
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                    <input type="hidden" name="items[INDEX][sales_order_item_id]" value="">
                    <input type="hidden" name="items[INDEX][delivery_order_item_id]" value="">
                </td>
                <td>
                    <input type="text" name="items[INDEX][description]" class="form-control form-control-sm" placeholder="Description">
                </td>
                <td>
                    <input type="number" name="items[INDEX][quantity]" class="form-control form-control-sm quantity-input" 
                           step="0.01" min="0.01" value="1" required>
                </td>
                <td>
                    <input type="number" name="items[INDEX][unit_price]" class="form-control form-control-sm price-input" 
                           step="0.01" min="0" value="0" required>
                </td>
                <td>
                    <input type="number" name="items[INDEX][discount_percent]" class="form-control form-control-sm discount-input" 
                           step="0.01" min="0" max="100" value="0">
                </td>
                <td>
                    <input type="number" name="items[INDEX][tax_percent]" class="form-control form-control-sm tax-input" 
                           step="0.01" min="0" max="100" value="0">
                </td>
                <td class="text-right">
                    <strong class="item-total">Rp 0</strong>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger remove-item" title="Remove">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        </template>
    @endif
@endsection

@push('scripts')
@if($invoice->isDraft())
<script>
let itemIndex = {{ $invoice->items->count() }};

$(document).ready(function() {
    // Calculate existing items
    $('.item-row').each(function() {
        calculateItemTotal($(this));
    });

    $('#add-item').click(function() {
        addItem();
    });

    $(document).on('click', '.remove-item', function() {
        if ($('.item-row').length > 1) {
            $(this).closest('tr').remove();
            calculateTotals();
            updateItemIndexes();
        } else {
            alert('At least one item is required');
        }
    });

    $(document).on('change', '.product-select', function() {
        const price = $(this).find(':selected').data('price');
        $(this).closest('tr').find('.price-input').val(price || 0);
        calculateItemTotal($(this).closest('tr'));
    });

    $(document).on('input', '.quantity-input, .price-input, .discount-input, .tax-input', function() {
        calculateItemTotal($(this).closest('tr'));
    });
});

function addItem() {
    const template = document.getElementById('item-row-template');
    const clone = template.content.cloneNode(true);
    const row = clone.querySelector('tr');
    row.innerHTML = row.innerHTML.replace(/INDEX/g, itemIndex);
    document.getElementById('items-container').appendChild(row);
    itemIndex++;
    
    $('#no-items-alert').hide();
    $('#items-table-container').show();
}

function updateItemIndexes() {
    itemIndex = 0;
    $('.item-row').each(function(index) {
        // Update all inputs with new index
        $(this).find('select, input').each(function() {
            const name = $(this).attr('name');
            if (name) {
                $(this).attr('name', name.replace(/items\[\d+\]/, `items[${index}]`));
            }
        });
        itemIndex++;
    });
}

function calculateItemTotal(row) {
    const quantity = parseFloat(row.find('.quantity-input').val()) || 0;
    const unitPrice = parseFloat(row.find('.price-input').val()) || 0;
    const discountPercent = parseFloat(row.find('.discount-input').val()) || 0;
    const taxPercent = parseFloat(row.find('.tax-input').val()) || 0;

    const subtotal = quantity * unitPrice;
    const discountAmount = subtotal * (discountPercent / 100);
    const amountAfterDiscount = subtotal - discountAmount;
    const taxAmount = amountAfterDiscount * (taxPercent / 100);
    const total = amountAfterDiscount + taxAmount;

    row.find('.item-total').text('Rp ' + formatNumber(total));
    calculateTotals();
}

function calculateTotals() {
    let subtotal = 0;
    let totalDiscount = 0;
    let totalTax = 0;

    $('.item-row').each(function() {
        const quantity = parseFloat($(this).find('.quantity-input').val()) || 0;
        const unitPrice = parseFloat($(this).find('.price-input').val()) || 0;
        const discountPercent = parseFloat($(this).find('.discount-input').val()) || 0;
        const taxPercent = parseFloat($(this).find('.tax-input').val()) || 0;

        const itemSubtotal = quantity * unitPrice;
        const discountAmount = itemSubtotal * (discountPercent / 100);
        const amountAfterDiscount = itemSubtotal - discountAmount;
        const taxAmount = amountAfterDiscount * (taxPercent / 100);

        subtotal += itemSubtotal;
        totalDiscount += discountAmount;
        totalTax += taxAmount;
    });

    const total = subtotal - totalDiscount + totalTax;

    $('#subtotal-display').text('Rp ' + formatNumber(subtotal));
    $('#discount-display').text('Rp ' + formatNumber(totalDiscount));
    $('#tax-display').text('Rp ' + formatNumber(totalTax));
    $('#total-display').text('Rp ' + formatNumber(total));
}

function formatNumber(num) {
    return Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

$('#invoice-form').submit(function(e) {
    if ($('.item-row').length === 0) {
        e.preventDefault();
        alert('Please add at least one item');
        return false;
    }
});
</script>
@endif
@endpush

@push('styles')
<style>
    .table td, .table th {
        vertical-align: middle;
    }
    
    .form-control-sm {
        font-size: 0.875rem;
    }
    
    #items-table tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .item-total {
        font-size: 0.95rem;
    }
</style>
@endpush
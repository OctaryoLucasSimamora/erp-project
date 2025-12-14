@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Create Customer Invoice</h3>
        <a href="{{ route('sales.invoice.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>

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

    <form action="{{ route('sales.invoice.store') }}" method="POST" id="invoice-form">
        @csrf

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
                                    class="form-control @error('customer_id') is-invalid @enderror" required>
                                <option value="">-- Select Customer --</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" 
                                        {{ old('customer_id', $sourceData->customer_id ?? ($sourceData->salesOrder->customer_id ?? '')) == $customer->id ? 'selected' : '' }}>
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
                                   value="{{ old('invoice_date', date('Y-m-d')) }}" required>
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
                                   value="{{ old('due_date', date('Y-m-d', strtotime('+30 days'))) }}" required>
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
                                    class="form-control @error('sales_order_id') is-invalid @enderror">
                                <option value="">-- Select Sales Order --</option>
                                @foreach($salesOrders as $so)
                                    <option value="{{ $so->id }}" 
                                        {{ old('sales_order_id', $sourceType == 'so' ? $sourceId : '') == $so->id ? 'selected' : '' }}>
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
                                    class="form-control @error('delivery_order_id') is-invalid @enderror">
                                <option value="">-- Select Delivery Order --</option>
                                @foreach($deliveryOrders as $do)
                                    <option value="{{ $do->id }}" 
                                        {{ old('delivery_order_id', $sourceType == 'do' ? $sourceId : '') == $do->id ? 'selected' : '' }}>
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
                                   value="{{ old('journal') }}" placeholder="e.g., Jurnal Penjualan">
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
                                      class="form-control @error('payment_terms') is-invalid @enderror">{{ old('payment_terms') }}</textarea>
                            @error('payment_terms')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="notes">Notes</label>
                            <textarea name="notes" id="notes" rows="2" 
                                      class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
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
                <button type="button" class="btn btn-sm btn-light" id="load-items-btn">
                    <i class="fas fa-sync-alt"></i> Load Items from Source
                </button>
            </div>
            <div class="card-body">
                <div class="alert alert-info" id="no-items-alert" style="display: none;">
                    No items available from the selected source.
                </div>
                
                <div class="table-responsive" id="items-table-container" style="display: none;">
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
                                <th width="5%"></th>
                            </tr>
                        </thead>
                        <tbody id="items-container">
                            <!-- Items will be loaded here -->
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
            </div>
        </div>

        <div class="text-right">
            <a href="{{ route('sales.invoice.index') }}" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary" id="submit-btn">
                <i class="fas fa-save"></i> Save Invoice
            </button>
        </div>
    </form>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let itemIndex = 0;

    // Load items from source
    $('#load-items-btn').click(function() {
        const salesOrderId = $('#sales_order_id').val();
        const deliveryOrderId = $('#delivery_order_id').val();
        
        if (salesOrderId) {
            loadSourceItems('so', salesOrderId);
        } else if (deliveryOrderId) {
            loadSourceItems('do', deliveryOrderId);
        } else {
            alert('Please select either Sales Order or Delivery Order');
        }
    });

    function loadSourceItems(sourceType, sourceId) {
        $.ajax({
            url: '{{ route("sales.invoice.source.items") }}',
            type: 'GET',
            data: {
                source_type: sourceType,
                source_id: sourceId
            },
            success: function(items) {
                if (items.length > 0) {
                    renderItems(items);
                    $('#items-table-container').show();
                    $('#no-items-alert').hide();
                    calculateTotals();
                } else {
                    clearItems();
                    $('#no-items-alert').show();
                }
            },
            error: function() {
                alert('Failed to load items');
            }
        });
    }

    function renderItems(items) {
        const container = $('#items-container');
        container.empty();
        itemIndex = 0;
        
        items.forEach(function(item) {
            const row = `
                <tr class="item-row">
                    <td>
                        <input type="hidden" name="items[${itemIndex}][product_id]" value="${item.product_id}">
                        <input type="hidden" name="items[${itemIndex}][sales_order_item_id]" value="${item.sales_order_item_id || ''}">
                        <input type="hidden" name="items[${itemIndex}][delivery_order_item_id]" value="${item.delivery_order_item_id || ''}">
                        <strong>${item.product_name}</strong>
                    </td>
                    <td>
                        <textarea name="items[${itemIndex}][description]" class="form-control form-control-sm" rows="1">${item.description || ''}</textarea>
                    </td>
                    <td>
                        <input type="number" name="items[${itemIndex}][quantity]" 
                               class="form-control form-control-sm quantity-input" 
                               min="0.01" step="0.01" value="${item.quantity}" required>
                    </td>
                    <td>
                        <input type="number" name="items[${itemIndex}][unit_price]" 
                               class="form-control form-control-sm price-input" 
                               min="0" step="0.01" value="${item.unit_price}" required>
                    </td>
                    <td>
                        <input type="number" name="items[${itemIndex}][discount_percent]" 
                               class="form-control form-control-sm discount-input" 
                               min="0" max="100" step="0.01" value="${item.discount_percent || 0}">
                    </td>
                    <td>
                        <input type="number" name="items[${itemIndex}][tax_percent]" 
                               class="form-control form-control-sm tax-input" 
                               min="0" max="100" step="0.01" value="${item.tax_percent || 0}">
                    </td>
                    <td class="text-right">
                        <strong class="item-total">Rp 0</strong>
                    </td>
                </tr>
            `;
            container.append(row);
            calculateItemTotal(container.find('.item-row').last());
            itemIndex++;
        });
    }

    function clearItems() {
        $('#items-container').empty();
        $('#items-table-container').hide();
        $('#no-items-alert').show();
        resetTotals();
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

    function resetTotals() {
        $('#subtotal-display').text('Rp 0');
        $('#discount-display').text('Rp 0');
        $('#tax-display').text('Rp 0');
        $('#total-display').text('Rp 0');
    }

    function formatNumber(num) {
        return Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    // Event listeners for input changes
    $(document).on('input', '.quantity-input, .price-input, .discount-input, .tax-input', function() {
        calculateItemTotal($(this).closest('tr'));
    });

    // Auto-load items if coming from source
    @if($sourceData && $sourceType)
        $(document).ready(function() {
            $('#load-items-btn').trigger('click');
        });
    @endif

    // Form validation
    $('#invoice-form').submit(function(e) {
        if ($('.item-row').length === 0) {
            e.preventDefault();
            alert('Please add at least one item');
            return false;
        }
    });
});
</script>
@endpush
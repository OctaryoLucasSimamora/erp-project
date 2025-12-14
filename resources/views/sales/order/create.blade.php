@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Create Sales Order</h3>
        <a href="{{ route('sales.order.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>

    @if ($quotation)
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <strong><i class="fas fa-info-circle"></i> Converting from Quotation:</strong> {{ $quotation->quotation_number }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error!</strong> Please check the form below.
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <form action="{{ route('sales.order.store') }}" method="POST" id="sales-order-form">
        @csrf

        @if ($quotation)
            <input type="hidden" name="quotation_id" value="{{ $quotation->id }}">
        @endif

        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Order Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="customer_id">Customer <span class="text-danger">*</span></label>
                            <select name="customer_id" id="customer_id"
                                class="form-control @error('customer_id') is-invalid @enderror" required>
                                <option value="">-- Select Customer --</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}"
                                        {{ old('customer_id', $quotation->customer_id ?? '') == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }} {{ $customer->company ? '(' . $customer->company . ')' : '' }}
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
                            <label for="order_date">Order Date <span class="text-danger">*</span></label>
                            {{-- Line 62-63 --}}
                            <input type="date" name="order_date" id="order_date"
                                class="form-control @error('order_date') is-invalid @enderror"
                                value="{{ old('order_date', isset($quotation) ? $quotation->order_date->format('Y-m-d') : date('Y-m-d')) }}"
                                required>
                            @error('order_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="expiration_date">Expiration Date <span class="text-danger">*</span></label>
                            <input type="date" name="expiration_date" id="expiration_date" 
       class="form-control @error('expiration_date') is-invalid @enderror" 
       value="{{ old('expiration_date', isset($quotation) ? $quotation->expiration_date->format('Y-m-d') : date('Y-m-d', strtotime('+30 days'))) }}" required>
                            @error('expiration_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="confirmation_date">Confirmation Date</label>
                            <input type="date" name="confirmation_date" id="confirmation_date"
                                class="form-control @error('confirmation_date') is-invalid @enderror"
                                value="{{ old('confirmation_date') }}">
                            @error('confirmation_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="commitment_date">Commitment Date (Delivery)</label>
                            <input type="date" name="commitment_date" id="commitment_date"
                                class="form-control @error('commitment_date') is-invalid @enderror"
                                value="{{ old('commitment_date') }}">
                            @error('commitment_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="pricelist">Pricelist</label>
                            <input type="text" name="pricelist" id="pricelist"
                                class="form-control @error('pricelist') is-invalid @enderror"
                                value="{{ old('pricelist') }}" placeholder="e.g., Standard Price">
                            @error('pricelist')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="warehouse">Warehouse</label>
                            <input type="text" name="warehouse" id="warehouse"
                                class="form-control @error('warehouse') is-invalid @enderror"
                                value="{{ old('warehouse') }}" placeholder="e.g., Main Warehouse">
                            @error('warehouse')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="incoterms">Incoterms</label>
                            <input type="text" name="incoterms" id="incoterms"
                                class="form-control @error('incoterms') is-invalid @enderror"
                                value="{{ old('incoterms') }}" placeholder="e.g., FOB, CIF, EXW">
                            @error('incoterms')
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
                                class="form-control @error('payment_terms') is-invalid @enderror">{{ old('payment_terms', $quotation->payment_terms ?? '') }}</textarea>
                            @error('payment_terms')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="notes">Notes</label>
                            <textarea name="notes" id="notes" rows="2" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $quotation->notes ?? '') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="terms_and_conditions">Terms and Conditions</label>
                    <textarea name="terms_and_conditions" id="terms_and_conditions" rows="3"
                        class="form-control @error('terms_and_conditions') is-invalid @enderror">{{ old('terms_and_conditions', $quotation->terms_and_conditions ?? '') }}</textarea>
                    @error('terms_and_conditions')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Order Items</h5>
                <button type="button" class="btn btn-sm btn-light" id="add-item">
                    <i class="fas fa-plus"></i> Add Item
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
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
                            @if ($quotation && $quotation->items->count() > 0)
                                @foreach ($quotation->items as $item)
                                    <tr class="item-row">
                                        <td>
                                            <select name="items[{{ $loop->index }}][product_id]"
                                                class="form-control form-control-sm product-select" required>
                                                <option value="">-- Select Product --</option>
                                                @foreach ($products as $product)
                                                    <option value="{{ $product->id }}"
                                                        data-price="{{ $product->price }}"
                                                        {{ $item->product_id == $product->id ? 'selected' : '' }}>
                                                        {{ $product->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="items[{{ $loop->index }}][description]"
                                                class="form-control form-control-sm" value="{{ $item->description }}"
                                                placeholder="Description">
                                        </td>
                                        <td>
                                            <input type="number" name="items[{{ $loop->index }}][quantity]"
                                                class="form-control form-control-sm quantity-input" step="0.01"
                                                min="0.01" value="{{ $item->quantity }}" required>
                                        </td>
                                        <td>
                                            <input type="number" name="items[{{ $loop->index }}][unit_price]"
                                                class="form-control form-control-sm price-input" step="0.01"
                                                min="0" value="{{ $item->unit_price }}" required>
                                        </td>
                                        <td>
                                            <input type="number" name="items[{{ $loop->index }}][discount_percent]"
                                                class="form-control form-control-sm discount-input" step="0.01"
                                                min="0" max="100" value="{{ $item->discount_percent }}">
                                        </td>
                                        <td>
                                            <input type="number" name="items[{{ $loop->index }}][tax_percent]"
                                                class="form-control form-control-sm tax-input" step="0.01"
                                                min="0" max="100" value="{{ $item->tax_percent }}">
                                        </td>
                                        <td class="text-right">
                                            <strong class="item-total">Rp 0</strong>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-danger remove-item"
                                                title="Remove">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>

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

        <div class="text-right">
            <a href="{{ route('sales.order.index') }}" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Sales Order
            </button>
        </div>
    </form>

    <!-- Item Row Template -->
    <template id="item-row-template">
        <tr class="item-row">
            <td>
                <select name="items[INDEX][product_id]" class="form-control form-control-sm product-select" required>
                    <option value="">-- Select Product --</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}" data-price="{{ $product->price }}">
                            {{ $product->name }}
                        </option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="text" name="items[INDEX][description]" class="form-control form-control-sm"
                    placeholder="Description">
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
                <input type="number" name="items[INDEX][discount_percent]"
                    class="form-control form-control-sm discount-input" step="0.01" min="0" max="100"
                    value="0">
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
@endsection

@push('scripts')
    <script>
        let itemIndex = {{ $quotation && $quotation->items->count() > 0 ? $quotation->items->count() : 0 }};

        $(document).ready(function() {
            @if (!$quotation || $quotation->items->count() == 0)
                addItem();
            @else
                $('.item-row').each(function() {
                    calculateItemTotal($(this));
                });
            @endif

            $('#add-item').click(function() {
                addItem();
            });

            $(document).on('click', '.remove-item', function() {
                if ($('.item-row').length > 1) {
                    $(this).closest('tr').remove();
                    calculateTotals();
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

        $('#sales-order-form').submit(function(e) {
            if ($('.item-row').length === 0) {
                e.preventDefault();
                alert('Please add at least one item');
                return false;
            }
        });
    </script>
@endpush

@push('styles')
    <style>
        .table td,
        .table th {
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

@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Create Delivery Order</h3>
        <a href="{{ route('sales.delivery.index') }}" class="btn btn-secondary">
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

    <form action="{{ route('sales.delivery.store') }}" method="POST" id="delivery-order-form">
        @csrf

        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Delivery Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="sales_order_id">Sales Order <span class="text-danger">*</span></label>
                            <select name="sales_order_id" id="sales_order_id" 
                                    class="form-control @error('sales_order_id') is-invalid @enderror" required>
                                <option value="">-- Select Sales Order --</option>
                                @foreach($salesOrders as $so)
                                    <option value="{{ $so->id }}" 
                                        {{ old('sales_order_id', $selectedSalesOrder->id ?? '') == $so->id ? 'selected' : '' }}>
                                        {{ $so->order_number }} - {{ $so->customer->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('sales_order_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="delivery_date">Delivery Date <span class="text-danger">*</span></label>
                            <input type="date" name="delivery_date" id="delivery_date" 
                                   class="form-control @error('delivery_date') is-invalid @enderror" 
                                   value="{{ old('delivery_date', date('Y-m-d')) }}" required>
                            @error('delivery_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="scheduled_date">Scheduled Date <span class="text-danger">*</span></label>
                            <input type="date" name="scheduled_date" id="scheduled_date" 
                                   class="form-control @error('scheduled_date') is-invalid @enderror" 
                                   value="{{ old('scheduled_date', date('Y-m-d', strtotime('+2 days'))) }}" required>
                            @error('scheduled_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="delivery_address">Delivery Address <span class="text-danger">*</span></label>
                            <textarea name="delivery_address" id="delivery_address" rows="3" 
                                      class="form-control @error('delivery_address') is-invalid @enderror" required>{{ old('delivery_address', $selectedSalesOrder->customer->address ?? '') }}</textarea>
                            @error('delivery_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="carrier">Carrier</label>
                                    <input type="text" name="carrier" id="carrier" 
                                           class="form-control @error('carrier') is-invalid @enderror" 
                                           value="{{ old('carrier') }}" placeholder="e.g., JNE, TIKI, GoSend">
                                    @error('carrier')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tracking_number">Tracking Number</label>
                                    <input type="text" name="tracking_number" id="tracking_number" 
                                           class="form-control @error('tracking_number') is-invalid @enderror" 
                                           value="{{ old('tracking_number') }}" placeholder="e.g., 123456789">
                                    @error('tracking_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
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
                <h5 class="mb-0">Delivery Items</h5>
                <button type="button" class="btn btn-sm btn-light" id="load-items-btn" 
                        {{ !$selectedSalesOrder ? 'disabled' : '' }}>
                    <i class="fas fa-sync-alt"></i> Load Items from Sales Order
                </button>
            </div>
            <div class="card-body">
                <div class="alert alert-info" id="no-items-alert" style="display: none;">
                    No items available for delivery from the selected sales order.
                </div>
                
                <div class="table-responsive" id="items-table-container" style="display: none;">
                    <table class="table table-bordered" id="items-table">
                        <thead class="thead-light">
                            <tr>
                                <th width="25%">Product</th>
                                <th width="25%">Description</th>
                                <th width="15%">Unit Price</th>
                                <th width="15%">Available Qty</th>
                                <th width="15%">Delivery Qty</th>
                                <th width="5%"></th>
                            </tr>
                        </thead>
                        <tbody id="items-container">
                            <!-- Items will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="text-right">
            <a href="{{ route('sales.delivery.index') }}" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary" id="submit-btn" disabled>
                <i class="fas fa-save"></i> Save Delivery Order
            </button>
        </div>
    </form>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let selectedSalesOrder = null;

    // Load items when sales order is selected
    $('#sales_order_id').change(function() {
        const salesOrderId = $(this).val();
        if (salesOrderId) {
            loadSalesOrderItems(salesOrderId);
            $('#load-items-btn').prop('disabled', false);
        } else {
            clearItems();
            $('#load-items-btn').prop('disabled', true);
        }
    });

    // Load items button
    $('#load-items-btn').click(function() {
        const salesOrderId = $('#sales_order_id').val();
        if (salesOrderId) {
            loadSalesOrderItems(salesOrderId);
        }
    });

    function loadSalesOrderItems(salesOrderId) {
        $.ajax({
            url: '{{ route("sales.delivery.sales.order.items", ["salesOrderId" => ":id"]) }}'.replace(':id', salesOrderId),
            type: 'GET',
            success: function(items) {
                if (items.length > 0) {
                    renderItems(items);
                    $('#items-table-container').show();
                    $('#no-items-alert').hide();
                    $('#submit-btn').prop('disabled', false);
                } else {
                    clearItems();
                    $('#no-items-alert').show();
                    $('#submit-btn').prop('disabled', true);
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
        
        items.forEach(function(item, index) {
            const row = `
                <tr class="item-row">
                    <td>
                        <input type="hidden" name="items[${index}][sales_order_item_id]" value="${item.id}">
                        <input type="hidden" name="items[${index}][product_id]" value="${item.product_id}">
                        <strong>${item.product_name}</strong>
                    </td>
                    <td>
                        <textarea name="items[${index}][description]" class="form-control form-control-sm" rows="1">${item.description || ''}</textarea>
                    </td>
                    <td>
                        <input type="number" name="items[${index}][unit_price]" 
                               class="form-control form-control-sm" 
                               value="${item.unit_price}" readonly>
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm" 
                               value="${item.remaining_quantity}" readonly>
                    </td>
                    <td>
                        <input type="number" name="items[${index}][quantity]" 
                               class="form-control form-control-sm delivery-qty" 
                               min="0.01" max="${item.remaining_quantity}" step="0.01"
                               value="${item.quantity}" required>
                    </td>
                    <td>
                        <textarea name="items[${index}][notes]" class="form-control form-control-sm" rows="1" placeholder="Notes"></textarea>
                    </td>
                </tr>
            `;
            container.append(row);
        });
    }

    function clearItems() {
        $('#items-container').empty();
        $('#items-table-container').hide();
        $('#no-items-alert').hide();
    }

    // Validate form before submit
    $('#delivery-order-form').submit(function(e) {
        let hasItems = false;
        let allValid = true;
        
        $('.delivery-qty').each(function() {
            const max = parseFloat($(this).attr('max'));
            const value = parseFloat($(this).val());
            
            if (value > 0) {
                hasItems = true;
            }
            
            if (value > max) {
                alert('Delivery quantity cannot exceed available quantity');
                allValid = false;
                return false;
            }
        });
        
        if (!hasItems) {
            alert('Please select at least one item with quantity > 0');
            e.preventDefault();
            return false;
        }
        
        if (!allValid) {
            e.preventDefault();
            return false;
        }
    });
});
</script>
@endpush
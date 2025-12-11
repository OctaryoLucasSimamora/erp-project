@extends('layouts.app')
@section('title', 'Vendor Bill Management')

@section('content')
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Vendor Bills</h5>
            <a href="{{ route('purchase.vendor-bill.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Bill
            </a>
        </div>

        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th width="50">No</th>
                        <th>Bill Number</th>
                        <th>Vendor</th>
                        <th>Bill Date</th>
                        <th>Due Date</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th width="200" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vendorBills as $index => $bill)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $bill->bill_number }}</strong>
                                @if ($bill->purchaseOrder)
                                    <br><small class="text-muted">From: {{ $bill->purchaseOrder->po_number }}</small>
                                @endif
                            </td>
                            <td>{{ $bill->vendor->name }}</td>
                            <td>{{ date('d/m/Y', strtotime($bill->bill_date)) }}</td>
                            <td>
                                @if ($bill->due_date)
                                    {{ date('d/m/Y', strtotime($bill->due_date)) }}
                                    @if ($bill->due_date < date('Y-m-d') && $bill->status != 'paid')
                                        <br><span class="badge bg-danger">Overdue</span>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td>Rp {{ number_format($bill->total_amount, 2) }}</td>
                            <td>
                                @php
                                    $statusColors = [
                                        'draft' => 'secondary',
                                        'posted' => 'info',
                                        'paid' => 'success',
                                        'cancelled' => 'danger',
                                    ];
                                    $color = $statusColors[$bill->status] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $color }}">{{ strtoupper($bill->status) }}</span>
                                @if ($bill->status != 'paid')
                                    <br><small class="text-muted">Balance: Rp
                                        {{ number_format($bill->balance, 2) }}</small>
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($bill->status == 'draft' || $bill->status == 'posted')
                                    <a href="{{ route('purchase.vendor-bill.payment.create', $bill->id) }}"
                                        class="btn btn-sm btn-success">
                                        <i class="fas fa-money-bill-wave"></i> Pay
                                    </a>
                                @endif

                                <a href="{{ route('purchase.vendor-bill.edit', $bill->id) }}"
                                    class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i> Edit
                                </a>

                                <form action="{{ route('purchase.vendor-bill.destroy', $bill->id) }}" method="POST"
                                    class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"
                                        onclick="return confirm('Delete this bill?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">No Vendor Bills found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $vendorBills->links() }}
        </div>
    </div>

    <!-- Payment Modals -->
    @foreach ($vendorBills as $bill)
        @if ($bill->status == 'draft' || $bill->status == 'posted')
            <div class="modal fade" id="paymentModal{{ $bill->id }}" tabindex="-1"
                aria-labelledby="paymentModalLabel{{ $bill->id }}" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('purchase.vendor-bill.payment.process', $bill->id) }}" method="POST">
                            @csrf
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title">Process Payment</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Vendor Bill</label>
                                    <input type="text" class="form-control"
                                        value="{{ $bill->bill_number }} - {{ $bill->vendor->name }}" readonly>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Total Amount</label>
                                    <input type="text" class="form-control"
                                        value="Rp {{ number_format($bill->total_amount, 2) }}" readonly>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Balance Due</label>
                                    <input type="text" class="form-control"
                                        value="Rp {{ number_format($bill->balance, 2) }}" readonly>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Payment Method *</label>
                                    <select name="payment_method" class="form-control" required>
                                        <option value="">Select Method</option>
                                        <option value="cash">Cash</option>
                                        <option value="transfer">Bank Transfer</option>
                                        <option value="check">Check</option>
                                        <option value="credit_card">Credit Card</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Amount *</label>
                                    <input type="number" name="amount" class="form-control" step="0.01" min="0.01"
                                        max="{{ $bill->balance }}" value="{{ $bill->balance }}" required>
                                    <div class="form-text">Maximum: Rp {{ number_format($bill->balance, 2) }}</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Payment Date *</label>
                                    <input type="date" name="payment_date" class="form-control"
                                        value="{{ date('Y-m-d') }}" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Memo</label>
                                    <textarea name="memo" class="form-control" rows="2"></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Reference</label>
                                    <input type="text" name="reference" class="form-control">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check"></i> Process Payment
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
@endsection

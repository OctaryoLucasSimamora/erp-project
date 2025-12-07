@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Create Bill of Materials</h4>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('bom.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label>Product <span class="text-danger">*</span></label>
            <select name="product_id" class="form-control" required>
                <option value="">-- Pilih Product --</option>
                @foreach($products as $p)
                    <option value="{{ $p->id }}" {{ old('product_id') == $p->id ? 'selected' : '' }}>
                        {{ $p->name }}
                    </option>
                @endforeach
            </select>
            @error('product_id')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <div class="form-group">
            <label>Qty Produced <span class="text-danger">*</span></label>
            <input type="number" name="quantity" class="form-control" value="{{ old('quantity', 1) }}" step="0.01" min="0.01" required>
            @error('quantity')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <hr>

        <h5>Bahan Baku <span class="text-danger">*</span></h5>
        <table class="table table-bordered" id="bomTable">
            <thead>
                <tr>
                    <th width="40%">Bahan Baku</th>
                    <th width="20%">Qty</th>
                    <th width="20%">Harga Satuan</th>
                    <th width="15%">Subtotal</th>
                    <th width="5%"></th>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-right"><strong>Total Cost:</strong></td>
                    <td><strong id="grandTotal">Rp 0</strong></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        <button type="button" id="addLine" class="btn btn-secondary">+ Tambah Bahan</button>

        <br><br>
        <button type="submit" class="btn btn-primary">Simpan BoM</button>
        <a href="{{ route('bom.index') }}" class="btn btn-secondary">Batal</a>

    </form>
</div>

<script>
let rawMaterials = @json($raws);

document.getElementById('addLine').onclick = function() {
    let options = '';
    rawMaterials.forEach(r => {
        options += `<option value="${r.id}" data-price="${r.price}">${r.name} (Rp ${formatNumber(r.price)})</option>`;
    });

    let html = `
    <tr>
        <td>
            <select name="raw_material_id[]" class="form-control raw-select" required>
                <option value="">-- Pilih Bahan --</option>
                ${options}
            </select>
        </td>

        <td>
            <input type="number" name="qty[]" class="form-control qty-input" step="0.01" min="0.01" value="1" required>
        </td>

        <td>
            <input type="text" class="form-control price-display" readonly>
        </td>

        <td>
            <input type="text" class="form-control subtotal-display" readonly>
        </td>

        <td>
            <button type="button" class="btn btn-danger btn-sm remove">X</button>
        </td>
    </tr>
    `;
    document.querySelector('#bomTable tbody').insertAdjacentHTML('beforeend', html);
};

// Format number to currency
function formatNumber(num) {
    return parseFloat(num).toLocaleString('id-ID');
}

// Calculate subtotal
function calculateRow(row) {
    let select = row.querySelector('.raw-select');
    let qtyInput = row.querySelector('.qty-input');
    let priceDisplay = row.querySelector('.price-display');
    let subtotalDisplay = row.querySelector('.subtotal-display');

    let price = parseFloat(select.selectedOptions[0]?.dataset.price || 0);
    let qty = parseFloat(qtyInput.value || 0);
    let subtotal = price * qty;

    priceDisplay.value = 'Rp ' + formatNumber(price);
    subtotalDisplay.value = 'Rp ' + formatNumber(subtotal);

    calculateTotal();
}

// Calculate grand total
function calculateTotal() {
    let total = 0;
    document.querySelectorAll('#bomTable tbody tr').forEach(row => {
        let select = row.querySelector('.raw-select');
        let qty = parseFloat(row.querySelector('.qty-input').value || 0);
        let price = parseFloat(select.selectedOptions[0]?.dataset.price || 0);
        total += (price * qty);
    });
    document.getElementById('grandTotal').textContent = 'Rp ' + formatNumber(total);
}

// Event listeners
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('raw-select') || e.target.classList.contains('qty-input')) {
        calculateRow(e.target.closest('tr'));
    }
});

document.addEventListener('click', function(e) {
    if(e.target.classList.contains('remove')) {
        e.target.closest('tr').remove();
        calculateTotal();
    }
});

// Add first row on load
document.getElementById('addLine').click();
</script>

@endsection
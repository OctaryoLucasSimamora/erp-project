@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Edit Bill of Materials</h4>

    <form action="{{ route('bom.update', $bom->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label>Product</label>
            <select name="product_id" class="form-control" disabled>
                @foreach($products as $p)
                    <option value="{{ $p->id }}" {{ $bom->product_id == $p->id ? 'selected' : '' }}>
                        {{ $p->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Qty Produced</label>
            <input type="number" name="quantity" value="{{ $bom->quantity }}" class="form-control">
        </div>

        <hr>

        <h5>BoM Lines</h5>

        <table class="table" id="bomTable">
            <thead>
                <tr>
                    <th>Raw Material</th>
                    <th>Qty</th>
                    <th>Unit Cost</th>
                    <th>Subtotal</th>
                    <th>#</th>
                </tr>
            </thead>
            <tbody>

                @foreach($bom->lines as $line)
                <tr>
                    <td>
                        <select name="raw_material_id[]" class="form-control raw-select">
                            @foreach($raws as $r)
                                <option value="{{ $r->id }}" 
                                    data-price="{{ $r->price }}"
                                    {{ $r->id == $line->raw_material_id ? 'selected' : '' }}>
                                    {{ $r->name }} (Rp {{ $r->price }})
                                </option>
                            @endforeach
                        </select>
                    </td>

                    <td>
                        <input type="number" name="qty[]" class="form-control qty-input" step="0.01" value="{{ $line->quantity }}">
                    </td>

                    <td>
                        <input type="text" class="form-control cost-input" value="{{ $line->cost }}" readonly>
                    </td>

                    <td>
                        <input type="text" class="form-control subtotal-input" value="{{ $line->subtotal }}" readonly>
                    </td>

                    <td>
                        <button type="button" class="btn btn-danger remove">X</button>
                    </td>
                </tr>
                @endforeach

            </tbody>
        </table>

        <button type="button" id="addLine" class="btn btn-secondary mb-3">Tambah Baris</button>

        <button class="btn btn-primary">Update</button>
    </form>
</div>

<script>
function recalc(row) {
    let price = parseFloat(row.querySelector('.raw-select').selectedOptions[0].dataset.price);
    let qty = parseFloat(row.querySelector('.qty-input').value || 0);
    row.querySelector('.cost-input').value = price;
    row.querySelector('.subtotal-input').value = (price * qty).toFixed(2);
}

document.addEventListener('change', function(e) {
    if (e.target.classList.contains('raw-select') || e.target.classList.contains('qty-input')) {
        recalc(e.target.closest('tr'));
    }
});

document.getElementById('addLine').onclick = function() {
    let html = `
    <tr>
        <td>
            <select name="raw_material_id[]" class="form-control raw-select">
                @foreach($raws as $r)
                    <option value="{{ $r->id }}" data-price="{{ $r->price }}">
                        {{ $r->name }} (Rp {{ $r->price }})
                    </option>
                @endforeach
            </select>
        </td>

        <td>
            <input type="number" name="qty[]" class="form-control qty-input" step="0.01" value="1">
        </td>

        <td><input type="text" class="form-control cost-input" readonly></td>
        <td><input type="text" class="form-control subtotal-input" readonly></td>

        <td><button type="button" class="btn btn-danger remove">X</button></td>
    </tr>
    `;
    let tbody = document.querySelector('#bomTable tbody');
    tbody.insertAdjacentHTML('beforeend', html);
};

document.addEventListener('click', function(e) {
    if(e.target.classList.contains('remove')) {
        e.target.closest('tr').remove();
    }
});
</script>
@endsection

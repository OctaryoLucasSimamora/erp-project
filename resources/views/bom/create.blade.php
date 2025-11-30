@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Create Bill of Materials</h4>

    <form action="{{ route('bom.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label>Product</label>
            <select name="product_id" class="form-control">
                @foreach($products as $p)
                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Qty Produced</label>
            <input type="number" name="quantity" class="form-control" value="1">
        </div>

        <hr>

        <h5>Bahan Baku</h5>
        <table class="table" id="bomTable">
            <thead>
                <tr>
                    <th>Bahan Baku</th>
                    <th>Qty</th>
                    <th>Satuan</th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

        <button type="button" id="addLine" class="btn btn-secondary">Tambah Baris</button>

        <br><br>
        <button class="btn btn-primary">Simpan</button>

    </form>
</div>

<script>
document.getElementById('addLine').onclick = function() {
    let html = `
    <tr>
        <td>
            <select name="raw_material_id[]" class="form-control">
                @foreach($raws as $r)
                    <option value="{{ $r->id }}">{{ $r->name }} (Rp {{ $r->price }})</option>
                @endforeach
            </select>
        </td>

        <td>
            <input type="number" name="qty[]" class="form-control" step="0.01" required>
        </td>

        <td>
            <input type="text" class="form-control" value="auto" disabled>
        </td>

        <td>
            <button type="button" class="btn btn-danger remove">X</button>
        </td>
    </tr>
    `;
    document.querySelector('#bomTable tbody').insertAdjacentHTML('beforeend', html);
};

document.addEventListener('click', function(e) {
    if(e.target.classList.contains('remove')) {
        e.target.closest('tr').remove();
    }
});
</script>

@endsection

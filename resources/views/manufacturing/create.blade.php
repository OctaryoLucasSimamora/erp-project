@extends('layouts.app')

@section('content')
<h3>Create Manufacturing Order</h3>

@if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

<form action="{{ route('manufacturing.store') }}" method="POST">
@csrf

<div class="mb-3">
    <label>Product</label>
    <select name="product_id" class="form-control" required>
        <option value="">Pilih Product</option>
        @foreach($products as $p)
            <option value="{{ $p->id }}">{{ $p->product_code }} - {{ $p->name }}</option>
        @endforeach
    </select>
</div>

<div class="mb-3">
    <label>Quantity</label>
    <input type="number" name="quantity" class="form-control" min="1" required>
</div>

<div class="mb-3">
    <label>Deadline</label>
    <input type="date" name="deadline" class="form-control">
</div>

<div class="mb-3">
    <label>BoM</label>
    <select name="bom_id" class="form-control" required>
        <option value="">Pilih Bill of Materials</option>
        @foreach($boms as $b)
            <option value="{{ $b->id }}">
                BoM #{{ $b->id }} - {{ $b->product->name }} 
                ({{ $b->quantity }} {{ $b->product->unit }})
            </option>
        @endforeach
    </select>
    <small class="text-muted">Pilih BOM yang sesuai dengan produk</small>
</div>

<button class="btn btn-success">Save Manufacturing Order</button>
<a href="{{ route('manufacturing.index') }}" class="btn btn-secondary">Cancel</a>

</form>
@endsection
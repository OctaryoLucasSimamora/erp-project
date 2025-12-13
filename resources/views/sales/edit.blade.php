@extends('layouts.app')

@section('content')

<h3>Manufacturing Order #{{ $mo->id }}</h3>

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger">
        {!! session('error') !!}
    </div>
@endif

{{-- STATUS DISPLAY --}}
@php
    $statusColors = [
        'draft' => 'secondary',
        'to_do' => 'warning',
        'check' => 'info',
        'done' => 'success'
    ];
    $color = $statusColors[$mo->status] ?? 'secondary';
@endphp

<div class="alert alert-{{ $color }}">
    <strong>Status:</strong> <span class="badge bg-{{ $color }}">{{ strtoupper($mo->status) }}</span>
</div>

{{-- FORM UPDATE (HANYA UNTUK EDIT) --}}
<form action="{{ route('manufacturing.update', $mo->id) }}" method="POST">
    @csrf
    @method('PUT')

    {{-- PRODUCT --}}
    <div class="mb-3">
        <label>Product</label>
        <select name="product_id" class="form-control" disabled>
            <option value="{{ $mo->product->id }}">
                {{ $mo->product->product_code }} - {{ $mo->product->name }}
            </option>
        </select>
    </div>

    {{-- QUANTITY --}}
    <div class="mb-3">
        <label>Quantity</label>
        <input type="number" class="form-control" value="{{ $mo->quantity }}" disabled>
        <small class="text-muted">Unit: {{ $mo->product->unit }}</small>
    </div>

    {{-- DEADLINE --}}
    <div class="mb-3">
        <label>Deadline</label>
        <input type="date" name="deadline" class="form-control"
               value="{{ $mo->deadline ? date('Y-m-d', strtotime($mo->deadline)) : '' }}">
    </div>

    {{-- UPDATE ONLY DEADLINE --}}
    <button class="btn btn-primary mb-3">Save Deadline</button>
</form>

<hr>

{{-- MATERIAL LINES --}}
<h5>Bahan Baku</h5>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Nama Bahan Baku</th>
            <th>Kode</th>
            <th>Qty Required</th>
            <th>Qty Consumed</th>
            <th>Unit</th>
            <th>Stok Tersedia</th>
        </tr>
    </thead>
    <tbody>
        @foreach($mo->lines as $line)
        @php
            $availableStock = $line->raw->stock ?? 0;
            $isSufficient = $availableStock >= $line->qty_required;
        @endphp
        <tr class="{{ !$isSufficient ? 'table-warning' : '' }}">
            <td>{{ $line->raw->name }}</td>
            <td>{{ $line->raw->material_code }}</td>
            <td>{{ $line->qty_required }}</td>
            <td>{{ $line->qty_consumed }}</td>
            <td>{{ $line->raw->unit }}</td>
            <td class="{{ !$isSufficient ? 'text-danger' : 'text-success' }}">
                {{ $availableStock }}
                @if(!$isSufficient)
                    <br><small>Kurang: {{ $line->qty_required - $availableStock }}</small>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<hr>

{{-- STATUS LOGIC BUTTON --}}
<form action="{{ route('manufacturing.status', $mo->id) }}" method="POST">
    @csrf

    @if($mo->status == 'draft')
        <button class="btn btn-warning">Mark as To Do</button>

    @elseif($mo->status == 'to_do')
        <button class="btn btn-info">Check Availability</button>

    @elseif($mo->status == 'check')
        <button class="btn btn-success">Mark as Done</button>

    @elseif($mo->status == 'done')
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> Manufacturing Completed
        </div>
    @endif

</form>

<hr>
<a href="{{ route('manufacturing.index') }}" class="btn btn-secondary">Back to List</a>

@endsection
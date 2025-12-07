@extends('layouts.app')

@section('content')
<h3>Manufacturing Order</h3>

<a href="{{ route('manufacturing.create') }}" class="btn btn-primary mb-3">Create</a>

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

<table class="table table-bordered">
    <thead>
        <tr>
            <th>No</th>
            <th>Kode Produk</th>
            <th>Quantity</th>
            <th>Deadline</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
    </thead>

    <tbody>
        @forelse($mos as $index => $mo)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $mo->product->product_code ?? 'N/A' }} - {{ $mo->product->name ?? 'N/A' }}</td>
            <td>{{ $mo->quantity }} {{ $mo->product->unit ?? '' }}</td>
            <td>{{ $mo->deadline ? date('d/m/Y', strtotime($mo->deadline)) : '-' }}</td>
            <td>
                @php
                    $statusColors = [
                        'draft' => 'secondary',
                        'to_do' => 'warning',
                        'check' => 'info',
                        'done' => 'success'
                    ];
                    $color = $statusColors[$mo->status] ?? 'secondary';
                @endphp
                <span class="badge bg-{{ $color }}">{{ strtoupper($mo->status) }}</span>
            </td>
            <td>
                <a href="{{ route('manufacturing.edit', $mo->id) }}" 
                   class="btn btn-sm btn-info">Detail</a>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="6" class="text-center">Tidak ada Manufacturing Order</td>
        </tr>
        @endforelse
    </tbody>
</table>
@endsection
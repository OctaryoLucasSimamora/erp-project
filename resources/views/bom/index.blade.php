@extends('layouts.app')

@section('content')
    <div class="container">
        <h4 class="mb-3">Bill of Materials</h4>

        <a href="{{ route('bom.create') }}" class="btn btn-primary mb-3">+ Create BoM</a>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Qty Produced</th>
                    <th>Total Cost</th>
                    <th>Created At</th>
                    <th width="150px">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($boms as $bom)
                    <tr>
                        <td>{{ $bom->product->name }}</td>
                        <td>{{ $bom->quantity }}</td>
                        <td>Rp {{ number_format($bom->total_cost, 0) }}</td>
                        <td>{{ $bom->created_at->format('d-m-Y') }}</td>
                        <td>
                            <a href="{{ route('bom.edit', $bom->id) }}" class="btn btn-warning btn-sm">Edit</a>

                            <form action="{{ route('bom.destroy', $bom->id) }}" method="POST" style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button onclick="return confirm('Delete BoM?')" class="btn btn-danger btn-sm">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

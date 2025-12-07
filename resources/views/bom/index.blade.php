@extends('layouts.app')

@section('content')
    <div class="container">
        <h4 class="mb-3">Bill of Materials</h4>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif

        <a href="{{ route('bom.create') }}" class="btn btn-primary mb-3">
            <i class="fas fa-plus"></i> Create BoM
        </a>

        @if($boms->count() > 0)
        <table class="table table-striped table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>No</th>
                    <th>Product</th>
                    <th>Qty Produced</th>
                    <th>Total Cost</th>
                    <th>Created At</th>
                    <th width="180px">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($boms as $index => $bom)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $bom->product->name }}</td>
                        <td>{{ number_format($bom->quantity, 2) }}</td>
                        <td>Rp {{ number_format($bom->total_cost, 0, ',', '.') }}</td>
                        <td>{{ $bom->created_at->format('d M Y') }}</td>
                        <td>
                            <a href="{{ route('bom.edit', $bom->id) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>

                            <form action="{{ route('bom.destroy', $bom->id) }}" method="POST" style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button onclick="return confirm('Yakin ingin menghapus BoM ini?')" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @else
            <div class="alert alert-info">
                Belum ada data BoM. <a href="{{ route('bom.create') }}">Buat BoM pertama Anda</a>
            </div>
        @endif
    </div>
@endsection
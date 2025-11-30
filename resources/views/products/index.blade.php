@extends('layouts.app')
@section('title','Produk')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4>Daftar Produk</h4>
    <a href="{{ route('products.create') }}" class="btn btn-primary">Tambah Produk</a>
</div>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card shadow">
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>Kategori</th>
                    <th>Satuan</th>
                    <th>Harga</th>
                    <th width="150">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $p)
                <tr>
                    <td>{{ $p->product_code }}</td>
                    <td>{{ $p->name }}</td>
                    <td>{{ $p->category }}</td>
                    <td>{{ $p->unit }}</td>
                    <td>{{ number_format($p->price) }}</td>
                    <td>
                        <a href="{{ route('products.edit',$p->id) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('products.destroy',$p->id) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Hapus produk ini?')">Del</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $products->links() }}
    </div>
</div>
@endsection

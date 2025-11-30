@extends('layouts.app')
@section('title','Bahan Baku')

@section('content')

<div class="d-flex justify-content-between mb-3">
    <h4>Daftar Bahan Baku</h4>
    <a href="{{ route('raw-materials.create') }}" class="btn btn-primary">Tambah Bahan</a>
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
                @foreach($materials as $m)
                <tr>
                    <td>{{ $m->material_code }}</td>
                    <td>{{ $m->name }}</td>
                    <td>{{ $m->category }}</td>
                    <td>{{ $m->unit }}</td>
                    <td>{{ number_format($m->price) }}</td>
                    <td>
                        <a href="{{ route('raw-materials.edit',$m->id) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('raw-materials.destroy',$m->id) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Hapus bahan ini?')">Del</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{ $materials->links() }}
    </div>
</div>

@endsection

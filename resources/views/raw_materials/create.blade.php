@extends('layouts.app')
@section('title','Tambah Bahan Baku')

@section('content')
<div class="card shadow">
    <div class="card-body">

        <form action="{{ route('raw-materials.store') }}" method="POST">
            @csrf

            <div class="form-group mb-3">
                <label>Nama Bahan</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="form-group mb-3">
                <label>Kategori</label>
                <select name="category" class="form-control">
                    @foreach($categories as $c)
                    <option value="{{ $c }}">{{ $c }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group mb-3">
                <label>Satuan</label>
                <select name="unit" class="form-control">
                    @foreach($units as $u)
                    <option value="{{ $u }}">{{ $u }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group mb-3">
                <label>Harga per Satuan</label>
                <input type="number" name="price" class="form-control" required>
            </div>

            <div class="form-group mb-3">
                <label>Deskripsi</label>
                <textarea name="description" class="form-control"></textarea>
            </div>

            <button class="btn btn-primary">Simpan</button>
        </form>

    </div>
</div>
@endsection

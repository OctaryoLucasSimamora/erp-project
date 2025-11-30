@extends('layouts.app')
@section('title','Edit Bahan Baku')

@section('content')
<div class="card shadow">
    <div class="card-body">

        <form action="{{ route('raw-materials.update', $material->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group mb-3">
                <label>Kode Bahan</label>
                <input type="text" class="form-control" value="{{ $material->material_code }}" disabled>
            </div>

            <div class="form-group mb-3">
                <label>Nama Bahan</label>
                <input type="text" name="name" class="form-control" value="{{ $material->name }}" required>
            </div>

            <div class="form-group mb-3">
                <label>Kategori</label>
                <select name="category" class="form-control">
                    @foreach($categories as $c)
                    <option value="{{ $c }}" {{ $material->category == $c ? 'selected' : '' }}>
                        {{ $c }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group mb-3">
                <label>Satuan</label>
                <select name="unit" class="form-control">
                    @foreach($units as $u)
                    <option value="{{ $u }}" {{ $material->unit == $u ? 'selected' : '' }}>
                        {{ $u }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group mb-3">
                <label>Harga per Satuan</label>
                <input type="number" name="price" class="form-control" value="{{ $material->price }}" required>
            </div>

            <div class="form-group mb-3">
                <label>Deskripsi</label>
                <textarea name="description" class="form-control">{{ $material->description }}</textarea>
            </div>

            <button class="btn btn-primary">Update</button>
        </form>

    </div>
</div>
@endsection


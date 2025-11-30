@extends('layouts.app')
@section('title', 'Edit Produk')

@section('content')
    <div class="card shadow">
        <div class="card-body">

            <form action="{{ route('products.update', $product->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group mb-3">
                    <label>Kode Produk</label>
                    <input type="text" class="form-control" value="{{ $product->product_code }}" disabled>
                </div>

                <div class="form-group mb-3">
                    <label>Nama Produk</label>
                    <input type="text" class="form-control" name="name" value="{{ $product->name }}" required>
                </div>

                <div class="form-group mb-3">
                    <label>Kategori</label>
                    <select name="category" class="form-control">
                        @foreach ($categories as $c)
                            <option value="{{ $c }}" {{ $product->category == $c ? 'selected' : '' }}>
                                {{ $c }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label>Satuan</label>
                    <select name="unit" class="form-control">
                        @foreach ($units as $u)
                            <option value="{{ $u }}" {{ $product->unit == $u ? 'selected' : '' }}>
                                {{ $u }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label>Harga Jual</label>
                    <input type="

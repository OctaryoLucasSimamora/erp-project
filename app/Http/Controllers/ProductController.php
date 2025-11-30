<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // AUTO GENERATE KODE PRODUK
    private function generateProductCode()
    {
        $last = Product::orderBy('id', 'DESC')->first();
        $next = $last ? $last->id + 1 : 1;
        return 'PRD' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    public function index()
    {
        $products = Product::latest()->paginate(10);
        return view('products.index', compact('products'));
    }

    public function create()
    {
        $categories = ['Makanan', 'Minuman'];
        $units = ['pcs', 'cup', 'pack', 'box'];
        return view('products.create', compact('categories', 'units'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'category' => 'required',
            'unit' => 'required',
            'price' => 'required|numeric',
        ]);

        Product::create([
            'product_code' => $this->generateProductCode(),
            'name' => $request->name,
            'category' => $request->category,
            'unit' => $request->unit,
            'price' => $request->price,
            'description' => $request->description,
        ]);

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil ditambahkan!');
    }

    public function edit(Product $product)
    {
        $categories = ['Makanan', 'Minuman'];
        $units = ['pcs', 'cup', 'pack', 'box'];

        return view('products.edit', compact('product', 'categories', 'units'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required',
            'category' => 'required',
            'unit' => 'required',
            'price' => 'required|numeric',
        ]);

        $product->update($request->all());

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil diupdate!');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil dihapus!');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\RawMaterial;
use Illuminate\Http\Request;

class RawMaterialController extends Controller
{
    // AUTO GENERATE KODE: RM0001, RM0002 dst
    private function generateCode()
    {
        $last = RawMaterial::orderBy('id', 'DESC')->first();
        $next = $last ? $last->id + 1 : 1;
        return 'RM' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    public function index()
    {
        $materials = RawMaterial::latest()->paginate(10);
        return view('raw_materials.index', compact('materials'));
    }

    public function create()
    {
        $categories = ['Bahan Makanan', 'Bahan Minuman', 'Kemasan'];
        $units = ['gr', 'ml', 'pcs', 'cup', 'pack'];
        return view('raw_materials.create', compact('categories','units'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'category' => 'required',
            'unit' => 'required',
            'price' => 'required|numeric',
            'stock' => 'required|numeric|min:0', // VALIDASI STOK
        ]);

        RawMaterial::create([
            'material_code' => $this->generateCode(),
            'name' => $request->name,
            'category' => $request->category,
            'unit' => $request->unit,
            'price' => $request->price,
            'stock' => $request->stock, // SIMPAN STOK
            'description' => $request->description,
        ]);

        return redirect()->route('raw-materials.index')
            ->with('success','Bahan baku berhasil ditambahkan');
    }

    public function edit(RawMaterial $raw_material)
    {
        $categories = ['Bahan Makanan', 'Bahan Minuman', 'Kemasan'];
        $units = ['gr', 'ml', 'pcs', 'cup', 'pack'];

        return view('raw_materials.edit', [
            'material' => $raw_material,
            'categories' => $categories,
            'units' => $units
        ]);
    }

    public function update(Request $request, RawMaterial $raw_material)
    {
        $request->validate([
            'name' => 'required',
            'category' => 'required',
            'unit' => 'required',
            'price' => 'required|numeric',
            'stock' => 'required|numeric|min:0', // VALIDASI STOK
        ]);

        $raw_material->update([
            'name' => $request->name,
            'category' => $request->category,
            'unit' => $request->unit,
            'price' => $request->price,
            'stock' => $request->stock, // UPDATE STOK
            'description' => $request->description,
        ]);

        return redirect()->route('raw-materials.index')
            ->with('success','Bahan baku berhasil diperbarui');
    }

    public function destroy(RawMaterial $raw_material)
    {
        $raw_material->delete();
        return redirect()->route('raw-materials.index')
            ->with('success','Bahan baku berhasil dihapus');
    }
}
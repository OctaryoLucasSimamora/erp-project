<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BoM;        
use App\Models\BoMLine;   // ← TAMBAHKAN INI
use App\Models\Product;
use App\Models\RawMaterial;


class BomController extends Controller
{
    public function index()
    {
        $boms = BoM::with('product')->get();
        return view('bom.index', compact('boms'));
    }

    public function create()
    {
        $products = Product::all();
        $raws = RawMaterial::all();

        return view('bom.create', compact('products', 'raws'));
    }

    public function store(Request $request)
{
    $request->validate([
        'product_id'        => 'required',
        'raw_material_id'   => 'required|array',
        'raw_material_id.*' => 'required',
        'qty'               => 'required|array',
        'qty.*'             => 'required|numeric|min:0.0001',
    ]);

    $bom = BoM::create([
        'product_id' => $request->product_id,
        'quantity' => $request->quantity ?? 1, // jika ada field quantity di bom header
        'total_cost' => 0,
    ]);

    $total = 0;

    for ($i = 0; $i < count($request->raw_material_id); $i++) {
        $raw = RawMaterial::find($request->raw_material_id[$i]);

        // pastikan kolom harga pada raw material bernama 'price' atau 'cost' sesuai migration
        $unitPrice = $raw->price ?? $raw->cost ?? 0;

        $qty = $request->qty[$i];
        $subtotal = $unitPrice * $qty;
        $total += $subtotal;

        BoMLine::create([
            'bom_id' => $bom->id,
            'raw_material_id' => $request->raw_material_id[$i],
            'quantity' => $qty,
            'cost' => $unitPrice,
            'subtotal' => $subtotal,
        ]);
    }

    $bom->update(['total_cost' => $total]);

    return redirect()->route('bom.index')->with('success', 'BoM berhasil dibuat');
}

}

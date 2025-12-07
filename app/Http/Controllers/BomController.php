<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BoM;
use App\Models\BoMLine;
use App\Models\Product;
use App\Models\RawMaterial;
use Illuminate\Support\Facades\DB;

class BomController extends Controller
{
    public function index()
    {
        $boms = BoM::with('product')->orderBy('created_at', 'desc')->get();
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
            'product_id'        => 'required|exists:products,id',
            'quantity'          => 'required|numeric|min:0.0001',
            'raw_material_id'   => 'required|array|min:1',
            'raw_material_id.*' => 'required|exists:raw_materials,id',
            'qty'               => 'required|array',
            'qty.*'             => 'required|numeric|min:0.0001',
        ]);

        DB::beginTransaction();
        
        try {
            // Buat BoM header
            $bom = BoM::create([
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'total_cost' => 0,
            ]);

            $totalMaterialCost = 0;

            // Buat BoM Lines
            for ($i = 0; $i < count($request->raw_material_id); $i++) {
                $raw = RawMaterial::find($request->raw_material_id[$i]);
                $unitPrice = $raw->price ?? 0;
                $qtyMaterial = $request->qty[$i];
                $subtotal = $unitPrice * $qtyMaterial;
                $totalMaterialCost += $subtotal;

                BoMLine::create([
                    'bom_id' => $bom->id,
                    'raw_material_id' => $request->raw_material_id[$i],
                    'quantity' => $qtyMaterial,
                    'cost' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);
            }

            // Total Cost = Total Material Cost × Qty Produced
            $finalTotalCost = $totalMaterialCost * $request->quantity;
            $bom->update(['total_cost' => $finalTotalCost]);

            DB::commit();
            
            return redirect()->route('bom.index')->with('success', 'BoM berhasil dibuat!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal membuat BoM: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $bom = BoM::with('lines.rawMaterial')->findOrFail($id);
        $products = Product::all();
        $raws = RawMaterial::all();

        return view('bom.edit', compact('bom', 'products', 'raws'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity'          => 'required|numeric|min:0.0001',
            'raw_material_id'   => 'required|array|min:1',
            'raw_material_id.*' => 'required|exists:raw_materials,id',
            'qty'               => 'required|array',
            'qty.*'             => 'required|numeric|min:0.0001',
        ]);

        DB::beginTransaction();
        
        try {
            $bom = BoM::findOrFail($id);
            
            // Update quantity produced
            $bom->quantity = $request->quantity;
            $bom->save();

            // Hapus semua lines lama
            $bom->lines()->delete();

            // Buat lines baru
            $totalMaterialCost = 0;

            for ($i = 0; $i < count($request->raw_material_id); $i++) {
                $raw = RawMaterial::find($request->raw_material_id[$i]);
                $unitPrice = $raw->price ?? 0;
                $qtyMaterial = $request->qty[$i];
                $subtotal = $unitPrice * $qtyMaterial;
                $totalMaterialCost += $subtotal;

                BoMLine::create([
                    'bom_id' => $bom->id,
                    'raw_material_id' => $request->raw_material_id[$i],
                    'quantity' => $qtyMaterial,
                    'cost' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);
            }

            // Total Cost = Total Material Cost × Qty Produced
            $finalTotalCost = $totalMaterialCost * $request->quantity;
            $bom->update(['total_cost' => $finalTotalCost]);

            DB::commit();
            
            return redirect()->route('bom.index')->with('success', 'BoM berhasil diupdate!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal mengupdate BoM: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        
        try {
            $bom = BoM::findOrFail($id);
            
            // Hapus semua lines (otomatis karena cascade di migration)
            $bom->lines()->delete();
            
            // Hapus bom
            $bom->delete();

            DB::commit();
            
            return redirect()->route('bom.index')->with('success', 'BoM berhasil dihapus!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus BoM: ' . $e->getMessage());
        }
    }

    // Method tambahan untuk melihat detail BoM (opsional)
    public function show($id)
    {
        $bom = BoM::with(['product', 'lines.rawMaterial'])->findOrFail($id);
        return view('bom.show', compact('bom'));
    }
}
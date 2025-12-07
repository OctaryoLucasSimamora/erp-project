<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ManufacturingOrder;
use App\Models\ManufacturingOrderLine;
use App\Models\Product;
use App\Models\BoM;
use App\Models\BoMLine;
use App\Models\RawMaterial;

class ManufacturingOrderController extends Controller
{
    public function index()
    {
        $mos = ManufacturingOrder::with('product')->orderBy('created_at', 'desc')->get();
        return view('manufacturing.index', compact('mos'));
    }

    public function create()
    {
        $products = Product::all();
        $boms = BoM::with('product')->get();
        return view('manufacturing.create', compact('products', 'boms'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'deadline' => 'nullable|date',
            'bom_id' => 'required|exists:boms,id',
        ]);

        $mo = ManufacturingOrder::create([
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
            'deadline' => $request->deadline,
            'bom_id' => $request->bom_id,
            'status' => 'draft'
        ]);

        // Ambil data dari BOM yang dipilih
        $bomLines = BoMLine::where('bom_id', $request->bom_id)->get();

        foreach ($bomLines as $line) {
            // Hitung kebutuhan bahan berdasarkan quantity MO
            $qtyRequired = $line->quantity * $request->quantity;
            
            ManufacturingOrderLine::create([
                'mo_id' => $mo->id,
                'raw_material_id' => $line->raw_material_id,
                'qty_required' => $qtyRequired,
                'qty_consumed' => 0,
            ]);
        }

        return redirect()->route('manufacturing.edit', $mo->id)
            ->with('success', 'Manufacturing Order berhasil dibuat!');
    }

    public function edit($id)
    {
        $mo = ManufacturingOrder::with(['product', 'lines.raw'])->findOrFail($id);
        return view('manufacturing.edit', compact('mo'));
    }

    public function update(Request $request, $id)
    {
        $mo = ManufacturingOrder::findOrFail($id);
        
        $request->validate([
            'deadline' => 'nullable|date',
        ]);

        $mo->update([
            'deadline' => $request->deadline
        ]);

        return redirect()->back()->with('success', 'Deadline berhasil diperbarui!');
    }

    public function updateStatus(Request $request, $id)
    {
        $mo = ManufacturingOrder::with('lines.raw')->findOrFail($id);
        $lines = $mo->lines;

        switch ($mo->status) {
            case 'draft':
                $mo->update(['status' => 'to_do']);
                $message = 'Status berhasil diubah menjadi To Do!';
                break;

            case 'to_do':
                // CHECK AVAILABILITY - cek stok bahan baku
                $isAvailable = true;
                $unavailableMaterials = [];
                
                foreach ($lines as $line) {
                    // Asumsi: RawMaterial memiliki field 'stock' (Anda perlu menambahkannya di migration)
                    // Jika belum ada, Anda perlu menambahkan field stock di tabel raw_materials
                    $currentStock = $line->raw->stock ?? 0;
                    
                    if ($currentStock < $line->qty_required) {
                        $isAvailable = false;
                        $unavailableMaterials[] = [
                            'name' => $line->raw->name,
                            'required' => $line->qty_required,
                            'available' => $currentStock,
                            'shortage' => $line->qty_required - $currentStock
                        ];
                    }
                }
                
                if ($isAvailable) {
                    $mo->update(['status' => 'check']);
                    $message = 'Stok tersedia! Status berubah menjadi Check.';
                } else {
                    // Kembalikan informasi bahan yang kurang
                    $errorMessage = 'Stok tidak mencukupi untuk: <br>';
                    foreach ($unavailableMaterials as $material) {
                        $errorMessage .= "- {$material['name']}: butuh {$material['required']}, tersedia {$material['available']} (kurang {$material['shortage']})<br>";
                    }
                    
                    return redirect()->back()->with('error', $errorMessage);
                }
                break;

            case 'check':
                // MARK AS DONE - kurangi stok bahan baku
                foreach ($lines as $line) {
                    // Kurangi stok bahan baku
                    $rawMaterial = RawMaterial::find($line->raw_material_id);
                    if ($rawMaterial) {
                        // Update stok (pastikan ada field stock)
                        $rawMaterial->decrement('stock', $line->qty_required);
                        
                        // Update qty_consumed
                        $line->update(['qty_consumed' => $line->qty_required]);
                    }
                }
                
                $mo->update(['status' => 'done']);
                $message = 'Manufacturing Order selesai! Stok bahan baku telah dikurangi.';
                break;

            case 'done':
                $message = 'Manufacturing Order sudah selesai.';
                break;
        }

        return redirect()->back()->with('success', $message);
    }
}
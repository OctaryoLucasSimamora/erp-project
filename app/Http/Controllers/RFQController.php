<?php

namespace App\Http\Controllers;

use App\Models\RFQ;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\RFQLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RFQController extends Controller
{
    public function index()
    {
        $rfqs = RFQ::with('vendor')->orderBy('created_at', 'desc')->paginate(10);
        return view('purchase.rfq.index', compact('rfqs'));
    }

    public function create()
    {
        $vendors = Vendor::orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        return view('purchase.rfq.create', compact('vendors', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'deadline' => 'nullable|date',
            'arrival_date' => 'nullable|date',
            'company' => 'nullable|string|max:255',
            'product_id' => 'required|array|min:1',
            'product_id.*' => 'required|exists:products,id',
            'quantity' => 'required|array|min:1',
            'quantity.*' => 'required|numeric|min:0.01',
            'price' => 'required|array|min:1',
            'price.*' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        
        try {
            // Create RFQ
            $rfq = RFQ::create([
                'vendor_id' => $request->vendor_id,
                'deadline' => $request->deadline,
                'arrival_date' => $request->arrival_date,
                'company' => $request->company,
                'notes' => $request->notes,
                'status' => 'draft',
                'total_amount' => 0,
            ]);

            $totalAmount = 0;

            // Create RFQ Lines
            for ($i = 0; $i < count($request->product_id); $i++) {
                $quantity = $request->quantity[$i];
                $price = $request->price[$i];
                $subtotal = $quantity * $price;
                $totalAmount += $subtotal;

                RFQLine::create([
                    'rfq_id' => $rfq->id,
                    'product_id' => $request->product_id[$i],
                    'quantity' => $quantity,
                    'price' => $price,
                    'subtotal' => $subtotal,
                    'description' => $request->description[$i] ?? null,
                ]);
            }

            // Update total amount
            $rfq->update(['total_amount' => $totalAmount]);

            DB::commit();

            return redirect()->route('purchase.rfq.index')
                ->with('success', 'RFQ berhasil dibuat!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal membuat RFQ: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $rfq = RFQ::with(['vendor', 'lines.product'])->findOrFail($id);
        $vendors = Vendor::orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        
        return view('purchase.rfq.edit', compact('rfq', 'vendors', 'products'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'deadline' => 'nullable|date',
            'arrival_date' => 'nullable|date',
            'company' => 'nullable|string|max:255',
            'product_id' => 'required|array|min:1',
            'product_id.*' => 'required|exists:products,id',
            'quantity' => 'required|array|min:1',
            'quantity.*' => 'required|numeric|min:0.01',
            'price' => 'required|array|min:1',
            'price.*' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        
        try {
            $rfq = RFQ::findOrFail($id);
            
            // Update RFQ
            $rfq->update([
                'vendor_id' => $request->vendor_id,
                'deadline' => $request->deadline,
                'arrival_date' => $request->arrival_date,
                'company' => $request->company,
                'notes' => $request->notes,
            ]);

            // Delete old lines
            $rfq->lines()->delete();

            $totalAmount = 0;

            // Create new lines
            for ($i = 0; $i < count($request->product_id); $i++) {
                $quantity = $request->quantity[$i];
                $price = $request->price[$i];
                $subtotal = $quantity * $price;
                $totalAmount += $subtotal;

                RFQLine::create([
                    'rfq_id' => $rfq->id,
                    'product_id' => $request->product_id[$i],
                    'quantity' => $quantity,
                    'price' => $price,
                    'subtotal' => $subtotal,
                    'description' => $request->description[$i] ?? null,
                ]);
            }

            // Update total amount
            $rfq->update(['total_amount' => $totalAmount]);

            DB::commit();

            return redirect()->route('purchase.rfq.index')
                ->with('success', 'RFQ berhasil diperbarui!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal memperbarui RFQ: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $rfq = RFQ::findOrFail($id);
        $rfq->delete();

        return redirect()->route('purchase.rfq.index')
            ->with('success', 'RFQ berhasil dihapus!');
    }

     public function updateStatus(Request $request, $id)
    {
        $rfq = RFQ::findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:draft,sent,done,cancelled'
        ]);

        DB::beginTransaction();
        
        try {
            $oldStatus = $rfq->status;
            $rfq->update(['status' => $request->status]);
            
            // Jika status berubah menjadi "done", bisa dibuat PO
            if ($request->status == 'done' && $oldStatus != 'done') {
                // Bisa tambahkan log atau notification di sini
            }
            
            DB::commit();
            
            return redirect()->back()->with('success', 'Status RFQ berhasil diubah!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal mengubah status: ' . $e->getMessage());
        }
    }

    // Method untuk konversi RFQ ke PO
    public function convertToPO($id)
    {
        $rfq = RFQ::with(['vendor', 'lines.product'])->findOrFail($id);
        
        if ($rfq->status != 'done') {
            return redirect()->back()->with('error', 'RFQ harus berstatus "DONE" untuk dikonversi ke Purchase Order');
        }
        
        // Cek apakah sudah ada PO dari RFQ ini
        if ($rfq->purchaseOrder) {
            return redirect()->route('purchase.po.edit', $rfq->purchaseOrder->id)
                ->with('info', 'RFQ ini sudah dikonversi ke PO: ' . $rfq->purchaseOrder->po_number);
        }
        
        // Redirect ke form create PO dengan data RFQ
        return redirect()->route('purchase.po.create')->with('rfq_data', $rfq->id);
    }
}
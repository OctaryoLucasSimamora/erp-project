<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\Vendor;
use App\Models\RFQ;
use App\Models\Product;
use App\Models\POLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $purchaseOrders = PurchaseOrder::with('vendor')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return view('purchase.po.index', compact('purchaseOrders'));
    }

       public function create()
    {
        $vendors = Vendor::orderBy('name')->get();
        $rfqs = RFQ::where('status', 'done')->orderBy('created_at', 'desc')->get();
        $products = Product::orderBy('name')->get();
        
        // Cek apakah ada data RFQ yang akan dikonversi
        $rfqId = session('rfq_data');
        $rfq = null;
        
        if ($rfqId) {
            $rfq = RFQ::with(['vendor', 'lines.product'])->find($rfqId);
        }
        
        return view('purchase.po.create', compact('vendors', 'rfqs', 'products', 'rfq'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'rfq_id' => 'nullable|exists:rfqs,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date',
            'product_id' => 'required|array|min:1',
            'product_id.*' => 'required|exists:products,id',
            'quantity' => 'required|array|min:1',
            'quantity.*' => 'required|numeric|min:0.01',
            'price' => 'required|array|min:1',
            'price.*' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        
        try {
            // Create Purchase Order
            $po = PurchaseOrder::create([
                'vendor_id' => $request->vendor_id,
                'rfq_id' => $request->rfq_id,
                'order_date' => $request->order_date,
                'expected_delivery_date' => $request->expected_delivery_date,
                'ship_to' => $request->ship_to,
                'incoterm' => $request->incoterm,
                'payment_term' => $request->payment_term,
                'notes' => $request->notes,
                'status' => 'draft',
                'subtotal' => 0,
                'tax_amount' => 0,
                'total_amount' => 0,
            ]);

            $subtotal = 0;

            // Create PO Lines
            for ($i = 0; $i < count($request->product_id); $i++) {
                $quantity = $request->quantity[$i];
                $price = $request->price[$i];
                $lineSubtotal = $quantity * $price;
                $subtotal += $lineSubtotal;

                POLine::create([
                    'purchase_order_id' => $po->id,
                    'product_id' => $request->product_id[$i],
                    'quantity' => $quantity,
                    'price' => $price,
                    'subtotal' => $lineSubtotal,
                    'description' => $request->description[$i] ?? null,
                ]);
            }

            // Calculate tax (asumsi 10%)
            $taxAmount = $subtotal * 0.10;
            $totalAmount = $subtotal + $taxAmount;

            // Update PO totals
            $po->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
            ]);

            DB::commit();

            return redirect()->route('purchase.po.index')
                ->with('success', 'Purchase Order berhasil dibuat!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal membuat Purchase Order: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $po = PurchaseOrder::with(['vendor', 'rfq', 'lines.product'])->findOrFail($id);
        $vendors = Vendor::orderBy('name')->get();
        $rfqs = RFQ::where('status', 'done')->orderBy('created_at', 'desc')->get();
        $products = Product::orderBy('name')->get();
        
        return view('purchase.po.edit', compact('po', 'vendors', 'rfqs', 'products'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date',
            'product_id' => 'required|array|min:1',
            'product_id.*' => 'required|exists:products,id',
            'quantity' => 'required|array|min:1',
            'quantity.*' => 'required|numeric|min:0.01',
            'price' => 'required|array|min:1',
            'price.*' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        
        try {
            $po = PurchaseOrder::findOrFail($id);
            
            // Update PO
            $po->update([
                'vendor_id' => $request->vendor_id,
                'order_date' => $request->order_date,
                'expected_delivery_date' => $request->expected_delivery_date,
                'ship_to' => $request->ship_to,
                'incoterm' => $request->incoterm,
                'payment_term' => $request->payment_term,
                'notes' => $request->notes,
            ]);

            // Delete old lines
            $po->lines()->delete();

            $subtotal = 0;

            // Create new lines
            for ($i = 0; $i < count($request->product_id); $i++) {
                $quantity = $request->quantity[$i];
                $price = $request->price[$i];
                $lineSubtotal = $quantity * $price;
                $subtotal += $lineSubtotal;

                POLine::create([
                    'purchase_order_id' => $po->id,
                    'product_id' => $request->product_id[$i],
                    'quantity' => $quantity,
                    'price' => $price,
                    'subtotal' => $lineSubtotal,
                    'description' => $request->description[$i] ?? null,
                ]);
            }

            // Calculate tax (asumsi 10%)
            $taxAmount = $subtotal * 0.10;
            $totalAmount = $subtotal + $taxAmount;

            // Update PO totals
            $po->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
            ]);

            DB::commit();

            return redirect()->route('purchase.po.index')
                ->with('success', 'Purchase Order berhasil diperbarui!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal memperbarui Purchase Order: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $po = PurchaseOrder::findOrFail($id);
        $po->delete();

        return redirect()->route('purchase.po.index')
            ->with('success', 'Purchase Order berhasil dihapus!');
    }

     public function updateStatus(Request $request, $id)
    {
        $po = PurchaseOrder::findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:draft,sent,confirmed,received,cancelled'
        ]);

        DB::beginTransaction();
        
        try {
            $oldStatus = $po->status;
            $po->update(['status' => $request->status]);
            
            DB::commit();
            
            return redirect()->back()->with('success', 'Status PO berhasil diubah!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal mengubah status: ' . $e->getMessage());
        }
    }

   // Method untuk konversi PO ke Vendor Bill
    public function convertToVendorBill($id)
    {
        $po = PurchaseOrder::with(['vendor', 'lines.product'])->findOrFail($id);
        
        if ($po->status != 'received') {
            return redirect()->back()->with('error', 'PO harus berstatus "RECEIVED" untuk dikonversi ke Vendor Bill');
        }
        
        // Cek apakah sudah ada Vendor Bill dari PO ini
        if ($po->vendorBills()->count() > 0) {
            $vendorBill = $po->vendorBills()->first();
            return redirect()->route('purchase.vendor-bill.edit', $vendorBill->id)
                ->with('info', 'PO ini sudah dikonversi ke Vendor Bill: ' . $vendorBill->bill_number);
        }
        
        // Redirect ke form create Vendor Bill dengan data PO
        return redirect()->route('purchase.vendor-bill.create')->with('po_data', $po->id);
    }
}
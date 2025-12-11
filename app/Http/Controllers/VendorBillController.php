<?php

namespace App\Http\Controllers;

use App\Models\VendorBill;
use App\Models\Vendor;
use App\Models\PurchaseOrder;
use App\Models\Product;
use App\Models\VendorBillLine;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendorBillController extends Controller
{
    public function index()
    {
        $vendorBills = VendorBill::with('vendor')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return view('purchase.vendor_bill.index', compact('vendorBills'));
    }

    public function create()
    {
        $vendors = Vendor::orderBy('name')->get();
        $purchaseOrders = PurchaseOrder::where('status', 'received')->orderBy('created_at', 'desc')->get();
        $products = Product::orderBy('name')->get();
        
        return view('purchase.vendor_bill.create', compact('vendors', 'purchaseOrders', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'bill_date' => 'required|date',
            'due_date' => 'nullable|date',
            'product_id' => 'required|array|min:1',
            'product_id.*' => 'required|exists:products,id',
            'quantity' => 'required|array|min:1',
            'quantity.*' => 'required|numeric|min:0.01',
            'price' => 'required|array|min:1',
            'price.*' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        
        try {
            // Create Vendor Bill
            $vendorBill = VendorBill::create([
                'vendor_id' => $request->vendor_id,
                'purchase_order_id' => $request->purchase_order_id,
                'bill_date' => $request->bill_date,
                'due_date' => $request->due_date,
                'payment_reference' => $request->payment_reference,
                'notes' => $request->notes,
                'status' => 'draft',
                'total_amount' => 0,
                'paid_amount' => 0,
                'balance' => 0,
            ]);

            $totalAmount = 0;

            // Create Vendor Bill Lines
            for ($i = 0; $i < count($request->product_id); $i++) {
                $quantity = $request->quantity[$i];
                $price = $request->price[$i];
                $subtotal = $quantity * $price;
                $totalAmount += $subtotal;

                VendorBillLine::create([
                    'vendor_bill_id' => $vendorBill->id,
                    'product_id' => $request->product_id[$i],
                    'quantity' => $quantity,
                    'price' => $price,
                    'subtotal' => $subtotal,
                    'description' => $request->description[$i] ?? null,
                ]);
            }

            // Update bill total
            $vendorBill->update([
                'total_amount' => $totalAmount,
                'balance' => $totalAmount,
            ]);

            DB::commit();

            return redirect()->route('purchase.vendor_bill.index')
                ->with('success', 'Vendor Bill berhasil dibuat!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal membuat Vendor Bill: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $vendorBill = VendorBill::with(['vendor', 'purchaseOrder', 'lines.product'])->findOrFail($id);
        $vendors = Vendor::orderBy('name')->get();
        $purchaseOrders = PurchaseOrder::where('status', 'received')->orderBy('created_at', 'desc')->get();
        $products = Product::orderBy('name')->get();
        
        return view('purchase.vendor_bill.edit', compact('vendorBill', 'vendors', 'purchaseOrders', 'products'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'bill_date' => 'required|date',
            'due_date' => 'nullable|date',
            'product_id' => 'required|array|min:1',
            'product_id.*' => 'required|exists:products,id',
            'quantity' => 'required|array|min:1',
            'quantity.*' => 'required|numeric|min:0.01',
            'price' => 'required|array|min:1',
            'price.*' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        
        try {
            $vendorBill = VendorBill::findOrFail($id);
            
            // Update Vendor Bill
            $vendorBill->update([
                'vendor_id' => $request->vendor_id,
                'bill_date' => $request->bill_date,
                'due_date' => $request->due_date,
                'payment_reference' => $request->payment_reference,
                'notes' => $request->notes,
            ]);

            // Delete old lines
            $vendorBill->lines()->delete();

            $totalAmount = 0;

            // Create new lines
            for ($i = 0; $i < count($request->product_id); $i++) {
                $quantity = $request->quantity[$i];
                $price = $request->price[$i];
                $subtotal = $quantity * $price;
                $totalAmount += $subtotal;

                VendorBillLine::create([
                    'vendor_bill_id' => $vendorBill->id,
                    'product_id' => $request->product_id[$i],
                    'quantity' => $quantity,
                    'price' => $price,
                    'subtotal' => $subtotal,
                    'description' => $request->description[$i] ?? null,
                ]);
            }

            // Update bill total and balance
            $newBalance = $totalAmount - $vendorBill->paid_amount;
            
            $vendorBill->update([
                'total_amount' => $totalAmount,
                'balance' => $newBalance,
            ]);

            DB::commit();

            return redirect()->route('purchase.vendor_bill.index')
                ->with('success', 'Vendor Bill berhasil diperbarui!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal memperbarui Vendor Bill: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $vendorBill = VendorBill::findOrFail($id);
        $vendorBill->delete();

        return redirect()->route('purchase.vendor_bill.index')
            ->with('success', 'Vendor Bill berhasil dihapus!');
    }

    public function updateStatus(Request $request, $id)
    {
        $vendorBill = VendorBill::findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:draft,posted,paid,cancelled'
        ]);

        $vendorBill->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Status Vendor Bill berhasil diubah!');
    }

    // Method untuk menampilkan halaman payment
    public function createPayment($id)
    {
        $vendorBill = VendorBill::with(['vendor', 'purchaseOrder', 'payments'])->findOrFail($id);
        
        return view('purchase.vendor_bill.payment', compact('vendorBill'));
    }

    // Method untuk memproses payment
    public function processPayment(Request $request, $id)
    {
        $request->validate([
            'payment_method' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'memo' => 'nullable|string',
            'reference' => 'nullable|string|max:100',
        ]);

        DB::beginTransaction();
        
        try {
            $vendorBill = VendorBill::findOrFail($id);
            
            // Validasi status bill
            if ($vendorBill->status == 'paid') {
                return redirect()->back()->with('error', 'Bill sudah dibayar lunas.');
            }
            
            if ($vendorBill->status == 'cancelled') {
                return redirect()->back()->with('error', 'Tidak bisa memproses payment untuk bill yang dibatalkan.');
            }
            
            // Validasi amount tidak melebihi balance
            if ($request->amount > $vendorBill->balance) {
                return redirect()->back()->with('error', 'Jumlah pembayaran melebihi sisa tagihan.');
            }
            
            // Create payment record
            $payment = Payment::create([
                'vendor_bill_id' => $vendorBill->id,
                'payment_method' => $request->payment_method,
                'amount' => $request->amount,
                'payment_date' => $request->payment_date,
                'memo' => $request->memo,
                'reference' => $request->reference,
            ]);
            
            // Update vendor bill
            $newPaidAmount = $vendorBill->paid_amount + $request->amount;
            $newBalance = $vendorBill->total_amount - $newPaidAmount;
            
            // Tentukan status baru
            $newStatus = $vendorBill->status;
            if ($newBalance <= 0) {
                $newStatus = 'paid';
            } elseif ($vendorBill->status == 'draft') {
                $newStatus = 'posted';
            }
            
            $vendorBill->update([
                'paid_amount' => $newPaidAmount,
                'balance' => $newBalance,
                'status' => $newStatus,
            ]);
            
            DB::commit();
            
            $message = 'Pembayaran berhasil diproses.';
            if ($newStatus == 'paid') {
                $message .= ' Bill telah LUNAS.';
            }
            
            return redirect()->route('purchase.vendor-bill.index')
                ->with('success', $message);
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal memproses pembayaran: ' . $e->getMessage());
        }
    }

    // Method untuk convert PO ke Vendor Bill
    public function convertFromPO($poId)
    {
        $po = PurchaseOrder::with(['vendor', 'lines.product'])->findOrFail($poId);
        
        if ($po->status !== 'received') {
            return redirect()->back()->with('error', 'PO harus berstatus "received" untuk dikonversi ke Vendor Bill');
        }

        $vendors = Vendor::orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        
        return view('purchase.vendor_bill.create_from_po', compact('po', 'vendors', 'products'));
    }
}
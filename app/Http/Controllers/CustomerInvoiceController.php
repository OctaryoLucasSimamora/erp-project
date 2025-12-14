<?php

namespace App\Http\Controllers;

use App\Models\CustomerInvoice;
use App\Models\CustomerInvoiceItem;
use App\Models\JournalItem;
use App\Models\SalesOrder;
use App\Models\DeliveryOrder;
use App\Models\SalesOrderItem;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CustomerInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = CustomerInvoice::with(['customer', 'salesOrder'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Filter by customer
        if ($request->has('customer_id') && $request->customer_id != '') {
            $query->where('customer_id', $request->customer_id);
        }

        // Search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('salesOrder', function ($q) use ($search) {
                        $q->where('order_number', 'like', "%{$search}%");
                    });
            });
        }

        $invoices = $query->paginate(15);
        $customers = Customer::orderBy('name')->get();

        return view('sales.invoice.index', compact('invoices', 'customers'));
    }

    public function create(Request $request)
    {
        $customers = Customer::orderBy('name')->get();
        $salesOrders = SalesOrder::where('status', 'sales_order')
            ->orderBy('order_number', 'desc')
            ->get();
        $deliveryOrders = DeliveryOrder::where('status', 'done')
            ->orderBy('delivery_number', 'desc')
            ->get();

        // Pre-select if from SO or DO
        $sourceType = $request->get('source_type');
        $sourceId = $request->get('source_id');
        $sourceData = null;

        if ($sourceType == 'so' && $sourceId) {
            $sourceData = SalesOrder::with(['items.product', 'customer'])->find($sourceId);
        } elseif ($sourceType == 'do' && $sourceId) {
            $sourceData = DeliveryOrder::with(['items.product', 'salesOrder.customer'])->find($sourceId);
        }

        return view('sales.invoice.create', compact('customers', 'salesOrders', 'deliveryOrders', 'sourceType', 'sourceData'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sales_order_id' => 'nullable|exists:sales_orders,id',
            'delivery_order_id' => 'nullable|exists:delivery_orders,id',
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'journal' => 'nullable|string',
            'payment_terms' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.sales_order_item_id' => 'nullable|exists:sales_order_items,id',
            'items.*.delivery_order_item_id' => 'nullable|exists:delivery_order_items,id',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.tax_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();
        try {
            // Create invoice
            $invoice = CustomerInvoice::create([
                'invoice_number' => CustomerInvoice::generateInvoiceNumber(),
                'sales_order_id' => $validated['sales_order_id'] ?? null,
                'delivery_order_id' => $validated['delivery_order_id'] ?? null,
                'customer_id' => $validated['customer_id'],
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'],
                'journal' => $validated['journal'] ?? null,
                'payment_terms' => $validated['payment_terms'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => 'draft',
                'created_by' => Auth::id(),
            ]);

            // Create invoice items
            $subtotal = 0;
            $totalTax = 0;
            $totalDiscount = 0;

            foreach ($validated['items'] as $item) {
                $invoiceItem = new CustomerInvoiceItem([
                    'product_id' => $item['product_id'],
                    'sales_order_item_id' => $item['sales_order_item_id'] ?? null,
                    'delivery_order_item_id' => $item['delivery_order_item_id'] ?? null,
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_percent' => $item['discount_percent'] ?? 0,
                    'tax_percent' => $item['tax_percent'] ?? 0,
                ]);

                $invoiceItem->calculateTotals();
                $invoice->items()->save($invoiceItem);

                $subtotal += $invoiceItem->subtotal;
                $totalDiscount += $invoiceItem->discount_amount;
                $totalTax += $invoiceItem->tax_amount;
            }

            // Update invoice totals
            $invoice->update([
                'subtotal' => $subtotal,
                'discount_amount' => $totalDiscount,
                'tax_amount' => $totalTax,
                'total_amount' => $subtotal - $totalDiscount + $totalTax,
            ]);

            DB::commit();

            return redirect()->route('sales.invoice.index')
                ->with('success', 'Invoice created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to create invoice: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $invoice = CustomerInvoice::with([
            'customer',
            'salesOrder',
            'deliveryOrder.salesOrder.customer', // Perbaiki ini
            'items.product',
            'journalItems',
            'createdBy',
            'updatedBy'
        ])->findOrFail($id);

        return view('sales.invoice.show', compact('invoice'));
    }

    public function edit($id)
    {
        $invoice = CustomerInvoice::with('items.product')->findOrFail($id);

        if (!$invoice->isDraft()) {
            return redirect()->route('sales.invoice.index')
                ->with('error', 'Only draft invoices can be edited.');
        }

        $customers = Customer::orderBy('name')->get();
        $salesOrders = SalesOrder::where('status', 'sales_order')
            ->orderBy('order_number', 'desc')
            ->get();
        $deliveryOrders = DeliveryOrder::where('status', 'done')
            ->orderBy('delivery_number', 'desc')
            ->get();

        // Tambahkan ini:
        $products = \App\Models\Product::orderBy('name')->get();

        return view('sales.invoice.edit', compact('invoice', 'customers', 'salesOrders', 'deliveryOrders', 'products'));
    }

    public function update(Request $request, $id)
    {
        $invoice = CustomerInvoice::findOrFail($id);

        if (!$invoice->isDraft()) {
            return redirect()->route('sales.invoice.index')
                ->with('error', 'Only draft invoices can be edited.');
        }

        $validated = $request->validate([
            'sales_order_id' => 'nullable|exists:sales_orders,id',
            'delivery_order_id' => 'nullable|exists:delivery_orders,id',
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'journal' => 'nullable|string',
            'payment_terms' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.sales_order_item_id' => 'nullable|exists:sales_order_items,id',
            'items.*.delivery_order_item_id' => 'nullable|exists:delivery_order_items,id',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.tax_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();
        try {
            // Update invoice
            $invoice->update([
                'sales_order_id' => $validated['sales_order_id'] ?? null,
                'delivery_order_id' => $validated['delivery_order_id'] ?? null,
                'customer_id' => $validated['customer_id'],
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'],
                'journal' => $validated['journal'] ?? null,
                'payment_terms' => $validated['payment_terms'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'updated_by' => Auth::id(),
            ]);

            // Delete old items
            $invoice->items()->delete();

            // Create new items
            $subtotal = 0;
            $totalTax = 0;
            $totalDiscount = 0;

            foreach ($validated['items'] as $item) {
                $invoiceItem = new CustomerInvoiceItem([
                    'product_id' => $item['product_id'],
                    'sales_order_item_id' => $item['sales_order_item_id'] ?? null,
                    'delivery_order_item_id' => $item['delivery_order_item_id'] ?? null,
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_percent' => $item['discount_percent'] ?? 0,
                    'tax_percent' => $item['tax_percent'] ?? 0,
                ]);

                $invoiceItem->calculateTotals();
                $invoice->items()->save($invoiceItem);

                $subtotal += $invoiceItem->subtotal;
                $totalDiscount += $invoiceItem->discount_amount;
                $totalTax += $invoiceItem->tax_amount;
            }

            // Update invoice totals
            $invoice->update([
                'subtotal' => $subtotal,
                'discount_amount' => $totalDiscount,
                'tax_amount' => $totalTax,
                'total_amount' => $subtotal - $totalDiscount + $totalTax,
            ]);

            DB::commit();

            return redirect()->route('sales.invoice.index')
                ->with('success', 'Invoice updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to update invoice: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $invoice = CustomerInvoice::findOrFail($id);

        if (!$invoice->isDraft()) {
            return back()->with('error', 'Only draft invoices can be deleted.');
        }

        DB::beginTransaction();
        try {
            $invoice->journalItems()->delete();
            $invoice->items()->delete();
            $invoice->delete();

            DB::commit();

            return redirect()->route('sales.invoice.index')
                ->with('success', 'Invoice deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete invoice: ' . $e->getMessage());
        }
    }

    public function markAsPosted($id)
    {
        $invoice = CustomerInvoice::findOrFail($id);

        if (!$invoice->isDraft()) {
            return back()->with('error', 'Only draft invoices can be posted.');
        }

        DB::beginTransaction();
        try {
            $invoice->markAsPosted();
            DB::commit();

            return back()->with('success', 'Invoice posted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to post invoice: ' . $e->getMessage());
        }
    }

    public function markAsPaid($id)
    {
        $invoice = CustomerInvoice::findOrFail($id);

        if (!$invoice->isPosted()) {
            return back()->with('error', 'Only posted invoices can be marked as paid.');
        }

        DB::beginTransaction();
        try {
            $invoice->markAsPaid();
            DB::commit();

            return back()->with('success', 'Invoice marked as paid successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to mark invoice as paid: ' . $e->getMessage());
        }
    }

    public function getSourceItems(Request $request)
    {
        $sourceType = $request->get('source_type');
        $sourceId = $request->get('source_id');

        if ($sourceType == 'so' && $sourceId) {
            $salesOrder = SalesOrder::with(['items.product'])->find($sourceId);
            $items = [];

            foreach ($salesOrder->items as $item) {
                $remaining = $item->quantity - $item->invoiced_quantity;
                if ($remaining > 0) {
                    $items[] = [
                        'sales_order_item_id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product->name,
                        'description' => $item->description,
                        'unit_price' => $item->unit_price,
                        'quantity' => $remaining,
                        'discount_percent' => $item->discount_percent,
                        'tax_percent' => $item->tax_percent,
                    ];
                }
            }

            return response()->json($items);
        } elseif ($sourceType == 'do' && $sourceId) {
            $deliveryOrder = DeliveryOrder::with(['items.product', 'items.salesOrderItem'])->find($sourceId);
            $items = [];

            foreach ($deliveryOrder->items as $item) {
                $items[] = [
                    'delivery_order_item_id' => $item->id,
                    'sales_order_item_id' => $item->sales_order_item_id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'description' => $item->description,
                    'unit_price' => $item->unit_price,
                    'quantity' => $item->quantity,
                    'discount_percent' => $item->salesOrderItem->discount_percent ?? 0,
                    'tax_percent' => $item->salesOrderItem->tax_percent ?? 0,
                ];
            }

            return response()->json($items);
        }

        return response()->json([]);
    }
}

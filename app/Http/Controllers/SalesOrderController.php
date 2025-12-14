<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Quotation;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SalesOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = SalesOrder::with(['customer', 'salesperson', 'items'])
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
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // UNCOMMENT BARIS INI ↓
        $salesOrders = $query->paginate(15);
        $customers = Customer::orderBy('name')->get();

        // TAMBAHKAN $salesOrders KE COMPACT ↓
        return view('sales.order.index', compact('salesOrders', 'customers'));
    }

    public function create(Request $request)
    {
        $customers = Customer::orderBy('name')->get();
        $products = Product::orderBy('name')->get();

        // Check if coming from quotation convert
        $quotation = null;
        if (session()->has('quotation')) {
            $quotation = session('quotation');
        } elseif ($request->has('quotation_id')) {
            $quotation = Quotation::with('items')->findOrFail($request->quotation_id);
        }

        return view('sales.order.create', compact('customers', 'products', 'quotation'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'quotation_id' => 'nullable|exists:quotations,id',
            'customer_id' => 'required|exists:customers,id',
            'order_date' => 'required|date',
            'confirmation_date' => 'nullable|date',
            'commitment_date' => 'nullable|date',
            'expiration_date' => 'required|date|after_or_equal:order_date',
            'salesperson_id' => 'nullable|exists:users,id',
            'pricelist' => 'nullable|string',
            'warehouse' => 'nullable|string',
            'incoterms' => 'nullable|string',
            'payment_terms' => 'nullable|string',
            'terms_and_conditions' => 'nullable|string',
            'tags' => 'nullable|array',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.tax_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();
        try {
            // Create sales order
            $salesOrder = SalesOrder::create([
                'order_number' => SalesOrder::generateOrderNumber(),
                'quotation_id' => $validated['quotation_id'] ?? null,
                'customer_id' => $validated['customer_id'],
                'order_date' => $validated['order_date'],
                'confirmation_date' => $validated['confirmation_date'] ?? null,
                'commitment_date' => $validated['commitment_date'] ?? null,
                'expiration_date' => $validated['expiration_date'],
                'salesperson_id' => $validated['salesperson_id'] ?? null,
                'pricelist' => $validated['pricelist'] ?? null,
                'warehouse' => $validated['warehouse'] ?? null,
                'incoterms' => $validated['incoterms'] ?? null,
                'payment_terms' => $validated['payment_terms'] ?? null,
                'terms_and_conditions' => $validated['terms_and_conditions'] ?? null,
                'tags' => $validated['tags'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => 'quotation',
                'created_by' => Auth::id(),
            ]);

            // Create sales order items
            $subtotal = 0;
            $totalTax = 0;
            $totalDiscount = 0;

            foreach ($validated['items'] as $item) {
                $salesOrderItem = new SalesOrderItem([
                    'product_id' => $item['product_id'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_percent' => $item['discount_percent'] ?? 0,
                    'tax_percent' => $item['tax_percent'] ?? 0,
                ]);

                $salesOrderItem->calculateTotals();
                $salesOrder->items()->save($salesOrderItem);

                $subtotal += $salesOrderItem->subtotal;
                $totalDiscount += $salesOrderItem->discount_amount;
                $totalTax += $salesOrderItem->tax_amount;
            }

            // Update sales order totals
            $salesOrder->update([
                'subtotal' => $subtotal,
                'discount_amount' => $totalDiscount,
                'tax_amount' => $totalTax,
                'total_amount' => $subtotal - $totalDiscount + $totalTax,
            ]);

            DB::commit();

            return redirect()->route('sales.order.index')
                ->with('success', 'Sales Order created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to create sales order: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $salesOrder = SalesOrder::with('items.product')->findOrFail($id);

        if (!$salesOrder->canEdit()) {
            return redirect()->route('sales.order.index')
                ->with('error', 'This sales order cannot be edited.');
        }

        $customers = Customer::orderBy('name')->get();
        $products = Product::orderBy('name')->get();

        return view('sales.order.edit', compact('salesOrder', 'customers', 'products'));
    }

    public function update(Request $request, $id)
    {
        $salesOrder = SalesOrder::findOrFail($id);

        if (!$salesOrder->canEdit()) {
            return redirect()->route('sales.order.index')
                ->with('error', 'This sales order cannot be edited.');
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'order_date' => 'required|date',
            'confirmation_date' => 'nullable|date',
            'commitment_date' => 'nullable|date',
            'expiration_date' => 'required|date|after_or_equal:order_date',
            'salesperson_id' => 'nullable|exists:users,id',
            'pricelist' => 'nullable|string',
            'warehouse' => 'nullable|string',
            'incoterms' => 'nullable|string',
            'payment_terms' => 'nullable|string',
            'terms_and_conditions' => 'nullable|string',
            'tags' => 'nullable|array',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.tax_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();
        try {
            // Update sales order
            $salesOrder->update([
                'customer_id' => $validated['customer_id'],
                'order_date' => $validated['order_date'],
                'confirmation_date' => $validated['confirmation_date'] ?? null,
                'commitment_date' => $validated['commitment_date'] ?? null,
                'expiration_date' => $validated['expiration_date'],
                'salesperson_id' => $validated['salesperson_id'] ?? null,
                'pricelist' => $validated['pricelist'] ?? null,
                'warehouse' => $validated['warehouse'] ?? null,
                'incoterms' => $validated['incoterms'] ?? null,
                'payment_terms' => $validated['payment_terms'] ?? null,
                'terms_and_conditions' => $validated['terms_and_conditions'] ?? null,
                'tags' => $validated['tags'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'updated_by' => Auth::id(),
            ]);

            // Delete old items
            $salesOrder->items()->delete();

            // Create new items
            $subtotal = 0;
            $totalTax = 0;
            $totalDiscount = 0;

            foreach ($validated['items'] as $item) {
                $salesOrderItem = new SalesOrderItem([
                    'product_id' => $item['product_id'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_percent' => $item['discount_percent'] ?? 0,
                    'tax_percent' => $item['tax_percent'] ?? 0,
                ]);

                $salesOrderItem->calculateTotals();
                $salesOrder->items()->save($salesOrderItem);

                $subtotal += $salesOrderItem->subtotal;
                $totalDiscount += $salesOrderItem->discount_amount;
                $totalTax += $salesOrderItem->tax_amount;
            }

            // Update sales order totals
            $salesOrder->update([
                'subtotal' => $subtotal,
                'discount_amount' => $totalDiscount,
                'tax_amount' => $totalTax,
                'total_amount' => $subtotal - $totalDiscount + $totalTax,
            ]);

            DB::commit();

            return redirect()->route('sales.order.index')
                ->with('success', 'Sales Order updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to update sales order: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $salesOrder = SalesOrder::findOrFail($id);

        if (!$salesOrder->canDelete()) {
            return back()->with('error', 'This sales order cannot be deleted.');
        }

        DB::beginTransaction();
        try {
            $salesOrder->items()->delete();
            $salesOrder->delete();

            DB::commit();

            return redirect()->route('sales.order.index')
                ->with('success', 'Sales Order deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete sales order: ' . $e->getMessage());
        }
    }

    public function confirmOrder(Request $request, $id)
    {
        $salesOrder = SalesOrder::findOrFail($id);

        if (!$salesOrder->canConfirm()) {
            return back()->with('error', 'This sales order cannot be confirmed.');
        }

        DB::beginTransaction();
        try {
            $salesOrder->confirmOrder();
            DB::commit();

            return back()->with('success', 'Sales Order confirmed successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to confirm sales order: ' . $e->getMessage());
        }
    }

    public function lockOrder(Request $request, $id)
    {
        $salesOrder = SalesOrder::findOrFail($id);

        if (!$salesOrder->canLock()) {
            return back()->with('error', 'This sales order cannot be locked.');
        }

        DB::beginTransaction();
        try {
            $salesOrder->lockOrder();
            DB::commit();

            return back()->with('success', 'Sales Order locked successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to lock sales order: ' . $e->getMessage());
        }
    }
}

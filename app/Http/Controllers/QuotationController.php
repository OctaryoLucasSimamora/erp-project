<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
        $query = Quotation::with(['customer', 'salesperson', 'items'])
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
                $q->where('quotation_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $quotations = $query->paginate(15);
        $customers = Customer::orderBy('name')->get(); // UNCOMMENT INI

        return view('sales.quotation.index', compact('quotations', 'customers')); // UNCOMMENT INI
    }

public function create()
{
    $customers = Customer::orderBy('name')->get(); // TAMBAHKAN INI
    $products = Product::orderBy('name')->get();
    // $salespersons = User::where('role', 'salesperson')->orWhere('role', 'admin')->orderBy('name')->get();

    return view('sales.quotation.create', compact('customers', 'products'));
}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'order_date' => 'required|date',
            'expiration_date' => 'required|date|after_or_equal:order_date',
            'salesperson_id' => 'nullable|exists:users,id',
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
            // Create quotation
            $quotation = Quotation::create([
                'quotation_number' => Quotation::generateQuotationNumber(),
                'customer_id' => $validated['customer_id'],
                'order_date' => $validated['order_date'],
                'expiration_date' => $validated['expiration_date'],
                'salesperson_id' => $validated['salesperson_id'] ?? null,
                'payment_terms' => $validated['payment_terms'] ?? null,
                'terms_and_conditions' => $validated['terms_and_conditions'] ?? null,
                'tags' => $validated['tags'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => 'quotation',
                'created_by' => Auth::id(),
            ]);

            // Create quotation items
            $subtotal = 0;
            $totalTax = 0;
            $totalDiscount = 0;

            foreach ($validated['items'] as $item) {
                $quotationItem = new QuotationItem([
                    'product_id' => $item['product_id'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_percent' => $item['discount_percent'] ?? 0,
                    'tax_percent' => $item['tax_percent'] ?? 0,
                ]);

                $quotationItem->calculateTotals();
                $quotation->items()->save($quotationItem);

                $subtotal += $quotationItem->subtotal;
                $totalDiscount += $quotationItem->discount_amount;
                $totalTax += $quotationItem->tax_amount;
            }

            // Update quotation totals
            $quotation->update([
                'subtotal' => $subtotal,
                'discount_amount' => $totalDiscount,
                'tax_amount' => $totalTax,
                'total_amount' => $subtotal - $totalDiscount + $totalTax,
            ]);

            DB::commit();

            return redirect()->route('sales.quotation.index')
                ->with('success', 'Quotation created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to create quotation: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $quotation = Quotation::with('items.product')->findOrFail($id);

        if (!$quotation->canEdit()) {
            return redirect()->route('sales.quotation.index')
                ->with('error', 'This quotation cannot be edited.');
        }

        $customers = Customer::orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        // $salespersons = User::where('role', 'salesperson')->orWhere('role', 'admin')->orderBy('name')->get();

        return view('sales.quotation.edit', compact('quotation', 'customers', 'products'));
    }

    public function update(Request $request, $id)
    {
        $quotation = Quotation::findOrFail($id);

        if (!$quotation->canEdit()) {
            return redirect()->route('sales.quotation.index')
                ->with('error', 'This quotation cannot be edited.');
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'order_date' => 'required|date',
            'expiration_date' => 'required|date|after_or_equal:order_date',
            'salesperson_id' => 'nullable|exists:users,id',
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
            // Update quotation
            $quotation->update([
                'customer_id' => $validated['customer_id'],
                'order_date' => $validated['order_date'],
                'expiration_date' => $validated['expiration_date'],
                'salesperson_id' => $validated['salesperson_id'] ?? null,
                'payment_terms' => $validated['payment_terms'] ?? null,
                'terms_and_conditions' => $validated['terms_and_conditions'] ?? null,
                'tags' => $validated['tags'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'updated_by' => Auth::id(),
            ]);

            // Delete old items
            $quotation->items()->delete();

            // Create new items
            $subtotal = 0;
            $totalTax = 0;
            $totalDiscount = 0;

            foreach ($validated['items'] as $item) {
                $quotationItem = new QuotationItem([
                    'product_id' => $item['product_id'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_percent' => $item['discount_percent'] ?? 0,
                    'tax_percent' => $item['tax_percent'] ?? 0,
                ]);

                $quotationItem->calculateTotals();
                $quotation->items()->save($quotationItem);

                $subtotal += $quotationItem->subtotal;
                $totalDiscount += $quotationItem->discount_amount;
                $totalTax += $quotationItem->tax_amount;
            }

            // Update quotation totals
            $quotation->update([
                'subtotal' => $subtotal,
                'discount_amount' => $totalDiscount,
                'tax_amount' => $totalTax,
                'total_amount' => $subtotal - $totalDiscount + $totalTax,
            ]);

            DB::commit();

            return redirect()->route('sales.quotation.index')
                ->with('success', 'Quotation updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to update quotation: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $quotation = Quotation::findOrFail($id);

        if (!$quotation->canDelete()) {
            return back()->with('error', 'This quotation cannot be deleted.');
        }

        DB::beginTransaction();
        try {
            $quotation->items()->delete();
            $quotation->delete();

            DB::commit();

            return redirect()->route('sales.quotation.index')
                ->with('success', 'Quotation deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete quotation: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:quotation,sent',
        ]);

        $quotation = Quotation::findOrFail($id);

        DB::beginTransaction();
        try {
            if ($validated['status'] === 'sent') {
                $quotation->markAsSent();
            } else {
                $quotation->update([
                    'status' => $validated['status'],
                    'updated_by' => Auth::id(),
                ]);
            }

            DB::commit();

            return back()->with('success', 'Quotation status updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update status: ' . $e->getMessage());
        }
    }

    public function convertToSalesOrder($id)
    {
        $quotation = Quotation::with('items')->findOrFail($id);

        if (!$quotation->isSent()) {
            return back()->with('error', 'Only sent quotations can be converted to sales orders.');
        }

        // Redirect to sales order create with quotation data
        return redirect()->route('sales.order.create')
            ->with('quotation', $quotation);
    }
}

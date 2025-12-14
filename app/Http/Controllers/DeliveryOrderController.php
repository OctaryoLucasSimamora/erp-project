<?php

namespace App\Http\Controllers;

use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DeliveryOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = DeliveryOrder::with(['salesOrder.customer', 'items'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Filter by sales order
        if ($request->has('sales_order_id') && $request->sales_order_id != '') {
            $query->where('sales_order_id', $request->sales_order_id);
        }

        // Search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('delivery_number', 'like', "%{$search}%")
                    ->orWhere('tracking_number', 'like', "%{$search}%")
                    ->orWhereHas('salesOrder', function ($q) use ($search) {
                        $q->where('order_number', 'like', "%{$search}%")
                            ->orWhereHas('customer', function ($q) use ($search) {
                                $q->where('name', 'like', "%{$search}%");
                            });
                    });
            });
        }

        $deliveryOrders = $query->paginate(15);
        $salesOrders = SalesOrder::where('status', 'sales_order')->get();

        return view('sales.delivery.index', compact('deliveryOrders', 'salesOrders'));
    }

    public function create(Request $request)
    {
        $salesOrders = SalesOrder::with('customer')
            ->where('status', 'sales_order')
            ->orderBy('order_number', 'desc')
            ->get();

        // Pre-select sales order if specified
        $selectedSalesOrder = null;
        if ($request->has('sales_order_id')) {
            $selectedSalesOrder = SalesOrder::with(['items.product', 'customer'])
                ->find($request->sales_order_id);
        }

        return view('sales.delivery.create', compact('salesOrders', 'selectedSalesOrder'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sales_order_id' => 'required|exists:sales_orders,id',
            'delivery_date' => 'required|date',
            'scheduled_date' => 'required|date',
            'delivery_address' => 'required|string',
            'carrier' => 'nullable|string',
            'tracking_number' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.sales_order_item_id' => 'required|exists:sales_order_items,id',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.description' => 'nullable|string',
            'items.*.notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Create delivery order
            $deliveryOrder = DeliveryOrder::create([
                'delivery_number' => DeliveryOrder::generateDeliveryNumber(),
                'sales_order_id' => $validated['sales_order_id'],
                'delivery_date' => $validated['delivery_date'],
                'scheduled_date' => $validated['scheduled_date'],
                'delivery_address' => $validated['delivery_address'],
                'carrier' => $validated['carrier'] ?? null,
                'tracking_number' => $validated['tracking_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => 'waiting',
                'created_by' => Auth::id(),
            ]);

            // Create delivery order items
            foreach ($validated['items'] as $item) {
                DeliveryOrderItem::create([
                    'delivery_order_id' => $deliveryOrder->id,
                    'sales_order_item_id' => $item['sales_order_item_id'],
                    'product_id' => $item['product_id'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('sales.delivery.index')
                ->with('success', 'Delivery Order created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to create delivery order: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $deliveryOrder = DeliveryOrder::with([
            'salesOrder.customer',
            'salesOrder.salesperson',
            'items.product',
            'createdBy',
            'updatedBy'
        ])->findOrFail($id);

        return view('sales.delivery.show', compact('deliveryOrder'));
    }

    public function edit($id)
    {
        $deliveryOrder = DeliveryOrder::with(['salesOrder', 'items.product'])->findOrFail($id);

        if ($deliveryOrder->status != 'waiting') {
            return redirect()->route('sales.delivery.index')
                ->with('error', 'Only waiting delivery orders can be edited.');
        }

        $salesOrders = SalesOrder::with('customer')
            ->where('status', 'sales_order')
            ->orderBy('order_number', 'desc')
            ->get();

        return view('sales.delivery.edit', compact('deliveryOrder', 'salesOrders'));
    }

    public function update(Request $request, $id)
    {
        $deliveryOrder = DeliveryOrder::findOrFail($id);

        if ($deliveryOrder->status != 'waiting') {
            return redirect()->route('sales.delivery.index')
                ->with('error', 'Only waiting delivery orders can be edited.');
        }

        $validated = $request->validate([
            'sales_order_id' => 'required|exists:sales_orders,id',
            'delivery_date' => 'required|date',
            'scheduled_date' => 'required|date',
            'delivery_address' => 'required|string',
            'carrier' => 'nullable|string',
            'tracking_number' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.sales_order_item_id' => 'required|exists:sales_order_items,id',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.description' => 'nullable|string',
            'items.*.notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Update delivery order
            $deliveryOrder->update([
                'sales_order_id' => $validated['sales_order_id'],
                'delivery_date' => $validated['delivery_date'],
                'scheduled_date' => $validated['scheduled_date'],
                'delivery_address' => $validated['delivery_address'],
                'carrier' => $validated['carrier'] ?? null,
                'tracking_number' => $validated['tracking_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'updated_by' => Auth::id(),
            ]);

            // Delete old items
            $deliveryOrder->items()->delete();

            // Create new items
            foreach ($validated['items'] as $item) {
                DeliveryOrderItem::create([
                    'delivery_order_id' => $deliveryOrder->id,
                    'sales_order_item_id' => $item['sales_order_item_id'],
                    'product_id' => $item['product_id'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('sales.delivery.index')
                ->with('success', 'Delivery Order updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to update delivery order: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $deliveryOrder = DeliveryOrder::findOrFail($id);

        if ($deliveryOrder->status != 'waiting') {
            return back()->with('error', 'Only waiting delivery orders can be deleted.');
        }

        DB::beginTransaction();
        try {
            $deliveryOrder->items()->delete();
            $deliveryOrder->delete();

            DB::commit();

            return redirect()->route('sales.delivery.index')
                ->with('success', 'Delivery Order deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete delivery order: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $deliveryOrder = DeliveryOrder::findOrFail($id);
        $status = $request->input('status');
        $deliveredQuantities = $request->input('delivered_quantities', []);

        if (!in_array($status, ['ready', 'done'])) {
            return back()->with('error', 'Invalid status.');
        }

        DB::beginTransaction();
        try {
            if ($status == 'done') {
                // Validate delivered quantities
                foreach ($deliveryOrder->items as $item) {
                    $deliveredQty = $deliveredQuantities[$item->id] ?? 0;
                    if ($deliveredQty < 0 || $deliveredQty > $item->quantity) {
                        throw new \Exception("Invalid delivered quantity for product: {$item->product->name}");
                    }
                    
                    $item->update(['delivered_quantity' => $deliveredQty]);
                }

                $deliveryOrder->markAsDone();
            } else {
                $deliveryOrder->markAsReady();
            }

            DB::commit();

            return back()->with('success', "Delivery Order marked as {$status} successfully!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update status: ' . $e->getMessage());
        }
    }

    public function getSalesOrderItems($salesOrderId)
    {
        $salesOrder = SalesOrder::with(['items.product'])
            ->findOrFail($salesOrderId);

        $items = [];
        foreach ($salesOrder->items as $item) {
            $remaining = $item->quantity - $item->delivered_quantity;
            if ($remaining > 0) {
                $items[] = [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'description' => $item->description,
                    'unit_price' => $item->unit_price,
                    'remaining_quantity' => $remaining,
                    'quantity' => $remaining, // Default to remaining quantity
                ];
            }
        }

        return response()->json($items);
    }
}
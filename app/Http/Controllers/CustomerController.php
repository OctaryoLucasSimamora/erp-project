<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::orderBy('name', 'asc');

        // Search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%");
            });
        }

        $customers = $query->paginate(15);

        return view('sales.customer.index', compact('customers'));
    }

    public function create()
    {
        return view('sales.customer.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'company' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
            'mobile' => 'nullable|string|max:20',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        DB::beginTransaction();
        try {
            // Handle image upload
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('customers', 'public');
                $validated['image'] = $imagePath;
            }

            Customer::create($validated);

            DB::commit();

            return redirect()->route('sales.customer.index')
                ->with('success', 'Customer created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to create customer: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $customer = Customer::with(['quotations' => function($query) {
            $query->orderBy('created_at', 'desc')->limit(10);
        }])->findOrFail($id);

        return view('sales.customer.show', compact('customer'));
    }

    public function edit($id)
    {
        $customer = Customer::findOrFail($id);

        return view('sales.customer.edit', compact('customer'));
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'company' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
            'mobile' => 'nullable|string|max:20',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'remove_image' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            // Prepare data for update
            $updateData = [
                'name' => $validated['name'],
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'company' => $validated['company'] ?? null,
                'title' => $validated['title'] ?? null,
                'position' => $validated['position'] ?? null,
                'mobile' => $validated['mobile'] ?? null,
            ];

            // Handle remove image checkbox
            if ($request->has('remove_image') && $request->remove_image == '1') {
                if ($customer->image && Storage::disk('public')->exists($customer->image)) {
                    Storage::disk('public')->delete($customer->image);
                }
                $updateData['image'] = null;
            }
            
            // Handle new image upload
            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                // Delete old image if exists
                if ($customer->image && Storage::disk('public')->exists($customer->image)) {
                    Storage::disk('public')->delete($customer->image);
                }
                
                $imagePath = $request->file('image')->store('customers', 'public');
                $updateData['image'] = $imagePath;
            }

            $customer->update($updateData);

            DB::commit();

            return redirect()->route('sales.customer.index')
                ->with('success', 'Customer updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            // Log::error('Customer update failed: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Failed to update customer: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);

        // Check if customer has related records
        if ($customer->quotations()->exists()) {
            return back()->with('error', 'Cannot delete customer because they have related quotations.');
        }

        // Check if used in sales orders
        if (\App\Models\SalesOrder::where('customer_id', $id)->exists()) {
            return back()->with('error', 'Cannot delete customer because they have related sales orders.');
        }

        // Check if used in invoices
        if (\App\Models\CustomerInvoice::where('customer_id', $id)->exists()) {
            return back()->with('error', 'Cannot delete customer because they have related invoices.');
        }

        // Check if used in payments
        if (\App\Models\CustomerPayment::where('customer_id', $id)->exists()) {
            return back()->with('error', 'Cannot delete customer because they have related payments.');
        }

        DB::beginTransaction();
        try {
            // Delete image if exists
            if ($customer->image) {
                Storage::disk('public')->delete($customer->image);
            }

            $customer->delete();

            DB::commit();

            return redirect()->route('sales.customer.index')
                ->with('success', 'Customer deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete customer: ' . $e->getMessage());
        }
    }
}
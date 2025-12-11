<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index()
    {
        $vendors = Vendor::orderBy('name')->paginate(10);
        return view('purchase.vendor.index', compact('vendors'));
    }

    public function create()
    {
        return view('purchase.vendor.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'company_type' => 'required|in:individual,company',
            'email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:20',
        ]);

        Vendor::create($request->all());

        return redirect()->route('purchase.vendor.index')
            ->with('success', 'Vendor berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $vendor = Vendor::findOrFail($id);
        return view('purchase.vendor.edit', compact('vendor'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'company_type' => 'required|in:individual,company',
            'email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:20',
        ]);

        $vendor = Vendor::findOrFail($id);
        $vendor->update($request->all());

        return redirect()->route('purchase.vendor.index')
            ->with('success', 'Vendor berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $vendor = Vendor::findOrFail($id);
        $vendor->delete();

        return redirect()->route('purchase.vendor.index')
            ->with('success', 'Vendor berhasil dihapus!');
    }
}
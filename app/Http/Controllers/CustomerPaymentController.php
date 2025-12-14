<?php

namespace App\Http\Controllers;

use App\Models\CustomerPayment;
use App\Models\PaymentInvoice;
use App\Models\CustomerInvoice;
use App\Models\Customer;
use App\Models\JournalItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CustomerPaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = CustomerPayment::with(['customer'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Filter by customer
        if ($request->has('customer_id') && $request->customer_id != '') {
            $query->where('customer_id', $request->customer_id);
        }

        // Filter by payment method
        if ($request->has('payment_method') && $request->payment_method != '') {
            $query->where('payment_method', $request->payment_method);
        }

        // Search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('payment_number', 'like', "%{$search}%")
                    ->orWhere('memo', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $payments = $query->paginate(15);
        $customers = Customer::orderBy('name')->get();

        return view('sales.payment.index', compact('payments', 'customers'));
    }

    public function create(Request $request)
    {
        $customers = Customer::orderBy('name')->get();

        // Pre-select customer if specified
        $selectedCustomer = null;
        if ($request->has('customer_id')) {
            $selectedCustomer = Customer::find($request->customer_id);
        }

        return view('sales.payment.create', compact('customers', 'selectedCustomer'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank_transfer,credit_card,check,other',
            'memo' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Create payment
            $payment = CustomerPayment::create([
                'payment_number' => CustomerPayment::generatePaymentNumber(),
                'customer_id' => $validated['customer_id'],
                'payment_date' => $validated['payment_date'],
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'memo' => $validated['memo'] ?? null,
                'status' => 'draft',
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            return redirect()->route('sales.payment.index')
                ->with('success', 'Payment created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to create payment: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $payment = CustomerPayment::with([
            'customer',
            'paymentInvoices.customerInvoice',
            'journalItems',
            'createdBy',
            'updatedBy'
        ])->findOrFail($id);

        return view('sales.payment.show', compact('payment'));
    }

    public function edit($id)
    {
        $payment = CustomerPayment::findOrFail($id);

        if (!$payment->isDraft()) {
            return redirect()->route('sales.payment.index')
                ->with('error', 'Only draft payments can be edited.');
        }

        $customers = Customer::orderBy('name')->get();

        return view('sales.payment.edit', compact('payment', 'customers'));
    }

    public function update(Request $request, $id)
    {
        $payment = CustomerPayment::findOrFail($id);

        if (!$payment->isDraft()) {
            return redirect()->route('sales.payment.index')
                ->with('error', 'Only draft payments can be edited.');
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank_transfer,credit_card,check,other',
            'memo' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Update payment
            $payment->update([
                'customer_id' => $validated['customer_id'],
                'payment_date' => $validated['payment_date'],
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'memo' => $validated['memo'] ?? null,
                'updated_by' => Auth::id(),
            ]);

            DB::commit();

            return redirect()->route('sales.payment.index')
                ->with('success', 'Payment updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to update payment: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $payment = CustomerPayment::findOrFail($id);

        if (!$payment->isDraft()) {
            return back()->with('error', 'Only draft payments can be deleted.');
        }

        DB::beginTransaction();
        try {
            $payment->journalItems()->delete();
            $payment->paymentInvoices()->delete();
            $payment->delete();

            DB::commit();

            return redirect()->route('sales.payment.index')
                ->with('success', 'Payment deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete payment: ' . $e->getMessage());
        }
    }

    public function markAsPosted($id)
    {
        $payment = CustomerPayment::findOrFail($id);

        if (!$payment->isDraft()) {
            return back()->with('error', 'Only draft payments can be posted.');
        }

        DB::beginTransaction();
        try {
            $payment->markAsPosted();
            DB::commit();

            return back()->with('success', 'Payment posted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to post payment: ' . $e->getMessage());
        }
    }

    public function markAsReconciled($id)
    {
        $payment = CustomerPayment::findOrFail($id);

        if (!$payment->isPosted()) {
            return back()->with('error', 'Only posted payments can be reconciled.');
        }

        DB::beginTransaction();
        try {
            $payment->markAsReconciled();
            DB::commit();

            return back()->with('success', 'Payment reconciled successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to reconcile payment: ' . $e->getMessage());
        }
    }

    public function allocateInvoices($id)
    {
        $payment = CustomerPayment::with(['customer', 'paymentInvoices'])->findOrFail($id);

        if (!$payment->isPosted()) {
            return redirect()->route('sales.payment.index')
                ->with('error', 'Only posted payments can be allocated to invoices.');
        }

        // Get unpaid invoices for this customer
        $invoices = CustomerInvoice::where('customer_id', $payment->customer_id)
            ->where('status', 'posted')
            ->whereColumn('total_amount', '>', DB::raw('COALESCE((SELECT SUM(amount) FROM payment_invoices WHERE customer_invoice_id = customer_invoices.id), 0)'))
            ->with('salesOrder')
            ->orderBy('invoice_date', 'asc')
            ->get();

        return view('sales.payment.allocate', compact('payment', 'invoices'));
    }

    public function storeAllocation(Request $request, $id)
    {
        $payment = CustomerPayment::findOrFail($id);

        if (!$payment->isPosted()) {
            return back()->with('error', 'Only posted payments can be allocated to invoices.');
        }

        $validated = $request->validate([
            'allocations' => 'required|array',
            'allocations.*.invoice_id' => 'required|exists:customer_invoices,id',
            'allocations.*.amount' => 'required|numeric|min:0.01',
        ]);

        DB::beginTransaction();
        try {
            $totalAllocated = 0;

            foreach ($validated['allocations'] as $allocation) {
                $invoice = CustomerInvoice::find($allocation['invoice_id']);

                // Check if invoice belongs to same customer
                if ($invoice->customer_id != $payment->customer_id) {
                    throw new \Exception('Invoice does not belong to the same customer.');
                }

                // Check if invoice is posted
                if (!$invoice->isPosted()) {
                    throw new \Exception('Invoice is not posted.');
                }

                // Check allocation amount doesn't exceed invoice remaining amount
                $allocatedAmount = PaymentInvoice::where('customer_invoice_id', $invoice->id)->sum('amount');
                $remainingAmount = $invoice->total_amount - $allocatedAmount;

                if ($allocation['amount'] > $remainingAmount) {
                    throw new \Exception('Allocation amount exceeds invoice remaining amount.');
                }

                // Check total allocation doesn't exceed payment amount
                $totalAllocated += $allocation['amount'];
                if ($totalAllocated > $payment->amount) {
                    throw new \Exception('Total allocation exceeds payment amount.');
                }

                // Create allocation
                PaymentInvoice::create([
                    'customer_payment_id' => $payment->id,
                    'customer_invoice_id' => $allocation['invoice_id'],
                    'amount' => $allocation['amount'],
                ]);

                // Mark invoice as paid if fully allocated
                if ($remainingAmount - $allocation['amount'] <= 0.01) {
                    $invoice->markAsPaid();
                }
            }

            DB::commit();

            return redirect()->route('sales.payment.show', $payment->id)
                ->with('success', 'Invoices allocated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to allocate invoices: ' . $e->getMessage());
        }
    }

    public function getCustomerInvoices($customerId)
    {
        $invoices = CustomerInvoice::where('customer_id', $customerId)
            ->where('status', 'posted')
            ->with(['salesOrder'])
            ->orderBy('invoice_date', 'asc')
            ->get()
            ->map(function ($invoice) {
                $allocatedAmount = PaymentInvoice::where('customer_invoice_id', $invoice->id)->sum('amount');
                $remainingAmount = $invoice->total_amount - $allocatedAmount;

                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'invoice_date' => $invoice->invoice_date->format('d M Y'),
                    'due_date' => $invoice->due_date->format('d M Y'),
                    'total_amount' => $invoice->total_amount,
                    'allocated_amount' => $allocatedAmount,
                    'remaining_amount' => $remainingAmount,
                    'sales_order_number' => $invoice->salesOrder->order_number ?? null,
                ];
            });

        return response()->json($invoices);
    }
}

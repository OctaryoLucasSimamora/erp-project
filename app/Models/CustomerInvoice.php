<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'sales_order_id',
        'delivery_order_id',
        'customer_id',
        'invoice_date',
        'due_date',
        'journal',
        'subtotal',
        'tax_amount',
        'total_amount',
        'payment_terms',
        'notes',
        'status',
        'posted_at',
        'paid_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'posted_at' => 'datetime',
        'paid_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    // Relationships
    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function deliveryOrder()
    {
        return $this->belongsTo(DeliveryOrder::class, 'delivery_order_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(CustomerInvoiceItem::class);
    }

    public function journalItems()
    {
        return $this->morphMany(JournalItem::class, 'reference');
    }


    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopePosted($query)
    {
        return $query->where('status', 'posted');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    // Status Helpers
    public function isDraft()
    {
        return $this->status === 'draft';
    }

    public function isPosted()
    {
        return $this->status === 'posted';
    }

    public function isPaid()
    {
        return $this->status === 'paid';
    }

    // Status Badges
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'draft' => 'secondary',
            'posted' => 'info',
            'paid' => 'success'
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    // Actions
    public function markAsPosted()
    {
        $this->update([
            'status' => 'posted',
            'posted_at' => now(),
        ]);

        // Create journal entries (sederhana)
        $this->createJournalEntries();
    }

    public function markAsPaid()
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        // Update sales order invoiced quantities
        if ($this->salesOrder) {
            foreach ($this->items as $invoiceItem) {
                if ($invoiceItem->sales_order_item_id) {
                    $salesOrderItem = SalesOrderItem::find($invoiceItem->sales_order_item_id);
                    if ($salesOrderItem) {
                        $salesOrderItem->invoiced_quantity += $invoiceItem->quantity;
                        $salesOrderItem->save();
                    }
                }
            }

            // Check and auto lock sales order
            $this->salesOrder->checkAndAutoLock();
        }
    }

    // Create journal entries (contoh sederhana)
    protected function createJournalEntries()
    {
        // Hapus journal items lama
        $this->journalItems()->delete();

        // Account Receivable (debit)
        $this->journalItems()->create([
            'account_code' => '1101',
            'account_name' => 'Piutang Usaha',
            'debit' => $this->total_amount,
            'credit' => 0,
            'description' => 'Invoice ' . $this->invoice_number,
        ]);

        // Sales (credit)
        $this->journalItems()->create([
            'account_code' => '4101',
            'account_name' => 'Pendapatan Penjualan',
            'debit' => 0,
            'credit' => $this->subtotal,
            'description' => 'Penjualan ' . $this->invoice_number,
        ]);

        // Tax (credit)
        if ($this->tax_amount > 0) {
            $this->journalItems()->create([
                'account_code' => '2103',
                'account_name' => 'PPN Keluaran',
                'debit' => 0,
                'credit' => $this->tax_amount,
                'description' => 'PPN ' . $this->invoice_number,
            ]);
        }
    }

    // Generate Invoice Number
    public static function generateInvoiceNumber()
    {
        $prefix = 'INV';
        $date = now()->format('Ymd');
        $lastInvoice = self::whereDate('created_at', today())
            ->latest('id')
            ->first();

        $sequence = $lastInvoice ? intval(substr($lastInvoice->invoice_number, -4)) + 1 : 1;

        return $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}

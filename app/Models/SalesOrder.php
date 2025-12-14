<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'quotation_id',
        'customer_id',
        'order_date',
        'confirmation_date',
        'commitment_date',
        'expiration_date',
        'salesperson_id',
        'pricelist',
        'warehouse',
        'incoterms',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'terms_and_conditions',
        'payment_terms',
        'tags',
        'status',
        'notes',
        'confirmed_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'order_date' => 'date',
        'confirmation_date' => 'date',
        'commitment_date' => 'date',
        'expiration_date' => 'date',
        'confirmed_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'tags' => 'array',
    ];

    // Relationships
    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesperson()
    {
        return $this->belongsTo(User::class, 'salesperson_id');
    }

    public function items()
    {
        return $this->hasMany(SalesOrderItem::class);
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
    public function scopeQuotation($query)
    {
        return $query->where('status', 'quotation');
    }

    public function scopeSalesOrder($query)
    {
        return $query->where('status', 'sales_order');
    }

    public function scopeLocked($query)
    {
        return $query->where('status', 'locked');
    }

    // Status Helpers
    public function isQuotation()
    {
        return $this->status === 'quotation';
    }

    public function isSalesOrder()
    {
        return $this->status === 'sales_order';
    }

    public function isLocked()
    {
        return $this->status === 'locked';
    }

    // Permission Helpers
    public function canEdit()
    {
        return in_array($this->status, ['quotation', 'sales_order']);
    }

    public function canDelete()
    {
        return $this->status === 'quotation';
    }

    public function canConfirm()
    {
        return $this->status === 'quotation';
    }

    public function canLock()
    {
        return $this->status === 'sales_order';
    }

    // Actions
    public function confirmOrder()
    {
        $this->update([
            'status' => 'sales_order',
            'confirmation_date' => now(),
            'confirmed_at' => now(),
        ]);
    }

    public function lockOrder()
    {
        $this->update([
            'status' => 'locked',
        ]);
    }

    // Check if order has deliveries or invoices
    public function hasDeliveriesOrInvoices()
    {
        foreach ($this->items as $item) {
            if ($item->delivered_quantity > 0 || $item->invoiced_quantity > 0) {
                return true;
            }
        }
        return false;
    }

    // Auto lock if fully delivered or invoiced
    public function checkAndAutoLock()
    {
        if ($this->status !== 'sales_order') {
            return;
        }

        $shouldLock = false;
        foreach ($this->items as $item) {
            if ($item->delivered_quantity > 0 || $item->invoiced_quantity > 0) {
                $shouldLock = true;
                break;
            }
        }

        if ($shouldLock) {
            $this->lockOrder();
        }
    }

    // Generate Sales Order Number
    public static function generateOrderNumber()
    {
        $prefix = 'SO';
        $date = now()->format('Ymd');
        $lastOrder = self::whereDate('created_at', today())
            ->latest('id')
            ->first();

        $sequence = $lastOrder ? intval(substr($lastOrder->order_number, -4)) + 1 : 1;

        return $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
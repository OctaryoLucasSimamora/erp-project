<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorBill extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_number',
        'vendor_id',
        'purchase_order_id',
        'bill_date',
        'due_date',
        'status',
        'payment_reference',
        'total_amount',
        'paid_amount',
        'balance',
        'notes'
    ];

    protected $casts = [
        'status' => 'string',
        'bill_date' => 'date',
        'due_date' => 'date',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    // Auto generate Bill number
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->bill_number)) {
                $last = self::orderBy('id', 'desc')->first();
                $next = $last ? $last->id + 1 : 1;
                $model->bill_number = 'BILL' . str_pad($next, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    // Relationships
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function lines()
    {
        return $this->hasMany(VendorBillLine::class);
    }

   public function payments()
{
    return $this->hasMany(Payment::class, 'vendor_bill_id');
}
}
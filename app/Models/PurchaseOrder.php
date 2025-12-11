<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number',
        'vendor_id',
        'rfq_id', // <-- INI 'rfq_id' bukan 'r_f_q_id'
        'order_date',
        'expected_delivery_date',
        'ship_to',
        'incoterm',
        'payment_term',
        'status',
        'notes',
        'subtotal',
        'tax_amount',
        'total_amount'
    ];

    protected $casts = [
        'status' => 'string',
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    // Auto generate PO number
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->po_number)) {
                $last = self::orderBy('id', 'desc')->first();
                $next = $last ? $last->id + 1 : 1;
                $model->po_number = 'PO' . str_pad($next, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    // Relationships - PERBAIKI FOREIGN KEY DI SINI
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function rfq()
    {
        // TENTUKAN FOREIGN KEY SECARA EKSPLISIT
        return $this->belongsTo(RFQ::class, 'rfq_id'); // <-- TAMBAHKAN PARAMETER KEDUA
    }

    public function lines()
    {
        return $this->hasMany(POLine::class, 'purchase_order_id');
    }

    public function vendorBills()
    {
        return $this->hasMany(VendorBill::class, 'purchase_order_id');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorBillLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_bill_id',
        'product_id',
        'quantity',
        'price',
        'subtotal',
        'description'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    // Relationships
    public function vendorBill()
    {
        return $this->belongsTo(VendorBill::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
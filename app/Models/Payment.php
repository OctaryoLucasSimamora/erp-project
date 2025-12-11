<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_bill_id',
        'payment_method',
        'amount',
        'payment_date',
        'memo',
        'reference'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    // Relationships
    public function vendorBill()
    {
        return $this->belongsTo(VendorBill::class);
    }
}
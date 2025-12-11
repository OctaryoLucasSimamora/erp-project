<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'company_type',
        'street',
        'state',
        'country',
        'contact_phone',
        'email',
        'bank_account',
        'notes'
    ];

    protected $casts = [
        'company_type' => 'string',
    ];

    // Relationships
    public function rfqs()
    {
        return $this->hasMany(RFQ::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function vendorBills()
    {
        return $this->hasMany(VendorBill::class);
    }
}
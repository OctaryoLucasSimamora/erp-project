<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_id',
        'reference_type', // 'customer_invoice' atau 'customer_payment'
        'account_code',
        'account_name',
        'debit',
        'credit',
        'description',
    ];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    // Relationships
    public function customerInvoice()
    {
        return $this->belongsTo(CustomerInvoice::class, 'reference_id')
            ->where('reference_type', 'customer_invoice');
    }

    public function customerPayment()
    {
        return $this->belongsTo(CustomerPayment::class, 'reference_id')
            ->where('reference_type', 'customer_payment');
    }
}
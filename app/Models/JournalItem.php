<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_invoice_id',
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
        return $this->belongsTo(CustomerInvoice::class);
    }
}
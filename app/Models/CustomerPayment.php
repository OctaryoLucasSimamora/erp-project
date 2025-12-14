<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_number',
        'customer_invoice_id',
        'customer_id',
        'payment_date',
        'amount',
        'payment_method',
        'reference',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    // Relationships
    public function customerInvoice()
    {
        return $this->belongsTo(CustomerInvoice::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Generate Payment Number
    public static function generatePaymentNumber()
    {
        $prefix = 'PAY';
        $date = now()->format('Ymd');
        $lastPayment = self::whereDate('created_at', today())
            ->latest('id')
            ->first();

        $sequence = $lastPayment ? intval(substr($lastPayment->payment_number, -4)) + 1 : 1;

        return $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    // Payment method options
    public static function paymentMethods()
    {
        return [
            'cash' => 'Cash',
            'bank_transfer' => 'Bank Transfer',
            'credit_card' => 'Credit Card',
            'check' => 'Check',
            'other' => 'Other',
        ];
    }
}
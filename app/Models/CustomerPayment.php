<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_number',
        'customer_id',
        'payment_date',
        'amount',
        'payment_method',
        'memo',
        'status',
        'posted_at',
        'reconciled_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'posted_at' => 'datetime',
        'reconciled_at' => 'datetime',
    ];

    // Relationships
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function journalItems()
    {
        return $this->hasMany(JournalItem::class, 'reference_id')->where('reference_type', 'customer_payment');
    }

    public function paymentInvoices()
    {
        return $this->hasMany(PaymentInvoice::class);
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

    public function scopeReconciled($query)
    {
        return $query->where('status', 'reconciled');
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

    public function isReconciled()
    {
        return $this->status === 'reconciled';
    }

    // Status Badges
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'draft' => 'secondary',
            'posted' => 'info',
            'reconciled' => 'success'
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    // Payment Method Labels
    public function getPaymentMethodLabelAttribute()
    {
        $methods = [
            'cash' => 'Cash',
            'bank_transfer' => 'Bank Transfer',
            'credit_card' => 'Credit Card',
            'check' => 'Check',
            'other' => 'Other'
        ];

        return $methods[$this->payment_method] ?? $this->payment_method;
    }

    // Get total allocated amount (to invoices)
    public function getAllocatedAmountAttribute()
    {
        return $this->paymentInvoices->sum('amount');
    }

    // Get remaining amount (unallocated)
    public function getRemainingAmountAttribute()
    {
        return $this->amount - $this->allocated_amount;
    }

    // Actions
    public function markAsPosted()
    {
        $this->update([
            'status' => 'posted',
            'posted_at' => now(),
        ]);

        // Create journal entries
        $this->createJournalEntries();
    }

    public function markAsReconciled()
    {
        $this->update([
            'status' => 'reconciled',
            'reconciled_at' => now(),
        ]);
    }

    // Create journal entries
    protected function createJournalEntries()
    {
        // Hapus journal items lama
        $this->journalItems()->delete();

        // Cash/Bank (debit)
        $accountCode = $this->getAccountCodeForPaymentMethod();
        
        JournalItem::create([
            'reference_id' => $this->id,
            'reference_type' => 'customer_payment',
            'account_code' => $accountCode,
            'account_name' => $this->getAccountNameForPaymentMethod(),
            'debit' => $this->amount,
            'credit' => 0,
            'description' => 'Payment ' . $this->payment_number,
        ]);

        // Account Receivable (credit)
        JournalItem::create([
            'reference_id' => $this->id,
            'reference_type' => 'customer_payment',
            'account_code' => '1101', // Piutang Usaha
            'account_name' => 'Piutang Usaha',
            'debit' => 0,
            'credit' => $this->amount,
            'description' => 'Payment ' . $this->payment_number,
        ]);
    }

    protected function getAccountCodeForPaymentMethod()
    {
        $accounts = [
            'cash' => '1102', // Kas
            'bank_transfer' => '1103', // Bank
            'credit_card' => '1104', // Credit Card Receivable
            'check' => '1105', // Checks
            'other' => '1199', // Other Receivables
        ];

        return $accounts[$this->payment_method] ?? '1199';
    }

    protected function getAccountNameForPaymentMethod()
    {
        $accounts = [
            'cash' => 'Kas',
            'bank_transfer' => 'Bank',
            'credit_card' => 'Piutang Kartu Kredit',
            'check' => 'Cek',
            'other' => 'Piutang Lainnya',
        ];

        return $accounts[$this->payment_method] ?? 'Piutang Lainnya';
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
}
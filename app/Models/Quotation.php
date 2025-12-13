<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_number',
        'customer_id',
        'order_date',
        'expiration_date',
        'salesperson_id',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'terms_and_conditions',
        'payment_terms',
        'tags',
        'status',
        'notes',
        'sent_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expiration_date' => 'date',
        'sent_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'tags' => 'array',
    ];

    // Relationships
    // public function customer()
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesperson()
    {
        return $this->belongsTo(User::class, 'salesperson_id');
    }

    // public function items()
    public function items()
    {
        return $this->hasMany(QuotationItem::class);
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

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    // Helpers
    public function isQuotation()
    {
        return $this->status === 'quotation';
    }

    public function isSent()
    {
        return $this->status === 'sent';
    }

    public function canEdit()
    {
        return in_array($this->status, ['quotation']);
    }

    public function canDelete()
    {
        return in_array($this->status, ['quotation']);
    }

    public function markAsSent()
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    // Generate Quotation Number
    public static function generateQuotationNumber()
    {
        $prefix = 'QT';
        $date = now()->format('Ymd');
        $lastQuotation = self::whereDate('created_at', today())
            ->latest('id')
            ->first();

        $sequence = $lastQuotation ? intval(substr($lastQuotation->quotation_number, -4)) + 1 : 1;

        return $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}

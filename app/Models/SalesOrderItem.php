<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_order_id',
        'product_id',
        'description',
        'quantity',
        'delivered_quantity',
        'invoiced_quantity',
        'unit_price',
        'discount_percent',
        'discount_amount',
        'tax_percent',
        'tax_amount',
        'subtotal',
        'total',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'delivered_quantity' => 'decimal:2',
        'invoiced_quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    // Relationships
    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Calculate totals
    public function calculateTotals()
    {
        $this->subtotal = $this->quantity * $this->unit_price;
        
        // Calculate discount
        if ($this->discount_percent > 0) {
            $this->discount_amount = $this->subtotal * ($this->discount_percent / 100);
        }
        
        $amountAfterDiscount = $this->subtotal - $this->discount_amount;
        
        // Calculate tax
        if ($this->tax_percent > 0) {
            $this->tax_amount = $amountAfterDiscount * ($this->tax_percent / 100);
        }
        
        $this->total = $amountAfterDiscount + $this->tax_amount;
    }

    // Get remaining quantity to deliver
    public function getRemainingQuantity()
    {
        return $this->quantity - $this->delivered_quantity;
    }

    // Check if item is fully delivered
    public function isFullyDelivered()
    {
        return $this->delivered_quantity >= $this->quantity;
    }

    // Check if item is fully invoiced
    public function isFullyInvoiced()
    {
        return $this->invoiced_quantity >= $this->quantity;
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerInvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_invoice_id',
        'sales_order_item_id',
        'delivery_order_item_id',
        'product_id',
        'description',
        'quantity',
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
        'unit_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    // Relationships
    public function customerInvoice()
    {
        return $this->belongsTo(CustomerInvoice::class);
    }

    public function salesOrderItem()
    {
        return $this->belongsTo(SalesOrderItem::class);
    }

    public function deliveryOrderItem()
    {
        return $this->belongsTo(DeliveryOrderItem::class);
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
}
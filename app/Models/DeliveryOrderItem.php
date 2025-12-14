<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_order_id',
        'sales_order_item_id',
        'product_id',
        'description',
        'quantity',
        'delivered_quantity',
        'unit_price',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'delivered_quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
    ];

    // Relationships
    public function deliveryOrder()
    {
        return $this->belongsTo(DeliveryOrder::class);
    }

    public function salesOrderItem()
    {
        return $this->belongsTo(SalesOrderItem::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Get remaining quantity to deliver
    public function getRemainingQuantityAttribute()
    {
        return $this->quantity - $this->delivered_quantity;
    }

    // Check if item is fully delivered
    public function isFullyDelivered()
    {
        return $this->delivered_quantity >= $this->quantity;
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class POLine extends Model
{
    use HasFactory;

    // Tentukan nama tabel secara eksplisit
    protected $table = 'po_lines';

    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'quantity',
        'price',
        'subtotal',
        'description'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    // Relationships
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RFQLine extends Model
{
    use HasFactory;

    protected $table = 'rfq_lines';

    protected $fillable = [
        'rfq_id', // <-- Pastikan ini 'rfq_id' bukan 'r_f_q_id'
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
    public function rfq()
    {
        // Tentukan foreign key secara eksplisit
        return $this->belongsTo(RFQ::class, 'rfq_id'); // <-- TAMBAHKAN PARAMETER KEDUA
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
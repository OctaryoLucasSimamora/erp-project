<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManufacturingOrder extends Model
{
    protected $fillable = [
        'product_id',
        'quantity',
        'deadline',
        'status',
        'bom_id'
    ];

    public function product() {
        return $this->belongsTo(Product::class);
    }

    public function lines() {
        return $this->hasMany(ManufacturingOrderLine::class, 'mo_id');
    }
}


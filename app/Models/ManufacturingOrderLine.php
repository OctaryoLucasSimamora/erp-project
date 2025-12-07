<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManufacturingOrderLine extends Model
{
    protected $fillable = [
        'mo_id',
        'raw_material_id',
        'qty_required',
        'qty_consumed'
    ];

    public function raw() {
        return $this->belongsTo(RawMaterial::class, 'raw_material_id');
    }
}


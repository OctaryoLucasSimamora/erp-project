<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoMLine extends Model
{
    protected $table = 'bom_lines';

    protected $fillable = [
        'bom_id',
        'raw_material_id',
        'quantity',
        'cost',
        'subtotal'
    ];

    // Cast untuk konsistensi tipe data
    protected $casts = [
        'quantity' => 'float',
        'cost' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function bom()
    {
        return $this->belongsTo(BoM::class);
    }

    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class, 'raw_material_id');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoMLine extends Model
{
    protected $table = 'bom_lines';

    protected $fillable = [
        'bom_id',
        'raw_material_id',
        'quantity',   // gunakan nama ini (bukan qty)
        'cost',       // harga per unit bahan
        'subtotal'    // quantity * cost
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

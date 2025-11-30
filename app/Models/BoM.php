<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoM extends Model
{
    protected $table = 'boms';

    protected $fillable = [
        'product_id',
        'quantity'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function lines()
    {
        return $this->hasMany(BoMLine::class, 'bom_id');
    }
}

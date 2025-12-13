<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'company',
        'title',
        'position',
        'mobile',
        'image',
    ];

    // Relationship
    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }
}
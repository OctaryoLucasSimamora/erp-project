<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalItem extends Model
{
    protected $fillable = [
        'reference_id',
        'reference_type',
        'account_code',
        'account_name',
        'debit',
        'credit',
        'description',
    ];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    // Polymorphic relation
    public function reference()
    {
        return $this->morphTo();
    }
}
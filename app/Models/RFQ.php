<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RFQ extends Model
{
    use HasFactory;

    protected $table = 'rfqs';

    protected $fillable = [
        'rfq_number',
        'vendor_id',
        'deadline',
        'arrival_date',
        'company',
        'status',
        'notes',
        'total_amount'
    ];

    protected $casts = [
        'status' => 'string',
        'deadline' => 'date',
        'arrival_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    // Auto generate RFQ number
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->rfq_number)) {
                $last = self::orderBy('id', 'desc')->first();
                $next = $last ? $last->id + 1 : 1;
                $model->rfq_number = 'RFQ' . str_pad($next, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    // Relationships - PERBAIKI DI SINI JUGA
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function lines()
    {
        return $this->hasMany(RFQLine::class, 'rfq_id');
    }

    public function purchaseOrder()
    {
        // TENTUKAN FOREIGN KEY SECARA EKSPLISIT
        return $this->hasOne(PurchaseOrder::class, 'rfq_id'); // <-- TAMBAHKAN PARAMETER KEDUA
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_number',
        'sales_order_id',
        'delivery_date',
        'scheduled_date',
        'delivery_address',
        'carrier',
        'tracking_number',
        'notes',
        'status',
        'delivered_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'scheduled_date' => 'date',
        'delivered_at' => 'datetime',
    ];

    // Relationships
    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function items()
    {
        return $this->hasMany(DeliveryOrderItem::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting');
    }

    public function scopeReady($query)
    {
        return $query->where('status', 'ready');
    }

    public function scopeDone($query)
    {
        return $query->where('status', 'done');
    }

    // Status Helpers
    public function isWaiting()
    {
        return $this->status === 'waiting';
    }

    public function isReady()
    {
        return $this->status === 'ready';
    }

    public function isDone()
    {
        return $this->status === 'done';
    }

    // Status Badges
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'waiting' => 'warning',
            'ready' => 'info',
            'done' => 'success'
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    // Get total items count
    public function getTotalItemsAttribute()
    {
        return $this->items->sum('quantity');
    }

    // Get total delivered quantity
    public function getTotalDeliveredAttribute()
    {
        return $this->items->sum('delivered_quantity');
    }

    // Check if all items are fully delivered
    public function isFullyDelivered()
    {
        foreach ($this->items as $item) {
            if ($item->quantity > $item->delivered_quantity) {
                return false;
            }
        }
        return true;
    }

    // Actions
    public function markAsReady()
    {
        $this->update([
            'status' => 'ready'
        ]);
    }

    public function markAsDone()
    {
        $this->update([
            'status' => 'done',
            'delivered_at' => now()
        ]);

        // Update sales order items delivered quantities
        foreach ($this->items as $item) {
            $salesOrderItem = SalesOrderItem::find($item->sales_order_item_id);
            if ($salesOrderItem) {
                $salesOrderItem->delivered_quantity += $item->delivered_quantity;
                $salesOrderItem->save();
            }
        }

        // Check and auto lock sales order if fully delivered
        $this->salesOrder->checkAndAutoLock();
    }

    // Generate Delivery Order Number
    public static function generateDeliveryNumber()
    {
        $prefix = 'DO';
        $date = now()->format('Ymd');
        $lastDO = self::whereDate('created_at', today())
            ->latest('id')
            ->first();

        $sequence = $lastDO ? intval(substr($lastDO->delivery_number, -4)) + 1 : 1;

        return $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
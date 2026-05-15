<?php

namespace Weboldalnet\WebshopAiDefault\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $order_number
 * @property string $status
 * @property string|null $customer_name
 * @property string|null $customer_email
 * @property string|null $customer_phone
 * @property string|null $billing_data
 * @property string|null $shipping_data
 * @property float $total_price
 * @property string $currency
 * @property bool $is_completed
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property string|null $admin_note
 * @property-read \Illuminate\Database\Eloquent\Collection $items
 * @mixin \Eloquent
 */
class WebshopOrder extends Model
{
    use SoftDeletes;

    protected $table = 'public.webshop_orders';

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    const STATUSES = [
        self::STATUS_PENDING => 'Függőben',
        self::STATUS_PROCESSING => 'Feldolgozás alatt',
        self::STATUS_SHIPPED => 'Kiszállítva',
        self::STATUS_COMPLETED => 'Teljesítve',
        self::STATUS_CANCELLED => 'Törölve',
    ];

    protected $fillable = [
        'order_number', 'status', 'customer_name', 'customer_email', 'customer_phone',
        'billing_data', 'shipping_data', 'total_price', 'currency',
        'is_completed', 'completed_at', 'admin_note',
    ];

    protected $casts = ['total_price' => 'float', 'is_completed' => 'boolean', 'completed_at' => 'datetime'];

    public function items() { return $this->hasMany(WebshopOrderItem::class, 'order_id'); }

    public function scopeSearch($query, $search)
    {
        return $search ? $query->where(function ($q) use ($search) {
            $q->where('order_number', 'ILIKE', '%'.$search.'%')
              ->orWhere('customer_name', 'ILIKE', '%'.$search.'%')
              ->orWhere('customer_email', 'ILIKE', '%'.$search.'%');
        }) : $query;
    }

    public function scopeByStatus($query, $status) { return $status ? $query->where('status', $status) : $query; }

    public function scopeCompleted($query, $completed)
    {
        return ($completed !== null && $completed !== '') ? $query->where('is_completed', $completed === '1' || $completed === true) : $query;
    }

    public function getStatusLabelAttribute(): string { return self::STATUSES[$this->status] ?? $this->status; }
}

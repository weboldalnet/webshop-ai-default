<?php

namespace Weboldalnet\WebshopAiDefault\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Elállási kérelem.
 *
 * @property int $id
 * @property int|null $order_id
 * @property string|null $order_number
 * @property string|null $customer_name
 * @property string|null $customer_email
 * @property string $status
 * @property string|null $reason
 * @property bool $is_full
 * @property float $total_amount
 * @property string|null $admin_note
 * @mixin \Eloquent
 */
class WebshopWithdrawal extends Model
{
    protected $table = 'public.webshop_withdrawals';

    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_CLOSED = 'closed';

    const STATUSES = [
        self::STATUS_PENDING => 'Függőben',
        self::STATUS_IN_PROGRESS => 'Folyamatban',
        self::STATUS_CLOSED => 'Lezárva',
    ];

    protected $fillable = [
        'order_id', 'order_number', 'customer_name', 'customer_email',
        'status', 'reason', 'is_full', 'total_amount', 'admin_note',
    ];

    protected $casts = [
        'order_id' => 'integer',
        'is_full' => 'boolean',
        'total_amount' => 'float',
    ];

    public function order()
    {
        return $this->belongsTo(WebshopOrder::class, 'order_id');
    }

    public function items()
    {
        return $this->hasMany(WebshopWithdrawalItem::class, 'withdrawal_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Bootstrap badge szín a státuszhoz – az admin listán és az adatlapon is ez megy.
     */
    public function getStatusBadgeAttribute(): string
    {
        switch ($this->status) {
            case self::STATUS_CLOSED:
                return 'success';
            case self::STATUS_IN_PROGRESS:
                return 'info';
            default:
                return 'warning';
        }
    }

    /**
     * Elállással érintett darabszám (a tételek összege).
     */
    public function getTotalQuantityAttribute(): int
    {
        return (int) $this->items->sum('quantity');
    }

    public function scopeByStatus($query, $status)
    {
        return $status ? $query->where('status', $status) : $query;
    }

    public function scopeSearch($query, $search)
    {
        if (!$search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('order_number', 'ILIKE', '%' . $search . '%')
                ->orWhere('customer_name', 'ILIKE', '%' . $search . '%')
                ->orWhere('customer_email', 'ILIKE', '%' . $search . '%');
        });
    }
}

<?php

namespace Weboldalnet\WebshopAiDefault\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Elállási kérelem tétele.
 *
 * A terméknevet és az árat a kérelem beadásakor másoljuk ide, hogy a termék
 * későbbi módosítása ne írja felül, mire állt el a vásárló.
 *
 * @property int $id
 * @property int $withdrawal_id
 * @property int|null $order_item_id
 * @property int|null $product_id
 * @property string $product_name
 * @property int $quantity
 * @property float $unit_price
 * @property float $total_price
 * @mixin \Eloquent
 */
class WebshopWithdrawalItem extends Model
{
    protected $table = 'public.webshop_withdrawal_items';

    protected $fillable = [
        'withdrawal_id', 'order_item_id', 'product_id',
        'product_name', 'quantity', 'unit_price', 'total_price',
    ];

    protected $casts = [
        'withdrawal_id' => 'integer',
        'order_item_id' => 'integer',
        'product_id' => 'integer',
        'quantity' => 'integer',
        'unit_price' => 'float',
        'total_price' => 'float',
    ];

    public function withdrawal()
    {
        return $this->belongsTo(WebshopWithdrawal::class, 'withdrawal_id');
    }

    public function orderItem()
    {
        return $this->belongsTo(WebshopOrderItem::class, 'order_item_id');
    }

    public function product()
    {
        return $this->belongsTo(WebshopProduct::class, 'product_id');
    }
}

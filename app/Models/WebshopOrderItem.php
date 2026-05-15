<?php

namespace Weboldalnet\WebshopAiDefault\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $order_id
 * @property int|null $product_id
 * @property string $product_name
 * @property int $quantity
 * @property float $unit_price
 * @property float $total_price
 * @mixin \Eloquent
 */
class WebshopOrderItem extends Model
{
    protected $table = 'public.webshop_order_items';

    protected $fillable = ['order_id', 'product_id', 'product_name', 'quantity', 'unit_price', 'total_price'];

    protected $casts = [
        'order_id' => 'integer', 'product_id' => 'integer', 'quantity' => 'integer',
        'unit_price' => 'float', 'total_price' => 'float',
    ];

    public function order() { return $this->belongsTo(WebshopOrder::class, 'order_id'); }
    public function product() { return $this->belongsTo(WebshopProduct::class, 'product_id'); }
}

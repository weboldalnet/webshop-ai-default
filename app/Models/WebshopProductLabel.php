<?php

namespace Weboldalnet\WebshopAiDefault\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $bg_color
 * @property string $text_color
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @mixin \Eloquent
 */
class WebshopProductLabel extends Model
{
    protected $table = 'public.webshop_product_labels';

    protected $fillable = [
        'name', 'bg_color', 'text_color'
    ];

    public function products()
    {
        return $this->hasMany(WebshopProduct::class, 'label_id');
    }
}

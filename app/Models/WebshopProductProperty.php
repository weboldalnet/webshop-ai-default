<?php

namespace Weboldalnet\WebshopAiDefault\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $product_id
 * @property int $property_category_id
 * @property int|null $property_id
 * @property float|null $number_value
 * @mixin \Eloquent
 */
class WebshopProductProperty extends Model
{
    protected $table = 'public.webshop_product_properties';

    protected $fillable = ['product_id', 'property_category_id', 'property_id', 'number_value'];

    protected $casts = [
        'product_id' => 'integer', 'property_category_id' => 'integer',
        'property_id' => 'integer', 'number_value' => 'float',
    ];

    public function product() { return $this->belongsTo(WebshopProduct::class, 'product_id'); }
    public function propertyCategory() { return $this->belongsTo(WebshopPropertyCategory::class, 'property_category_id'); }
    public function property() { return $this->belongsTo(WebshopProperty::class, 'property_id'); }
}

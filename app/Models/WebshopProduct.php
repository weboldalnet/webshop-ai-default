<?php

namespace Weboldalnet\WebshopAiDefault\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $category_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $primary_image
 * @property string|null $sku
 * @property bool $stock_enabled
 * @property int|null $stock_quantity
 * @property float|null $price
 * @property float|null $sale_price
 * @property bool $is_active
 * @property int $sort_order
 * @property-read \Weboldalnet\WebshopAiDefault\Models\WebshopCategory $category
 * @mixin \Eloquent
 */
class WebshopProduct extends Model
{
    use SoftDeletes;

    protected $table = 'public.webshop_products';

    protected $fillable = [
        'category_id', 'name', 'slug', 'description', 'primary_image', 'sku',
        'stock_enabled', 'stock_quantity', 'price', 'sale_price', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'category_id' => 'integer', 'stock_enabled' => 'boolean', 'stock_quantity' => 'integer',
        'price' => 'float', 'sale_price' => 'float', 'is_active' => 'boolean', 'sort_order' => 'integer',
    ];

    public function category() { return $this->belongsTo(WebshopCategory::class, 'category_id'); }
    public function productProperties() { return $this->hasMany(WebshopProductProperty::class, 'product_id'); }
    public function galleryImages() { return $this->hasMany(WebshopProductGalleryImage::class, 'product_id'); }

    public function relatedProducts()
    {
        return $this->belongsToMany(self::class, 'public.webshop_product_related', 'product_id', 'related_product_id')->withTimestamps();
    }

    public function variations()
    {
        return $this->belongsToMany(self::class, 'public.webshop_product_variations', 'product_id', 'variation_product_id')->withTimestamps();
    }

    public function scopeActive($query) { return $query->where('is_active', true); }
    public function scopeOrdered($query) { return $query->orderBy('sort_order'); }
    public function scopeSearch($query, $search) { return $search ? $query->where('name', 'ILIKE', '%'.$search.'%') : $query; }
    public function scopeByCategory($query, $categoryId) { return $categoryId ? $query->where('category_id', $categoryId) : $query; }
}

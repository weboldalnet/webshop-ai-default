<?php

namespace Weboldalnet\WebshopAiDefault\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $category_id
 * @property int|null $label_id
 * @property string $name
 * @property string $secondary_name
 * @property string $slug
 * @property string|null $description
 * @property string|null $primary_image
 * @property string|null $primary_image_thumb
 * @property string|null $sku
 * @property bool $stock_enabled
 * @property int|null $stock_quantity
 * @property float|null $price
 * @property float|null $sale_price
 * @property bool $is_active
 * @property int $sort_order
 * @property-read \Weboldalnet\WebshopAiDefault\Models\WebshopCategory $category
 * @property-read \Weboldalnet\WebshopAiDefault\Models\WebshopProductLabel|null $label
 * @property-read \Illuminate\Database\Eloquent\Collection $reviews
 * @mixin \Eloquent
 */
class WebshopProduct extends Model
{
    use SoftDeletes;

    protected $table = 'public.webshop_products';

    protected $fillable = [
        'category_id', 'label_id', 'name', 'slug', 'description', 'short_desc', 'secondary_name', 'crm_identifier',
        'primary_image', 'primary_image_thumb', 'sku',
        'stock_enabled', 'stock_quantity', 'price', 'sale_price', 'is_active', 'sort_order', 'weight',
    ];

    protected $casts = [
        'category_id' => 'integer', 'label_id' => 'integer', 'stock_enabled' => 'boolean', 'stock_quantity' => 'integer',
        'price' => 'float', 'sale_price' => 'float', 'is_active' => 'boolean', 'sort_order' => 'integer',
    ];

    public function category() { return $this->belongsTo(WebshopCategory::class, 'category_id'); }
    public function label() { return $this->belongsTo(WebshopProductLabel::class, 'label_id'); }
    public function productProperties() { return $this->hasMany(WebshopProductProperty::class, 'product_id'); }
    public function galleryImages() { return $this->hasMany(WebshopProductGalleryImage::class, 'product_id'); }
    public function defaultGalleryImages() { return $this->hasMany(WebshopProductGalleryImage::class, 'product_id')->where('gallery_type', 'default'); }
    public function secondaryGalleryImages() { return $this->hasMany(WebshopProductGalleryImage::class, 'product_id')->where('gallery_type', 'secondary'); }
    public function productDocuments() { return $this->hasMany(WebshopProductDocument::class, 'product_id'); }
    public function reviews() { return $this->hasMany(WebshopProductReview::class, 'product_id'); }

    public function relatedProducts()
    {
        return $this->belongsToMany(self::class, 'public.webshop_product_related', 'product_id', 'related_product_id')->withTimestamps();
    }

    public function variations()
    {
        return $this->belongsToMany(self::class, 'public.webshop_product_variations', 'product_id', 'variation_product_id')->withTimestamps();
    }

    public function scopeActive($query) { return $query->where('is_active', true); }

    /**
     * Egy variációcsoportból CSAK a képviselőt hagyja meg a listában.
     *
     * A variációk ugyanannak a terméknek a változatai (szín, méret), ezért a
     * terméklistában külön sorként felsorolva csak duplikátumnak látszanak – a
     * vásárló a termék adatlapján tud köztük váltani.
     *
     * A képviselő a csoport LEGKISEBB sort_order-ű tagja, azonos érték esetén a
     * legkisebb id. A pivot szimmetrikus (a syncVariations mindkét irányt beírja),
     * ezért nincs külön "szülő" oszlop, amire támaszkodhatnánk – így viszont a
     * választás determinisztikus, és nem ugrál lista-újratöltésenként.
     *
     * A variációval NEM rendelkező termékeket nem érinti.
     */
    public function scopeVariationRepresentative($query)
    {
        return $query->whereNotExists(function ($sub) {
            $sub->selectRaw('1')
                ->from('public.webshop_product_variations as wpv')
                ->join('public.webshop_products as wp2', 'wp2.id', '=', 'wpv.variation_product_id')
                ->whereColumn('wpv.product_id', 'webshop_products.id')
                ->where('wp2.is_active', true)
                ->where(function ($q) {
                    $q->whereColumn('wp2.sort_order', '<', 'webshop_products.sort_order')
                        ->orWhere(function ($q2) {
                            $q2->whereColumn('wp2.sort_order', '=', 'webshop_products.sort_order')
                                ->whereColumn('wp2.id', '<', 'webshop_products.id');
                        });
                });
        });
    }
    public function scopeOrdered($query) { return $query->orderBy('sort_order'); }
    public function scopeSearch($query, $search) { return $search ? $query->where('name', 'ILIKE', '%'.$search.'%')->orWhere('secondary_name', 'ILIKE', '%'.$search.'%') : $query; }
    public function scopeByCategory($query, $categoryId) { return $categoryId ? $query->where('category_id', $categoryId) : $query; }

    public function getDiscountPercentageAttribute()
    {
        if (!$this->price || !$this->sale_price || $this->price <= $this->sale_price) {
            return 0;
        }

        return round((($this->price - $this->sale_price) / $this->price) * 100);
    }
}

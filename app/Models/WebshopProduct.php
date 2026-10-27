<?php

namespace Weboldalnet\WebshopAiDefault\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopPriceHistoryService;

/**
 * @property int $id
 * @property int $category_id
 * @property int|null $label_id
 * @property string $name
 * @property string $secondary_name
 * @property string $slug
 * @property string|null $description
 * @property string|null $og_title SEO/megosztási cím — üresen a termék neve
 * @property string|null $og_description Meta és OG leírás — üresen a rövid leírásból
 * @property string|null $og_img Megosztási kép (1200x630) — üresen az elsődleges kép
 * @property string|null $primary_image
 * @property string|null $primary_image_thumb
 * @property string|null $sku
 * @property bool $stock_enabled
 * @property int|null $stock_quantity
 * @property float|null $price
 * @property float|null $sale_price
 * @property float|null $sale_reference_price Az akciót megelőző 30 nap legalacsonyabb ára (Omnibusz)
 * @property \Illuminate\Support\Carbon|null $sale_started_at
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
        'og_title', 'og_description', 'og_img',
        'primary_image', 'primary_image_thumb', 'sku',
        'stock_enabled', 'stock_quantity', 'price', 'sale_price', 'is_active', 'sort_order', 'weight',
        'unit_quantity', 'unit_measure', 'legal_info',
    ];

    protected $casts = [
        'category_id' => 'integer', 'label_id' => 'integer', 'stock_enabled' => 'boolean', 'stock_quantity' => 'integer',
        'price' => 'float', 'sale_price' => 'float', 'is_active' => 'boolean', 'sort_order' => 'integer',
        'sale_reference_price' => 'float', 'sale_started_at' => 'datetime',
        'unit_quantity' => 'float', 'legal_info' => 'array',
    ];

    protected static function booted()
    {
        // Ártörténet az akciós „korábbi ár” (Omnibusz) számításához
        static::saving(function (self $product) {
            if (!$product->exists || $product->isDirty(['price', 'sale_price'])) {
                WebshopPriceHistoryService::syncSaleReference($product);
            }
        });

        static::saved(function (self $product) {
            if ($product->wasRecentlyCreated || $product->wasChanged(['price', 'sale_price'])) {
                WebshopPriceHistoryService::record($product);
            }
        });
    }

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

    /**
     * A vásárló által fizetendő ár: az akciós ár, ha van, különben az alapár.
     */
    public function sellingPrice(): ?float
    {
        return WebshopPriceHistoryService::sellingPrice($this->price, $this->sale_price);
    }

    /**
     * Az akciós ár mellett feltüntethető korábbi ár: az akciót megelőző 30 nap
     * legalacsonyabb eladási ára (Omnibusz). Ártörténet hiányában az alapár.
     */
    public function referencePrice(): ?float
    {
        return $this->sale_reference_price ?: $this->price;
    }

    /**
     * Hirdethető-e árcsökkentésként (áthúzott ár, százalék).
     *
     * Csak ha az akciós ár ténylegesen alacsonyabb a 30 napos legalacsonyabb árnál –
     * különben az akciós ár sima eladási árként jelenik meg.
     */
    public function hasActiveSale(): bool
    {
        $reference = $this->referencePrice();

        return $this->sale_price !== null && $this->sale_price > 0
            && $reference !== null && $reference > $this->sale_price;
    }

    public function getDiscountPercentageAttribute()
    {
        if (!$this->hasActiveSale()) {
            return 0;
        }

        $reference = $this->referencePrice();

        return round((($reference - $this->sale_price) / $reference) * 100);
    }
}

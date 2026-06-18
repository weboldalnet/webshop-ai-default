<?php

namespace Weboldalnet\WebshopAiDefault\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopSettingsService;

/**
 * @property int $id
 * @property int|null $parent_id
 * @property string $name_singular
 * @property string $name_plural
 * @property string $slug
 * @property string|null $description
 * @property string|null $og_title
 * @property string|null $og_description
 * @property string|null $og_img
 * @property string|null $icon
 * @property bool $is_active
 * @property bool $show_in_sticky_header
 * @property int $primary_image_width
 * @property int $primary_image_height
 * @property int $card_width_units
 * @property string|null $list_image_mode
 * @property int|null $list_image_product_id
 * @property string|null $list_image_path
 * @property string|null $list_image_cropped_path
 * @property string|null $list_image_cropped_path_wide
 * @property string|null $google_merchant_id
 * @property string|null $facebook_merchant_id
 * @property int $sort_order
 * @property-read \Weboldalnet\WebshopAiDefault\Models\WebshopCategory|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection $children
 * @property-read \Illuminate\Database\Eloquent\Collection $propertyCategories
 * @property-read \Illuminate\Database\Eloquent\Collection $relatedCategories
 * @property-read \Illuminate\Database\Eloquent\Collection $products
 * @mixin \Eloquent
 */
class WebshopCategory extends Model
{
    use SoftDeletes;

    protected $table = 'public.webshop_categories';

    protected $fillable = [
        'parent_id', 'name_singular', 'name_plural', 'slug', 'description',
        'og_title', 'og_description', 'og_img', 'icon', 'is_active', 'show_in_sticky_header',
        'primary_image_width', 'primary_image_height', 'sort_order',
        'card_width_units', 'list_image_mode', 'list_image_product_id', 'list_image_path', 'list_image_cropped_path', 'list_image_cropped_path_wide',
        'google_merchant_id', 'facebook_merchant_id',
    ];

    protected $casts = [
        'parent_id' => 'integer',
        'is_active' => 'boolean',
        'show_in_sticky_header' => 'boolean',
        'primary_image_width' => 'integer',
        'primary_image_height' => 'integer',
        'sort_order' => 'integer',
        'card_width_units' => 'integer',
        'list_image_product_id' => 'integer',
    ];

    public function getRouteKeyName() { return 'slug'; }

    public function getRouteKey()
    {
        return $this->slug . '-c' . $this->id;
    }

    public function resolveRouteBinding($value, $field = null)
    {
        if (preg_match('/-c(\d+)$/', $value, $matches)) {
            return $this->where('id', $matches[1])->first() ?? abort(404);
        }

        return $this->where($field ?? $this->getRouteKeyName(), $value)
            ->when(!$field && is_numeric($value), function($query) use ($value) {
                $query->orWhere('id', $value);
            })
            ->first() ?? abort(404);
    }

    public function parent() { return $this->belongsTo(self::class, 'parent_id'); }
    public function children() { return $this->hasMany(self::class, 'parent_id'); }

    public function propertyCategories()
    {
        return $this->belongsToMany(WebshopPropertyCategory::class, 'public.webshop_category_property_category', 'category_id', 'property_category_id')
            ->withPivot('show_on_product_card', 'sort_order')->withTimestamps();
    }

    public function relatedCategories()
    {
        return $this->belongsToMany(self::class, 'public.webshop_related_categories', 'category_id', 'related_category_id')->withTimestamps();
    }

    public function products() { return $this->hasMany(WebshopProduct::class, 'category_id'); }
    public function listImageProduct() { return $this->belongsTo(WebshopProduct::class, 'list_image_product_id'); }

    public function getCardColumnClass(): string
    {
        if (!WebshopSettingsService::getBool('category_sizing_enabled')) {
            return 'col-lg-3';
        }

        return match ((int)$this->card_width_units) {
            2 => 'col-lg-6',
            3 => 'col-lg-9',
            4 => 'col-lg-12',
            default => 'col-lg-3',
        };
    }

    public function getListImageUrl(): ?string
    {
        $iconEnabled = WebshopSettingsService::getBool('category_icon_enabled');
        $listImageEnabled = WebshopSettingsService::getBool('category_list_image_enabled');

        if (!$listImageEnabled) {
            return $iconEnabled ? $this->icon : null;
        }

        switch ($this->list_image_mode) {
            case 'product_image':
                return $this->listImageProduct->primary_image ?? ($iconEnabled ? $this->icon : null);
            case 'upload':
                return $this->list_image_path ?? ($iconEnabled ? $this->icon : null);
            case 'cropped_upload':
                return $this->list_image_cropped_path ?? $this->list_image_path ?? ($iconEnabled ? $this->icon : null);
            case 'icon':
            default:
                return $iconEnabled ? $this->icon : null;
        }
    }

    public function getListImageWideUrl(): ?string
    {
        if (!WebshopSettingsService::getBool('category_list_image_enabled')) {
            return null;
        }

        if ($this->list_image_mode === 'cropped_upload' && (int)$this->card_width_units > 1) {
            return $this->list_image_cropped_path_wide ?? $this->getListImageUrl();
        }

        return $this->getListImageUrl();
    }

    public function scopeActive($query) { return $query->where('is_active', true); }
    public function scopeStickyHeader($query) { return $query->where('show_in_sticky_header', true); }
    public function scopeOrdered($query) { return $query->orderBy('sort_order'); }
    public function scopeTopLevel($query) { return $query->whereNull('parent_id'); }

    public function scopeSearch($query, $search)
    {
        return $search ? $query->where(function ($q) use ($search) {
            $q->where('name_singular', 'ILIKE', '%'.$search.'%')->orWhere('name_plural', 'ILIKE', '%'.$search.'%');
        }) : $query;
    }

    public function getHierarchicalNameAttribute(): string
    {
        return $this->parent ? $this->parent->name_singular . ' > ' . $this->name_singular : $this->name_singular;
    }
}

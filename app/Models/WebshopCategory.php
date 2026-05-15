<?php

namespace Weboldalnet\WebshopAiDefault\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'og_title', 'og_description', 'og_img', 'icon', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'parent_id' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

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

    public function scopeActive($query) { return $query->where('is_active', true); }
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

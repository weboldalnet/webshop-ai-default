<?php

namespace Weboldalnet\WebshopAiDefault\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $property_category_id
 * @property string $name
 * @property string $slug
 * @property bool $is_active
 * @property int $sort_order
 * @property-read \Weboldalnet\WebshopAiDefault\Models\WebshopPropertyCategory $propertyCategory
 * @mixin \Eloquent
 */
class WebshopProperty extends Model
{
    use SoftDeletes;

    protected $table = 'public.webshop_properties';

    protected $fillable = ['property_category_id', 'name', 'slug', 'is_active', 'sort_order'];

    protected $casts = [
        'property_category_id' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function propertyCategory()
    {
        return $this->belongsTo(WebshopPropertyCategory::class, 'property_category_id');
    }

    public function scopeActive($query) { return $query->where('is_active', true); }
    public function scopeOrdered($query) { return $query->orderBy('sort_order'); }
    public function scopeSearch($query, $search) { return $search ? $query->where('name', 'ILIKE', '%'.$search.'%') : $query; }
}

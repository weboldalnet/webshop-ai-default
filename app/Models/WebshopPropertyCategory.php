<?php

namespace Weboldalnet\WebshopAiDefault\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property bool $filter_enabled
 * @property string $filter_type
 * @property string|null $suffix
 * @property bool $is_active
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\Weboldalnet\WebshopAiDefault\Models\WebshopProperty[] $properties
 * @mixin \Eloquent
 */
class WebshopPropertyCategory extends Model
{
    use SoftDeletes;

    protected $table = 'public.webshop_property_categories';

    protected $fillable = [
        'name', 'slug', 'filter_enabled', 'filter_type', 'suffix', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'filter_enabled' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function properties()
    {
        return $this->hasMany(WebshopProperty::class, 'property_category_id');
    }

    public function categories()
    {
        return $this->belongsToMany(WebshopCategory::class, 'public.webshop_category_property_category', 'property_category_id', 'category_id');
    }

    public function scopeActive($query) { return $query->where('is_active', true); }
    public function scopeOrdered($query) { return $query->orderBy('sort_order'); }
    public function scopeSearch($query, $search) { return $search ? $query->where('name', 'ILIKE', '%'.$search.'%') : $query; }
    public function isNumber(): bool { return $this->filter_type === 'number'; }

    /**
     * Szám-tulajdonság értéke a mértékegységgel: "30 adag", "750 ml" — korábban szóköz nélkül
     * ("30adag") jelent meg. A % és a ° jel szóköz nélkül tapad a számhoz (30%, 40°).
     */
    public function formatNumberValue($value): string
    {
        $number = (string) (float) $value;

        if (!$this->suffix) {
            return $number;
        }

        $separator = in_array(mb_substr($this->suffix, 0, 1), ['%', '°'], true) ? '' : "\u{00A0}";

        return $number . $separator . $this->suffix;
    }
}

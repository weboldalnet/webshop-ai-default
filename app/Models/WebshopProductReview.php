<?php

namespace Weboldalnet\WebshopAiDefault\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $product_id
 * @property string $name
 * @property int $rating
 * @property string $review
 * @property bool $is_active
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \Weboldalnet\WebshopAiDefault\Models\WebshopProduct $product
 * @mixin \Eloquent
 */
class WebshopProductReview extends Model
{
    use SoftDeletes;

    protected $table = 'public.webshop_product_reviews';

    protected $fillable = [
        'product_id', 'name', 'rating', 'review', 'is_active',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'rating' => 'integer',
        'is_active' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(WebshopProduct::class, 'product_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLatestFirst($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}

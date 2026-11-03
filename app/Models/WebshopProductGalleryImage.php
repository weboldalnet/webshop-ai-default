<?php

namespace Weboldalnet\WebshopAiDefault\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Weboldalnet\WebshopAiDefault\Models\Concerns\UsesWebshopConnection;
use Weboldalnet\WebshopAiDefault\Models\Concerns\HasWebshopTranslations;

/**
 * @property int $id
 * @property int $product_id
 * @property string $image
 * @property string $image_thumb
 * @property string|null $alt
 * @property int $sort_order
 * @property bool $is_active
 * @mixin \Eloquent
 */
class WebshopProductGalleryImage extends Model
{
    use UsesWebshopConnection;
    use HasWebshopTranslations;

    /** WebshopTranslationService::TYPES kulcsa */
    const TRANSLATION_TYPE = 'gallery_image';

    use SoftDeletes;

    protected $table = 'public.webshop_product_gallery_images';

    protected $fillable = ['product_id', 'image', 'image_thumb', 'alt', 'sort_order', 'is_active', 'gallery_type'];

    protected $casts = ['product_id' => 'integer', 'sort_order' => 'integer', 'is_active' => 'boolean'];

    public function product() { return $this->belongsTo(WebshopProduct::class, 'product_id'); }
    public function scopeActive($query) { return $query->where('is_active', true); }
    public function scopeOrdered($query) { return $query->orderBy('sort_order'); }
}

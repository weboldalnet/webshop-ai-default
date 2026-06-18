<?php

namespace Weboldalnet\WebshopAiDefault\Models;

use Illuminate\Database\Eloquent\Model;

class WebshopProductDocument extends Model
{
    protected $table = 'public.webshop_product_documents';

    protected $fillable = [
        'product_id', 'name', 'type', 'url', 'file_path', 'sort_order', 'is_active'
    ];

    protected $casts = [
        'product_id' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean'
    ];

    public function product()
    {
        return $this->belongsTo(WebshopProduct::class, 'product_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}

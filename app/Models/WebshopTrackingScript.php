<?php

namespace Weboldalnet\WebshopAiDefault\Models;

use Illuminate\Database\Eloquent\Model;

class WebshopTrackingScript extends Model
{
    protected $table = 'public.webshop_tracking_scripts';

    protected $fillable = [
        'page_type', 'name', 'script', 'is_active', 'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByPage($query, $pageType)
    {
        return $query->where('page_type', $pageType);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}

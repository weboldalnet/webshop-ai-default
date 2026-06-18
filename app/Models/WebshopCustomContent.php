<?php

namespace Weboldalnet\WebshopAiDefault\Models;

use Illuminate\Database\Eloquent\Model;

class WebshopCustomContent extends Model
{
    protected $table = 'public.webshop_custom_contents';

    protected $fillable = [
        'type', 'checkout_mode', 'payment_method', 'shipping_method', 'title', 'content', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}

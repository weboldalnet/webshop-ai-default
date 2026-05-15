<?php

namespace Weboldalnet\WebshopAiDefault\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $key
 * @property string|null $value
 * @property string $type
 * @property string $group
 * @mixin \Eloquent
 */
class WebshopSetting extends Model
{
    protected $table = 'public.webshop_settings';

    protected $fillable = ['key', 'value', 'type', 'group'];

    public function scopeByGroup($query, $group) { return $query->where('group', $group); }
    public function getBoolValueAttribute(): bool { return $this->value === 'true' || $this->value === '1'; }
}

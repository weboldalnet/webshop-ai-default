<?php

namespace Weboldalnet\WebshopAiDefault\Models;

use Illuminate\Database\Eloquent\Model;
use Weboldalnet\WebshopAiDefault\Models\Concerns\UsesWebshopConnection;

/**
 * Egy AI-fordítás által felülírt (vagy újonnan létrehozott) fordítás előző állapota — a visszavonáshoz.
 * old_value null: a mezőnek korábban nem volt fordítása (visszavonáskor törlődik).
 */
class WebshopTranslationBackup extends Model
{
    use UsesWebshopConnection;

    const UPDATED_AT = null;

    protected $table = 'public.webshop_translation_backups';

    protected $fillable = [
        'run_id', 'run_item_id', 'type', 'record_id', 'locale', 'field', 'old_value', 'old_source_hash', 'old_origin', 'new_value_hash', 'admin_id',
    ];

    protected $casts = [
        'run_id' => 'integer',
        'run_item_id' => 'integer',
        'record_id' => 'integer',
    ];
}

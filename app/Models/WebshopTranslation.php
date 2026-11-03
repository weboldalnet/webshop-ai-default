<?php

namespace Weboldalnet\WebshopAiDefault\Models;

use Illuminate\Database\Eloquent\Model;
use Weboldalnet\WebshopAiDefault\Models\Concerns\UsesWebshopConnection;

/**
 * Egy webshop-mező egy nem alapnyelvű változata (lásd a 2026_11_03 migrációt és WebshopTranslationService).
 *
 * @property int $id
 * @property string $type
 * @property int $record_id
 * @property string $locale
 * @property string $field
 * @property string|null $value
 * @property string|null $source_hash
 * @property string $origin
 * @property int|null $admin_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class WebshopTranslation extends Model
{
    use UsesWebshopConnection;

    const ORIGIN_AI = 'ai';
    const ORIGIN_MANUAL = 'manual';

    protected $table = 'public.webshop_translations';

    protected $fillable = [
        'type', 'record_id', 'locale', 'field', 'value', 'source_hash', 'origin', 'admin_id',
    ];

    protected $casts = [
        'record_id' => 'integer',
        'admin_id' => 'integer',
    ];
}

<?php

namespace Weboldalnet\WebshopAiDefault\Models;

use Illuminate\Database\Eloquent\Model;
use Weboldalnet\WebshopAiDefault\Models\Concerns\UsesWebshopConnection;

/**
 * Egy futás egy tétele: egy rekord egy nyelven.
 */
class WebshopTranslationRunItem extends Model
{
    use UsesWebshopConnection;

    const STATUS_PENDING = 'pending';
    const STATUS_DONE = 'done';
    const STATUS_ERROR = 'error';
    const STATUS_SKIPPED = 'skipped';
    const STATUS_RESTORED = 'restored';

    protected $table = 'public.webshop_translation_run_items';

    protected $fillable = [
        'run_id', 'type', 'record_id', 'locale', 'status', 'fields_done', 'fields_error', 'message',
    ];

    protected $casts = [
        'run_id' => 'integer',
        'record_id' => 'integer',
    ];

    public function run()
    {
        return $this->belongsTo(WebshopTranslationRun::class, 'run_id');
    }
}

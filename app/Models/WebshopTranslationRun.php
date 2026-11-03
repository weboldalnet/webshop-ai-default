<?php

namespace Weboldalnet\WebshopAiDefault\Models;

use Illuminate\Database\Eloquent\Model;
use Weboldalnet\WebshopAiDefault\Models\Concerns\UsesWebshopConnection;

/**
 * Egy AI-fordítási futás (lásd a 2026_11_03 translation_runs migrációt és WebshopTranslationRunService).
 *
 * options: ['locales' => ['en'], 'mode' => untranslated|changes|all, 'groups' => ['texts', 'seo', 'slug', 'images'],
 *           'keep_manual' => true]
 */
class WebshopTranslationRun extends Model
{
    use UsesWebshopConnection;

    const STATUS_PENDING = 'pending';
    const STATUS_RUNNING = 'running';
    const STATUS_PAUSED = 'paused';
    const STATUS_DONE = 'done';
    const STATUS_CANCELLED = 'cancelled';

    protected $table = 'public.webshop_translation_runs';

    protected $fillable = [
        'admin_id', 'status', 'options', 'scope_label', 'total_items', 'done_items', 'error_items', 'skipped_items',
        'translated_fields', 'characters', 'ai_calls', 'error', 'finished_at',
    ];

    protected $casts = [
        'options' => 'array',
        'finished_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(WebshopTranslationRunItem::class, 'run_id');
    }

    public function isFinished(): bool
    {
        return in_array($this->status, [self::STATUS_DONE, self::STATUS_CANCELLED], true);
    }
}

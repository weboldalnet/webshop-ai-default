<?php

namespace Weboldalnet\WebshopAiDefault\Models;

use Illuminate\Database\Eloquent\Model;

class WebshopTrackingScript extends Model
{
    protected $table = 'public.webshop_tracking_scripts';

    /**
     * Hozzájárulási kategóriák. A kulcsok a core sütiablakának (wn_cookie_consent) kategóriái;
     * a „necessary” kód azonnal fut, a többi csak az adott kategóriához adott hozzájárulás után.
     */
    public const CONSENT_NECESSARY = 'necessary';

    public const CONSENT_CATEGORIES = [
        'necessary' => 'Feltétlenül szükséges (hozzájárulás nélkül fut)',
        'preferences' => 'Funkcionális / beállítások',
        'statistics' => 'Statisztikai (pl. Google Analytics)',
        'marketing' => 'Marketing (pl. Meta Pixel, Google Ads, remarketing)',
    ];

    protected $fillable = [
        'page_type', 'name', 'script', 'is_active', 'sort_order', 'consent_category'
    ];

    /**
     * Kell-e hozzájárulás a futásához? Ismeretlen kategória: igen (a szigorúbb irány).
     */
    public function requiresConsent(): bool
    {
        return ($this->consent_category ?: 'marketing') !== self::CONSENT_NECESSARY;
    }

    public function consentCategory(): string
    {
        $category = $this->consent_category ?: 'marketing';

        return array_key_exists($category, self::CONSENT_CATEGORIES) ? $category : 'marketing';
    }

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

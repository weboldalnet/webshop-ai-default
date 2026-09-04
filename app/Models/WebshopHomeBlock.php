<?php

namespace Weboldalnet\WebshopAiDefault\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A webshop egyedi nyitóoldalának egy blokkja.
 *
 * Minden típusfüggő igazság (címke, ikon, nézet, méretek) ITT, konstansban van,
 * nem a config-ban: a projektben létezik bootstrap/cache/config.php, és
 * cache-elt confignál a csomag mergeConfigFrom-ja kimarad – egy
 * config('webshop.…') hívás ilyenkor null-t adna, amin a foreach elhasalna.
 */
class WebshopHomeBlock extends Model
{
    protected $table = 'public.webshop_home_blocks';

    protected $fillable = [
        'type', 'title', 'sort_order', 'is_active', 'layout',
        'bg_color', 'title_color', 'btn_color', 'btn_align', 'buttons',
        'link_url', 'link_label',
        'img_desktop', 'img_tablet', 'img_mobile', 'img_alt',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'settings' => 'array',
        'buttons' => 'array',
    ];

    const TYPE_PRODUCTS_MANUAL = 'products_manual';
    const TYPE_PRODUCTS_CATEGORY = 'products_category';
    const TYPE_CATEGORIES = 'categories';
    const TYPE_BANNER = 'banner';
    const TYPE_TEXT = 'text';

    /**
     * Blokktípusok: admin címke, ikon és a site-oldali nézet neve egy helyen.
     * A 'view' azért kell külön, mert két típus (products_manual és
     * products_category) UGYANAZT a site-nézetet használja.
     */
    const TYPES = [
        self::TYPE_PRODUCTS_MANUAL => [
            'label' => 'Termékek (egyenként választva)',
            'icon' => 'fa-box',
            'view' => 'products',
        ],
        self::TYPE_PRODUCTS_CATEGORY => [
            'label' => 'Termékek (kategóriából)',
            'icon' => 'fa-boxes-stacked',
            'view' => 'products',
        ],
        self::TYPE_CATEGORIES => [
            'label' => 'Kategóriák',
            'icon' => 'fa-folder-open',
            'view' => 'categories',
        ],
        self::TYPE_BANNER => [
            'label' => 'Banner sor',
            'icon' => 'fa-image',
            'view' => 'banner',
        ],
        self::TYPE_TEXT => [
            'label' => 'Szöveg',
            'icon' => 'fa-align-left',
            'view' => 'text',
        ],
    ];

    /** Ezeknél a típusoknál van értelme a scroll/többsoros választásnak */
    const LAYOUT_TYPES = [self::TYPE_PRODUCTS_MANUAL, self::TYPE_PRODUCTS_CATEGORY, self::TYPE_CATEGORIES];

    /** Ezeknél jelenik meg a "További termékek" gomb mezője */
    const LINK_TYPES = [self::TYPE_PRODUCTS_MANUAL, self::TYPE_PRODUCTS_CATEGORY];

    const LAYOUT_SCROLL = 'scroll';
    const LAYOUT_MULTI_ROW = 'multi_row';

    const LAYOUTS = [
        self::LAYOUT_SCROLL => 'Egy sorban, görgethető',
        self::LAYOUT_MULTI_ROW => 'Több sorban',
    ];

    const PRODUCT_SORTS = [
        'sort_order' => 'Admin sorrend',
        'newest' => 'Legújabb',
        'price_asc' => 'Ár szerint növekvő',
        'price_desc' => 'Ár szerint csökkenő',
        'name_asc' => 'Név szerint',
    ];

    /**
     * A banner képmetszőinek szélessége. MAGASSÁG SZÁNDÉKOSAN NINCS:
     * a metsző így szabad magasságú vágást enged (data-height nélkül nem kap
     * fix képarányt), a bannerek magassága a feltöltött képtől függ.
     */
    const IMAGE_SIZES = [
        'img_desktop' => ['label' => 'Asztali', 'width' => 1320],
        'img_tablet' => ['label' => 'Tablet', 'width' => 1024],
        'img_mobile' => ['label' => 'Mobil', 'width' => 420],
    ];

    /** Gombszínek – a site btn-{szín}-color osztályaihoz */
    const BTN_COLORS = [
        'main' => 'Elsődleges',
        'secondary' => 'Másodlagos',
        'third' => 'Harmadlagos',
    ];

    /** Gombok igazítása – közvetlenül flexbox segédosztály */
    const BTN_ALIGNS = [
        'justify-content-start' => 'Balra',
        'justify-content-center' => 'Középre',
        'justify-content-end' => 'Jobbra',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type]['label'] ?? $this->type;
    }

    public function getTypeIconAttribute(): string
    {
        return self::TYPES[$this->type]['icon'] ?? 'fa-cube';
    }

    /** A site-oldali nézet neve (products / categories / banner / text) */
    public function getViewNameAttribute(): ?string
    {
        return self::TYPES[$this->type]['view'] ?? null;
    }

    public function hasLayoutChoice(): bool
    {
        return in_array($this->type, self::LAYOUT_TYPES, true);
    }

    public function hasLinkField(): bool
    {
        return in_array($this->type, self::LINK_TYPES, true);
    }

    public function isScroll(): bool
    {
        return $this->layout === self::LAYOUT_SCROLL;
    }

    /** Egy típusfüggő beállítás kiolvasása alapértelmezettel */
    public function setting(string $kulcs, $alapertelmezett = null)
    {
        return $this->settings[$kulcs] ?? $alapertelmezett;
    }
}

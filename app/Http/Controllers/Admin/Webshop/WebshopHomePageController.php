<?php

namespace Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop;

use App\Http\Controllers\Admin\AdminExtendedController;
use Illuminate\Http\Request;
use Weboldalnet\WebshopAiDefault\Models\WebshopCategory;
use Weboldalnet\WebshopAiDefault\Models\WebshopHomeBlock;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopFileService;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopHomeBlockService;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopSettingsService;

/**
 * A webshop egyedi nyitóoldalának szerkesztője.
 *
 * Nincs autosave: a blokkok fájlfeltöltő mezőket is tartalmaznak, az autosave
 * pedig minden mentés után kiüríti a file inputokat – a metszőben lévő, még
 * el nem küldött képek némán elvesznének.
 */
class WebshopHomePageController extends AdminExtendedController
{
    /** A nyitóoldal főkapcsolója és SEO mezői */
    const SETTING_ENABLED = 'site_home_page_enabled';
    const SETTING_META_TITLE = 'site_home_page_meta_title';
    const SETTING_META_DESCRIPTION = 'site_home_page_meta_description';
    const SETTING_H1 = 'site_home_page_h1';

    public function index()
    {
        return view('admin.webshop.extra-settings.home-page', [
            'ws' => WebshopSettingsService::all(),
            'blocks' => WebshopHomeBlockService::hydrateForAdmin(),
            'categoryOptions' => self::categoryOptions(),
            'types' => WebshopHomeBlock::TYPES,
            'layouts' => WebshopHomeBlock::LAYOUTS,
            'productSorts' => WebshopHomeBlock::PRODUCT_SORTS,
            'imageSizes' => WebshopHomeBlock::IMAGE_SIZES,
            'btnColors' => WebshopHomeBlock::BTN_COLORS,
            'btnAligns' => WebshopHomeBlock::BTN_ALIGNS,
            // Az újonnan felvett blokkot nyitva mutatjuk, hogy rögtön szerkeszthető legyen
            'openBlockId' => session('open_block'),
        ]);
    }

    /**
     * Főkapcsoló és SEO mezők mentése (a blokkoktól független űrlap).
     */
    public function storeSettings(Request $request)
    {
        $request->validate([
            self::SETTING_META_TITLE => 'nullable|string|max:255',
            self::SETTING_META_DESCRIPTION => 'nullable|string|max:500',
            self::SETTING_H1 => 'nullable|string|max:255',
        ]);

        WebshopSettingsService::save([
            self::SETTING_ENABLED => $request->has(self::SETTING_ENABLED) ? 'true' : 'false',
            self::SETTING_META_TITLE => $request->input(self::SETTING_META_TITLE) ?? '',
            self::SETTING_META_DESCRIPTION => $request->input(self::SETTING_META_DESCRIPTION) ?? '',
            self::SETTING_H1 => $request->input(self::SETTING_H1) ?? '',
        ]);

        return redirect()->back()->with('success', 'Nyitóoldal beállításai elmentve.');
    }

    /**
     * Új blokk felvétele. A törzsét utána, a blokk saját űrlapján lehet kitölteni.
     */
    public function storeBlock(Request $request)
    {
        $type = $request->input('type');

        // Whitelist: a típus vezérli, melyik nézet és melyik mentő ág fut le
        if (!array_key_exists($type, WebshopHomeBlock::TYPES)) {
            return redirect()->back()->with('error', 'Ismeretlen blokktípus.');
        }

        $block = WebshopHomeBlock::create([
            'type' => $type,
            'title' => $request->input('title') ?: null,
            'sort_order' => (int) WebshopHomeBlock::max('sort_order') + 1,
            'is_active' => true,
            'layout' => WebshopHomeBlock::LAYOUT_MULTI_ROW,
            'settings' => [],
        ]);

        return redirect()->back()
            ->with('success', 'Blokk hozzáadva.')
            ->with('open_block', $block->id);
    }

    /**
     * Egy blokk mentése. A közös mezők itt, a típusfüggő rész a settings…() metódusokban.
     */
    public function updateBlock(Request $request, $block)
    {
        $block = WebshopHomeBlock::findOrFail($block);

        $request->validate([
            'title' => 'nullable|string|max:255',
            'link_url' => 'nullable|string|max:500',
            'link_label' => 'nullable|string|max:100',
            'img_alt' => 'nullable|string|max:255',
        ]);

        $btnColor = $request->input('btn_color');
        $btnAlign = $request->input('btn_align');

        $adatok = [
            'title' => $request->input('title') ?: null,
            'settings' => $this->buildSettings($request, $block),

            // Szabad HEX színek; üresen hagyva nincs egyedi szín
            'bg_color' => self::hexSzin($request->input('bg_color')),
            'title_color' => self::hexSzin($request->input('title_color')),

            // A színt és az igazítást osztálynévbe tesszük a site-on, ezért whitelist
            'btn_color' => array_key_exists($btnColor, WebshopHomeBlock::BTN_COLORS) ? $btnColor : 'main',
            'btn_align' => array_key_exists($btnAlign, WebshopHomeBlock::BTN_ALIGNS) ? $btnAlign : 'justify-content-start',

            /*
               A gombok a projekt hero-inál is használt szerkezetében érkeznek.
               A mezőnév-előtag blokkonként egyedi (hb_<id>), mert a button-multi
               md5($variable)-ből képzi az elem-azonosítóit – közös előtaggal az
               ikonválasztó a lap másik blokkjába írna.
            */
            'buttons' => $request->input('hb_' . $block->id . '.btn') ?: null,
        ];

        if ($block->hasLayoutChoice()) {
            $layout = $request->input('layout');
            $adatok['layout'] = array_key_exists($layout, WebshopHomeBlock::LAYOUTS)
                ? $layout
                : WebshopHomeBlock::LAYOUT_MULTI_ROW;
        }

        // A link_url már csak a banneré: ott a TELJES kép kattintható, ami más,
        // mint egy gomb. A blokkok gombjai a buttons mezőbe kerülnek.
        if ($block->type === WebshopHomeBlock::TYPE_BANNER) {
            $adatok['link_url'] = $request->input('link_url') ?: null;
            $adatok['img_alt'] = $request->input('img_alt') ?: null;
            $adatok = array_merge($adatok, $this->handleBannerImages($request, $block));
        }

        $block->update($adatok);

        return redirect()->back()
            ->with('success', 'Blokk elmentve.')
            ->with('open_block', $block->id);
    }

    public function destroyBlock($block)
    {
        $block = WebshopHomeBlock::findOrFail($block);

        foreach (array_keys(WebshopHomeBlock::IMAGE_SIZES) as $mezo) {
            WebshopFileService::deleteHomeBlockImage($block->{$mezo});
        }

        $block->delete();

        return redirect()->back()->with('success', 'Blokk törölve.');
    }

    /**
     * Sorrend mentése (drag&drop után, AJAX).
     * A WebshopProductController::sort() mintája.
     */
    public function sortBlocks(Request $request)
    {
        $sorrend = 1;
        foreach ($request->input('orderedIds', []) as $id) {
            WebshopHomeBlock::where('id', $id)->update(['sort_order' => $sorrend++]);
        }

        return response()->json(['success' => true, 'message' => 'Sorrend mentve.']);
    }

    /**
     * Aktív kapcsoló (AJAX).
     * A segéd JS STRINGKÉNT küldi az értéket, ezért nem boolean() a vizsgálat.
     */
    public function toggleBlockActive(Request $request)
    {
        $block = WebshopHomeBlock::findOrFail($request->input('id'));
        $ertek = $request->input('is_active');

        $block->update(['is_active' => $ertek === 'true' || $ertek === true || $ertek === '1']);

        return response()->json(['success' => true, 'message' => 'Állapot mentve.']);
    }

    /**
     * A típusfüggő beállítások összeállítása.
     * Egy új blokktípus ára: egy ág itt + egy admin partial + egy site partial.
     */
    protected function buildSettings(Request $request, WebshopHomeBlock $block): array
    {
        switch ($block->type) {
            case WebshopHomeBlock::TYPE_PRODUCTS_MANUAL:
                return ['product_ids' => self::intLista($request->input('product_ids', []))];

            case WebshopHomeBlock::TYPE_PRODUCTS_CATEGORY:
                $limit = (int) $request->input('limit', 8);
                $sort = $request->input('sort');

                return [
                    'category_id' => $request->input('category_id') ? (int) $request->input('category_id') : null,
                    'limit' => max(1, min(48, $limit ?: 8)),
                    'sort' => array_key_exists($sort, WebshopHomeBlock::PRODUCT_SORTS) ? $sort : 'sort_order',
                ];

            case WebshopHomeBlock::TYPE_CATEGORIES:
                return ['category_ids' => self::intLista($request->input('category_ids', []))];

            case WebshopHomeBlock::TYPE_TEXT:
                return ['content' => $request->input('content') ?? ''];

            default:
                // banner: minden adata saját oszlopban van
                return [];
        }
    }

    /**
     * A banner három képváltozatának mentése és törlése.
     *
     * Minden méret magasság nélkül vágódik, a szélességek a modell
     * IMAGE_SIZES konstansából jönnek – nem configból, mert cache-elt confignál
     * a csomag merge-elt kulcsai kimaradnának.
     */
    protected function handleBannerImages(Request $request, WebshopHomeBlock $block): array
    {
        $valtozas = [];

        foreach (WebshopHomeBlock::IMAGE_SIZES as $mezo => $meret) {
            // Külön jelölőnégyzet a meglévő kép eltávolításához
            if ($request->boolean($mezo . '_delete')) {
                WebshopFileService::deleteHomeBlockImage($block->{$mezo});
                $valtozas[$mezo] = null;
                continue;
            }

            if (!$request->hasFile($mezo)) {
                continue;
            }

            $regi = $block->{$mezo};

            $valtozas[$mezo] = WebshopFileService::saveHomeBlockImage(
                $request->file($mezo),
                'home-' . $block->id . '-' . $mezo,
                $meret['width']
            );

            WebshopFileService::deleteHomeBlockImage($regi);
        }

        return $valtozas;
    }

    /**
     * Szín normalizálása.
     *
     * Az érték a site-on inline style-ba kerül, ezért csak valódi HEX-et
     * engedünk át – így nem lehet a stíluson keresztül tetszőleges CSS-t beadni.
     */
    protected static function hexSzin($ertek): ?string
    {
        $ertek = trim((string) $ertek);

        if ($ertek === '') {
            return null;
        }

        return preg_match('/^#[0-9a-fA-F]{3,8}$/', $ertek) ? $ertek : null;
    }

    /** Beérkező azonosítólista tisztítása: csak pozitív egészek, duplikátum nélkül */
    protected static function intLista($ertekek): array
    {
        if (!is_array($ertekek)) {
            return [];
        }

        $tisztitott = [];
        foreach ($ertekek as $ertek) {
            $id = (int) $ertek;
            if ($id > 0 && !in_array($id, $tisztitott, true)) {
                $tisztitott[] = $id;
            }
        }

        return $tisztitott;
    }

    /**
     * Kategóriák a legördülőkhöz, szülő → gyerek behúzással.
     *
     * A modellen nincs kész "hierarchikus név" mező, ezért itt állítjuk elő.
     */
    protected static function categoryOptions(): array
    {
        $kategoriak = WebshopCategory::ordered()->get();
        $gyerekek = $kategoriak->groupBy('parent_id');

        $lista = [];

        $bejar = function ($szuloId, $szint) use (&$bejar, $gyerekek, &$lista) {
            foreach ($gyerekek->get($szuloId, collect()) as $kategoria) {
                $lista[$kategoria->id] = str_repeat('— ', $szint) . $kategoria->name_singular;
                $bejar($kategoria->id, $szint + 1);
            }
        };

        $bejar(null, 0);

        return $lista;
    }
}

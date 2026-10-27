<?php

namespace Weboldalnet\WebshopAiDefault\Services\Webshop;

use Weboldalnet\WebshopAiDefault\Models\WebshopCategory;
use Weboldalnet\WebshopAiDefault\Models\WebshopProduct;

/**
 * A webshop oldalainak SEO-fejléce: böngészőcím, meta/OG leírás és megosztási kép.
 *
 * Miért kell: a webshop oldaltípusokhoz korábban semmilyen meta adat nem volt bekötve —
 * a title csak a termék/kategória neve volt (márka nélkül), a meta description és az
 * og:image pedig üresen maradt mind a 15 oldalon.
 *
 * Elv: ami az adminban ki van töltve (`og_title` / `og_description` / `og_img`), az az
 * irányadó; ami üres, arra a tartalom SAJÁT adataiból képzünk tartalékot — így az oldal
 * sosem marad üres leírással, a szerkesztő mégis bármikor felülírhatja.
 */
class WebshopSeoService
{
    /** A meta leíráshoz ennél hosszabb szöveget szóhatáron vágunk. */
    public const DESCRIPTION_MAX = 160;

    /**
     * Termékoldal. Tartalék: név, rövid leírás (ennek híján a hosszú), elsődleges kép.
     *
     * A sorrendnél figyelni kell arra, hogy a „rövid leírás" mezőbe sok webshop
     * SPECIFIKÁCIÓT ír, nem mondatot („3 l · hidegen sajtolva") — ez önmagában haszontalan
     * meta leírás. Ezért a rövid leírás csak akkor visz, ha önmagában is értelmes
     * hosszúságú; különben a termékleírás a jobb forrás.
     */
    public static function product(WebshopProduct $product): array
    {
        return [
            'title' => self::branded($product->og_title ?: $product->name),
            'description' => self::text(
                $product->og_description,
                self::usableAsDescription($product->short_desc) ? $product->short_desc : null,
                $product->description,
                $product->short_desc
            ),
            'image' => self::absolute($product->og_img ?: $product->primary_image),
        ];
    }

    /**
     * Mondatnyi-e a szöveg? A küszöb tapasztalati: egy specifikációs sor jellemzően 20-40
     * karakter, egy értelmes meta leírás 60 fölött kezdődik.
     */
    private static function usableAsDescription(?string $text): bool
    {
        return mb_strlen(trim(preg_replace('/\s+/u', ' ', strip_tags((string) $text)))) >= 60;
    }

    /** Kategóriaoldal. Tartalék: kategórianév, kategórialeírás, listakép. */
    public static function category(WebshopCategory $category): array
    {
        return [
            'title' => self::branded($category->og_title ?: $category->name_singular),
            'description' => self::text($category->og_description, $category->description),
            'image' => self::absolute($category->og_img ?: self::categoryImage($category)),
        ];
    }

    /**
     * A webshop nyitóoldala (/webshop). Ha egy szülőkategória alkategóriáit listázza,
     * az a kategória SAJÁT adatait használja — ugyanaz az oldal, csak más tartalommal.
     *
     * Gyökérszinten az adminban megadott nyitóoldal-meta (Webshop > Nyitóoldal) az
     * irányadó; ennek híján a tényleges kategóriakínálatból képzünk leírást, hogy ne
     * maradjon üres.
     */
    public static function shopHome(?WebshopCategory $parentCategory = null): array
    {
        if ($parentCategory) {
            return self::category($parentCategory);
        }

        return [
            'title' => self::branded(WebshopSettingsService::get('site_home_page_meta_title') ?: 'Webshop'),
            'description' => self::text(
                WebshopSettingsService::get('site_home_page_meta_description'),
                self::categoryListDescription()
            ),
            'image' => self::absolute(self::homeImage()),
        ];
    }

    /**
     * Cím + márkanév, a core oldalainak mintájára („Cím - Cégnév"). Ha a szerkesztő már
     * beleírta a márkanevet az OG címbe, nem duplázzuk.
     */
    private static function branded(?string $title): string
    {
        $title = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $title)));
        $brand = trim((string) config('app.shop_name'));

        if ($title === '') {
            return $brand;
        }

        if ($brand === '' || mb_stripos($title, $brand) !== false) {
            return $title;
        }

        return $title . ' - ' . $brand;
    }

    /**
     * Az első nem üres forrásból képzett meta leírás: HTML nélkül, egy sorban,
     * DESCRIPTION_MAX hosszra szóhatáron vágva.
     */
    private static function text(?string ...$sources): string
    {
        foreach ($sources as $source) {
            // A TinyMCE „üres" mezője valójában &nbsp;-t tartalmazhat, ezért a tagek
            // levágása UTÁN vizsgáljuk, maradt-e tényleges szöveg.
            $plain = trim(preg_replace('/\s+/u', ' ', html_entity_decode(
                strip_tags((string) $source), ENT_QUOTES | ENT_HTML5, 'UTF-8'
            )));
            // A nem törhető szóköz nem számít tartalomnak.
            $plain = trim(str_replace("\xC2\xA0", ' ', $plain));

            if ($plain === '') {
                continue;
            }

            if (mb_strlen($plain) <= self::DESCRIPTION_MAX) {
                return $plain;
            }

            $cut = mb_substr($plain, 0, self::DESCRIPTION_MAX);
            $lastSpace = mb_strrpos($cut, ' ');

            return rtrim($lastSpace ? mb_substr($cut, 0, $lastSpace) : $cut, " ,.;:-") . '…';
        }

        return '';
    }

    /**
     * Megosztási kép abszolút URL-ként. Az og:image-et a közösségi oldalak és a keresők
     * robotjai önálló kérésben töltik le, ezért relatív útvonallal nem tudnak mit kezdeni —
     * a tárolt érték viszont `/storage/…` alakú.
     */
    private static function absolute(?string $url): string
    {
        $url = trim((string) $url);

        if ($url === '' || preg_match('~^(https?:)?//~i', $url)) {
            return $url;
        }

        return rtrim(request()->getSchemeAndHttpHost(), '/') . '/' . ltrim($url, '/');
    }

    /** Kategória tartalék képe: a lista-kép, ennek híján az első aktív termék képe. */
    private static function categoryImage(WebshopCategory $category): ?string
    {
        $listImage = $category->getListImageUrl();
        if (is_string($listImage) && preg_match('~^(/|https?://)~', $listImage)) {
            return $listImage;
        }

        return $category->products()
            ->where('is_active', true)
            ->whereNotNull('primary_image')
            ->orderBy('sort_order')
            ->value('primary_image');
    }

    /** A nyitóoldal tartalék képe: az első aktív termék elsődleges képe. */
    private static function homeImage(): ?string
    {
        return WebshopProduct::query()
            ->where('is_active', true)
            ->whereNotNull('primary_image')
            ->orderBy('sort_order')
            ->value('primary_image');
    }

    /** A nyitóoldal tartalék leírása a tényleges, aktív főkategóriákból. */
    private static function categoryListDescription(): string
    {
        $names = WebshopCategory::active()->topLevel()->ordered()
            ->pluck('name_plural')
            ->filter()
            ->take(8)
            ->implode(', ');

        $brand = trim((string) config('app.shop_name'));

        if ($names === '') {
            return $brand !== '' ? $brand . ' webáruház.' : '';
        }

        return ($brand !== '' ? $brand . ' webáruház: ' : 'Webáruházunk kínálata: ') . $names . '.';
    }
}

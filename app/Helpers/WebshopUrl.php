<?php

namespace Weboldalnet\WebshopAiDefault\Helpers;

use Illuminate\Support\Facades\Route;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopTranslationService;

/**
 * A webshop címei a vásárló nyelvén. Blade-ben a `WsUrl` aliasszal: {{ WsUrl::route('site.webshop.checkout.index') }},
 * {{ WsUrl::product($product) }}, {{ WsUrl::category($category) }}.
 *
 * Útvonalnevek: az alapnyelv a megszokott `site.webshop.*`, a többi nyelv `site.webshop.{nyelv}.*` (routes/web.php) — így
 * a core `routeIs('site.webshop.*')` ellenőrzései minden nyelven igazak maradnak. Egynyelvű projektben (vagy régi core-nál,
 * ahol nincs Lang::siteLocales()) minden az alapnyelvi útvonalra megy, pontosan úgy, mint eddig.
 */
class WebshopUrl
{
    public const NAME_PREFIX = 'site.webshop.';

    /** A site nyelvei, amelyekre a webshop útvonalai épülnek (az első az alapnyelv) */
    public static function locales(): array
    {
        // egy forrás a fordításokkal: a core nyelvei (próbához a webshop.locales), az alapnyelv elöl
        return array_values(array_unique(array_merge([self::defaultLocale()], WebshopTranslationService::locales())));
    }

    public static function defaultLocale(): string
    {
        return defined(\App\Helpers\Lang::class . '::DEFAULT_LANG') ? \App\Helpers\Lang::DEFAULT_LANG : 'hu';
    }

    /** A címek nyelve: többnyelvű site-on a megadott vagy az aktuális, egyébként az alapnyelv */
    public static function urlLocale(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();

        return in_array($locale, self::locales(), true) ? $locale : self::defaultLocale();
    }

    /** Útszó a config('webshop.url_words') térképből; hiányában az angol, végül az alapnyelvi szó */
    public static function word(string $key, ?string $locale = null): string
    {
        $words = (array) config('webshop.url_words', []);
        $locale = $locale ?: self::defaultLocale();

        return (string) ($words[$locale][$key] ?? $words['en'][$key] ?? $words[self::defaultLocale()][$key] ?? $key);
    }

    /** Egy nyelv útvonalneve: site.webshop.checkout.index → site.webshop.en.checkout.index */
    public static function localizedName(string $name, ?string $locale = null): string
    {
        $locale = self::urlLocale($locale);
        if ($locale === self::defaultLocale() || !str_starts_with($name, self::NAME_PREFIX)) {
            return $name;
        }

        return self::NAME_PREFIX . $locale . '.' . substr($name, strlen(self::NAME_PREFIX));
    }

    /** A nyelv útvonalneve, ha az útvonal létezik, különben az alapnyelvi (aláírt címekhez: URL::signedRoute()) */
    public static function existingName(string $name, ?string $locale = null): string
    {
        $localized = self::localizedName($name, $locale);

        return Route::has($localized) ? $localized : $name;
    }

    /** Termékoldal az azonosítóból (pl. a kosár tételéből); törölt terméknél null */
    public static function productById(int $productId, ?string $locale = null): ?string
    {
        static $products = [];
        if (!array_key_exists($productId, $products)) {
            $products[$productId] = \Weboldalnet\WebshopAiDefault\Models\WebshopProduct::query()->find($productId);
        }

        return $products[$productId] ? self::product($products[$productId], $locale) : null;
    }

    /** A route() nyelvkövető párja: a nyelv útvonala, ha létezik, különben az alapnyelvi */
    public static function route(string $name, $parameters = [], bool $absolute = true, ?string $locale = null): string
    {
        $localized = self::localizedName($name, $locale);

        return route(Route::has($localized) ? $localized : $name, $parameters, $absolute);
    }

    /** Az aktuális kérés útvonalának nyelvfüggetlen neve (site.webshop.en.products.show → site.webshop.products.show) */
    public static function baseName(?string $name): ?string
    {
        if (!$name || !str_starts_with($name, self::NAME_PREFIX)) {
            return $name;
        }

        foreach (self::locales() as $locale) {
            $prefix = self::NAME_PREFIX . $locale . '.';
            if (str_starts_with($name, $prefix)) {
                return self::NAME_PREFIX . substr($name, strlen($prefix));
            }
        }

        return $name;
    }

    /** Webshop-oldal-e az aktuális kérés (bármelyik nyelven) — a request()->is('webshop*') helyett */
    public static function isWebshopRequest(): bool
    {
        $route = request()->route();
        $name = $route ? $route->getName() : null;

        return ($name && str_starts_with($name, self::NAME_PREFIX)) || request()->is('webshop*');
    }

    public static function product($product, ?string $locale = null, bool $absolute = true): string
    {
        $locale = self::urlLocale($locale);
        $original = $product->getRawOriginal('slug') ?? $product->slug;
        $slug = method_exists($product, 'translated') ? $product->translated('slug', $locale) : $original;

        // az alapnyelvi útvonal a {product:slug} kötést használja, a nyelvi a {productSlug}-ot (routes/web.php)
        $name = self::localizedName('site.webshop.products.show', $locale);
        if ($name !== 'site.webshop.products.show' && Route::has($name)) {
            return route($name, ['productSlug' => $slug ?: $original], $absolute);
        }

        return route('site.webshop.products.show', ['product' => $original], $absolute);
    }

    public static function category($category, ?string $locale = null, bool $absolute = true, string $suffix = ''): string
    {
        $locale = self::urlLocale($locale);

        return self::route('site.webshop.categories.show', ['categorySlug' => self::categoryKey($category, $locale) . $suffix], $absolute, $locale);
    }

    /** A kategória címkulcsa a nyelven: {lefordított slug}-c{id} (az azonosító dönt, a slug csak olvashatóság) */
    public static function categoryKey($category, ?string $locale = null): string
    {
        $slug = method_exists($category, 'translated') ? $category->translated('slug', self::urlLocale($locale)) : $category->slug;

        return ($slug ?: $category->slug) . '-c' . $category->id;
    }
}

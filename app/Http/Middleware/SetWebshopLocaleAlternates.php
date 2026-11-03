<?php

namespace Weboldalnet\WebshopAiDefault\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Weboldalnet\WebshopAiDefault\Helpers\WebshopUrl;
use Weboldalnet\WebshopAiDefault\Models\WebshopCategory;
use Weboldalnet\WebshopAiDefault\Models\WebshopProduct;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopTranslationService;

/**
 * A webshop-oldal megfelelője a többi nyelven — a core nyelvválasztójának és hreflang-sorainak.
 *
 * A core LocaleUrlService csak a saját (App\Http\Controllers\Site\…) oldalait ismeri; csomag-oldalon a nyelvválasztó a
 * másik nyelv főoldalára vinne, hreflang pedig nem lenne. Amíg a core-ban nincs erre bővítési pont, a webshop a core
 * kérésen belüli gyorsítótárába (request attribútum: LocaleUrlService::REQUEST_KEY) előre beírja a címeket. Ha a
 * core ezt a kulcsot megváltoztatja, a működés csendben a régire esik vissza (nyelvi főoldal), hibát nem okoz.
 */
class SetWebshopLocaleAlternates
{
    /** A core LocaleUrlService::REQUEST_KEY értéke */
    private const CORE_REQUEST_KEY = 'wn_locale_alternates';

    public function handle(Request $request, Closure $next)
    {
        if (count(WebshopUrl::locales()) > 1 && $request->isMethod('GET') && !$request->attributes->has(self::CORE_REQUEST_KEY)) {
            try {
                $alternates = $this->alternates($request);
                if ($alternates) {
                    $request->attributes->set(self::CORE_REQUEST_KEY, $alternates);
                }
            } catch (\Throwable $e) {
                // a nyelvválasztó tartalékra (nyelvi főoldal) esik vissza — az oldal kiszolgálását nem akaszthatja meg
                report($e);
            }
        }

        return $next($request);
    }

    /** [nyelv => relatív cím] */
    protected function alternates(Request $request): array
    {
        $route = $request->route();
        $name = WebshopUrl::baseName($route ? $route->getName() : null);
        if (!$name) {
            return [];
        }

        $alternates = [];
        foreach (WebshopUrl::locales() as $locale) {
            $url = $this->urlFor($name, $route->parameters(), $locale);
            if ($url !== null) {
                $alternates[$locale] = $url;
            }
        }

        return $alternates;
    }

    protected function urlFor(string $name, array $parameters, string $locale): ?string
    {
        switch ($name) {
            case 'site.webshop.products.show':
                $product = $this->product($parameters);
                if (!$product || !WebshopTranslationService::isVisibleInLocale($product, $locale)) {
                    return null;
                }

                return WebshopUrl::product($product, $locale, false);

            case 'site.webshop.categories.show':
                $categorySlug = (string) ($parameters['categorySlug'] ?? '');
                // a szűrő-tokenek (;f12;page-2) azonosító-alapúak, nyelvtől függetlenek — változatlanul mennek tovább
                $suffix = str_contains($categorySlug, ';') ? substr($categorySlug, strpos($categorySlug, ';')) : '';
                $category = preg_match('/-c(\d+)(?:;|$)/', $categorySlug, $m) ? WebshopCategory::query()->find((int) $m[1]) : null;

                return $category ? WebshopUrl::category($category, $locale, false, $suffix) : null;

            case 'site.webshop.categories.index':
            case 'site.webshop.compare.index':
            case 'site.webshop.checkout.index':
                return WebshopUrl::route($name, [], false, $locale);

            // Rendeléseim (a vásárlói fiók oldalán): ugyanaz a rendelés a másik nyelven
            case 'site.webshop.account.orders':
            case 'site.webshop.account.orders.show':
                return WebshopUrl::route($name, $parameters, false, $locale);

            default:
                // rendeléshez, aláírt címhez, AJAX-hoz nincs nyelvi párja a nyelvválasztóban (főoldalra visz)
                return null;
        }
    }

    protected function product(array $parameters): ?WebshopProduct
    {
        if (($parameters['product'] ?? null) instanceof WebshopProduct) {
            return $parameters['product'];
        }

        $slug = $parameters['productSlug'] ?? $parameters['product'] ?? null;
        if (!is_string($slug) || $slug === '') {
            return null;
        }

        $locale = WebshopUrl::urlLocale();
        $id = WebshopTranslationService::recordIdBySlug(WebshopProduct::TRANSLATION_TYPE, $locale, $slug);

        return $id ? WebshopProduct::query()->find($id) : WebshopProduct::query()->where('slug', $slug)->first();
    }
}

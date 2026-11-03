<?php

namespace Weboldalnet\WebshopAiDefault\Support;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Weboldalnet\WebshopAiDefault\Helpers\WebshopUrl;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopTranslationService;

/**
 * A rendelés nyelve (webshop_orders.locale) a rendelés után is: a vásárlónak menő levelek, a számla és a fizetési oldal
 * ezen a nyelven készül — akkor is, ha parancssorból / fizetési visszahívásból / sorból fut (ott nincs kérés-nyelv).
 *
 * A futtatás a nyelvet kétfelé állítja: az app nyelvét (felületi szövegek, __()) és a webshop-fordításokat
 * (HasWebshopTranslations: termék-, levél- és köszönőoldal-szövegek), majd visszaállítja mindkettőt.
 */
final class WebshopOrderLocale
{
    /** A Számlázz.hu számlanyelvei (a cseh kódja náluk „cz”) */
    private const INVOICE_LANGUAGES = ['hu', 'en', 'de', 'it', 'ro', 'sk', 'hr', 'fr', 'es', 'cz', 'pl', 'bg', 'nl', 'ru', 'si'];

    /** A rendelés nyelve, ha az egy élő site-nyelv; különben (régi rendelés, kikapcsolt nyelv) az alapnyelv */
    public static function of($order): string
    {
        $locale = is_object($order) ? (string) ($order->locale ?? '') : '';

        return in_array($locale, WebshopUrl::locales(), true) ? $locale : WebshopUrl::defaultLocale();
    }

    /** A $callback a megadott nyelven; utána a korábbi nyelv és az automatikus fordítás-nyelv áll vissza */
    public static function run(string $locale, callable $callback)
    {
        $previous = app()->getLocale();
        app()->setLocale($locale);
        WebshopTranslationService::useSiteLocale($locale);

        try {
            return $callback();
        } finally {
            app()->setLocale($previous);
            WebshopTranslationService::useSiteLocale(null, true);
        }
    }

    /** Levél a megadott nyelven (a tárgy, a sablon szövegei, a linkek és a webshop-tartalmak is) */
    public static function mail(string $to, Mailable $mail, string $locale): void
    {
        self::run($locale, fn () => Mail::to($to)->locale($locale)->send($mail));
    }

    /** A fizetési oldal nyelve a szolgáltatóknak (Barion, SimplePay: nagybetűs kód; ismeretlennél a szolgáltató alapja) */
    public static function paymentLanguage(string $locale): string
    {
        return strtoupper($locale === 'cs' ? 'cz' : $locale);
    }

    /** A számla nyelve (Számlázz.hu); nem támogatott nyelvnél magyar */
    public static function invoiceLanguage(string $locale): string
    {
        $code = $locale === 'cs' ? 'cz' : ($locale === 'sl' ? 'si' : $locale);

        return in_array($code, self::INVOICE_LANGUAGES, true) ? $code : 'hu';
    }
}

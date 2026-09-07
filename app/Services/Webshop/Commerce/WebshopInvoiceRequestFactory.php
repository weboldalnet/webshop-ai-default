<?php

namespace Weboldalnet\WebshopAiDefault\Services\Webshop\Commerce;

use Weboldalnet\WebshopAiDefault\Models\WebshopOrder;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopSettingsService;

class WebshopInvoiceRequestFactory
{
    /**
     * A terméksorokon FELÜLI számlatételek.
     *
     * Mindegyik külön ki-be kapcsolható és átnevezhető az adminban, mert van
     * olyan bolt, ahol ezeket kézzel viszik fel a számlázó programban – ott a
     * mi tételünk duplikáció lenne. A kulcs adja a beállítás nevét:
     *   invoice_line_<kulcs>_enabled   (checkbox, alap: be)
     *   invoice_line_<kulcs>_name      (szöveg, alap: a lenti default_name)
     *
     * A 'forras' a WebshopOrder::priceBreakdown() kulcsa, az 'elojel' pedig azt
     * mondja meg, hozzáad vagy levon. Új tétel felvétele: egy sor ide.
     */
    const EXTRA_LINES = [
        'coupon' => [
            'default_name' => 'Kuponkedvezmény',
            'forras' => 'kedvezmeny',
            'elojel' => -1,
            'admin_label' => 'Kuponkedvezmény',
        ],
        'shipping' => [
            'default_name' => 'Szállítási díj',
            'forras' => 'szallitas',
            'elojel' => 1,
            'admin_label' => 'Szállítási díj',
        ],
        'cod' => [
            'default_name' => 'Utánvét díja',
            'forras' => 'utanvet',
            'elojel' => 1,
            'admin_label' => 'Utánvét díja',
        ],
    ];

    /**
     * A számla áfakulcsa – MINDEN tételre, a terméksorokra is.
     *
     * Miért egy közös kulcs, és miért nem terméksoronként: a webshop
     * adatmodelljében NINCS áfa fogalom. A webshop_products és a
     * webshop_order_items táblában nincs áfakulcs oszlop, sehol nem is
     * tároljuk – tehát nincs miből soronként származtatni. Amíg ez így van,
     * a bolt egyetlen áfakulccsal dolgozik, és az legyen kimondva, ne pedig
     * egy néma 27%-os visszaesés mögé rejtve.
     *
     * Egy alanyi adómentes bolt így az EGÉSZ számlát AAM-re állítja, nem
     * keveredik 27%-os terméksor egy AAM-es szállítási sorral.
     */
    const SETTING_VAT_KEY = 'invoice_vat_key';

    /** Ha nincs beállítva, ez az érték – a korábbi viselkedéssel azonos */
    const DEFAULT_VAT_KEY = '27';

    /**
     * A választható áfakulcsok.
     *
     * A szerver oldali ellenőrzés is ehhez a listához köt, hogy egy kézzel
     * összerakott POST se tudjon értelmezhetetlen kulcsot a számlára tenni:
     * a SzamlazzhuService is_numeric() vizsgálata a nem numerikus kulcsot
     * NÉMÁN 0% áfának veszi, tehát egy elgépelt "27%" 0 forint áfát adna.
     * Új kulcs igénye esetén: egy sor ide.
     */
    const VAT_KEYS = [
        '27' => '27% (általános)',
        '18' => '18%',
        '5' => '5%',
        '0' => '0%',
        'AAM' => 'AAM – alanyi adómentes',
        'TAM' => 'TAM – tárgyi adómentes',
    ];

    public static function fromOrder(WebshopOrder $order, array $options = []): array
    {
        $billingData = $order->getBillingDataArray();
        $items = self::items($order);

        return [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'customer_name' => $billingData['name'] ?? $order->customer_name,
            'customer_email' => $order->customer_email,
            'customer_tax_number' => $order->customer_tax_number,
            'billing_data' => [
                'zip' => $billingData['zip'] ?? null,
                'city' => $billingData['city'] ?? null,
                'address' => $billingData['address'] ?? null,
                'country' => $billingData['country'] ?? 'HU',
            ],
            // A webshop árai bruttók. A nettó/áfa bontást a számlázó provider végzi,
            // mert az áfakerekítés szolgáltatófüggő.
            'items' => $items,
            // A ténylegesen elküldött tételek összege, NEM a rendelés végösszege.
            // A kettő akkor tér el, ha az adminban kikapcsoltak egy extra tételt
            // (mert kézzel viszik fel) – ilyenkor a helyi bizonylat-nyilvántartásba
            // is az kerüljön, amiről a számla ténylegesen szól.
            'gross_total' => round(array_sum(array_column($items, 'gross_total')), 2),
            'currency' => $order->currency ?? 'HUF',
            'language' => $options['language'] ?? 'hu',
            'extra' => [
                'issue_date' => date('Y-m-d'),
                'fulfillment_date' => date('Y-m-d'),
                'payment_due_date' => date('Y-m-d', strtotime('+8 days')),
                'payment_method_text' => WebshopCommerceService::getPaymentMethodLabel($order->payment_method),
                'comment' => $order->note,
                'is_electronic' => true,
            ],
        ];
    }

    /**
     * A számla tételei: a terméksorok, majd az engedélyezett extra tételek.
     *
     * Ha minden extra tétel be van kapcsolva, a tételek összege PONTOSAN a
     * rendelés végösszege – erre a WebshopOrder::priceBreakdown() képlete a
     * garancia. Az 'egyeb' maradék is felkerül, hogy egy régi vagy kézzel
     * módosított rendelésnél se maradjon megmagyarázatlan különbözet.
     */
    protected static function items(WebshopOrder $order): array
    {
        // Minden sor ugyanazt az áfakulcsot kapja – lásd a SETTING_VAT_KEY
        // magyarázatát: a webshop adatmodelljében nincs soronkénti áfa.
        $afakulcs = self::vatKey();

        $tetelek = $order->items->map(function ($item) use ($afakulcs) {
            return [
                'name' => $item->product_name,
                'quantity' => $item->quantity,
                'unit' => 'db',
                'gross_unit_price' => (float) $item->unit_price,
                'gross_total' => (float) $item->total_price,
                'vat_key' => $afakulcs,
            ];
        })->toArray();

        $bontas = $order->priceBreakdown();

        foreach (self::EXTRA_LINES as $kulcs => $meta) {
            $osszeg = round(((float) ($bontas[$meta['forras']] ?? 0)) * $meta['elojel'], 2);

            if ($osszeg == 0.0 || !self::lineEnabled($kulcs)) {
                continue;
            }

            $tetelek[] = self::extraItem(self::lineName($kulcs, $meta['default_name'], $order), $osszeg, $afakulcs);
        }

        // Ami az ismert tételek után is marad (régi, cod_fee nélküli rendelés
        // furcsa kerekítése vagy kézi módosítás). Új rendelésnél mindig 0.
        if (round((float) $bontas['egyeb'], 2) != 0.0) {
            $tetelek[] = self::extraItem('Egyéb díj', round((float) $bontas['egyeb'], 2), $afakulcs);
        }

        return $tetelek;
    }

    /** Egy nem termék jellegű számlatétel (1 db, bruttó = egységár) */
    protected static function extraItem(string $nev, float $osszeg, string $afakulcs): array
    {
        return [
            'name' => $nev,
            'quantity' => 1,
            'unit' => 'db',
            'gross_unit_price' => $osszeg,
            'gross_total' => $osszeg,
            'vat_key' => $afakulcs,
        ];
    }

    /** Be van-e kapcsolva ez az extra tétel (alapértelmezés: IGEN) */
    protected static function lineEnabled(string $kulcs): bool
    {
        return WebshopSettingsService::getBool('invoice_line_' . $kulcs . '_enabled', true);
    }

    /**
     * A tétel neve.
     *
     * Ha az admin megadott saját nevet, azt PONTOSAN úgy használjuk – a mező
     * azt ígéri, ami a számlára kerül. A futárszolgálat / kuponkód nevét csak
     * az ALAPÉRTELMEZETT névhez fűzzük hozzá; különben egy "Szállítási költség"
     * névből "Szállítási költség (GLS futárszolgálat)" lenne, és egy FoxPost
     * rendelésnél a beírt "Szállítási díj (GLS)" mellé még egy zárójel kerülne.
     */
    protected static function lineName(string $kulcs, string $alapertelmezett, WebshopOrder $order): string
    {
        $nev = trim((string) WebshopSettingsService::get('invoice_line_' . $kulcs . '_name', ''));

        if ($nev !== '') {
            return $nev;
        }

        $nev = $alapertelmezett;

        if ($kulcs === 'shipping' && $order->shipping_method) {
            $mod = WebshopCommerceService::getShippingMethodLabel($order->shipping_method);

            if ($mod && mb_stripos($nev, $mod) === false) {
                $nev .= ' (' . $mod . ')';
            }
        }

        if ($kulcs === 'coupon' && $order->coupon_code) {
            $nev .= ' (' . $order->coupon_code . ')';
        }

        return $nev;
    }

    /**
     * A számla áfakulcsa.
     *
     * Csak a VAT_KEYS-ben szereplő értéket fogadjuk el. Egy ismeretlen kulcs
     * (elgépelés, régi beállítás) nem kerülhet ki a számlára: a
     * SzamlazzhuService a nem numerikus kulcsot némán 0% áfának veszi, tehát
     * egy "27%" éppen 0 forint áfát eredményezne.
     */
    public static function vatKey(): string
    {
        $ertek = trim((string) WebshopSettingsService::get(self::SETTING_VAT_KEY, ''));

        return array_key_exists($ertek, self::VAT_KEYS) ? $ertek : self::DEFAULT_VAT_KEY;
    }
}

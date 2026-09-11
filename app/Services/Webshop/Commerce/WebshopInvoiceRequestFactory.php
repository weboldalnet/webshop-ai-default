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
     * A 'source' a WebshopOrder::priceBreakdown() kulcsa, az 'sign' pedig azt
     * mondja meg, hozzáad vagy levon. Új tétel felvétele: egy sor ide.
     */
    const EXTRA_LINES = [
        'coupon' => [
            'default_name' => 'Kuponkedvezmény',
            'source' => 'discount',
            'sign' => -1,
            'admin_label' => 'Kuponkedvezmény',
        ],
        'shipping' => [
            'default_name' => 'Szállítási díj',
            'source' => 'shipping',
            'sign' => 1,
            'admin_label' => 'Szállítási díj',
        ],
        'cod' => [
            'default_name' => 'Utánvét díja',
            'source' => 'cod',
            'sign' => 1,
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
     * garancia. Az 'other' maradék is felkerül, hogy egy régi vagy kézzel
     * módosított rendelésnél se maradjon megmagyarázatlan különbözet.
     */
    protected static function items(WebshopOrder $order): array
    {
        // Minden sor ugyanazt az áfakulcsot kapja – lásd a SETTING_VAT_KEY
        // magyarázatát: a webshop adatmodelljében nincs soronkénti áfa.
        $vatKey = self::vatKey();

        $lines = $order->items->map(function ($item) use ($vatKey) {
            return [
                'name' => $item->product_name,
                'quantity' => $item->quantity,
                'unit' => 'db',
                'gross_unit_price' => (float) $item->unit_price,
                'gross_total' => (float) $item->total_price,
                'vat_key' => $vatKey,
            ];
        })->toArray();

        $breakdown = $order->priceBreakdown();

        foreach (self::EXTRA_LINES as $lineKey => $meta) {
            $amount = round(((float) ($breakdown[$meta['source']] ?? 0)) * $meta['sign'], 2);

            if ($amount == 0.0 || !self::lineEnabled($lineKey)) {
                continue;
            }

            $lines[] = self::extraItem(self::lineName($lineKey, $meta['default_name'], $order), $amount, $vatKey);
        }

        // Ami az ismert tételek után is marad (régi, cod_fee nélküli rendelés
        // furcsa kerekítése vagy kézi módosítás). Új rendelésnél mindig 0.
        if (round((float) $breakdown['other'], 2) != 0.0) {
            $lines[] = self::extraItem('Egyéb díj', round((float) $breakdown['other'], 2), $vatKey);
        }

        return $lines;
    }

    /** Egy nem termék jellegű számlatétel (1 db, bruttó = egységár) */
    protected static function extraItem(string $name, float $amount, string $vatKey): array
    {
        return [
            'name' => $name,
            'quantity' => 1,
            'unit' => 'db',
            'gross_unit_price' => $amount,
            'gross_total' => $amount,
            'vat_key' => $vatKey,
        ];
    }

    /** Be van-e kapcsolva ez az extra tétel (alapértelmezés: IGEN) */
    protected static function lineEnabled(string $lineKey): bool
    {
        return WebshopSettingsService::getBool('invoice_line_' . $lineKey . '_enabled', true);
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
    protected static function lineName(string $lineKey, string $defaultName, WebshopOrder $order): string
    {
        $name = trim((string) WebshopSettingsService::get('invoice_line_' . $lineKey . '_name', ''));

        if ($name !== '') {
            return $name;
        }

        $name = $defaultName;

        if ($lineKey === 'shipping' && $order->shipping_method) {
            $methodLabel = WebshopCommerceService::getShippingMethodLabel($order->shipping_method);

            if ($methodLabel && mb_stripos($name, $methodLabel) === false) {
                $name .= ' (' . $methodLabel . ')';
            }
        }

        if ($lineKey === 'coupon' && $order->coupon_code) {
            $name .= ' (' . $order->coupon_code . ')';
        }

        return $name;
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
        $value = trim((string) WebshopSettingsService::get(self::SETTING_VAT_KEY, ''));

        return array_key_exists($value, self::VAT_KEYS) ? $value : self::DEFAULT_VAT_KEY;
    }
}

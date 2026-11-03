<?php

/*
 * Közös felületi szövegek és formátumok: __('webshop::common.*').
 * Minden webshop-nyelvi fájlnak van angol párja (../en/). Hiányzó kulcsnál a Laravel az alapnyelvre (fallback_locale)
 * esik vissza, így egy félig fordított nyelv sem ír ki nyers kulcsot. A projekt felülírhatja:
 * lang/vendor/webshop/{locale}/common.php (vagy resources/lang/vendor/webshop/...).
 */
return [
    // Formátumok (WsFormat::money(), ::number(), ::date(), ::datetime())
    'money' => ':amount Ft',
    'unit_price' => ':amount Ft/:unit',
    'thousands_separator' => ' ',
    'decimal_separator' => ',',
    'date' => 'Y. m. d.',
    'datetime' => 'Y. m. d. H:i',

    // Mértékegységek (WebshopUnitPriceService::MEASURES label / base)
    'units' => [
        'pcs' => 'db',
    ],

    'pcs' => 'db',
    // darabszám mértékegységgel: trans_choice('webshop::common.quantity_pcs', $n, ['count' => $n])
    'quantity_pcs' => ':count db',
    'total' => 'Összesen',
    'product' => 'Termék',
    'quantity' => 'Mennyiség',
    'unit_price_label' => 'Egységár',
    'order_number' => 'Rendelésszám',
    'note' => 'Megjegyzés',
    'open' => 'Megnyitás',
    'close' => 'Bezárás',
    'back' => 'Vissza',
    'cancel' => 'Mégse',
    'save' => 'Mentés',
    'search' => 'Keresés',
    'loading' => 'Betöltés…',
    'yes' => 'Igen',
    'no' => 'Nem',
    'webshop' => 'Webshop',
];

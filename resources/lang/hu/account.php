<?php

/*
 * Rendeléseim a vásárlói fiókban (weboldalnet/commerce-users): __('webshop::account.*').
 * Nézetek: site/webshop/account/orders/index, site/webshop/account/orders/show;
 * üzenetek: Http/Controllers/Site/Webshop/WebshopAccountOrderController (újrarendelés).
 */
return [
    // Rendelések listája (orders/index)
    'orders' => [
        'title' => 'Rendeléseim',
        'empty' => 'Még nincs rendelésed.',
        'go_to_shop' => 'Irány a webshop',
        'quote_request' => 'ajánlatkérés',
        'more_items' => 'és még :count tétel',
        'details' => 'Részletek',
    ],

    // Egy rendelés (orders/show)
    'order' => [
        'title' => 'Rendelés #:number',
        'placed_at' => 'Leadva',
        'status' => 'Állapot',
        'payment' => 'Fizetés',
        'shipping' => 'Szállítás',
    ],

    // Újrarendelés (gomb, tipp és a kosárba tétel üzenetei)
    'reorder' => [
        'button' => 'Újrarendelés',
        'hint' => 'Az újrarendelés a termékeket a mai áron, a mai készlet szerint teszi a kosárba.',
        'none_available' => 'A rendelés termékei közül jelenleg egyik sem rendelhető, ezért nem került semmi a kosárba.',
        'added' => 'A #:number rendelés termékei a kosárba kerültek, a mai áron.',
        // :items = vesszővel elválasztott terméklista
        'partial' => 'Készlethiány miatt kevesebb került a kosárba: :items.',
        'unavailable' => 'Már nem rendelhető: :items.',
    ],
];

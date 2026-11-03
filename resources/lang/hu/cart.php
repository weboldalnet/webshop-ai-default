<?php

/*
 * Kosár: __('webshop::cart.*').
 * Nézet: site.webshop.cart.dropdown (a fejléc kosár-lenyílója). PHP: a WebshopCartController JSON-üzenetei és a
 * WebshopUserService::reorder() részben kosárba tett tétele.
 */
return [
    // cart/dropdown.blade.php
    'dropdown' => [
        'empty' => 'A kosár jelenleg üres.',
        'total' => 'Összesen:',
        'checkout' => 'Pénztár',
        'quote' => 'Ajánlatkérés',
    ],

    // WebshopCartController — kosárba tétel és mennyiség-módosítás (JSON message)
    'messages' => [
        'added' => 'A termék sikeresen kosárba került.',
        'out_of_stock' => 'Ebből a termékből jelenleg nincs több a raktáron.',
        // trans_choice(): angolul egyes/többes szám
        'added_partial' => 'Készlethiány miatt csak :count db került a kosárba.',
        'update_partial' => 'Készlethiány miatt csak :count db állítható be.',
    ],

    // WebshopUserService::reorder() — újrarendelésnél csak részben kosárba tett termék
    'reorder' => [
        'partial' => ':name (:added / :quantity db)',
    ],
];

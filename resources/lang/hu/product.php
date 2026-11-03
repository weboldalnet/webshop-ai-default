<?php

/*
 * Termék: __('webshop::product.*').
 * Nézetek: site/webshop/products/** (show, partials/legal-info, reviews, variations), modals/related-products,
 * modals/review-form, partials/product-price, valamint a kártyák/táblázat/összehasonlítás közös gombjai, készlet- és
 * árfeliratai; PHP: WebshopReviewController, WebshopReviewService.
 */
return [
    // Termékoldal (products/show)
    'show' => [
        'short_desc' => 'Rövid leírás',
        'properties' => 'Tulajdonságok',
        'buy' => 'Vásárlás',
        'related' => 'Kapcsolódó termékek',
        'similar' => 'Hasonló termékek',
        'gallery_prev' => 'Előző képek',
        'gallery_next' => 'További képek',
    ],

    // Termékoldal fülei (products/show)
    'tabs' => [
        'description' => 'Leírás',
        'legal_info' => 'Termékinformációk',
        'reviews' => 'Vélemények',
        'documents' => 'Dokumentumok',
        'document_name' => 'Dokumentum neve',
    ],

    // Gombok és mennyiség (products/show, categories/partials/product-card, product-table, compare, modals)
    'actions' => [
        'add_to_cart' => 'Kosárba',
        'add_to_cart_long' => 'Kosárba rakom',
        'unavailable' => 'Jelenleg nem rendelhető',
        'compare' => 'Összehasonlítás',
        'quantity' => 'Mennyiség:',
        'quantity_option' => ':count db',
    ],

    // Készlet-állapot (products/show, categories/partials/product-card)
    'stock' => [
        'out_of_stock' => 'Elfogyott',
        'last_items' => 'Utolsó :count db',
        'in_stock' => 'Raktáron',
    ],

    // Akciós ár (partials/product-price, search-results, categories/partials/product-table, compare/index)
    'price' => [
        'reference_title' => 'Az akciót megelőző 30 nap legalacsonyabb ára',
        'reference_note' => 'Áthúzott ár: az akciót megelőző 30 nap legalacsonyabb ára.',
    ],

    // Variációk doboz (products/partials/variations)
    'variations' => [
        'title' => 'Variációk',
    ],

    // Vélemények (products/partials/reviews, WebshopReviewService, WebshopReviewController)
    'reviews' => [
        'title' => 'Vásárlói vélemények',
        'write' => 'Vélemény írása',
        'verified_badge' => 'Ellenőrzött vásárló',
        'empty' => 'Még nem érkezett vélemény ehhez a termékhez. Legyen Ön az első!',
        'disclosure_verified_only' => 'Véleményt csak a terméket nálunk megvásárló vevők írhatnak: ezt a rendelésszám és az e-mail-cím alapján ellenőrizzük.',
        'disclosure_verified_marked' => 'Az „Ellenőrzött vásárló” jelölésű véleményeknél a véleményező a rendelésszámával igazolta, hogy a terméket nálunk vásárolta. A jelölés nélküli véleményeknél a vásárlást nem ellenőrizzük.',
        'disclosure_moderated' => 'A véleményeket közzététel előtt átnézzük.',
        'not_allowed' => 'A véleményezés nem engedélyezett.',
        'thanks_moderated' => 'Köszönjük a véleményét! Közzététel előtt átnézzük.',
        'thanks' => 'Köszönjük a véleményét.',
    ],

    // Véleményírás modál (modals/review-form)
    'review_form' => [
        'rating' => 'Értékelés',
        'name' => 'Az Ön neve',
        'review' => 'Vélemény',
        'purchase_proof' => 'Vásárlás igazolása',
        'optional' => '(nem kötelező)',
        'proof_required_help' => 'Véleményt csak a terméket nálunk megvásárló vevők írhatnak. Add meg a rendelésszámodat és a rendeléskor használt e-mail-címedet.',
        'proof_optional_help' => 'Ha a terméket nálunk vásároltad, add meg a rendelésszámodat és a rendeléskor használt e-mail-címedet – a véleményed így „Ellenőrzött vásárló” jelölést kap.',
        'email' => 'E-mail-cím',
        'submit' => 'Küldés',
    ],

    // A véleménybeküldés ellenőrzési üzenetei (WebshopReviewController)
    'review_validation' => [
        'order_number_required' => 'Véleményt csak vásárlás után írhatsz: add meg a rendelésszámodat.',
        'order_email_required' => 'Add meg a rendeléskor használt e-mail-címedet.',
        'order_number_required_with' => 'Az e-mail-cím mellé add meg a rendelésszámot is.',
        'order_email_required_with' => 'A rendelésszám mellé add meg a rendeléskor használt e-mail-címet is.',
        'order_email_email' => 'Érvényes e-mail-címet adj meg.',
        'order_not_found' => 'Ehhez a rendelésszámhoz és e-mail-címhez nem találtunk olyan rendelést, amelyben ez a termék szerepel.',
        'attribute_order_number' => 'rendelésszám',
        'attribute_order_email' => 'e-mail-cím',
    ],

    // Kosárba tétel utáni ajánló (modals/related-products)
    'added_modal' => [
        'title' => 'Termék a kosárba került!',
        'recommend' => 'Ezeket a termékeket is ajánljuk Önnek:',
        'other_products' => 'Egyéb termékek',
        'continue' => 'Tovább vásárolok',
        'send_quote' => 'Ajánlatkérés küldése',
        'place_order' => 'Rendelés leadása',
    ],
];

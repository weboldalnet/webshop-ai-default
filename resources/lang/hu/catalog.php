<?php

/*
 * Katalógus: __('webshop::catalog.*').
 * Nézetek: site/webshop/categories/** (index, show, partials), compare/** (index, dropdown), a fejléc részletei
 * (partials/header-search, search-results, cart-button, compare-button, category-menu, category-menu-mobile-node);
 * PHP: WebshopCategoryController, WebshopCompareController, WebshopCompareService, WebshopSeoService.
 */
return [
    // Webshop nyitóoldal / kategórialista (categories/index)
    'index' => [
        'title' => 'Válasszon kategóriát',
        'meta_title' => 'Webshop kategóriák',
        // link a teljes kínálatra (pl. a lábléc webshop-hasábjában)
        'all_products' => 'Összes termék',
    ],

    // Terméklista (categories/show, partials/product-list, product-table, product-card)
    'list' => [
        'view_card' => 'Kártyás nézet',
        'view_table' => 'Táblázatos nézet',
        'per_page' => ':count/oldal',
        'empty' => 'Nincs a megadott feltételeknek megfelelő termék a kategóriában.',
        'related_categories' => 'Kapcsolódó kategóriák',
        'variations' => ':count változat',
        'image' => 'Kép',
        'name' => 'Terméknév',
        'price' => 'Ár',
    ],

    // Rendezés (categories/show)
    'sort' => [
        'newest' => 'Legújabbak elöl',
        'price_asc' => 'Ár szerint növekvő',
        'price_desc' => 'Ár szerint csökkenő',
        'name_asc' => 'Név szerint (A-Z)',
        'name_desc' => 'Név szerint (Z-A)',
    ],

    // Szűrők (categories/partials/filter-sidebar, partials/category-menu)
    'filter' => [
        'title' => 'Szűrők',
        'min' => 'Min',
        'max' => 'Max',
        'clear' => 'Szűrők törlése',
        'mobile_button' => 'Szűrés',
    ],

    // Kategória-menü (partials/category-menu, category-menu-mobile-node)
    'menu' => [
        'categories' => 'Kategóriák',
        'subcategories' => ':name alkategóriái',
    ],

    // Fejléc lenyílói (partials/cart-button, compare-button)
    'header' => [
        'loading' => 'Betöltés...',
    ],

    // Fejléc-kereső (partials/header-search, search-results)
    'search' => [
        'placeholder' => 'Keresés (min. :count karakter)',
        'label' => 'Termékkeresés',
        'more' => 'További :count találat — pontosítsd a keresést.',
        'empty' => 'Nincs találat a keresésre.',
    ],

    // Összehasonlítás (compare/index, compare/dropdown, WebshopCompareController, WebshopCompareService)
    'compare' => [
        'page_title' => 'Termék összehasonlítás',
        'title' => 'Termékek összehasonlítása',
        'remove' => 'Törlés',
        'price' => 'Ár',
        'action' => 'Művelet',
        'empty' => 'Az összehasonlítási lista üres.',
        'min_items' => 'Legalább 2 termék szükséges az összehasonlításhoz.',
        'disabled' => 'Összehasonlítás funkció kikapcsolva.',
        'already_added' => 'A termék már szerepel az összehasonlításban.',
        'not_found' => 'A termék nem található.',
        'same_category_only' => 'Csak azonos kategóriában lévő termékeket lehet összehasonlítani.',
        'added' => 'Termék hozzáadva az összehasonlításhoz.',
    ],

    // A nyitóoldal tartalék meta leírása (WebshopSeoService)
    'seo' => [
        'home_brand' => ':brand webáruház.',
        'home_brand_categories' => ':brand webáruház: :categories.',
        'home_categories' => 'Webáruházunk kínálata: :categories.',
    ],
];

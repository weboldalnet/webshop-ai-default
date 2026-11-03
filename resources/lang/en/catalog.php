<?php

/*
 * Katalógus: __('webshop::catalog.*') — angol változat. A forrás a magyar fájl (../hu/catalog.php);
 * a hiányzó kulcsok arra esnek vissza.
 */
return [
    'index' => [
        'title' => 'Choose a category',
        'meta_title' => 'Shop categories',
        // link a teljes kínálatra (pl. a lábléc webshop-hasábjában)
        'all_products' => 'All products',
    ],

    'list' => [
        'view_card' => 'Grid view',
        'view_table' => 'Table view',
        'per_page' => ':count per page',
        'empty' => 'No products in this category match the selected filters.',
        'related_categories' => 'Related categories',
        'variations' => '{1} :count variant|[2,*] :count variants',
        'image' => 'Image',
        'name' => 'Product name',
        'price' => 'Price',
    ],

    'sort' => [
        'newest' => 'Newest first',
        'price_asc' => 'Price: low to high',
        'price_desc' => 'Price: high to low',
        'name_asc' => 'Name (A-Z)',
        'name_desc' => 'Name (Z-A)',
    ],

    'filter' => [
        'title' => 'Filters',
        'min' => 'Min',
        'max' => 'Max',
        'clear' => 'Clear filters',
        'mobile_button' => 'Filter',
    ],

    'menu' => [
        'categories' => 'Categories',
        'subcategories' => 'Subcategories of :name',
    ],

    'header' => [
        'loading' => 'Loading...',
    ],

    'search' => [
        'placeholder' => '{1} Search (min. :count char)|[2,*] Search (min. :count chars)',
        'label' => 'Product search',
        'more' => '{1} :count more result — please refine your search.|[2,*] :count more results — please refine your search.',
        'empty' => 'No results found.',
    ],

    'compare' => [
        'page_title' => 'Product comparison',
        'title' => 'Compare products',
        'remove' => 'Remove',
        'price' => 'Price',
        'action' => 'Action',
        'empty' => 'Your comparison list is empty.',
        'min_items' => 'Add at least 2 products to compare.',
        'disabled' => 'Product comparison is turned off.',
        'already_added' => 'This product is already in your comparison list.',
        'not_found' => 'Product not found.',
        'same_category_only' => 'Only products from the same category can be compared.',
        'added' => 'Product added to comparison.',
    ],

    'seo' => [
        'home_brand' => ':brand online shop.',
        'home_brand_categories' => ':brand online shop: :categories.',
        'home_categories' => 'Our range: :categories.',
    ],
];

<?php

/*
 * Rendeléseim a vásárlói fiókban (weboldalnet/commerce-users): __('webshop::account.*') — angol változat
 * (a forrás a ../hu/account.php). Nézetek: site/webshop/account/orders/index, site/webshop/account/orders/show;
 * üzenetek: Http/Controllers/Site/Webshop/WebshopAccountOrderController (újrarendelés).
 */
return [
    'orders' => [
        'title' => 'My orders',
        'empty' => 'You have no orders yet.',
        'go_to_shop' => 'Go to the shop',
        'quote_request' => 'quote request',
        'more_items' => '{1} and :count more item|[2,*] and :count more items',
        'details' => 'Details',
    ],

    'order' => [
        'title' => 'Order #:number',
        'placed_at' => 'Placed on',
        'status' => 'Status',
        'payment' => 'Payment',
        'shipping' => 'Shipping',
    ],

    'reorder' => [
        'button' => 'Reorder',
        'hint' => 'Reordering adds the products to your basket at today’s prices, based on current stock.',
        'none_available' => 'None of the products in this order can currently be ordered, so nothing has been added to your basket.',
        'added' => 'The products from order #:number have been added to your basket at today’s prices.',
        'partial' => 'Due to limited stock, fewer items were added to your basket: :items.',
        'unavailable' => 'No longer available: :items.',
    ],
];

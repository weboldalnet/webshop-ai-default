<?php

/*
 * Cart: __('webshop::cart.*'). The Hungarian file (../hu/) is the source; missing keys fall back to it.
 */
return [
    'dropdown' => [
        'empty' => 'Your cart is currently empty.',
        'total' => 'Total:',
        'checkout' => 'Checkout',
        'quote' => 'Request a quote',
    ],

    'messages' => [
        'added' => 'The product has been added to your cart.',
        'out_of_stock' => 'There is no more of this product in stock at the moment.',
        'added_partial' => '{1} Due to limited stock, only :count item was added to your cart.|[2,*] Due to limited stock, only :count items were added to your cart.',
        'update_partial' => 'Due to limited stock, the quantity can be set to :count at most.',
    ],

    'reorder' => [
        'partial' => ':name (:added / :quantity pcs)',
    ],
];

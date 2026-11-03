<?php

/*
 * Order labels: __('webshop::order.*'). The Hungarian file (../hu/) is the source; missing keys fall back to it.
 */
return [
    'type' => [
        'order' => 'Order',
        'quote' => 'Quote request',
    ],

    'status' => [
        'pending' => 'Pending',
        'processing' => 'Processing',
        'shipped' => 'Shipped',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        'failed' => 'Failed',
    ],

    'payment_status' => [
        'unpaid' => 'Unpaid',
        'pending' => 'Payment in progress',
        'paid' => 'Paid',
        'failed' => 'Payment failed',
        'cancelled' => 'Payment cancelled',
        'refunded' => 'Refunded',
    ],

    'invoice_status' => [
        'not_required' => 'Not required',
        'pending' => 'Invoicing in progress',
        'invoiced' => 'Invoiced',
        'failed' => 'Invoicing failed',
        'voided' => 'Invoice cancelled',
    ],

    'shipping_status' => [
        'not_required' => 'Not required',
        'pending' => 'Shipping pending',
        'prepared' => 'Prepared',
        'shipped' => 'Shipped',
        'delivered' => 'Delivered',
        'failed' => 'Shipping failed',
    ],

    'payment_method' => [
        'bank_transfer' => 'Bank transfer in advance',
        'cod' => 'Cash on delivery',
        'manual' => 'Manual payment',
        'on_site' => 'Pay on collection',
    ],

    'shipping_method' => [
        'manual' => 'Manual shipping',
        'pickup' => 'Personal collection',
        'flat_rate' => 'Our own courier',
        'mpl' => 'MPL courier',
        'parcel_locker' => 'Parcel locker',
    ],

    'shipping_group' => [
        'home_delivery' => 'Home delivery',
        'parcel_shop' => 'Parcel shop or parcel locker',
        'pickup' => 'Personal collection',
    ],

    'provider_name' => [
        'bank_transfer' => 'Bank transfer',
        'cod' => 'Cash on delivery',
        'manual_payment' => 'Manual payment',
        'barion_card' => 'Card payment (Barion)',
        'simplepay_card' => 'Card payment (SimplePay)',
        'stripe_card' => 'Card payment (Stripe)',
        'flat_rate' => 'Flat-rate shipping',
        'manual_shipping' => 'Manual shipping',
        'pickup' => 'Personal collection',
        'gls_courier' => 'GLS courier',
        'gls_parcel_shop' => 'GLS parcel shop',
        'foxpost_parcel_locker' => 'Foxpost parcel locker',
        'foxpost_home_delivery' => 'Foxpost home delivery',
    ],

    'payment_message' => [
        'on_site_offline' => 'Pay on collection – offline mode.',
        'commerce_unavailable' => 'Commerce-core unavailable, offline mode.',
        'start_failed' => 'Could not start the payment: :error',
    ],

    'invoice_line' => [
        'coupon' => 'Coupon discount',
        'shipping' => 'Shipping fee',
        'cod' => 'Cash on delivery fee',
        'other' => 'Other fee',
    ],
];

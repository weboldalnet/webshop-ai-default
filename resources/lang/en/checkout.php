<?php

/*
 * Checkout, thank-you page and payment result: __('webshop::checkout.*'). The Hungarian file (../hu/) is the
 * source; missing keys fall back to it.
 */
return [
    'back_to_shop' => 'Back to the shop',

    'page' => [
        'title' => 'Place order',
        'quote_title' => 'Request a quote',
        'heading' => 'Checkout',
    ],

    'account' => [
        'logged_in_as' => 'Signed in as:',
        'prefilled' => 'We have filled in your details from your account.',
        'my_account' => 'My account',
        'guest_prompt' => 'Already have an account? Sign in and we will fill in your details and addresses for you.',
        'login' => 'Sign in',
        'register' => 'Register',
        'save_new_addresses' => 'Also save my new addresses to my account',
        'addresses_autosave' => 'We will save the addresses of this order to your account, so they are filled in for your next purchase.',
    ],

    'cart' => [
        'title' => 'Your cart',
        'unit_price' => 'Unit price: :price',
        'remove' => 'Remove',
        'total' => 'Cart total:',
    ],

    'personal' => [
        'title' => 'Personal details',
        'name' => 'Name',
        'email' => 'Email address',
        'email_locked' => 'The email address of your account; the order is linked to it.',
        'phone' => 'Phone number',
        'company' => 'Company name',
        'tax_number' => 'Tax number',
    ],

    'address' => [
        'zip' => 'Postcode',
        'city' => 'City',
        'street' => 'Street address',
    ],

    'shipping_address' => [
        'title' => 'Shipping address',
        'saved' => 'My saved addresses',
    ],

    'billing' => [
        'title' => 'Billing details',
        'same_as_shipping' => 'Same as shipping address',
        'saved' => 'My saved billing addresses',
        'name' => 'Billing name',
    ],

    'shipping' => [
        'title' => 'Shipping method',
        'free' => 'free',
    ],

    'gls' => [
        'title' => 'Choose a GLS parcel point',
        'selected_placeholder' => 'Choose a GLS parcel point on the map.',
        'required' => 'For parcel point delivery, please choose a pickup point.',
    ],

    'foxpost' => [
        'title' => 'Choose a FoxPost parcel locker',
        'search_placeholder' => 'Search by postcode, city or name…',
        'selected_placeholder' => 'Choose a parcel locker from the list.',
        'required' => 'For parcel locker delivery, please choose a parcel locker.',
        'no_results' => 'No results.',
        'load_error' => 'The parcel lockers could not be loaded.',
    ],

    'payment' => [
        'title' => 'Payment options',
        'on_site' => 'Pay on collection',
        'none' => 'No payment method is available at the moment.',
    ],

    'note' => [
        'placeholder' => 'If there is anything you would like to tell us about your order, you can write it here.',
    ],

    'coupon' => [
        'title' => 'Redeem a coupon',
        'code' => 'Coupon code',
        'apply' => 'Apply',
        'modal_title' => 'Coupon applied',
        'savings' => 'You save:',
        'code_required' => 'Please enter the coupon code.',
        'activated' => 'Applied: :name — :amount discount.',
        'not_applicable' => 'This coupon cannot be applied.',
        'check_failed' => 'We could not check the coupon. Please try again.',
        'disabled' => 'Coupon redemption is not available at the moment.',
    ],

    'summary' => [
        'title' => 'Summary',
        'cart_value' => 'Cart value',
        'shipping_fee' => 'Shipping fee',
        'free' => 'Free',
        'cod_fee' => 'Cash on delivery fee',
        'coupon_discount' => 'Coupon discount',
        'total_due' => 'Total to pay',
        // JOGI SZÖVEG — az angol változatot jogásznak ellenőriznie kell
        'tos_default' => 'I accept the General Terms and Conditions',
        // JOGI SZÖVEG — az angol változatot jogásznak ellenőriznie kell
        'privacy_default' => 'I accept the Privacy Notice',
        // JOGI SZÖVEG — az angol változatot jogásznak ellenőriznie kell
        'privacy_notice' => 'You can read about how we process your data in connection with your order in our :link.',
        // JOGI SZÖVEG — az angol változatot jogásznak ellenőriznie kell
        'privacy_notice_link' => 'Privacy Notice',
        // JOGI SZÖVEG — az angol változatot jogásznak ellenőriznie kell
        // (45/2014. (II. 26.) Korm. rendelet; Directive 2011/83/EU Art. 8(2): "order with obligation to pay")
        'submit_order' => 'Order with obligation to pay',
        'submit_quote' => 'Send quote request',
    ],

    'success' => [
        'title' => 'Order details',
        'heading' => 'Thank you for your order!',
        'order_id' => 'Your order number:',
        'contact_soon' => 'We will contact you shortly using the contact details you provided.',
    ],

    'order' => [
        'overview_title' => 'Order overview',
        'date' => 'Order date',
        'status' => 'Order status',
        'payment_status' => 'Payment status',
        'shipping_status' => 'Shipping status',
        'payment_method' => 'Payment method',
        'shipping_method' => 'Shipping method',
        'tracking_number' => 'Tracking number',
        'invoice_number' => 'Invoice number',
        'contact_title' => 'Contact and billing',
        'name' => 'Name',
        'email' => 'Email',
        'phone' => 'Phone',
        'company' => 'Company name',
        'tax_number' => 'Tax number',
        'billing_address' => 'Billing address',
        'delivery_title' => 'Delivery',
        'pickup_point' => 'Pickup point',
        'shipping_address' => 'Shipping address',
        'personal_pickup' => 'Personal collection – we will send you the details in the confirmation email.',
        'items_title' => 'Ordered products',
        'line_total' => 'Line total',
        'items_total' => 'Products total',
        'shipping_fee' => 'Shipping fee',
        'free' => 'Free',
        'coupon_discount' => 'Coupon discount',
        'cod_fee' => 'Cash on delivery fee',
        'other_fees' => 'Other fees',
        'your_note' => 'Your note',
        'qa_default' => 'Questionnaire',
        'no_answer' => 'no answer',
    ],

    'payment_result' => [
        'title' => 'Payment result',
        'paid_title' => 'Payment successful!',
        'paid_text' => 'The payment for your order :order was successful.',
        'paid_text_short' => 'The payment was successful.',
        'paid_at' => 'Payment date: :date',
        'failed_title' => 'Payment failed',
        'failed_text' => 'Unfortunately, the payment for your order :order was not successful.',
        'failed_hint' => 'Please try again or choose another payment method.',
        'cancelled_title' => 'Payment cancelled',
        'cancelled_text' => 'The payment for your order :order was interrupted.',
        'cancelled_hint' => 'Your order has been kept; you can retry the payment at any time.',
        'pending_title' => 'Payment in progress',
        'pending_text' => 'The payment for your order :order is being processed.',
        'pending_checking' => 'Checking the payment status…',
        'pending_timeout' => 'The payment confirmation is still in progress. We will notify you by email as soon as it arrives – you can also refresh this page.',
        'other_title' => 'Order status',
        'order_number' => 'Order number: :number',
        'payment_status' => 'Payment status: :status',
        'order_details' => 'Order details',
        'retry' => 'Retry payment',
        'summary_title' => 'Order summary',
        'summary_order_number' => 'Order number:',
        'summary_order_status' => 'Order status:',
        'summary_payment_status' => 'Payment status:',
        'summary_payment_method' => 'Payment method:',
        'summary_shipping_method' => 'Shipping method:',
        'summary_amount' => 'Amount:',
    ],

    'messages' => [
        'cart_empty' => 'Your cart is empty.',
        'login_required' => 'Please sign in to your account or register to make a purchase. Your cart will be kept.',
        // JOGI SZÖVEG — az angol változatot jogásznak ellenőriznie kell
        'accept_tos' => 'Please accept the General Terms and Conditions.',
        // JOGI SZÖVEG — az angol változatot jogásznak ellenőriznie kell
        'accept_privacy' => 'Please accept the Privacy Notice.',
        'coupon_invalidated' => 'The coupon is no longer valid: :message Please check your order and try again.',
        'stock_changed' => 'Some products in your cart have run out in the meantime: :items. Please adjust the quantities.',
        'stock_left' => ':name (only :count left)',
        'stock_sold_out' => ':name (sold out)',
        'payment_start_failed' => 'Your order has been created, but the payment could not be started. Please try again.',
        'order_placed' => 'Your order has been placed.',
        'order_failed' => 'An error occurred while processing your order. Please try again.',
        'retry_not_allowed' => 'The payment for this order cannot be retried.',
        'retry_started' => 'Payment started.',
        'retry_failed' => 'The payment could not be restarted.',
        'retry_error' => 'An error occurred while restarting the payment.',
    ],
];

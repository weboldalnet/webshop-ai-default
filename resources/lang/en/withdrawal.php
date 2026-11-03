<?php

/*
 * Elállás a vásárlástól („elállási funkció”): __('webshop::withdrawal.*') — angol változat (a forrás a ../hu/withdrawal.php).
 * Nézetek: site/webshop/withdrawals/create, site/webshop/modals/withdrawal, site/webshop/partials/withdrawal-link;
 * üzenetek: Http/Controllers/Site/Webshop/WebshopWithdrawalController.
 */
return [
    'title' => 'Withdrawal from purchase',

    // JOGI SZÖVEG — az angol változatot jogásznak ellenőriznie kell
    // (elállási jog a 45/2014. (II. 26.) Korm. rendelet szerint: 14 nap, indoklás nélkül; visszaigazolás tartós adathordozón)
    'form' => [
        'success_title' => 'Notice of withdrawal sent',
        'products_title' => 'Which products would you like to withdraw from?',
        'products_hint' => 'By default, you are withdrawing from the entire order. If you only want to withdraw from some of the products, change the quantity – 0 means you are keeping that product.',
        'ordered_quantity' => 'Quantity ordered: :count',
        'unit_price' => ':price each',
        'already_withdrawn' => 'Already withdrawn: :count',
        'not_withdrawable' => 'Cannot be withdrawn',
        'optional' => '(optional)',
        'reason_placeholder' => 'If you wish, you can tell us why you are withdrawing – this is not a condition of withdrawal.',
        'reason_help' => 'You may withdraw from the purchase without giving any reason within 14 days of receiving the goods.',
        'submit' => 'Confirm withdrawal',
        'confirmation_info' => 'We will send an email confirming receipt of your notice – including its content, date and time – to :email.',
    ],

    'lookup' => [
        'intro' => 'Enter your order number and the email address you used when ordering, and in the next step you can choose which products you would like to withdraw from. You can find your order number in the confirmation email.',
        'order_number_placeholder' => 'e.g. 20260901-H8B0',
        'email' => 'Email address',
        'email_placeholder' => 'the address you used when ordering',
        'next' => 'Continue',
        'too_many_attempts' => 'Too many attempts. Please wait a minute.',
        'check_failed' => 'We could not verify the details.',
        'unexpected_error' => 'An unexpected error occurred. Please try again.',
    ],

    'messages' => [
        'not_found' => 'We could not find an order with this order number and email address. Please check the details in your confirmation email.',
        'nothing_left' => 'A notice of withdrawal has already been received for every item in this order.',
        'invalid_link' => 'The withdrawal link has expired or is invalid. Please use the “:link” link in the footer to enter your order number and email address again.',
        'quantity_max' => 'The quantity entered is more than you can still withdraw from.',
        'select_one' => 'You must select at least one product.',
        'success' => 'We have received your notice of withdrawal. We have sent you a confirmation by email.',
    ],

    'attributes' => [
        'order_number' => 'order number',
        'email' => 'email address',
        'reason' => 'note',
    ],
];

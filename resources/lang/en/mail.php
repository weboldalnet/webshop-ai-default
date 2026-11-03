<?php

/*
 * Vásárlói levelek: __('webshop::mail.*') — angol változat (a forrás a ../hu/mail.php).
 * Nézetek: mail/order-mail, mail/withdrawal-mail, mail/order-cancelled-mail; tárgyak: app/Mail/Webshop*Mail.php.
 */
return [
    'greeting' => 'Dear :name,',
    'regards' => 'Kind regards,',
    'qty_short' => 'Qty',

    'order' => [
        'subject' => 'Order confirmation',
        'subject_quote' => 'Confirmation of your quote request',
        'details_title' => 'Order details (#:number)',
        'qty_short' => 'Qty',
        'items_total' => 'Products total:',
        'shipping_fee' => 'Shipping fee:',
        'free' => 'Free',
        'coupon_discount' => 'Coupon discount:',
        'coupon_discount_code' => 'Coupon discount (:code):',
        'cod_fee' => 'Cash on delivery fee:',
        'other_fees' => 'Other fees:',
        'grand_total' => 'Grand total:',
        'personal_title' => 'Personal details',
        'name' => 'Name:',
        'email' => 'Email:',
        'phone' => 'Phone:',
        'company' => 'Company:',
        'tax_number' => 'Tax number:',
        'billing_title' => 'Billing details',
        'shipping_title' => 'Shipping details',
        'note' => 'Order note:',
        'terms_attachment' => 'General-Terms-and-Conditions.pdf',
    ],

    // JOGI SZÖVEG — az angol változatot jogásznak ellenőriznie kell
    // (tájékoztatás a 45/2014. (II. 26.) Korm. rendelet szerint)
    'legal' => [
        'title' => 'Important information',
        'terms_label' => 'Terms and conditions:',
        'terms_text' => 'your order is subject to the General Terms and Conditions of our online shop.',
        'terms_attached' => 'We have attached the terms and conditions to this email as a PDF.',
        'terms_online' => 'You can also view them online:',
        'terms_link' => 'General Terms and Conditions',
        'withdrawal_label' => 'Right of withdrawal:',
        'withdrawal_text' => 'you may withdraw from the purchase without giving any reason within 14 days of receiving the goods.',
        'withdrawal_online' => 'The easiest way to do so is here:',
        'withdrawal_link' => 'Withdrawal from purchase',
        'withdrawal_statement' => 'You may also inform us of your decision to withdraw by any unequivocal statement, or by completing and sending the model withdrawal form below.',
        'privacy_label' => 'Data protection:',
        'privacy_text' => 'you can read about how we process your personal data in our :link.',
        'privacy_link' => 'Privacy Policy',
    ],

    // JOGI SZÖVEG — az angol változatot jogásznak ellenőriznie kell
    'seller' => [
        'title' => 'Seller details',
        'seat' => 'Registered office:',
        'registration_number' => 'Company registration number / registration number:',
        'tax_number' => 'Tax number:',
        'email' => 'Email:',
        'phone' => 'Phone:',
    ],

    // JOGI SZÖVEG — az angol változatot jogásznak ellenőriznie kell
    // (45/2014. (II. 26.) Korm. rendelet 2. melléklet; angol megfelelője: 2011/83/EU irányelv I. melléklet B. rész,
    // „Model withdrawal form”. Az irányelv „I/We”, „goods/service” választható alakjaiból az egyes számú,
    // termékre vonatkozó alak maradt, ahogy a magyar szövegben is.)
    'withdrawal_form' => [
        'title' => 'Model withdrawal form',
        'instruction' => '(complete and return this form only if you wish to withdraw from the contract)',
        'recipient' => 'To: :recipient',
        'declaration' => 'I hereby give notice that I withdraw from my contract of sale of the following goods:',
        'order_number' => 'order number: :number',
        'dates' => 'Ordered on / received on: :ordered_at / ………………………',
        'consumer_name' => 'Name of consumer: :name',
        'consumer_address' => 'Address of consumer: :address',
        'signature' => 'Signature of consumer (only if this form is notified on paper): ………………………',
        'date' => 'Date: ………………………',
    ],

    // JOGI SZÖVEG — az angol változatot jogásznak ellenőriznie kell
    // (az elállás visszaigazolása tartós adathordozón)
    'withdrawal' => [
        'subject' => 'Confirmation of your notice of withdrawal – :number',
        'subject_shop' => 'New notice of withdrawal – :number',
        'shop_title' => 'New notice of withdrawal received',
        'shop_intro' => 'A notice of withdrawal has been received for order :number from :name (:email).',
        'customer_intro' => 'We confirm that we have received your notice of withdrawal for order :number. We will contact you shortly regarding the return of the goods and the refund.',
        'received_at' => 'Notice received on',
        'submitted_by' => 'Notice given by',
        'items_title' => 'Products covered by the withdrawal',
        'total' => 'Total value concerned:',
        'is_full' => 'The notice applies to the :scope.',
        'full_order' => 'entire order',
        'note' => 'Note:',
        'contact' => 'If you have any questions, please write to us at :email.',
    ],

    'cancelled' => [
        'subject' => 'Your order has been cancelled – :number',
        'intro' => 'We have cancelled your order :number because the online payment was not received :within of the order being placed. You have not been charged anything for this order, and the reserved products have been released.',
        'within_hours' => '{1} within :count hour|[2,*] within :count hours',
        'placed_at' => 'Order placed',
        'items_title' => 'Items in the cancelled order',
        'reorder_text' => 'If you would still like to buy these products, please place your order again in our shop:',
        'shop_button' => 'Go to the shop',
        'contact' => 'If you did start the payment, or if you have any questions, please write to us at :email.',
    ],
];

<?php

/*
 * Termék: __('webshop::product.*') — angol változat. A forrás a magyar fájl (../hu/product.php);
 * a hiányzó kulcsok arra esnek vissza.
 */
return [
    'show' => [
        'short_desc' => 'Short description',
        'properties' => 'Specifications',
        'buy' => 'Buy',
        'related' => 'Related products',
        'similar' => 'Similar products',
        'gallery_prev' => 'Previous images',
        'gallery_next' => 'More images',
    ],

    'tabs' => [
        'description' => 'Description',
        'legal_info' => 'Product information',
        'reviews' => 'Reviews',
        'documents' => 'Documents',
        'document_name' => 'Document name',
    ],

    'actions' => [
        'add_to_cart' => 'Add to cart',
        'add_to_cart_long' => 'Add to cart',
        'unavailable' => 'Currently unavailable',
        'compare' => 'Compare',
        'quantity' => 'Quantity:',
        'quantity_option' => '{1} :count pc|[2,*] :count pcs',
    ],

    'stock' => [
        'out_of_stock' => 'Out of stock',
        'last_items' => 'Only :count left',
        'in_stock' => 'In stock',
    ],

    // JOGI SZÖVEG — az angol változatot jogásznak ellenőriznie kell (Omnibusz-ár, 98/6/EK irányelv 6a. cikk)
    'price' => [
        'reference_title' => 'Lowest price in the 30 days before the discount',
        'reference_note' => 'Struck-through price: the lowest price in the 30 days before the discount.',
    ],

    'variations' => [
        'title' => 'Variants',
    ],

    'reviews' => [
        'title' => 'Customer reviews',
        'write' => 'Write a review',
        'verified_badge' => 'Verified buyer',
        'empty' => 'No reviews for this product yet. Be the first to write one!',
        // JOGI SZÖVEG — az angol változatot jogásznak ellenőriznie kell (a vélemények eredetéről szóló tájékoztatás, Fttv., Omnibusz)
        'disclosure_verified_only' => 'Only customers who bought the product from us can write a review: we check this against the order number and e-mail address.',
        // JOGI SZÖVEG — az angol változatot jogásznak ellenőriznie kell
        'disclosure_verified_marked' => 'For reviews marked “Verified buyer”, the reviewer has confirmed with their order number that they bought the product from us. For reviews without this mark, we do not check whether a purchase was made.',
        // JOGI SZÖVEG — az angol változatot jogásznak ellenőriznie kell
        'disclosure_moderated' => 'We check reviews before they are published.',
        'not_allowed' => 'Reviews are not enabled.',
        'thanks_moderated' => 'Thank you for your review! We will check it before it is published.',
        'thanks' => 'Thank you for your review.',
    ],

    'review_form' => [
        'rating' => 'Rating',
        'name' => 'Your name',
        'review' => 'Review',
        'purchase_proof' => 'Proof of purchase',
        'optional' => '(optional)',
        'proof_required_help' => 'Only customers who bought the product from us can write a review. Please enter your order number and the e-mail address you used when ordering.',
        'proof_optional_help' => 'If you bought the product from us, enter your order number and the e-mail address you used when ordering – your review will then be marked “Verified buyer”.',
        'email' => 'E-mail address',
        'submit' => 'Send',
    ],

    'review_validation' => [
        'order_number_required' => 'You can only write a review after a purchase: please enter your order number.',
        'order_email_required' => 'Please enter the e-mail address you used when ordering.',
        'order_number_required_with' => 'Please enter the order number as well as the e-mail address.',
        'order_email_required_with' => 'Please enter the e-mail address you used when ordering as well as the order number.',
        'order_email_email' => 'Please enter a valid e-mail address.',
        'order_not_found' => 'We could not find an order containing this product for this order number and e-mail address.',
        'attribute_order_number' => 'order number',
        'attribute_order_email' => 'e-mail address',
    ],

    'added_modal' => [
        'title' => 'Product added to your cart!',
        'recommend' => 'You may also like these products:',
        'other_products' => 'Other products',
        'continue' => 'Continue shopping',
        'send_quote' => 'Send quote request',
        'place_order' => 'Place order',
    ],
];

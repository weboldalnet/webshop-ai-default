<?php

return [
    /*
     | A pénztár kérdőívéhez használt PROJEKT-modell. A csomag nem drótozza be
     | az App\ névteret; ha az osztály nincs meg, a kérdőív-funkció kikapcsolva marad.
     */
    'contact_qa_model' => \App\Models\ContactQa::class,

    'primary_image' => [
        'width' => 600,
        'height' => 450,
    ],
    'thumbnail' => [
        'width' => 120,
        'height' => null,
    ],
    'category_list_image_crop_sizes' => [
        1 => ['width' => 400, 'height' => 300],
        2 => ['width' => 620, 'height' => 300],
        3 => ['width' => 960, 'height' => 300],
        4 => ['width' => 1280, 'height' => 300],
    ],

    /*
     | Félbehagyott online fizetésű rendelések: ennyi óra után lemondja őket a webshop:cancel-unpaid-orders (a készlet
     | visszakerül, a vásárló levelet kap). 0 = kikapcsolva.
     */
    'unpaid_order_cancel_hours' => (int) env('WEBSHOP_UNPAID_ORDER_CANCEL_HOURS', 24),
    // ennél régebbi rendelésről nem megy levél (pl. az első futáskor egy régóta működő boltban) — csendben lemondja
    'unpaid_order_mail_max_age_hours' => (int) env('WEBSHOP_UNPAID_ORDER_MAIL_MAX_AGE', 72),

    /*
     | Ütemezett feladatok (a Laravel ütemezője futtatja; a szerveren a szokásos `schedule:run` cron kell). Érték:
     | metódusnév (daily, hourly …) vagy cron-kifejezés; üresen / false-szal kikapcsolva.
     */
    'schedule' => [
        // Google / Facebook merchant feed újragenerálása (csak bekapcsolt merchant feed beállításnál fut)
        'merchant_feeds' => env('WEBSHOP_MERCHANT_FEED_SCHEDULE', '15 3 * * *'),
        // félbehagyott online fizetésű rendelések lemondása
        'cancel_unpaid_orders' => env('WEBSHOP_UNPAID_ORDER_SCHEDULE', 'hourly'),
    ],
];

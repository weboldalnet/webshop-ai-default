<?php

/*
 * Rendelés-feliratok: __('webshop::order.*').
 * WebshopOrder (*_label accessorok, statusLabels() …), WebshopCommerceService (fizetési / szállítási módok és csoportok,
 * fizetésindítási üzenetek), ManualCourierShippingProvider (MPL), WebshopInvoiceRequestFactory (számlatételek).
 * A vásárlói oldalon, a levelekben és az adminban is ezek látszanak (az adott felület nyelvén).
 */
return [
    'type' => [
        'order' => 'Rendelés',
        'quote' => 'Ajánlatkérés',
    ],

    'status' => [
        'pending' => 'Függőben',
        'processing' => 'Feldolgozás alatt',
        'shipped' => 'Kiszállítva',
        'completed' => 'Teljesítve',
        'cancelled' => 'Törölve',
        'failed' => 'Sikertelen',
    ],

    'payment_status' => [
        'unpaid' => 'Fizetetlen',
        'pending' => 'Fizetés folyamatban',
        'paid' => 'Fizetve',
        'failed' => 'Fizetés sikertelen',
        'cancelled' => 'Fizetés törölve',
        'refunded' => 'Visszatérítve',
    ],

    'invoice_status' => [
        'not_required' => 'Nem szükséges',
        'pending' => 'Számlázás folyamatban',
        'invoiced' => 'Számlázva',
        'failed' => 'Számlázás sikertelen',
        'voided' => 'Számla érvénytelenítve',
    ],

    'shipping_status' => [
        'not_required' => 'Nem szükséges',
        'pending' => 'Szállítás függőben',
        'prepared' => 'Előkészítve',
        'shipped' => 'Kiszállítva',
        'delivered' => 'Kézbesítve',
        'failed' => 'Szállítás sikertelen',
    ],

    // A webshop saját / tartalék fizetési módjai (WebshopCommerceService)
    'payment_method' => [
        'bank_transfer' => 'Előre utalással',
        'cod' => 'Utánvét',
        'manual' => 'Manuális fizetés',
        'on_site' => 'Fizetés a helyszínen',
    ],

    // A webshop saját / tartalék szállítási módjai (WebshopCommerceService, ManualCourierShippingProvider)
    'shipping_method' => [
        'manual' => 'Manuális szállítás',
        'pickup' => 'Személyes átvétel',
        'flat_rate' => 'Saját futárszolgálat',
        'mpl' => 'MPL futárszolgálat',
        'parcel_locker' => 'Csomagpont automata',
    ],

    // A pénztár szállítási csoportjai (WebshopCommerceService::SHIPPING_GROUPS)
    'shipping_group' => [
        'home_delivery' => 'Házhoz szállítás',
        'parcel_shop' => 'Csomagpont, csomagautomata',
        'pickup' => 'Személyes átvétel',
    ],

    /*
     * A commerce-core és a fizetési / szállítási csomagok beégetett (magyar) alapnevei. Csak a betűre egyező név
     * fordítódik (WebshopCommerceService::translateProviderName()) — az adminban átírt elnevezés nem; ezért a magyar
     * szöveg itt NEM változtatható, csak együtt a csomagbeli névvel.
     */
    'provider_name' => [
        'bank_transfer' => 'Banki átutalás',
        'cod' => 'Utánvét',
        'manual_payment' => 'Manuális fizetés',
        'barion_card' => 'Barion bankkártyás fizetés',
        'simplepay_card' => 'SimplePay bankkártyás fizetés',
        'stripe_card' => 'Bankkártyás fizetés (Stripe)',
        'flat_rate' => 'Fix szállítási díj',
        'manual_shipping' => 'Manuális szállítás',
        'pickup' => 'Személyes átvétel',
        'gls_courier' => 'GLS futárszolgálat',
        'gls_parcel_shop' => 'GLS csomagpont',
        'foxpost_parcel_locker' => 'Foxpost csomagautomata',
        'foxpost_home_delivery' => 'Foxpost házhoz szállítás',
    ],

    // WebshopCommerceService::processOrderPayment() — a fizetés újraindításakor a vásárló is látja
    'payment_message' => [
        'on_site_offline' => 'Fizetés a helyszínen – offline mód.',
        'commerce_unavailable' => 'Commerce-core nem elérhető, offline mód.',
        'start_failed' => 'Fizetési folyamat indítása sikertelen: :error',
    ],

    // Számlatételek alapértelmezett megnevezése (WebshopInvoiceRequestFactory; adminban felülírható)
    'invoice_line' => [
        'coupon' => 'Kuponkedvezmény',
        'shipping' => 'Szállítási díj',
        'cod' => 'Utánvét díja',
        'other' => 'Egyéb díj',
    ],
];

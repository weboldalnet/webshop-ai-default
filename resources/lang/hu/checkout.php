<?php

/*
 * Pénztár, köszönőoldal és fizetési eredmény: __('webshop::checkout.*').
 * Nézetek: site.webshop.checkout.index, .success, .payment-result és a checkout/partials/* (a rendelés adatlap
 * részeit — order-addresses, order-items, order-extras — a fiók Rendeléseim oldala is használja).
 * PHP: WebshopCheckoutController és WebshopCouponService vásárlónak szóló üzenetei.
 */
return [
    'back_to_shop' => 'Vissza a webshophoz',

    // checkout/index.blade.php — oldalcím és fejléc
    'page' => [
        'title' => 'Rendelés leadása',
        'quote_title' => 'Ajánlatkérés',
        'heading' => 'Pénztár',
    ],

    // partials/account-notice.blade.php és save-addresses.blade.php — vásárlói fiók (commerce-users)
    'account' => [
        'logged_in_as' => 'Bejelentkezve:',
        'prefilled' => 'Az adataidat a fiókodból töltöttük ki.',
        'my_account' => 'Fiókom',
        'guest_prompt' => 'Van már fiókod? Jelentkezz be, és kitöltjük helyetted az adataidat és a címeidet.',
        'login' => 'Bejelentkezés',
        'register' => 'Regisztráció',
        'save_new_addresses' => 'Az új címeimet is mentsd el a fiókomba',
        'addresses_autosave' => 'A rendelés címeit elmentjük a fiókodba, a következő vásárlásnál már kitöltve várnak.',
    ],

    // partials/cart-items.blade.php
    'cart' => [
        'title' => 'A kosár tartalma',
        'unit_price' => 'Egységár: :price',
        'remove' => 'Törlés',
        'total' => 'Kosár összeg:',
    ],

    // partials/personal-data.blade.php
    'personal' => [
        'title' => 'Személyes adatok',
        'name' => 'Név',
        'email' => 'E-mail cím',
        'email_locked' => 'A fiókod e-mail-címe; a rendelés ide kötődik.',
        'phone' => 'Telefonszám',
        'company' => 'Cégnév',
        'tax_number' => 'Adószám',
    ],

    // partials/shipping-address.blade.php és billing.blade.php — közös címmezők
    'address' => [
        'zip' => 'Irsz.',
        'city' => 'Város',
        'street' => 'Utca, házszám',
    ],

    // partials/shipping-address.blade.php
    'shipping_address' => [
        'title' => 'Szállítási cím',
        'saved' => 'Mentett címeim',
    ],

    // partials/billing.blade.php
    'billing' => [
        'title' => 'Számlázási adatok',
        'same_as_shipping' => 'Megegyezik a szállítással',
        'saved' => 'Mentett számlázási címeim',
        'name' => 'Számlázási név',
    ],

    // partials/shipping.blade.php
    'shipping' => [
        'title' => 'Szállítási mód',
        'free' => 'ingyenes',
    ],

    // partials/gls-parcel-shop.blade.php
    'gls' => [
        'title' => 'GLS csomagpont választása',
        'selected_placeholder' => 'Válassz GLS csomagpontot a térképen!',
        'required' => 'A csomagpontos szállításhoz ki kell választani egy átvevőpontot.',
    ],

    // partials/foxpost-apm.blade.php
    'foxpost' => [
        'title' => 'FoxPost automata választása',
        'search_placeholder' => 'Keresés irányítószámra, városra vagy névre…',
        'selected_placeholder' => 'Válassz automatát a listából!',
        'required' => 'A csomagautomatás szállításhoz ki kell választani egy automatát.',
        'no_results' => 'Nincs találat.',
        'load_error' => 'Az automaták betöltése nem sikerült.',
    ],

    // partials/payment.blade.php
    'payment' => [
        'title' => 'Fizetési lehetőségek',
        'on_site' => 'Fizetés a helyszínen',
        'none' => 'Jelenleg nincs elérhető fizetési mód.',
    ],

    // partials/note.blade.php
    'note' => [
        'placeholder' => 'Ha bármit szeretnél jelezni a rendeléssel kapcsolatban, ide írhatod.',
    ],

    // partials/coupon.blade.php, scripts.blade.php (kuponbeváltás), WebshopCouponService
    'coupon' => [
        'title' => 'Kuponbeváltás',
        'code' => 'Kuponkód',
        'apply' => 'Aktiválás',
        'modal_title' => 'Sikeres kuponaktiválás',
        'savings' => 'Megtakarítás:',
        'code_required' => 'Add meg a kuponkódot.',
        'activated' => 'Aktiválva: :name — :amount kedvezmény.',
        'not_applicable' => 'A kupon nem aktiválható.',
        'check_failed' => 'A kupon ellenőrzése nem sikerült. Kérjük, próbáld újra.',
        'disabled' => 'A kuponbeváltás jelenleg nem elérhető.',
    ],

    // partials/summary.blade.php és scripts.blade.php (élő összesítő)
    'summary' => [
        'title' => 'Összegzés',
        'cart_value' => 'Kosár érték',
        'shipping_fee' => 'Szállítási díj',
        'free' => 'Ingyenes',
        'cod_fee' => 'Utánvét költsége',
        'coupon_discount' => 'Kuponkedvezmény',
        'total_due' => 'Összesen fizetendő',
        // Jogi szöveg: az admin felirat-mező üresen hagyásakor ez a tartalék
        'tos_default' => 'Elfogadom az Általános Szerződési Feltételeket',
        'privacy_default' => 'Elfogadom az Adatvédelmi tájékoztatót',
        // :link = <a> az Adatkezelési tájékoztatóra (privacy_notice_link a felirata)
        'privacy_notice' => 'A rendeléssel kapcsolatos adatkezelésről az :link olvashatsz.',
        'privacy_notice_link' => 'Adatkezelési tájékoztatóban',
        // Jogszabályi felirat (45/2014. (II. 26.) Korm. rendelet) — ne írd át „Rendelés leadása”-ra vagy hasonlóra
        'submit_order' => 'Megrendelés fizetési kötelezettséggel',
        'submit_quote' => 'Ajánlatkérés elküldése',
    ],

    // checkout/success.blade.php
    'success' => [
        'title' => 'Rendelés részletei',
        'heading' => 'Köszönjük a rendelését!',
        'order_id' => 'A rendelés azonosítója:',
        'contact_soon' => 'Hamarosan felvesszük Önnel a kapcsolatot a megadott elérhetőségeken.',
    ],

    // partials/order-overview, order-addresses, order-items, order-extras — a rendelés adatlapja
    'order' => [
        'overview_title' => 'Rendelés áttekintése',
        'date' => 'Rendelés dátuma',
        'status' => 'Rendelés állapota',
        'payment_status' => 'Fizetés állapota',
        'shipping_status' => 'Szállítás állapota',
        'payment_method' => 'Fizetési mód',
        'shipping_method' => 'Szállítási mód',
        'tracking_number' => 'Csomagazonosító',
        'invoice_number' => 'Számlaszám',
        'contact_title' => 'Elérhetőség és számlázás',
        'name' => 'Név',
        'email' => 'E-mail',
        'phone' => 'Telefon',
        'company' => 'Cégnév',
        'tax_number' => 'Adószám',
        'billing_address' => 'Számlázási cím',
        'delivery_title' => 'Átvétel',
        'pickup_point' => 'Átvevőpont',
        'shipping_address' => 'Szállítási cím',
        'personal_pickup' => 'Személyes átvétel – a részleteket a visszaigazoló e-mailben küldjük.',
        'items_title' => 'Rendelt termékek',
        'line_total' => 'Sorösszeg',
        'items_total' => 'Termékek összesen',
        'shipping_fee' => 'Szállítási díj',
        'free' => 'Ingyenes',
        'coupon_discount' => 'Kuponkedvezmény',
        'cod_fee' => 'Utánvét díja',
        'other_fees' => 'Egyéb díjak',
        'your_note' => 'Megjegyzésed',
        'qa_default' => 'Kérdőív',
        'no_answer' => 'nincs válasz',
    ],

    // checkout/payment-result.blade.php (a JS-ből cserélt szövegek is); :order = <strong>#rendelésszám</strong>
    'payment_result' => [
        'title' => 'Fizetési eredmény',
        'paid_title' => 'Sikeres fizetés!',
        'paid_text' => 'A :order rendelésed fizetése sikeresen megtörtént.',
        'paid_text_short' => 'A fizetés sikeresen megtörtént.',
        'paid_at' => 'Fizetés dátuma: :date',
        'failed_title' => 'Sikertelen fizetés',
        'failed_text' => 'A :order rendelésed fizetése sajnos nem sikerült.',
        'failed_hint' => 'Kérjük, próbálja meg újra, vagy válasszon másik fizetési módot.',
        'cancelled_title' => 'Fizetés megszakítva',
        'cancelled_text' => 'A :order rendelésed fizetése megszakadt.',
        'cancelled_hint' => 'A rendelés megmarad, fizetését bármikor megismételheti.',
        'pending_title' => 'Fizetés folyamatban',
        'pending_text' => 'A :order rendelésed fizetése feldolgozás alatt áll.',
        'pending_checking' => 'Ellenőrizzük a fizetés állapotát…',
        'pending_timeout' => 'A fizetés visszaigazolása még folyamatban van. Amint megérkezik, e-mailben értesítünk – az oldalt is frissítheted.',
        'other_title' => 'Rendelés állapota',
        'order_number' => 'Rendelés száma: :number',
        'payment_status' => 'Fizetési státusz: :status',
        'order_details' => 'Rendelés részletei',
        'retry' => 'Fizetés újrapróbálása',
        'summary_title' => 'Rendelés összefoglalója',
        'summary_order_number' => 'Rendelésszám:',
        'summary_order_status' => 'Rendelés státusz:',
        'summary_payment_status' => 'Fizetési státusz:',
        'summary_payment_method' => 'Fizetési mód:',
        'summary_shipping_method' => 'Szállítási mód:',
        'summary_amount' => 'Összeg:',
    ],

    // WebshopCheckoutController — flash- és hibaüzenetek
    'messages' => [
        'cart_empty' => 'A kosár üres.',
        'login_required' => 'A vásárláshoz jelentkezz be a fiókodba, vagy regisztrálj. A kosarad tartalma megmarad.',
        'accept_tos' => 'Kérjük, fogadja el az Általános Szerződési Feltételeket.',
        'accept_privacy' => 'Kérjük, fogadja el az Adatvédelmi tájékoztatót.',
        // :message = a kupon modul indoklása (pontra végződő mondat)
        'coupon_invalidated' => 'A kupon időközben érvénytelenné vált: :message Kérjük, ellenőrizd a rendelést és próbáld újra.',
        // :items = vesszővel elválasztott stock_left / stock_sold_out tételek
        'stock_changed' => 'A kosaradban lévő termékek közül időközben elfogyott: :items. Kérjük, módosítsd a mennyiséget.',
        'stock_left' => ':name (már csak :count db)',
        'stock_sold_out' => ':name (elfogyott)',
        'payment_start_failed' => 'A rendelés létrejött, de a fizetés indítása sikertelen volt. Kérjük, próbálja újra.',
        'order_placed' => 'Rendelés sikeresen leadva.',
        'order_failed' => 'Hiba történt a rendelés feldolgozása során. Kérjük, próbálja újra.',
        'retry_not_allowed' => 'Ez a rendelés nem próbálható újra.',
        'retry_started' => 'Fizetés elindítva.',
        'retry_failed' => 'Fizetés újraindítása sikertelen.',
        'retry_error' => 'Hiba történt a fizetés újraindításakor.',
    ],
];

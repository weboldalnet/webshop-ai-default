<?php

/*
 * Vásárlói levelek: __('webshop::mail.*').
 * Nézetek: mail/order-mail, mail/withdrawal-mail, mail/order-cancelled-mail; tárgyak: app/Mail/Webshop*Mail.php.
 * A levél nyelvét a küldő állítja be (Mail::to(...)->locale(...)); a tárgy és a szöveg a build() alatt dől el.
 */
return [
    // Közös
    'greeting' => 'Kedves :name!',
    'regards' => 'Üdvözlettel,',
    'qty_short' => 'Menny.',

    // Rendelés / árajánlatkérés visszaigazolása (order-mail)
    'order' => [
        'subject' => 'Rendelés visszaigazolása',
        'subject_quote' => 'Árajánlatkérés visszaigazolása',
        'details_title' => 'Rendelés adatai (#:number)',
        'qty_short' => 'Mh.',
        'items_total' => 'Termékek összesen:',
        'shipping_fee' => 'Szállítási díj:',
        'free' => 'Ingyenes',
        'coupon_discount' => 'Kuponkedvezmény:',
        'coupon_discount_code' => 'Kuponkedvezmény (:code):',
        'cod_fee' => 'Utánvét díja:',
        'other_fees' => 'Egyéb díjak:',
        'grand_total' => 'Végösszeg:',
        'personal_title' => 'Személyes adatok',
        'name' => 'Név:',
        'email' => 'Email:',
        'phone' => 'Tel:',
        'company' => 'Cég:',
        'tax_number' => 'Adószám:',
        'billing_title' => 'Számlázási adatok',
        'shipping_title' => 'Szállítási adatok',
        'note' => 'Megjegyzés a rendeléshez:',
        'terms_attachment' => 'Altalanos-Szerzodesi-Feltetelek.pdf',
    ],

    // Jogi tájékoztatás a rendelési levélben (45/2014. (II. 26.) Korm. rendelet)
    'legal' => [
        'title' => 'Fontos tudnivalók',
        'terms_label' => 'Szerződési feltételek:',
        'terms_text' => 'a rendelésre webáruházunk Általános Szerződési Feltételei vonatkoznak.',
        'terms_attached' => 'A feltételeket PDF-ben csatoltuk ehhez a levélhez.',
        'terms_online' => 'Online is megtekintheti:',
        'terms_link' => 'Általános Szerződési Feltételek',
        'withdrawal_label' => 'Elállási jog:',
        'withdrawal_text' => 'a termék átvételétől számított 14 napon belül indoklás nélkül elállhat a vásárlástól.',
        'withdrawal_online' => 'Ezt a legegyszerűbben itt teheti meg:',
        'withdrawal_link' => 'Elállás a vásárlástól',
        'withdrawal_statement' => 'Elállási szándékát bármilyen egyértelmű nyilatkozattal, vagy az alábbi nyilatkozatminta kitöltésével és elküldésével is jelezheti.',
        'privacy_label' => 'Adatkezelés:',
        // :link = az „Adatvédelmi tájékoztatóban” link (privacy_link)
        'privacy_text' => 'személyes adatai kezeléséről az :link olvashat.',
        'privacy_link' => 'Adatvédelmi tájékoztatóban',
    ],

    // Az eladó adatai a rendelési levélben
    'seller' => [
        'title' => 'Az eladó adatai',
        'seat' => 'Székhely:',
        'registration_number' => 'Cégjegyzékszám / nyilvántartási szám:',
        'tax_number' => 'Adószám:',
        'email' => 'E-mail:',
        'phone' => 'Telefon:',
    ],

    // Elállási nyilatkozatminta (45/2014. (II. 26.) Korm. rendelet 2. melléklet), a rendelés adataival előtöltve
    'withdrawal_form' => [
        'title' => 'Elállási nyilatkozatminta',
        'instruction' => '(csak a szerződéstől való elállási szándék esetén töltse ki és juttassa vissza)',
        'recipient' => 'Címzett: :recipient',
        'declaration' => 'Alulírott kijelentem, hogy gyakorlom elállási jogomat az alábbi termék(ek) adásvételére irányuló szerződés tekintetében:',
        'order_number' => 'rendelésszám: :number',
        'dates' => 'Szerződéskötés időpontja / átvétel időpontja: :ordered_at / ………………………',
        'consumer_name' => 'A fogyasztó neve: :name',
        'consumer_address' => 'A fogyasztó címe: :address',
        'signature' => 'A fogyasztó aláírása (kizárólag papíron tett nyilatkozat esetén): ………………………',
        'date' => 'Kelt: ………………………',
    ],

    // Elállási nyilatkozat visszaigazolása (withdrawal-mail) — a vásárlónak és a webshop címére
    'withdrawal' => [
        'subject' => 'Elállási nyilatkozat visszaigazolása – :number',
        'subject_shop' => 'Új elállási nyilatkozat – :number',
        'shop_title' => 'Új elállási nyilatkozat érkezett',
        'shop_intro' => 'A(z) :number számú rendeléshez elállási nyilatkozat érkezett :name (:email) részéről.',
        'customer_intro' => 'Visszaigazoljuk, hogy elállási nyilatkozatát megkaptuk a(z) :number számú rendeléséhez. Hamarosan felvesszük Önnel a kapcsolatot a termék visszajuttatásával és a visszatérítéssel kapcsolatban.',
        'received_at' => 'A nyilatkozat beérkezése',
        'submitted_by' => 'A nyilatkozatot tette',
        'items_title' => 'Az elállással érintett termékek',
        'total' => 'Érintett érték összesen:',
        // :scope = félkövér „teljes rendelésre” (full_order)
        'is_full' => 'A nyilatkozat a :scope vonatkozik.',
        'full_order' => 'teljes rendelésre',
        'note' => 'Megjegyzés:',
        'contact' => 'Kérdés esetén írjon nekünk a(z) :email címre.',
    ],

    // Lemondás ki nem fizetett online rendelésnél (order-cancelled-mail, webshop:cancel-unpaid-orders)
    'cancelled' => [
        'subject' => 'Rendelését lemondtuk – :number',
        // :within = within_hours (pl. „24 órán belül”)
        'intro' => 'A(z) :number számú rendelését lemondtuk, mert a leadása után :within nem érkezett meg az online fizetés. A rendelés miatt nem terheltünk meg semmit, a lefoglalt termékeket pedig felszabadítottuk.',
        'within_hours' => ':count órán belül',
        'placed_at' => 'A rendelés leadása',
        'items_title' => 'A lemondott rendelés tételei',
        'reorder_text' => 'Ha továbbra is szeretné megvásárolni a termékeket, adja le újra a rendelést a webshopban:',
        'shop_button' => 'Tovább a webshopba',
        'contact' => 'Ha a fizetést mégis elindította, vagy kérdése van, írjon nekünk a(z) :email címre.',
    ],
];

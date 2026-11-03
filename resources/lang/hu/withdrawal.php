<?php

/*
 * Elállás a vásárlástól („elállási funkció”): __('webshop::withdrawal.*').
 * Nézetek: site/webshop/withdrawals/create, site/webshop/modals/withdrawal, site/webshop/partials/withdrawal-link;
 * üzenetek: Http/Controllers/Site/Webshop/WebshopWithdrawalController.
 */
return [
    // Oldalcím, modal-cím és a lábléc linkje
    'title' => 'Elállás a vásárlástól',

    // Elállási űrlap (withdrawals/create)
    'form' => [
        'success_title' => 'Elállási nyilatkozat elküldve',
        'products_title' => 'Mely termékektől szeretnél elállni?',
        'products_hint' => 'Alapértelmezetten a teljes rendeléstől állsz el. Ha csak egyes termékektől szeretnél, állítsd át a mennyiséget – a 0 azt jelenti, hogy azt a terméket megtartod.',
        'ordered_quantity' => 'Rendelt mennyiség: :count db',
        'unit_price' => ':price / db',
        'already_withdrawn' => 'Már elállt belőle: :count db',
        'not_withdrawable' => 'Nem elállható',
        'optional' => '(nem kötelező)',
        'reason_placeholder' => 'Ha szeretnéd, megírhatod, miért állsz el – ez nem feltétele az elállásnak.',
        'reason_help' => 'A vásárlástól a termék átvételétől számított 14 napon belül indoklás nélkül elállhatsz.',
        'submit' => 'Elállás megerősítése',
        // :email = a vásárló e-mail-címe, félkövéren
        'confirmation_info' => 'A nyilatkozat beérkezéséről – annak tartalmával, dátumával és időpontjával – visszaigazoló e-mailt küldünk a(z) :email címre.',
    ],

    // Rendelés azonosítása a láblécből nyíló modalban (modals/withdrawal)
    'lookup' => [
        'intro' => 'Add meg a rendelésszámodat és a rendeléskor használt e-mail-címedet, és a következő lépésben kiválaszthatod, mely termékektől szeretnél elállni. A rendelésszámot a visszaigazoló e-mailben találod.',
        'order_number_placeholder' => 'pl. 20260901-H8B0',
        'email' => 'E-mail-cím',
        'email_placeholder' => 'a rendeléskor megadott cím',
        'next' => 'Tovább',
        'too_many_attempts' => 'Túl sok próbálkozás. Kérjük, várj egy percet.',
        'check_failed' => 'Nem sikerült ellenőrizni az adatokat.',
        'unexpected_error' => 'Váratlan hiba történt. Kérjük, próbáld újra.',
    ],

    // Vásárlónak visszaadott üzenetek (WebshopWithdrawalController)
    'messages' => [
        'not_found' => 'Ehhez a rendelésszámhoz és e-mail-címhez nem találtunk rendelést. Kérjük, ellenőrizd az adatokat a visszaigazoló e-mailben.',
        'nothing_left' => 'Ehhez a rendeléshez már minden tételre beérkezett elállási nyilatkozat.',
        // :link = a lábléc linkjének felirata (withdrawal.title)
        'invalid_link' => 'Az elállási link lejárt vagy érvénytelen. Kérjük, a lábléc „:link” linkjén add meg újra a rendelésszámodat és az e-mail-címedet.',
        'quantity_max' => 'A megadott mennyiség több, mint amennyitől még el lehet állni.',
        'select_one' => 'Legalább egy terméket ki kell választani.',
        'success' => 'Elállási nyilatkozatodat megkaptuk. A visszaigazolást elküldtük e-mailben.',
    ],

    // Validációs mezőnevek
    'attributes' => [
        'order_number' => 'rendelésszám',
        'email' => 'e-mail-cím',
        'reason' => 'megjegyzés',
    ],
];

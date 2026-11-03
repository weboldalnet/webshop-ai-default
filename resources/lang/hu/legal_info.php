<?php

/*
 * Kötelező termékinformációk és eladói adatok feliratai: __('webshop::legal_info.*').
 * WebshopProductLegalInfoService (profiles(), pictogramLabels(), nutritionLabels(), sectionsFor() — a termékoldal
 * „Termékinformációk” füle és az admin termékszerkesztő), WebshopLegalInfoService (sellerFieldLabels()).
 */
return [
    // Termékkörök (profilok)
    'profile' => [
        'general' => 'Általános termékbiztonság (GPSR)',
        'food' => 'Élelmiszer',
        'chemical' => 'Veszélyes keverék (CLP)',
        'energy' => 'Energiacímke',
        'textile' => 'Textiltermék',
    ],

    'profile_description' => [
        'general' => 'Nem élelmiszer jellegű fogyasztói termékekhez (2023/988/EU rendelet).',
        'food' => 'Élelmiszerekhez, italokhoz, étrend-kiegészítőkhöz (1169/2011/EU rendelet).',
        'chemical' => 'Tisztítószerekhez, festékekhez, ragasztókhoz és más veszélyesként címkézett keverékekhez (1272/2008/EK rendelet).',
        'energy' => 'Energiacímkés termékekhez: háztartási gépek, világítás, TV, kijelzők stb. (2017/1369/EU rendelet).',
        'textile' => 'Ruházathoz, lakástextilhez (1007/2011/EU rendelet).',
    ],

    // Mezők megnevezése (a termékoldalon és a szerkesztőben)
    'field' => [
        'manufacturer' => 'Gyártó',
        'eu_responsible' => 'Uniós felelős személy',
        'identifier' => 'Termékazonosító',
        'warnings' => 'Figyelmeztetések, biztonsági információk',
        'ingredients' => 'Összetevők',
        'allergens' => 'Allergének',
        'nutrition' => 'Tápérték 100 g / 100 ml termékben',
        'storage' => 'Tárolási és felhasználási feltételek',
        'origin' => 'Származási ország / eredet helye',
        'alcohol' => 'Alkoholtartalom',
        'operator' => 'Élelmiszer-vállalkozó',
        'signal_word' => 'Figyelmeztetés',
        'pictograms' => 'Veszélyt jelző piktogramok',
        'hazard_statements' => 'Figyelmeztető mondatok (H)',
        'precautionary_statements' => 'Óvintézkedésre vonatkozó mondatok (P)',
        'ufi' => 'UFI-kód',
        'energy_class' => 'Energiahatékonysági osztály',
        'label_url' => 'Energiacímke (kép vagy PDF linkje)',
        'sheet_url' => 'Termékinformációs adatlap linkje',
        'fibre_composition' => 'Szálösszetétel',
        'care' => 'Kezelési útmutató',
    ],

    // Kitöltési súgó a szerkesztőben (az input alatt)
    'hint' => [
        'manufacturer' => 'Név (vagy bejegyzett márkanév), postai cím és elektronikus elérhetőség (e-mail vagy weboldal).',
        'eu_responsible' => 'Csak ha a gyártó nem az EU-ban van letelepedve: a felelős gazdasági szereplő neve, postai és elektronikus címe.',
        'identifier' => 'Típus, modell, tétel- vagy sorozatszám – ami alapján a termék azonosítható.',
        'warnings' => 'A terméken/csomagoláson szereplő figyelmeztetések magyarul (pl. „3 éven aluli gyermekek számára nem alkalmas”).',
        'ingredients' => 'Az allergéneket emeld ki dupla csillaggal, pl. **búzaliszt**, **tej** – a termékoldalon félkövérrel jelennek meg.',
        'allergens' => 'Pl. „Glutént, tejet tartalmaz. Nyomokban diót tartalmazhat.”',
        'alcohol' => '1,2 térfogatszázalék felett kötelező, pl. „12,5% vol”.',
        'operator' => 'Annak a neve és címe, akinek a nevén a termék forgalomba kerül.',
        'hazard_statements' => 'A címkén szereplő H-mondatok, pl. „H319 Súlyos szemirritációt okoz.”',
        'sheet_url' => 'Pl. az EPREL-adatbázis termékoldala.',
        'fibre_composition' => 'Pl. „95% pamut, 5% elasztán”.',
    ],

    // CLP-figyelmeztetés: a DB-ben a magyar szó van tárolva (Figyelem / Veszély), ez csak a kiírt felirat
    'signal_word' => [
        'warning' => 'Figyelem',
        'danger' => 'Veszély',
    ],

    // GHS-piktogramok (kulcs: a kód kisbetűvel)
    'pictogram' => [
        'ghs01' => 'Robbanóanyag',
        'ghs02' => 'Tűzveszélyes',
        'ghs03' => 'Oxidáló',
        'ghs04' => 'Nyomás alatti gáz',
        'ghs05' => 'Maró hatású',
        'ghs06' => 'Akut toxicitás',
        'ghs07' => 'Ártalmas, irritáló',
        'ghs08' => 'Egészségkárosító',
        'ghs09' => 'Környezetre veszélyes',
    ],

    // Tápérték-táblázat sorai
    'nutrition' => [
        'energy' => 'Energia (kJ / kcal)',
        'fat' => 'Zsír',
        'saturates' => '– amelyből telített zsírsavak',
        'carbohydrate' => 'Szénhidrát',
        'sugars' => '– amelyből cukrok',
        'fibre' => 'Rost',
        'protein' => 'Fehérje',
        'salt' => 'Só',
    ],

    'net_quantity' => 'Nettó mennyiség',

    // Az eladó adatai (WebshopLegalInfoService::SELLER_FIELDS)
    'seller' => [
        'name' => 'Eladó neve (cégnév / egyéni vállalkozó neve)',
        'seat' => 'Székhely',
        'registration_number' => 'Cégjegyzékszám / nyilvántartási szám',
        'tax_number' => 'Adószám',
        'email' => 'E-mail-cím',
        'phone' => 'Telefonszám',
    ],
];

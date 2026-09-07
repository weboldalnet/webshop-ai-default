# weboldalnet/webshop-ai-default

Moduláris webshop package Laravel 11+ alkalmazásokhoz. Teljes körű termék-, kategória-, kosár-, checkout- és rendeléskezelés, beleértve a `weboldalnet/commerce-core` integrációt fizetési, számlázási és szállítási folyamatokhoz.

---

## Telepítés

```bash
composer require weboldalnet/webshop-ai-default
```

### Migráció futtatása

```bash
php artisan migrate
```

### Config publikálása

```bash
php artisan vendor:publish --tag=webshop-config
```

### Assetek publikálása

```bash
php artisan vendor:publish --tag=webshop-assets
```

### A befogadó projekt composer.json-jába felveendő

Friss telepítéskor ezeknek le kell futniuk. Vedd fel őket a projekt
`composer.json`-jában a `scripts.post-install-cmd` tömb végére – így minden új
környezet magától a helyes állapotba kerül:

```json
"post-install-cmd": [
    "@php artisan storage:link",

    "@php artisan vendor:publish --tag=webshop-config",
    "@php artisan vendor:publish --tag=webshop-assets",

    "@php artisan vendor:publish --tag=commerce-core-config",
    "@php artisan vendor:publish --tag=commerce-barion-config",
    "@php artisan vendor:publish --tag=commerce-szamlazzhu-config",
    "@php artisan vendor:publish --tag=commerce-gls-config",
    "@php artisan vendor:publish --tag=commerce-simplepay-config",
    "@php artisan vendor:publish --tag=commerce-foxpost-config",

    "@php artisan vendor:publish --tag=commerce-gls-public"
]
```

A `post-install-cmd` csak `composer install`-kor fut, `composer update`-kor nem –
pontosan ez kell, mert ezek friss telepítési lépések.

**Amit szándékosan NEM publikálunk:**

| Elem | Miért nem |
|---|---|
| `webshop-views` (és a `webshop-all`) | A csomag nézetei `addLocation()`-nel töltődnek be a vendorból, publish nélkül is működnek. Publikálva a projekt `resources/views`-jébe másolódnának, és onnantól **árnyékolnák a csomagot**: minden későbbi csomagfrissítés láthatatlan maradna, amíg valaki `--force`-szal újra nem publikál – az viszont a projekt testreszabásait írná felül. Ha egy projekten testre kell szabni egy nézetet, elég azt az **egy** fájlt bemásolni a projekt `resources/views`-jébe. |
| migrációk (`webshop-database`, `commerce-*-migrations`) | Minden érintett csomag `loadMigrationsFrom()`-mal tölti be őket, elég a `php artisan migrate`. |

**A `--force` veszélyei:**

- `webshop-assets --force` felülírja a projekt `public/packages/webshop/site/css/webshop-site.css` fájlját. Ha a projektben ez frissebb (pl. kézzel fordított SCSS-ből), a force **visszalépés**. Force előtt mindig fordítsd újra a csomag SCSS-ét, és hasonlítsd össze a két fájlt.
- `*-views --force` a projekt testreszabásait törli. Csak tételes összehasonlítás után.

Normál (force nélküli) publish biztonságos: a már létező fájlokat kihagyja.

---

## Rendelés modell – Commerce mezők

A `WebshopOrder` modell az alábbi commerce mezőkkel bővül:

| Mező | Leírás |
|------|--------|
| `payment_method` | Kiválasztott fizetési mód kódja (pl. `cod`, `bank_transfer`) |
| `payment_status` | Fizetési státusz: `unpaid`, `pending`, `paid`, `failed`, `cancelled`, `refunded` |
| `invoice_status` | Számla státusz: `not_required`, `pending`, `invoiced`, `failed`, `voided` |
| `shipping_method` | Kiválasztott szállítási mód kódja (pl. `flat_rate`, `pickup`) |
| `shipping_status` | Szállítási státusz: `not_required`, `pending`, `prepared`, `shipped`, `delivered`, `failed` |
| `commerce_payment_transaction_id` | Kapcsolódó `PaymentTransaction` ID |
| `commerce_invoice_document_id` | Kapcsolódó `InvoiceDocument` ID |
| `commerce_shipment_id` | Kapcsolódó `Shipment` ID |
| `paid_at` | Fizetés időpontja |
| `invoiced_at` | Számlázás időpontja |
| `shipped_at` | Szállítás időpontja |

### Státusz helper metódusok

```php
$order->isPaid();
$order->isPaymentPending();
$order->isPaymentFailed();
$order->isPaymentCancelled();
$order->isPaymentRetryable();
$order->markPaid($transactionId);
$order->markPaymentFailed();
$order->markPaymentCancelled();
$order->isInvoiced();
$order->markInvoiced($documentId);
$order->isShipped();
$order->markShipped($shipmentId);
```

---

## Checkout flow

### Offline fizetés (pl. utánvét, banki átutalás)

1. Vásárló kiválasztja a fizetési és szállítási módot
2. Rendelés létrejön `payment_status = unpaid`
3. `CommerceOrderProcessor` meghívódik → offline provider → nincs redirect
4. Siker oldal megjelenik

### Online fizetés

1. Vásárló kiválasztja az online payment providert
2. Rendelés létrejön `payment_status = pending`
3. `CommerceOrderProcessor` visszaad egy `redirectUrl`-t
4. Felhasználó a fizetési oldalra kerül
5. Visszatérés után `payment-result` oldal mutatja az aktuális státuszt (nem query param alapján, hanem DB-ből)
6. `PaymentSucceeded` event → `HandlePaymentSucceeded` listener → `payment_status = paid`

### Ajánlatkérési mód

- `type = quote`
- Nincs payment/invoice/shipping indítás
- Siker oldal

---

## Payment event listener működés

A service provider automatikusan regisztrálja az event listenereket, ha a commerce-core telepítve van:

| Event | Listener | Hatás |
|-------|----------|-------|
| `PaymentSucceeded` | `HandlePaymentSucceeded` | `payment_status = paid`, `paid_at = now()`, opcionálisan `CreateInvoiceForOrder` job |
| `PaymentFailed` | `HandlePaymentFailed` | `payment_status = failed` |
| `PaymentCancelled` | `HandlePaymentCancelled` | `payment_status = cancelled` |

Minden listener idempotens: ha a rendelés már `paid` státuszban van, nem dolgozza fel újra.

---

## Admin rendelés – commerce blokkok

Az admin szerkesztő oldalon az alábbi blokkok jelennek meg automatikusan, ha van payment/shipping adat:

- **Fizetés blokk**: fizetési mód, státusz, tranzakció ID, manuális "fizetve" gomb
- **Számla blokk**: számla státusz, bizonylat ID, "Számla készítése" gomb
- **Szállítás blokk**: szállítási mód, státusz, szállítmány ID, "Szállítmány létrehozása" gomb

---

## Invoice és Shipping job-ok

```php
// Számlakészítés indítása kézzel:
dispatch(new CreateInvoiceForOrder($order->id));

// Szállítmány indítása:
dispatch(new CreateShipmentForOrder($order->id));
```

A job-ok 3x próbálkoznak, és sikertelenség esetén `failed` státuszra állítják a rendelést.

---

## Hogyan lehet új commerce providert hozzáadni?

Külön package-ként, a `weboldalnet/commerce-core` interface-eket implementálva:

```php
use Weboldalnet\CommerceCore\Contracts\PaymentProviderInterface;

class MyPaymentProvider implements PaymentProviderInterface {
    public function getCode(): string { return 'my_provider'; }
    public function getName(): string { return 'My Payment'; }
    public function isOnline(): bool { return true; }
    // ...
}
```

Regisztráció a `commerce-core.php` config-ban:

```php
'payments' => [
    'providers' => [
        'my_provider' => [
            'driver' => MyPaymentProvider::class,
            'enabled' => true,
        ],
    ],
],
```

A webshop-ai-default automatikusan megtalálja és megjeleníti az engedélyezett providereket a checkout oldalon.

---

## Biztonsági megjegyzések

- **Összeget soha ne kliensből fogadj el** – mindig szerver oldalon számolódik újra a `WebshopCheckoutService`-ben
- **Callback / return alapján ne legyen automatikusan paid** – csak validált `PaymentSucceeded` event alapján
- **Idempotens event kezelés** – duplikált event nem okoz duplikált számlát/emailt
- **Érzékeny adatokat ne tárolj** – a provider payload logolása a commerce-core `ProviderLogger` feladata
- **Invoice/shipping hibák ne rontsák el a sikeres fizetést** – `payment_status = paid` marad, `invoice_status = failed` külön kezelt
- **Számlázás és szállítás queue-ban fusson** – a job-ok `ShouldQueue`-t implementálnak

---

## Commerce-core kapcsolódás

```php
// Elérhető fizetési módok lekérése:
WebshopCommerceService::getAvailablePaymentMethods();

// Elérhető szállítási módok lekérése:
WebshopCommerceService::getAvailableShippingMethods();

// Online-e egy fizetési mód:
WebshopCommerceService::isOnlinePaymentMethod('simplepay');

// Fizetés indítása:
WebshopCommerceService::processOrderPayment($order);
```

Ha a commerce-core nincs telepítve, az összes hívás gracefully degradál fallback adatokkal.

---

## Szabadszavas fejléc-kereső

Bekapcsolás: **Webshop beállítások → „Kereső a fejlécben"** (`site_header_search_enabled`).

- a mező a fejléc `global-btn-items` részletébe kerül, a kosár és összehasonlítás gombok mellé
- a találatok AJAX-szal, gépelés közben jönnek: `GET /webshop/kereses` →
  `site.webshop.products.search`
- legalább **3 karakter** kell a kereséshez, a végpont legfeljebb **5 találatot** ad vissza
- kikapcsolt kereső esetén a végpont 404-et ad, nem csak a mező tűnik el
- a keresőkifejezésben a `%` és `_` escape-elve megy a `LIKE`-ba, így egyetlen `%`
  nem listázza ki a teljes termékkészletet

### Assetek

A kereső stílusa és viselkedése a CSOMAG assetjeiben van, nem a blade-ben:

| fájl | mi van benne | hogyan kerül az oldalra |
| --- | --- | --- |
| `public/packages/webshop/site/css/webshop-site.scss` (+ `.css`) | a `.ws-search-*` szabályok | a site layout tölti be, `@isset($ws)` feltétellel – ugyanazzal, amivel a fejléc gombsora is renderelődik |
| `public/packages/webshop/site/js/webshop-search.js` | a gépelés-, lenyíló- és bezárás-kezelés | a `header-search.blade.php` `<script src>`-cel, tehát csak bekapcsolt keresőnél töltődik le |

Ezért **kell asset publish**. A projekt `post-install-cmd`-je a gyűjtő taget hívja:

```
@php artisan vendor:publish --tag=webshop-assets
```

> **Frissítéskor figyelj:** a `vendor:publish` `--force` nélkül a MÁR LÉTEZŐ
> fájlokat kihagyja. Az új `webshop-search.js` így magától kikerül, a MÓDOSÍTOTT
> `webshop-site.css` viszont NEM – a kereső ilyenkor stílus nélkül jelenne meg.
> Csomagfrissítés után tehát:
>
> ```bash
> php artisan vendor:publish --tag=webshop-assets-css --force
> ```
>
> Ha a projektben SCSS-figyelő fordítja a `webshop-site.css`-t, a `--force` publish
> felülírja a helyi fordítást – ilyenkor a `.scss`-t publikáld, és fordíttasd újra.

> **Miért külön JS fájl, és nem a `webshop-site.js` része?**
> A `webshop-site.js` nem minden webshop-oldalon töltődik be – oldalanként, kézzel
> van behúzva. A `/webshop/checkout/success/{id}` és a `/webshop/payment/{id}/result`
> oldalon például a fejléc (és benne a kereső) renderelődik, de a `webshop-site.js`
> nincs betöltve; ott a kereső némán halott lenne. Fordítva, ha a `webshop-site.js`-t
> húznánk be a fejlécbe, a többi aloldalon kétszer töltődne be és duplán kötné az
> eseménykezelőit.

---

## Kuponok

A kuponkezelést a külön `weboldalnet/commerce-coupons` csomag adja; ez a csomag a
webshop oldali bekötést hozza.

Bekapcsolás: **Webshop beállítások → „Kuponok engedélyezve"** (`coupons_enabled`).
Ez adja az admin oldalsáv „Kuponok" menüpontját és a pénztári beváltó dobozt is.
A menüpont mellett a `Route::has('admin.webshop.coupons.index')` feltétel is teljesül
kell – enélkül (ha a kupon csomag nincs telepítve) a menüpont nem jelenik meg.

### Mit ad hozzá ez a csomag

| elem | szerep |
| --- | --- |
| `WebshopCouponService` | híd a kupon modulhoz; a kosársorokat állítja elő, és a session-t kezeli |
| `POST /webshop/kupon/aktivalas` | a pénztári aktiválás AJAX végpontja |
| `checkout/partials/coupon.blade.php` | a beváltó doboz és a sikeres aktiválás modalja |
| migráció | `webshop_orders`: `coupon_id`, `coupon_code`, `discount_amount`, `coupon_data` |

### Fontos viselkedés

- **A session csak a kuponKÓDOT tárolja, az összeget soha.** Minden megjelenítéskor és
  a rendelés leadásakor újraszámolunk – így egy közben lejárt vagy átszerkesztett kupon
  nem tud elavult összeggel érvényesülni.
- A pénztárra lépéskor (`checkout index`) az aktív kupon **törlődik**: a beváltás egy
  pénztári művelet, nem hordozódik át oldalak között.
- A `store()` a rendelés létrehozása **előtt** újraértékeli a kupont. Ha időközben
  érvénytelenné vált, a rendelés NEM jön létre, a vásárló hibaüzenetet kap.
- A kedvezmény a **tételek összegére** vonatkozik: a szállítási díj és az utánvét
  költsége nem csökken vele.
- A rendelésre a kupon teljes pillanatképe mentődik (`coupon_data`), mert a kupon
  később átszerkeszthető vagy törölhető – a leadott rendeléshez viszont annak kell
  tartoznia, ami a leadás pillanatában érvényes volt.

### Megjelenítés

A kuponkedvezmény külön soron szerepel a pénztári összesítőben, a rendelés
adatlapján, az admin rendeléslapon, a visszaigazoló e-mailben és a számlán is.

---

## A fizetendő bontása és a számla tételei

A pénztár képlete:

```
total_price = tételek + szállítási díj + utánvét-felár − kuponkedvezmény
```

Ezt a bontást **egyetlen helyen** számoljuk: `WebshopOrder::priceBreakdown()`.
Ebből dolgozik a rendelés adatlapja, az admin rendeléslap, a visszaigazoló e-mail
ÉS a számla is – korábban ugyanez a képlet három blade-ben volt lemásolva, és az
egyikben a kuponkedvezmény némán eltűnt.

Az utánvét-felár a `cod_fee` oszlopban tárolódik. A nullable szándékos: a `null`
azt jelenti, hogy a rendelés még az oszlop bevezetése előtt született – ott a
felárat a végösszegből maradékként vezetjük le, hogy a régi rendelések bontása is
kiadja a végösszeget.

### Számlatételek

A számlára a terméksorok mellé három extra tétel kerül, ebben a sorrendben:

| tétel | forrás | előjel |
| --- | --- | --- |
| Kuponkedvezmény *(kód)* | `discount_amount` | − |
| Szállítási díj *(futárszolgálat)* | `shipping_cost` | + |
| Utánvét díja | `cod_fee` | + |

A 0 értékű tételek kimaradnak (ingyenes szállítás, nincs utánvét). Ha minden
tétel be van kapcsolva, **a számla tételeinek összege pontosan a rendelés
végösszege**.

### Ha kézzel viszik fel valamelyik tételt

**Webshop beállítások → Számla tételei** alatt tételenként külön kikapcsolható,
és a tétel neve is átírható:

| beállítás | jelentés |
| --- | --- |
| `invoice_line_<tétel>_enabled` | rákerüljön-e a számlára (alap: **igen**) |
| `invoice_line_<tétel>_name` | a tétel neve a számlán, pontosan így (üresen: alapértelmezett) |

A `<tétel>` a `WebshopInvoiceRequestFactory::EXTRA_LINES` kulcsa: `coupon`,
`shipping`, `cod`. Új extra tétel felvétele: egy sor abban a konstansban – az
admin felület és a mentés is a konstansból épül.

Ha a névmezőt üresen hagyod, az alapértelmezett névhez hozzáfűzzük a
futárszolgálat nevét, illetve a kuponkódot (pl. „Szállítási díj (GLS
futárszolgálat)”). Ha saját nevet adsz meg, azt PONTOSAN úgy használjuk –
különben egy FoxPost rendelésnél a beírt „Szállítási díj (GLS)” mellé még egy
zárójel kerülne.

Kikapcsolt tételnél a számla végösszege szándékosan NEM adja ki a rendelés
végösszegét (a beállítófelület erre figyelmeztet is), és a helyi
bizonylat-nyilvántartásba (`gross_total`) is a ténylegesen elküldött tételek
összege kerül, nem a rendelés végösszege.

### Áfakulcs

`invoice_vat_key` – a számla **minden** tételére ez az áfakulcs kerül, a
termékekre is. Választható: `27`, `18`, `5`, `0`, `AAM`, `TAM`
(`WebshopInvoiceRequestFactory::VAT_KEYS`); alapértelmezés `27`.

Miért egy közös kulcs és nem terméksoronkénti: **a webshop adatmodelljében
nincs áfa fogalom** – sem a `webshop_products`, sem a `webshop_order_items`
táblában nincs áfakulcs oszlop, tehát nincs miből soronként származtatni. Egy
alanyi adómentes bolt így az EGÉSZ számlát AAM-re állítja, és nem keveredik
27%-os terméksor egy AAM-es szállítási sorral.

A mentés és a beolvasás is a `VAT_KEYS` listához köt: egy ismeretlen kulcs
(elgépelés, kézzel összerakott POST, régi adat) nem kerülhet ki a számlára.
Ennek konkrét oka, hogy a `SzamlazzhuService` `is_numeric()` vizsgálata a nem
numerikus kulcsot NÉMÁN 0% áfának veszi – egy elgépelt `27%` így pontosan
0 forint áfát eredményezne.

> **Ha terméksoronkénti áfakulcs kell** (pl. vegyes 5%-os és 27%-os készlet), az
> egy külön fejlesztés: áfakulcs mező a terméken, pillanatkép a rendelési
> tételre a pénztárban (ahogy a `weight` is megy), és a `vat_key` onnan olvasva.

---

## Egyedi kategória menüsor

Két kapcsoló együtt aktiválja (**Webshop beállítások → Kategória beállítások**):

| beállítás | szerep |
| --- | --- |
| `category_parent_enabled` | többszintes kategóriarendszer (ez a feltétele) |
| `category_custom_menu_enabled` | **Egyedi kategória menüsor** – csak az előbbivel együtt jelenik meg |

Bekapcsolva a `sticky-categories.blade.php` a régi, vízszintesen görgethető sáv helyett
a `category-menu.blade.php`-t rendereli. A váltás ott, egy ponton dől el – a 8 include-helyet
nem kell hozzáérni, és a többszintes rendszer kikapcsolásakor a navigáció magától
visszaáll a régi sávra.

### Melyik kategória kerül a menübe

**Webshop extra beállítások → Kategória menüsor** (a kártya csak bekapcsolt funkciónál
jelenik meg, az útvonal is 404-et ad enélkül). A szerkesztő a teljes fát kirajzolja,
kategóriánként egy AJAX kapcsolóval.

A kapcsoló a **meglévő** `webshop_categories.show_in_sticky_header` oszlopot írja –
ugyanazt, amit a kategória szerkesztő űrlapján a „Megjelenik a sticky fejlécben” mező.
Egy fogalom, két felület; **nem kellett új oszlop**.

Az `is_active = false` kategória szürkén, letiltott kapcsolóval jelenik meg: a site-on
sem érhető el, a menübe tenni sem lenne értelme.

### Megjelenés

| nézet | mit ad |
| --- | --- |
| `lg` felett | vízszintes sáv a legfelső kategóriákkal; fölé húzva teljes szélességű panel nyílik le a mélyebb szintekkel |
| `lg` alatt | csak két kompakt gomb – **Szűrés** (ahol van szűrő) és **Kategóriák**; utóbbi bal oldalt kicsúszó fiókot nyit collapse-os kategórialistával |

A mobil nézet szándékosan NEM a kategóriák görgetősávja: egy 15×15×30-as kategóriafánál
az használhatatlan lenne.

### Assetek

| mi | hova |
| --- | --- |
| stílus | `webshop-site.scss` (+ fordított `.css`) – a layout ugyanazzal az `@isset($ws)` feltétellel tölti be, amivel a menü renderelődik |
| viselkedés | **`webshop-category-menu.js`** – külön fájl, mert a `webshop-site.js` nem minden webshop-oldalon töltődik be |

> **Buktató:** a lenyíló panelen SEMMILYEN Bootstrap display utility (`d-none`,
> `d-lg-block`) nem lehet. Azok `!important`-tal jönnek, felülírják a `.show`
> kapcsolót, és MINDEN panel egyszerre látszik, egymásra rajzolva. Az „csak asztali”
> viselkedést media query adja.

### A view composer szűrése

A `stickyCategories` composer korábban csak a GYÖKÉR szintre alkalmazta az `active()` és
`stickyHeader()` szűrést, az eager-loadolt gyerekek szűretlenül jöttek – vagyis egy
inaktív vagy a menüből kikapcsolt alkategória is megjelent a lenyílóban. Javítva:
a gyerek szintek ugyanazt a szűrést és rendezést kapják.
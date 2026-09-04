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

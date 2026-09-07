## [1.1.6] - 2026-09-07
### Javítás: friss adatbázison hibára futott a migráció
- a `2026_09_04_WEBSHOP_add_design_to_home_blocks` fájlnév ÁBÉCÉBEN megelőzte a
  táblát létrehozó `..._create_home_blocks_table`-t, ezért az ALTER futott előbb:
  `relation "public.webshop_home_blocks" does not exist`
- meglévő adatbázison ez észrevétlen maradt, mert ott a tábla már létezett
- a fájl dátuma 2026_09_05-re módosult; a migráció idempotens, ezért a már
  migrált adatbázisokon ártalmatlanul újrafut


## [1.4.0] - 2026-09-07
### Egyedi, több szintű kategória menüsor
- új beállítás: `category_custom_menu_enabled` (csak többszintes kategóriarendszerrel)
- új szerkesztő: Webshop extra beállítások → Kategória menüsor – a teljes fa
  kategóriánkénti AJAX kapcsolóval; a MEGLÉVŐ `show_in_sticky_header` oszlopot írja,
  új DB oszlop NEM kellett
- asztali nézet: lenyíló, teljes szélességű panel a mélyebb szintekkel
- mobil nézet: két kompakt gomb (Szűrés + Kategóriák), a kategóriák bal oldalt
  kicsúszó fiókban, collapse-os listával – nem görgetősávon
- a váltás a régi sáv és az új menü között a `sticky-categories.blade.php`-ban van,
  a 8 include-helyet nem kellett hozzáérni

### Javítás: a kategória-lenyíló szűretlen gyerekei
- a `stickyCategories` composer az `active()` és `stickyHeader()` szűrést csak a
  gyökér szintre alkalmazta; egy inaktív vagy a menüből kikapcsolt alkategória is
  megjelent a lenyílóban. A gyerek szintek is megkapják a szűrést és a rendezést.

## [1.3.1] - 2026-09-07
### A fejléc-kereső stílusa és scriptje a csomag assetjeibe került
- a `<style>` a `webshop-site.scss`-be (+ a fordított `.css`-be), a `<script>` egy
  új `webshop-search.js`-be – a blade már csak a markupot tartalmazza
- külön JS fájl, mert a `webshop-site.js` nem minden webshop-oldalon töltődik be
  (a rendelés-visszaigazolón és a fizetési eredmény oldalon nem), a kereső viszont
  ott is megjelenik

### Javítás: a csomag webshop-site.css elavult volt a saját .scss-éhez képest
- 21 sornyi terméklap-szabály (`.ws-product-card-title.sec-title`,
  `.ws-product-card-sec-title`, a táblázatos nézet `a.sec-title` szabályai és egy
  magasság) hiányzott a csomagba commitolt fordított CSS-ből
- emiatt egy `vendor:publish --tag=webshop-assets-css --force` VISSZALÉPTETTE volna
  a befogadó projekt terméklapjait; újrafordítva

## [1.3.0] - 2026-09-07
### A számla tételei kiadják a rendelés végösszegét
- a számlára felkerül a SZÁLLÍTÁSI DÍJ és az UTÁNVÉT-FELÁR is, nem csak a
  terméksorok – eddig a számla kevesebbről szólt, mint amit a vevő fizetett
- tételenként kikapcsolható és átnevezhető (Webshop beállítások → Számla tételei),
  mert van bolt, ahol ezeket kézzel viszik fel a számlázó programban
- ÚJ beállítás: a számla áfakulcsa (`invoice_vat_key`, alap 27%), ami MINDEN
  tételre vonatkozik – alanyi adómentes bolt így az egész számlát AAM-re állítja
- csak a felkínált kulcsok menthetők: a Számlázz.hu a nem numerikus kulcsot
  némán 0% áfának veszi, egy elgépelt "27%" 0 forint áfát adna
- megszűnt a `$item->vat_rate` olvasása: ilyen oszlop SOHA nem létezett a
  webshop_order_items táblában, tehát a soronkénti áfa néma 27%-ra esett vissza
- a payload `gross_total` mezője a ténylegesen elküldött tételek összege, nem a
  rendelés végösszege

### Új: cod_fee oszlop a rendelésen
- az utánvét-felárat eddig csak beleszámoltuk a végösszegbe, de nem tároltuk,
  így a rendelés utólag nem volt tételesen visszabontható
- nullable: a régi rendeléseknél a maradékos levezetés marad érvényben

### WebshopOrder::priceBreakdown() – egyetlen forrás
- a rendelés adatlapja, az admin rendeléslap, a visszaigazoló e-mail és a számla
  is ebből dolgozik; korábban ugyanaz a képlet három blade-ben volt lemásolva
- mindhárom felület megkapta a hiányzó "Utánvét díja" sort is

## [1.2.0] - 2026-09-04
### Szabadszavas fejléc-kereső
- `site_header_search_enabled` beállítás, önhordó nézetek (nincs asset publish)
- `GET /webshop/kereses`: min. 3 karakter, max. 5 találat, kikapcsolva 404
- a `%` és `_` escape-elve megy a LIKE-ba

### Kupon bekötés (weboldalnet/commerce-coupons)
- `coupons_enabled` beállítás, `WebshopCouponService` híd, pénztári beváltó doboz
- `webshop_orders`: `coupon_id`, `coupon_code`, `discount_amount`, `coupon_data`
- a session CSAK a kódot tárolja; az összeg minden megjelenítéskor újraszámolódik
- a `store()` a rendelés létrehozása előtt újraértékeli a kupont

### Javítás: hiányzó CSRF token az admin AJAX hívásokban
- a `webshop-admin.js` a `<meta name="csrf-token">` hiányában immár bármelyik form
  rejtett `_token` mezőjéből veszi a tokent
- enélkül MINDEN `WebshopAdmin` AJAX POST (aktív kapcsoló, sorrendezés, galéria
  feltöltés) némán 419-et kapott, a felhasználó csak „Hiba történt"-et látott

### Javítás: a fizetendő bontása nem adta ki a végösszeget
- a rendelés adatlapja, az admin rendeléslap és a visszaigazoló e-mail mostantól
  tételesen mutatja a szállítási díjat és a kuponkedvezményt is
- a számlára a kuponkedvezmény külön, negatív tételként kerül fel

## [1.0.0] - 2026-01-06
### Alap teszt - Commit #2

## [1.0.1] - 2026-01-06
### Fájlok feltöltésének tesztelése
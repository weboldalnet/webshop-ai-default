## [Unreleased]
### SEO: meta leírás, OG kép és márkanév a webshop oldalain
**1 új migráció** (additív, `Schema::hasColumn` védelemmel) — frissítés után `php artisan migrate`.
- **Termék SEO mezők**: `og_title`, `og_description`, `og_img` a termékeken, a kategóriákéval
  azonos névvel és szerkesztőfelülettel (termékszerkesztő → „SEO / OG adatok" doboz).
- **Tartalék minden oldalon** (`WebshopSeoService`): üres mezőnél a cím a termék/kategória nevéből,
  a leírás a rövid leírásból vagy a termékleírásból (tagek nélkül, 160 karakterre szóhatáron vágva),
  a kép az elsődleges képből, illetve a kategória listaképéből képződik. A rövid leírás csak akkor
  visz, ha mondatnyi (≥60 karakter) — sok shopban az a mező specifikáció („3 l · hidegen sajtolva"),
  ami önmagában használhatatlan meta leírás.
- **Böngészőcím márkanévvel**: minden webshop oldal címe „… - Cégnév" alakú (ha a szerkesztő már
  beleírta a márkát, nem duplázzuk).
- **og:image abszolút URL**: a közösségi és kereső robotok külön kérésben töltik le, relatív
  útvonalat nem tudnak feloldani.
- **Webshop nyitóoldal**: a Webshop → Nyitóoldal „Meta leírás" mezője eddig ki volt tölthető az
  adminban, de SEHOL nem jelent meg a lapon — mostantól kimegy; ennek híján a tényleges
  főkategóriákból képződik leírás.
- **Kategória**: üresen hagyott SEO mező mostantól NULL, nem a név/leírás másolata (eddig a teljes
  HTML-leírás másolódott az OG leírásba); a leírásmező 120 → 160 karakter, mert ez adja a meta
  descriptiont is.
- **Termékoldal képei**: a főkép és a bélyegkép `width`/`height` attribútumot kap a kategória
  beállított vágási méretéből (layout-ugrás, CLS ellen).

### Jogi megfelelés (online értékesítés) — javasolt verzió: 1.2.0
Minden pont termékfajtától független; ahol termékkör-függő adat kell, ott bekapcsolható profil van.
**5 új migráció** (mind additív, `Schema::hasColumn`/`hasTable` védelemmel) — frissítés után
`php artisan migrate` kell (a deploy ezt futtatja).
- **Rendelés gomb**: „Megrendelés fizetési kötelezettséggel" (45/2014. Korm. r.) — ajánlatkérés módban változatlan
- **Elállás indoklás nélkül**: a megjegyzés mező nem kötelező; „kérelem" helyett „nyilatkozat";
  a visszaigazoló levél a beérkezés dátumát és idejét is tartalmazza (tárgy: „Elállási nyilatkozat …")
- **Elállás védelme**: a keresés rendelésszám + e-mail-cím párost kér (kis-/nagybetű, szóköz
  közömbös), egyezés nélkül általános üzenet; az űrlap és a beküldés csak aláírt URL-lel érhető el
  (`WebshopWithdrawalService::formUrl()/storeUrl()`), lejárt/hamis linknél érthető üzenet; throttle
- **Rendelés-visszaigazoló levél**: eladó adatai, ÁSZF (PDF-mellékletként, ha helyi PDF van;
  kapcsolható), elállási tájékoztató aláírt linkkel, adatkezelési link, kitöltött
  nyilatkozatminta (45/2014. 2. melléklet). Eladói adatok: Extra beállítások → Dokumentumok
  („Eladó adatai", hiányjelzéssel; üres mezőnél a Kapcsolat beállítások adata)
- **Akciós ár (Omnibusz)**: áthúzott árként az akciót megelőző 30 nap legalacsonyabb eladási
  ára, ebből a %-os kedvezmény is. Új ártörténet-tábla (`webshop_product_price_history`), a
  termék mentési eseményei töltik (`WebshopPriceHistoryService`); lépcsőzetes akciónál a
  referencia marad. Ha az akciós ár nem alacsonyabb a 30 napos minimumnál, sima árként jelenik
  meg. Új model-metódusok: `sellingPrice()`, `referencePrice()`, `hasActiveSale()` — a kosár,
  az admin rendelésfelvétel, a schema és a Merchant feed is ezeket használja
- **Egységár**: kiszerelés (mennyiség + mértékegység) a terméken → „(798 Ft/l)" az ár alatt
  mindenhol (kártya, termékoldal, táblázat, összehasonlítás, kereső), Merchant feedben
  `unit_pricing_measure`. Üresen hagyva nincs egységár (`WebshopUnitPriceService`)
- **Vélemények**: „Ellenőrzött vásárló" jelölés rendelésszám + e-mail igazolással, moderálás
  (alapból bekapcsolva: új vélemény közzététel előtt jóváhagyásra vár), opcionálisan csak
  igazolt vásárlók írhatnak; a termékoldalon automatikus tájékoztató a beállítások szerint
- **Mérőkódok csak hozzájárulással**: a Mérési scriptek kapnak hozzájárulási kategóriát
  (alapértelmezés: marketing); a nem „feltétlenül szükséges" kód `<template>`-ben vár, és csak a
  core sütiablakának megfelelő hozzájárulása után fut (visszatérő látogatónál azonnal, döntéskor
  a döntés pillanatában). Sütiablak nélkül ezek nem futnak — az admin figyelmeztet
- **Kötelező termékinformációk**: új „Termékinformációk" beállítóoldal és termékoldali fül.
  Profilok: általános termékbiztonság (GPSR), élelmiszer (összetevők, allergének, tápérték …),
  veszélyes keverék (CLP piktogram, H/P-mondatok, UFI), energiacímke, textil. Bolt szintű
  alapérték (pl. gyártó) adható; a termékszerkesztőben csak a bekapcsolt profilok mezői látszanak
- **Apróbb javítások**: elállás-modal címkéi sötét láblécben is látszanak; a webshop főoldal
  megjeleníti az átirányítási üzeneteket; értékelésnél a szerver konkrét hibaüzenete jelenik
  meg; a Mérési scriptek súgójában a `@stack` szöveg már nem tűnik el

**Frissítési teendők egy élő boltban**: Dokumentumok → Eladó adatai kitöltése (cégjegyzékszám,
adószám); ellenőrizni, hogy az ÁSZF valóban ÁSZF-PDF (a levél mellékletként küldi);
Termékinformációk → profilok bekapcsolása; meglévő mérőkódok kategóriája alapból „marketing".
Felülírt (projektben másolt) nézeteknél az áthúzott árat `referencePrice()`-ra, a sima árat
`sellingPrice()`-ra érdemes cserélni, és beilleszteni a `site.webshop.partials.unit-price` részletet.

### Szállítás
- **MPL futárszolgálat** a beépített szállítási módok között, külön csomag nélkül — a
  pénztárban a házhoz szállítás egyik futárszolgálataként, szállítási címmel választható.
  Nincs API-kapcsolat: a szállítmányt az ügyfélszolgálat kézzel rögzíti az MPL
  rendszerében (a rendelésnél „függő" szállítmány jön létre, posta.hu nyomkövetési
  linkkel). Díj és ingyenes-szállítási értékhatár az adminban; **alapból kikapcsolva**,
  így frissítés után egyetlen bolt pénztárában sem jelenik meg magától. Általános
  `Shipping\ManualCourierShippingProvider` — további kézi futárszolgálat egy sorral
  felvehető (`WebshopAiDefaultServiceProvider::registerBuiltinShippingProviders()`)
- **Saját futárszolgálat díja adminból**: a fix díjas szállítás díját eddig csak `.env`-ből
  lehetett állítani — most a beállítóoldalon, ingyenes-szállítási értékhatárral együtt.
  A saját díjazású módok listája egy helyen: `WebshopCommerceService::MANUALLY_PRICED_SHIPPING_METHODS`
- **„Saját futárszolgálat" felirat** a fix díjas módnál — eddig maga is „Házhoz szállítás"
  volt, így a pénztárban a csoportnév alatt még egyszer ugyanaz állt
- **Alapértelmezett szállítási mód**: a csoportok sorrendjét követi (házhoz szállítás →
  csomagpont → személyes átvétel); eddig a providerek regisztrációs sorrendje döntött,
  és a személyes átvétel volt előre kiválasztva

### Pénztár
- **Összegző**: `col-lg-6` a `col-6` helyett — mobilon is fél szélességű volt
- **Rádiógombok igazítása**: a core `top: 0`-ja 3,5 px-rel a szöveg fölé tette a jelölőt;
  a csomag stíluslapja visszaadja a Bootstrap `0.25rem`-jét (a jelölőnégyzetekkel azonosan)

### Kategórialap
- **Szűrő nélküli kategória**: ha nincs megjeleníthető szűrő, a bal oldali oszlop elmarad,
  a lista teljes szélességű, xl-en 4 termék fér egy sorba (`col-xl-3`). A döntés közös
  forrásból jön: `WebshopProductFilterService::getVisibleFilterGroups()` / `hasVisibleFilters()`
- **Mobil „Szűrés" gomb**: `@isset` helyett `@if` — a `false` értéket is igaznak vette,
  és szűrő nélküli kategórián üres panelt nyitott

## [1.1.9] - 2026-09-18
### Admin javítások (a vegyiáru-bemutató feltöltése közben derültek ki)
- **Kategória listakép**: a képmetszővel vágott listakép eddig NYERSEN mentődött
  (~1000×750 px, ~1 MB PNG egy 400×300-as kártyához) — most a
  `webshop.category_list_image_crop_sizes` célméretére méretezve, webp-ként
  (`ImageService::saveCustomImage`), a normál és a széles változat is
- **Variációk szerkesztője**: a „Kapcsolódó termékek" feltételén belül ült, így
  kikapcsolt kapcsolódó termékeknél a variációkat sem lehetett kezelni — a két
  oszlop mostantól egymástól független
- **Új termék oldal**: a „Leírás" mező `js-tinymce` osztályú volt, de a szerkesztő
  csak az edit oldalon töltődött be — itt sima szövegdoboz maradt

### Site
- **Szűrők**: a szűrőpanel csak azokat az értékeket mutatja, amelyek a kategória
  legalább egy AKTÍV termékén szerepelnek (eddig a tulajdonság-csoport összes
  értéke látszott, pl. a Mosószereknél „Spray" és „WC" — ezekre mindig 0 találat
  jött); a bejelölt érték mindig látszik, az üres csoport kimarad
- **Variációk doboz a termékoldalon**: csak akkor jelenik meg, ha a terméken kívül legalább
  egy AKTÍV változat is van — a lista a jelenlegi terméket mindig tartalmazza, ezért eddig a
  variáció nélküli termékeken is ott volt egy egyelemű „Variációk" doboz
- **Szám-tulajdonság mértékegysége**: „30 adag" a „30adag" helyett — közös
  `WebshopPropertyCategory::formatNumberValue()` (nem törhető szóköz, a % és a °
  szóköz nélkül); termékkártya, táblázatos lista, összehasonlítás, termékoldal

### A kimaradt magyar azonosítók átnevezése
- az 1.1.8 „egész csomagos" átnevezéséből kimaradt ~75 azonosító angolra írva:
  nyitóoldal-blokkok (`hexSzin` → `hexColor`, `intLista` → `intList`, `$blokk`,
  `$idk`, `$adminMod`, …), kategóriamenü-fa (`fabaRendez` → `buildTree`,
  `$szuloId`, `$letezoIdk`, …), kereső (`$kifejezes`, `$minta`, `$osszesTalalat`),
  kosár (`$kertOsszesen`, `$engedett`), kupon (`$osztaly`, `$kod`),
  service provider (`$wsMenuSzuro`), valamint a JS-ek: `webshop-search.js`,
  `webshop-category-menu.js`, `webshop-site.js` és a nyitóoldal-szerkesztő, a
  beállítások, a pénztár és a fizetési eredmény oldal inline scriptjei
- tokenizáló alapú csere: kommentek, felületi szövegek és API-kulcsok (pl.
  `{ code: … }`, `obj.status_label`) változatlanok
- élőben ellenőrizve: fejléc-kereső, kategóriamenü (asztali panel + mobil fiók),
  nyitóoldal-szerkesztő (termékkereső, kategória hozzáadása, mentés — a bannerképek
  megmaradnak), kosár (készlethiány-üzenet), kupon aktiválás, 31 rendelés
  címblokkja

## [1.1.8] - 2026-09-11
### Minden azonosító angolra
- a magyar nevű PHP változók, metódusok és tömbkulcsok angolra íródtak át az
  egész csomagban; a KOMMENTEK maradtak magyarul
- érintett többek között a `WebshopStockService` teljes API-ja
  (`kezelesAktiv` → `managementEnabled`, `levon` → `reserve`,
  `visszair` → `restore`, `kosarEllenorzes` → `checkCart`, …), a
  `WebshopOrder::priceBreakdown()` tömbkulcsai
  (`tetelek/szallitas/utanvet/kedvezmeny/egyeb` → `items/shipping/cod/discount/other`)
  és a `WebshopInvoiceRequestFactory` `EXTRA_LINES` kulcsai
  (`forras` → `source`, `elojel` → `sign`)
- a `priceBreakdown()` mind a NÉGY fogyasztója (admin rendelés-szerkesztő,
  rendelés-részletek, visszaigazoló e-mail, pénztári összesítő) együtt módosult;
  mind a 31 meglévő rendelésre ellenőrizve, hogy a bontás továbbra is pontosan
  kiadja a végösszeget, és hogy a számla tételei is

### Javítás: callbackként átadott függvénynév az átnevezésnél
- a kuponaktiváló gomb eseménykezelője a függvényt REFERENCIAKÉNT kapja
  (`on('click', wsApplyCoupon)`), nem hívásként — az átnevezés első köre ezt
  kihagyta, így a gomb egy már nem létező függvényre mutatott, és a kupon némán
  nem aktiválódott
- élőben ellenőrizve: a kupon aktiválódik, a kedvezmény és a fizetendő is frissül

## [1.1.7] - 2026-09-10
### Készletkezelés (a beállítás eddig nem csinált semmit)
- új `WebshopStockService`: a készlet KÉT kapcsolón múlik – a bolt szintű
  `product_stock_enabled` és a terméken lévő `stock_enabled` –, és mindkettőnek
  állnia kell, különben a termék korlátlanul rendelhető
- a levonás a rendelés LEADÁSAKOR történik, a rendelést létrehozó tranzakción
  belül, `SELECT ... FOR UPDATE` sorzárolással: két egyidejű vásárló közül a
  második már a csökkentett készletet látja
- új `stock_applied` oszlop a rendelésen: enélkül egy kétszer lemondott rendelés
  kétszer adná vissza ugyanazokat a darabokat
- a törölt / meghiúsult rendelés készlete visszakerül a raktárba, a
  visszaállítotté újra lefoglalódik – ezt MINDHÁROM státuszt író végpont
  elvégzi: a szerkesztő űrlap, a lista gyors legördülője és a
  teljesítés-kapcsoló is
- kosárba tételkor a kért mennyiség a készlethez vágódik (elutasítás helyett),
  a vásárló pedig megkapja az okát: „Készlethiány miatt csak N db került a
  kosárba."
- a pénztár a rendelés létrehozása ELŐTT ellenőrzi a kosarat, hogy a vásárló
  érthető hibaüzenetet kapjon, ne egy visszagörgetett tranzakciót
- terméklapon készlet-jelzés (Raktáron / Utolsó N db / Elfogyott), a mennyiség
  legördülő a készletig megy, elfogyott terméknél a gomb tiltott
- a terméklista kártyáin ugyanez: „Utolsó N db" jelzés és tiltott gomb

### Variációk a terméklistán
- ha a variációk össze vannak vonva, a listába a csoportból csak a képviselő
  kerül – de a kártyán ott a „N változat" jelzés, hogy a vásárló tudjon a
  többiről (új `variationRepresentative()` scope + `withCount`)
- a hasonló termékek közül kiesik a saját variációcsoport
- a terméklap variációs blokkja a TELJES csoportot mutatja, a jelenlegi
  terméket is – eddig a vásárló nem látta, melyik változatot nézi éppen

### Másodlagos galéria: oldal széles, görgethető szalag
- a kártyába zárt, hatosával tördelt bélyegkép-rács helyett a lap teljes
  szélességét kitöltő, vízszintesen görgethető galéria (a projekt saját
  `.scroll-container` mintája, nyilakkal)
- a csempe mérete fix, a kép `object-fit: contain`-nel ül bele: a lusta
  betöltés így nem tudja 0 szélesre ejteni a szalagot, a nyilak pontosan egy
  képet léptetnek, és semmi nincs levágva
- a NAGY kép megy ki (1000 px), nem a 150 px-es bélyegkép – ekkora
  megjelenítésnél az utóbbi láthatóan pixeles lenne

### Az extra galéria címe átírható, a szekciónak nincs függőleges térköze
- új beállítás: „Az extra galéria címe" (Webshop beállítások → Extra látvány
  galéria alatt)
- a cím `h2`, középre rendezve jelenik meg a képsáv fölött
- üresen hagyva NEM csak a szöveg marad el, hanem a konténere is: egy üres
  címsor a saját alsó margójával és a konténer belső térközével ugyanúgy hézagot
  hagyna a képsáv fölött, csak épp láthatatlanul
- a szekció `py-0 my-0` utility osztályokat visel: a képsáv körül nincs saját
  függőleges térköz, a szomszédos blokkok adják a ritmust
- a SCSS-ből kikerült a szekció margin-szabálya: a utility osztályok
  !important-ja úgyis felülírta volna, tehát néma halott kód lett volna
### Rövid leírás a terméklapon
- az adminban kitölthető `short_desc` eddig SEHOL nem jelent meg a boltban,
  tehát írás-only mező volt
- mostantól a tulajdonságok FÖLÖTT jelenik meg, saját „Rövid leírás" cím alatt
- a „Terméklistán rövid leírás a tulajdonságok helyett" kapcsoló felirata
  egyértelműsítve: az a LISTA kártyáira vonatkozik, nem a terméklapra

### Javítás: kikapcsolt tulajdonság még látszott a terméklista kártyáin
- a terméklapi aktív-szűrés (szűrt eager load) nem ért el a kártyáig: az a nézet
  saját, szűretlen lekérdezéssel dolgozott, így a kikapcsolt tulajdonság-kategória
  és a kikapcsolt ÉRTÉK is megjelent
- a kártya mostantól ugyanazt a feltételt használja, mint a terméklap, és
  kategóriánként egy helyett EGY lekérdezéssel dolgozik (a listában kártyánként
  hívódik, ott az N+1 gyorsan összeadódik)

### Javítás: a kosár némán utasított el
- a kosárba-tétel JS-ében az `if (res.success)` ágnak nem volt `else` párja: ha a
  szerver elutasította a kérést, semmi nem történt – a vásárló kattintott, és nem
  tudta meg, miért nem került be semmi
- a válasz mostantól `level` mezőt is hordoz (siker / figyelmeztetés / hiba), mert
  három eset van, nem kettő: a kérés SIKERES lehet úgy is, hogy közben a
  mennyiséget a készlethez vágtuk
- a mennyiség-vágásról szóló figyelmeztetés a kapcsolódó termékek modalja MELLETT
  is megjelenik; eddig a modal elnyelte

### Javítás: a pénztárban a mennyiség mező a kért, nem a beállított darabszámot mutatta
- a JS optimistán beírta a kattintott értéket, és sosem korrigálta a szerver
  válaszából: 5 db-os készletnél a mező 7-et mutatott, miközben az összeg 5 db-ról
  szólt
- a szerver visszaadja a ténylegesen beállított mennyiséget, a mező arra áll be,
  és a vásárló megkapja az okát is
### Javítás: a kategórialap terméklistája üresen maradt
- a `product-card.blade.php`-ban egy egysoros `@php(...)` ELŐBB állt, mint egy
  `@php ... @endphp` blokk. A Blade a blokkokat a fordítás legelején, egy
  nem-mohó regexszel emeli ki, ami nem különbözteti meg a kettőt: a köztük lévő
  EGÉSZ szakaszt PHP-kódnak vette, a benne lévő `@if/@endif` párok le sem
  fordultak, a nézet pedig „unexpected endif"-fel elszállt
- a lista AJAX-szal töltődik, ezért a hiba csak egy örökké pörgő töltőjelként
  látszott
- a fájlban mostantól egyetlen `@php` stílus van, így a csapda nem térhet vissza

### Javítás: a pénztárban a mennyiség módosítása kitörölte a kitöltött űrlapot
- a mennyiség-módosítás újratöltötte az oldalt, tehát a vevő minden beírt adata
  elveszett; mostantól a DOM frissül, a kupon pedig újraszámolódik

### Javítás: a szűrés utáni Vissza gomb
- a kezdő betöltés `pushState` helyett `replaceState`-tel megy, így nem jön
  létre felesleges második history-bejegyzés ugyanarra az állapotra – eddig a
  Vissza gomb először „nem csinált semmit"

### További javítások a tesztjelentés alapján
- inaktivált tulajdonság nem jelenik meg többé az éles terméklapon
- a Vélemények fülnek van GOMBJA (darabszám-jelzéssel); a tartalma eddig is
  kirenderelődött, de semmi nem tudta aktiválni
- rejtett árnál nem marad üres, helyet foglaló `<div>` a kártyán
- a kapcsolódó kategóriák blokk a kategórialap alján, érthető beállítás-felirat
- a CRM azonosító mező felirata megmondja, mire való
- a galéria húzogatásához: a hiányzó CSRF meta miatt MINDEN admin AJAX POST
  419-cel szállt el; a JS mostantól az űrlap `_token` mezőjéből is kiolvassa,
  a rács rendezése pedig `intersect` toleranciával, containment nélkül megy

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
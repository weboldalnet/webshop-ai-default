@extends('admin.layouts.layout')
@section('title', 'Webshop beállítások')

@section('content')
    @php
        /*
         * WEBSHOP BEÁLLÍTÁSOK (újratervezve 2026-10-28): öt fül — Termékek, Kategóriák, Webshop oldal,
         * Pénztár, Számlázás. Az input-nevek és az alapértékek a régiek, a mentés
         * (WebshopSettingController::update) változatlan.
         *
         * MINDEN fül EGY űrlapban van: a rejtett fül mezői is elmennek mentéskor. A függő blokkok
         * (data-ws-show-when) csak display:none-t kapnak, disabled-et SOHA — egy be nem küldött
         * jelölőnégyzetet a mentés kikapcsoltnak venne.
         */
        $wsOn = fn (string $key, string $default = 'false') => ($ws[$key] ?? $default) === 'true';
        $wsToggle = 'admin.webshop.settings.partials.toggle';
    @endphp

    <div class="container-fluid ws-editor mt-3 mb-150">
        @include('admin.webshop.partials.alerts')

        <div class="ws-editor-head">
            <div>
                <span class="ws-editor-kicker">Webshop</span>
                <h2 class="ws-editor-title mb-1">Beállítások</h2>
                <span class="ws-hint mt-0">Itt kapcsolhatod be, mely funkciók érhetők el az adminban és a webshopban.</span>
            </div>
            <div class="ws-editor-head-actions">
                <a href="{{ route('admin.webshop.extra-settings.index') }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-sliders"></i> Extra beállítások
                </a>
            </div>
        </div>

        <nav class="nav ws-tabs js-ws-settings-tabs" role="tablist">
            <a class="nav-link active" data-toggle="tab" href="#ws-tab-products" role="tab"><i class="fa-solid fa-box"></i> Termékek</a>
            <a class="nav-link" data-toggle="tab" href="#ws-tab-categories" role="tab"><i class="fa-solid fa-folder-tree"></i> Kategóriák</a>
            <a class="nav-link" data-toggle="tab" href="#ws-tab-storefront" role="tab"><i class="fa-solid fa-store"></i> Webshop oldal</a>
            <a class="nav-link" data-toggle="tab" href="#ws-tab-checkout" role="tab"><i class="fa-solid fa-cart-shopping"></i> Pénztár</a>
            <a class="nav-link" data-toggle="tab" href="#ws-tab-invoicing" role="tab"><i class="fa-solid fa-file-invoice"></i> Számlázás</a>
        </nav>

        <form method="POST" action="{{ route('admin.webshop.settings.update') }}">
            @csrf
            <div class="tab-content">

                {{-- ===== TERMÉKEK: mi szerkeszthető a termékszerkesztőben ===== --}}
                <div class="tab-pane fade show active" id="ws-tab-products" role="tabpanel">
                    <div class="ws-card">
                        <header class="ws-card-head">
                            <span class="ws-card-icon"><i class="fa-solid fa-pen-to-square"></i></span>
                            <div class="ws-card-heading">
                                <h3 class="ws-card-title">Termékadatok</h3>
                                <span class="ws-card-lead">A bekapcsolt mezők jelennek meg a termékszerkesztőben.</span>
                            </div>
                        </header>
                        <div class="ws-toggle-grid">
                            <div class="ws-toggle-tile @if($wsOn('product_price_enabled')) is-on @endif">
                                @include($wsToggle, ['name' => 'product_price_enabled', 'label' => 'Ár és akciós ár', 'icon' => 'fa-tag',
                                    'hint' => 'Ár, akciós ár és egységár a terméknél.', 'checked' => $wsOn('product_price_enabled')])
                            </div>
                            <div class="ws-toggle-tile @if($wsOn('product_stock_enabled')) is-on @endif">
                                @include($wsToggle, ['name' => 'product_stock_enabled', 'label' => 'Készletkezelés', 'icon' => 'fa-boxes-stacked',
                                    'hint' => 'Termékenként bekapcsolható készletszámlálás.', 'checked' => $wsOn('product_stock_enabled')])
                            </div>
                            <div class="ws-toggle-tile @if($wsOn('admin_product_labels_enabled')) is-on @endif">
                                @include($wsToggle, ['name' => 'admin_product_labels_enabled', 'label' => 'Termékcímkék', 'icon' => 'fa-bookmark',
                                    'hint' => 'Színes címke a termékkártyán (pl. Újdonság).', 'checked' => $wsOn('admin_product_labels_enabled')])
                            </div>
                            <div class="ws-toggle-tile @if($wsOn('product_secondary_name_enabled')) is-on @endif">
                                @include($wsToggle, ['name' => 'product_secondary_name_enabled', 'label' => 'Másodlagos név', 'icon' => 'fa-heading',
                                    'hint' => 'Második sor a termék neve mellé.', 'checked' => $wsOn('product_secondary_name_enabled')])
                            </div>
                            <div class="ws-toggle-tile is-wide @if($wsOn('product_crm_id_enabled')) is-on @endif">
                                @include($wsToggle, ['name' => 'product_crm_id_enabled', 'label' => 'CRM azonosító', 'icon' => 'fa-id-card',
                                    'hint' => 'Szabadon kitölthető mező a termék külső rendszerbeli azonosítójának. Csak tárolja az értéket — a CRM-szinkronizációt (MiniCRM, Bevétel+) a Beállítások megfelelő integrációjánál kell bekapcsolni.',
                                    'checked' => $wsOn('product_crm_id_enabled')])
                            </div>
                        </div>
                    </div>

                    <div class="ws-card">
                        <header class="ws-card-head">
                            <span class="ws-card-icon"><i class="fa-solid fa-images"></i></span>
                            <div class="ws-card-heading"><h3 class="ws-card-title">Képek és dokumentumok</h3></div>
                        </header>
                        <div class="ws-toggle-grid">
                            <div class="ws-toggle-tile @if($wsOn('product_gallery_enabled')) is-on @endif">
                                @include($wsToggle, ['name' => 'product_gallery_enabled', 'label' => 'Galéria', 'icon' => 'fa-images',
                                    'hint' => 'Több kép a termékoldalon.', 'checked' => $wsOn('product_gallery_enabled')])
                                <div data-ws-show-when="#product_gallery_enabled">
                                    @include($wsToggle, ['name' => 'product_extra_gallery_enabled', 'label' => 'Extra látványgaléria', 'sub' => true,
                                        'hint' => 'Második képsáv a terméklapon.', 'checked' => $wsOn('product_extra_gallery_enabled')])
                                    <div class="ws-toggle-sub" data-ws-show-when="#product_extra_gallery_enabled">
                                        <div class="form-group">
                                            <label for="product_extra_gallery_title">Az extra galéria címe</label>
                                            <input type="text" class="form-control" id="product_extra_gallery_title"
                                                   name="product_extra_gallery_title" maxlength="120"
                                                   value="{{ $ws['product_extra_gallery_title'] ?? '' }}"
                                                   placeholder="Galéria">
                                            <span class="ws-hint">A képsáv fölött, középen jelenik meg. Üresen a galéria cím nélkül, kihagyott hely nélkül látszik.</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="ws-toggle-tile @if($wsOn('product_document_upload_enabled')) is-on @endif">
                                @include($wsToggle, ['name' => 'product_document_upload_enabled', 'label' => 'Dokumentumok', 'icon' => 'fa-file-lines',
                                    'hint' => 'Letölthető fájlok és linkek a termékoldalon.', 'checked' => $wsOn('product_document_upload_enabled')])
                            </div>
                            <div class="ws-toggle-tile ws-field-tile is-wide">
                                <div class="form-group">
                                    <label for="admin_product_primary_image_mode">Elsődleges kép feltöltése</label>
                                    <select class="form-control" id="admin_product_primary_image_mode" name="admin_product_primary_image_mode">
                                        <option value="cropper" @if(($ws['admin_product_primary_image_mode'] ?? 'cropper') == 'cropper') selected @endif>Képmetsző (Cropper)</option>
                                        <option value="simple" @if(($ws['admin_product_primary_image_mode'] ?? 'cropper') == 'simple') selected @endif>Egyszerű képfeltöltés</option>
                                        <option value="simple_white" @if(($ws['admin_product_primary_image_mode'] ?? 'cropper') == 'simple_white') selected @endif>Egyszerű képfeltöltés fehér háttérrel</option>
                                    </select>
                                    <span class="ws-hint">A képmetszővel a kép pontosan a kategória képméretére vágható.</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="ws-card">
                        <header class="ws-card-head">
                            <span class="ws-card-icon"><i class="fa-solid fa-link"></i></span>
                            <div class="ws-card-heading"><h3 class="ws-card-title">Termékkapcsolatok</h3></div>
                        </header>
                        <div class="ws-toggle-grid">
                            <div class="ws-toggle-tile @if($wsOn('product_related_enabled')) is-on @endif">
                                @include($wsToggle, ['name' => 'product_related_enabled', 'label' => 'Kapcsolódó termékek', 'icon' => 'fa-link',
                                    'hint' => 'Ajánlott termékek a termékoldal alján.', 'checked' => $wsOn('product_related_enabled')])
                            </div>
                            <div class="ws-toggle-tile @if($wsOn('product_variations_enabled')) is-on @endif">
                                @include($wsToggle, ['name' => 'product_variations_enabled', 'label' => 'Variációk', 'icon' => 'fa-layer-group',
                                    'hint' => 'Egy termék több kiszerelése vagy változata.', 'checked' => $wsOn('product_variations_enabled')])
                                <div data-ws-show-when="#product_variations_enabled">
                                    @include($wsToggle, ['name' => 'site_product_list_group_variations', 'label' => 'Összevonás a terméklistán', 'sub' => true,
                                        'hint' => 'Egy variációcsoportból egy kártya, rajta a változatok száma. Kikapcsolva minden változat külön kártya. A keresést és az adatlapot nem érinti.',
                                        'checked' => $wsOn('site_product_list_group_variations', 'true')])
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===== KATEGÓRIÁK ===== --}}
                <div class="tab-pane fade" id="ws-tab-categories" role="tabpanel">
                    <div class="ws-card">
                        <header class="ws-card-head">
                            <span class="ws-card-icon"><i class="fa-solid fa-sitemap"></i></span>
                            <div class="ws-card-heading">
                                <h3 class="ws-card-title">Kategóriarendszer</h3>
                                <span class="ws-card-lead">A bekapcsolt mezők jelennek meg a kategória-szerkesztőben.</span>
                            </div>
                        </header>
                        <div class="ws-toggle-grid">
                            {{-- Egyedi kategória menüsor: csak többszintes kategóriarendszerrel van értelme, ezért
                                 a szülő kapcsolóhoz kötve látszik. A DOM-ban rejtve is ott marad, így mentéskor elmegy. --}}
                            <div class="ws-toggle-tile @if($wsOn('category_parent_enabled')) is-on @endif">
                                @include($wsToggle, ['name' => 'category_parent_enabled', 'label' => 'Többszintű kategóriák', 'icon' => 'fa-sitemap',
                                    'hint' => 'Kategóriák egymás alá rendezése (szülő kategória).', 'checked' => $wsOn('category_parent_enabled')])
                                <div id="category-custom-menu-settings" data-ws-show-when="#category_parent_enabled">
                                    @include($wsToggle, ['name' => 'category_custom_menu_enabled', 'label' => 'Egyedi kategória menüsor', 'sub' => true,
                                        'hint' => 'Vízszintes sáv helyett lenyíló, többszintű menü. A tartalma: <a href="' . route('admin.webshop.extra-settings.category-menu.index') . '">Extra beállítások → Kategória menüsor</a>.',
                                        'checked' => $wsOn('category_custom_menu_enabled')])
                                </div>
                            </div>
                            <div class="ws-toggle-tile @if($wsOn('category_related_enabled')) is-on @endif">
                                @include($wsToggle, ['name' => 'category_related_enabled', 'label' => 'Kapcsolódó kategóriák', 'icon' => 'fa-link',
                                    'hint' => 'További kategóriák ajánlása a kategóriaoldalon.', 'checked' => $wsOn('category_related_enabled')])
                            </div>
                            <div class="ws-toggle-tile is-wide @if($wsOn('category_product_card_properties_enabled')) is-on @endif">
                                @include($wsToggle, ['name' => 'category_product_card_properties_enabled', 'label' => 'Tulajdonságok a termékkártyán', 'icon' => 'fa-list-check',
                                    'hint' => 'Kategóriánként választható, mely tulajdonságcsoportok látszanak a termékkártyán.',
                                    'checked' => $wsOn('category_product_card_properties_enabled')])
                            </div>
                        </div>
                    </div>

                    <div class="ws-card">
                        <header class="ws-card-head">
                            <span class="ws-card-icon"><i class="fa-solid fa-table-cells-large"></i></span>
                            <div class="ws-card-heading"><h3 class="ws-card-title">Megjelenés és feed</h3></div>
                        </header>
                        <div class="ws-toggle-grid">
                            <div class="ws-toggle-tile @if($wsOn('category_icon_enabled')) is-on @endif">
                                @include($wsToggle, ['name' => 'category_icon_enabled', 'label' => 'Kategória ikon', 'icon' => 'fa-icons',
                                    'hint' => 'Ikon az Ikonok könyvtárából.', 'checked' => $wsOn('category_icon_enabled')])
                            </div>
                            <div class="ws-toggle-tile @if($wsOn('category_list_image_enabled')) is-on @endif">
                                @include($wsToggle, ['name' => 'category_list_image_enabled', 'label' => 'Listakép', 'icon' => 'fa-image',
                                    'hint' => 'Saját kép a kategória kártyáján.', 'checked' => $wsOn('category_list_image_enabled')])
                            </div>
                            <div class="ws-toggle-tile @if($wsOn('category_sizing_enabled')) is-on @endif">
                                @include($wsToggle, ['name' => 'category_sizing_enabled', 'label' => 'Kártyaméret', 'icon' => 'fa-ruler-horizontal',
                                    'hint' => 'Kategóriánként 1–4 egység széles kártya.', 'checked' => $wsOn('category_sizing_enabled')])
                            </div>
                            <div class="ws-toggle-tile @if($wsOn('category_merchant_feed_enabled')) is-on @endif">
                                @include($wsToggle, ['name' => 'category_merchant_feed_enabled', 'label' => 'Merchant feed', 'icon' => 'fa-rss',
                                    'hint' => 'Google és Facebook merchant azonosító kategóriánként.', 'checked' => $wsOn('category_merchant_feed_enabled')])
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===== WEBSHOP OLDAL: amit a vásárló lát ===== --}}
                <div class="tab-pane fade" id="ws-tab-storefront" role="tabpanel">
                    <div class="ws-card">
                        <header class="ws-card-head">
                            <span class="ws-card-icon"><i class="fa-solid fa-table-list"></i></span>
                            <div class="ws-card-heading"><h3 class="ws-card-title">Terméklista</h3></div>
                        </header>
                        <div class="ws-toggle-grid">
                            <div class="ws-toggle-tile ws-field-tile">
                                <div class="form-group">
                                    <label for="site_category_cards_per_row">Kártyák soronként</label>
                                    <select class="form-control" id="site_category_cards_per_row" name="site_category_cards_per_row">
                                        <option value="3" @if(($ws['site_category_cards_per_row'] ?? '3') == '3') selected @endif>3 kártya</option>
                                        <option value="4" @if(($ws['site_category_cards_per_row'] ?? '3') == '4') selected @endif>4 kártya</option>
                                    </select>
                                </div>
                            </div>
                            <div class="ws-toggle-tile ws-field-tile">
                                <div class="form-group">
                                    <label for="site_product_list_default_view">Alapértelmezett nézet</label>
                                    <select class="form-control" id="site_product_list_default_view" name="site_product_list_default_view">
                                        <option value="card" @if(($ws['site_product_list_default_view'] ?? 'card') == 'card') selected @endif>Kártyás nézet</option>
                                        <option value="table" @if(($ws['site_product_list_default_view'] ?? 'card') == 'table') selected @endif>Táblázatos nézet</option>
                                    </select>
                                </div>
                            </div>
                            <div class="ws-toggle-tile @if($wsOn('site_category_view_switcher_enabled')) is-on @endif">
                                @include($wsToggle, ['name' => 'site_category_view_switcher_enabled', 'label' => 'Nézetváltó gomb', 'icon' => 'fa-table-cells',
                                    'hint' => 'A vásárló válthat kártyás és táblázatos nézet között.', 'checked' => $wsOn('site_category_view_switcher_enabled')])
                            </div>
                            <div class="ws-toggle-tile @if($wsOn('site_product_prices_visible')) is-on @endif">
                                @include($wsToggle, ['name' => 'site_product_prices_visible', 'label' => 'Árak megjelenítése', 'icon' => 'fa-money-bill-wave',
                                    'hint' => 'Kikapcsolva a webshop ár nélkül mutatja a termékeket.', 'checked' => $wsOn('site_product_prices_visible')])
                            </div>
                            <div class="ws-toggle-tile is-wide @if($wsOn('product_short_desc_instead_of_properties_enabled')) is-on @endif">
                                @include($wsToggle, ['name' => 'product_short_desc_instead_of_properties_enabled', 'label' => 'Rövid leírás a kártyán', 'icon' => 'fa-align-left',
                                    'hint' => 'A terméklista kártyáin a tulajdonság-címkék helyett a rövid leírás első 100 karaktere. Az adatlapot nem érinti: ott a rövid leírás mindig a név alatt látszik.',
                                    'checked' => $wsOn('product_short_desc_instead_of_properties_enabled')])
                            </div>
                        </div>
                    </div>

                    <div class="ws-card">
                        <header class="ws-card-head">
                            <span class="ws-card-icon"><i class="fa-solid fa-wand-magic-sparkles"></i></span>
                            <div class="ws-card-heading"><h3 class="ws-card-title">Vásárlói funkciók</h3></div>
                        </header>
                        <div class="ws-toggle-grid">
                            <div class="ws-toggle-tile @if($wsOn('site_header_search_enabled')) is-on @endif">
                                @include($wsToggle, ['name' => 'site_header_search_enabled', 'label' => 'Kereső a fejlécben', 'icon' => 'fa-magnifying-glass',
                                    'hint' => 'Szabadszavas kereső a kosár és az összehasonlítás gombjától balra.', 'checked' => $wsOn('site_header_search_enabled')])
                            </div>
                            <div class="ws-toggle-tile @if($wsOn('site_product_compare_enabled')) is-on @endif">
                                @include($wsToggle, ['name' => 'site_product_compare_enabled', 'label' => 'Termék-összehasonlítás', 'icon' => 'fa-scale-balanced',
                                    'hint' => 'Termékek egymás melletti összevetése.', 'checked' => $wsOn('site_product_compare_enabled')])
                            </div>
                            <div class="ws-toggle-tile @if($wsOn('site_related_products_modal_enabled')) is-on @endif">
                                @include($wsToggle, ['name' => 'site_related_products_modal_enabled', 'label' => 'Ajánló ablak kosárba tételkor', 'icon' => 'fa-window-restore',
                                    'hint' => 'Felugró ablakban ajánlja a termék kapcsolódó termékeit.', 'checked' => $wsOn('site_related_products_modal_enabled')])
                            </div>
                        </div>
                    </div>

                    {{-- A termékoldal a vélemények mellett ezek alapján tájékoztat arról, hogyan kezeljük és
                         ellenőrizzük őket (Fttv., Omnibusz) – a szöveg a beállításból készül. --}}
                    <div class="ws-card">
                        <header class="ws-card-head">
                            <span class="ws-card-icon"><i class="fa-solid fa-star"></i></span>
                            <div class="ws-card-heading"><h3 class="ws-card-title">Vélemények</h3></div>
                        </header>
                        <div class="ws-toggle-grid">
                            <div class="ws-toggle-tile is-wide @if($wsOn('site_product_reviews_enabled')) is-on @endif">
                                @include($wsToggle, ['name' => 'site_product_reviews_enabled', 'label' => 'Termékvélemények', 'icon' => 'fa-star',
                                    'hint' => 'A vásárlók értékelést írhatnak a termékoldalon.', 'checked' => $wsOn('site_product_reviews_enabled')])
                                <div data-ws-show-when="#site_product_reviews_enabled">
                                    @include($wsToggle, ['name' => 'site_product_reviews_moderation', 'label' => 'Megjelenés jóváhagyás után', 'sub' => true,
                                        'hint' => 'Az új vélemény csak az admin jóváhagyása után látszik.', 'checked' => $wsOn('site_product_reviews_moderation', 'true')])
                                    @include($wsToggle, ['name' => 'site_product_reviews_verified_only', 'label' => 'Csak igazolt vásárlók', 'sub' => true,
                                        'hint' => 'Igazolás: rendelésszám és e-mail-cím. Kikapcsolva is igazolhatja a vásárlást, aki akarja — az ilyen vélemény „Ellenőrzött vásárló” jelölést kap.',
                                        'checked' => $wsOn('site_product_reviews_verified_only')])
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===== PÉNZTÁR ===== --}}
                <div class="tab-pane fade" id="ws-tab-checkout" role="tabpanel">
                    <div class="ws-card">
                        <header class="ws-card-head">
                            <span class="ws-card-icon"><i class="fa-solid fa-cart-shopping"></i></span>
                            <div class="ws-card-heading"><h3 class="ws-card-title">Rendelés módja</h3></div>
                        </header>
                        <div class="form-group" style="max-width: 420px">
                            <label for="site_checkout_mode">Pénztár működése</label>
                            <select class="form-control" id="site_checkout_mode" name="site_checkout_mode">
                                <option value="order" @if(($ws['site_checkout_mode'] ?? 'order') == 'order') selected @endif>Rendelés leadása</option>
                                <option value="quote" @if(($ws['site_checkout_mode'] ?? 'order') == 'quote') selected @endif>Ajánlatkérés</option>
                            </select>
                            <span class="ws-hint">Ajánlatkérés módban a fizetési és szállítási módok nem jelennek meg.</span>
                        </div>
                    </div>

                    {{-- Rendelés leadása módban megjelenő extra beállítások --}}
                    <div id="checkout-order-settings" data-ws-show-when="#site_checkout_mode" data-ws-show-value="order">
                        <div class="ws-card">
                            <header class="ws-card-head">
                                <span class="ws-card-icon"><i class="fa-solid fa-credit-card"></i></span>
                                <div class="ws-card-heading"><h3 class="ws-card-title">Fizetési módok</h3></div>
                            </header>
                            <div class="ws-toggle-tile @if($wsOn('site_checkout_payment_options_enabled')) is-on @endif">
                                @include($wsToggle, ['name' => 'site_checkout_payment_options_enabled', 'label' => 'Fizetési lehetőségek', 'icon' => 'fa-credit-card',
                                    'hint' => 'A pénztárban választható fizetési módok.', 'checked' => $wsOn('site_checkout_payment_options_enabled')])
                                <div class="ws-toggle-sub" id="checkout-payment-options-settings" data-ws-show-when="#site_checkout_payment_options_enabled">
                                    <div class="ws-check-list">
                                        <div class="ws-check-item">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="site_checkout_payment_online_enabled" name="site_checkout_payment_online_enabled"
                                                       @if($wsOn('site_checkout_payment_online_enabled')) checked @endif>
                                                <label class="custom-control-label fw-600" for="site_checkout_payment_online_enabled">Online fizetés</label>
                                            </div>
                                            <div class="ws-check-extra" id="checkout-online-payment-providers" data-ws-show-when="#site_checkout_payment_online_enabled">
                                                @forelse($onlinePaymentProviders as $code => $provider)
                                                    <div class="d-flex align-items-center flex-wrap mb-1">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input"
                                                                   id="site_checkout_payment_method_{{ $code }}_enabled"
                                                                   name="site_checkout_payment_method_{{ $code }}_enabled"
                                                                   @if($provider['enabled']) checked @endif>
                                                            <label class="custom-control-label" for="site_checkout_payment_method_{{ $code }}_enabled">
                                                                {{ $provider['name'] }}
                                                                @if(empty($provider['active']))
                                                                    <span class="badge badge-warning ml-1">nincs bekapcsolva</span>
                                                                @endif
                                                            </label>
                                                        </div>
                                                        @if(!empty($provider['settings_url']))
                                                            <a href="{{ $provider['settings_url'] }}" class="ws-check-link ml-2"><i class="fa fa-cog"></i> Beállítások</a>
                                                        @endif
                                                    </div>
                                                @empty
                                                    <span class="ws-hint mt-0">Nincs telepítve online fizetési integráció.</span>
                                                @endforelse
                                            </div>
                                        </div>
                                        <div class="ws-check-item">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="site_checkout_payment_cod_enabled" name="site_checkout_payment_cod_enabled"
                                                       @if($wsOn('site_checkout_payment_cod_enabled')) checked @endif>
                                                <label class="custom-control-label fw-600" for="site_checkout_payment_cod_enabled">Utánvét</label>
                                            </div>
                                            <div class="ws-check-extra" data-ws-show-when="#site_checkout_payment_cod_enabled">
                                                <div class="form-group mb-0">
                                                    <label for="site_checkout_payment_cod_fee" class="fs-14">Utánvét felára <span class="font-weight-normal text-muted">(Ft)</span></label>
                                                    <input type="number" min="0" step="1" class="form-control form-control-sm ws-money"
                                                           id="site_checkout_payment_cod_fee" name="site_checkout_payment_cod_fee"
                                                           value="{{ $ws['site_checkout_payment_cod_fee'] ?? 0 }}">
                                                    <span class="ws-hint">Külön soron jelenik meg a pénztár összesítőjében, és a végösszegbe is beleszámít. 0 esetén nincs felár, és a sor sem látszik.</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="ws-check-item">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="site_checkout_payment_bank_transfer_enabled" name="site_checkout_payment_bank_transfer_enabled"
                                                       @if($wsOn('site_checkout_payment_bank_transfer_enabled')) checked @endif>
                                                <label class="custom-control-label fw-600" for="site_checkout_payment_bank_transfer_enabled">Előre utalás</label>
                                            </div>
                                        </div>
                                        <div class="ws-check-item">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="site_checkout_payment_on_site_enabled" name="site_checkout_payment_on_site_enabled"
                                                       @if($wsOn('site_checkout_payment_on_site_enabled')) checked @endif>
                                                <label class="custom-control-label fw-600" for="site_checkout_payment_on_site_enabled">Fizetés a helyszínen</label>
                                            </div>
                                            <span class="ws-hint mt-0">Személyes átvételnél jelenik meg.</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="ws-card">
                            <header class="ws-card-head">
                                <span class="ws-card-icon"><i class="fa-solid fa-truck-fast"></i></span>
                                <div class="ws-card-heading"><h3 class="ws-card-title">Szállítási módok</h3></div>
                            </header>
                            <div class="ws-toggle-tile @if($wsOn('site_checkout_shipping_options_enabled')) is-on @endif">
                                @include($wsToggle, ['name' => 'site_checkout_shipping_options_enabled', 'label' => 'Szállítási lehetőségek', 'icon' => 'fa-truck-fast',
                                    'hint' => 'A pénztárban választható szállítási módok.', 'checked' => $wsOn('site_checkout_shipping_options_enabled')])
                                <div class="ws-toggle-sub" id="checkout-shipping-options-settings" data-ws-show-when="#site_checkout_shipping_options_enabled">
                                    {{-- A csoportok (házhoz szállítás, csomagpont, személyes átvétel) alatt a telepített
                                         futárszolgálatok jelennek meg – a lista a commerce-core provider-nyilvántartásából
                                         jön, így egy új csomag (MPL, FoxPost…) itt külön módosítás nélkül megjelenik. --}}
                                    <div class="ws-check-list">
                                        @foreach($shippingGroups as $kind => $group)
                                            <div class="ws-check-item">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input"
                                                           id="{{ $group['key'] }}" name="{{ $group['key'] }}"
                                                           @if($group['enabled']) checked @endif>
                                                    <label class="custom-control-label fw-600" for="{{ $group['key'] }}">{{ $group['label'] }}</label>
                                                </div>

                                                @if($group['has_providers'])
                                                    <div class="ws-check-extra" id="shipping-group-{{ $kind }}" data-ws-show-when="#{{ $group['key'] }}">
                                                        @forelse($group['methods'] as $code => $method)
                                                            <div class="d-flex align-items-center flex-wrap mb-1">
                                                                <div class="custom-control custom-checkbox">
                                                                    <input type="checkbox" class="custom-control-input"
                                                                           id="site_checkout_shipping_method_{{ $code }}_enabled"
                                                                           name="site_checkout_shipping_method_{{ $code }}_enabled"
                                                                           @if($method['enabled']) checked @endif>
                                                                    <label class="custom-control-label" for="site_checkout_shipping_method_{{ $code }}_enabled">
                                                                        {{ $method['name'] }}
                                                                        @if(empty($method['active']))
                                                                            <span class="badge badge-warning ml-1">nincs bekapcsolva</span>
                                                                        @endif
                                                                    </label>
                                                                </div>
                                                                @if(!empty($method['settings_url']))
                                                                    <a href="{{ $method['settings_url'] }}" class="ws-check-link ml-2"><i class="fa fa-cog"></i> Beállítások</a>
                                                                @endif
                                                            </div>

                                                            {{-- A saját díjazású módoknak (saját futárszolgálat, MPL) nincs külső
                                                                 díjszámítása: a díjat itt, helyben kell megadni. A lista:
                                                                 WebshopCommerceService::MANUALLY_PRICED_SHIPPING_METHODS --}}
                                                            @if(in_array($code, \Weboldalnet\WebshopAiDefault\Services\Webshop\Commerce\WebshopCommerceService::MANUALLY_PRICED_SHIPPING_METHODS, true))
                                                                @php
                                                                    $wsFeeKey = 'site_checkout_shipping_' . $code . '_fee';
                                                                    $wsFreeAboveKey = 'site_checkout_shipping_' . $code . '_free_above';
                                                                @endphp
                                                                <div class="form-row pl-4 mb-2" id="{{ $code }}-fee-settings"
                                                                     data-ws-show-when="#site_checkout_shipping_method_{{ $code }}_enabled">
                                                                    <div class="form-group col-sm-6 mb-2">
                                                                        <label for="{{ $wsFeeKey }}" class="fs-14">Szállítási díj <span class="font-weight-normal text-muted">(Ft)</span></label>
                                                                        <input type="number" min="0" step="1" class="form-control form-control-sm ws-money"
                                                                               id="{{ $wsFeeKey }}" name="{{ $wsFeeKey }}"
                                                                               value="{{ $ws[$wsFeeKey] ?? 0 }}">
                                                                        <span class="ws-hint">A pénztárban a mód mellett és az összesítőben jelenik meg.</span>
                                                                    </div>
                                                                    <div class="form-group col-sm-6 mb-2">
                                                                        <label for="{{ $wsFreeAboveKey }}" class="fs-14">Ingyenes ettől <span class="font-weight-normal text-muted">(Ft)</span></label>
                                                                        <input type="number" min="0" step="1" class="form-control form-control-sm ws-money"
                                                                               id="{{ $wsFreeAboveKey }}" name="{{ $wsFreeAboveKey }}"
                                                                               value="{{ $ws[$wsFreeAboveKey] ?? '' }}">
                                                                        <span class="ws-hint">Kosárérték-határ. Üresen vagy 0-nál nincs ingyenes szállítás.</span>
                                                                    </div>
                                                                    @if($code === 'mpl')
                                                                        <span class="ws-hint col-12 mt-0">
                                                                            Az MPL-lel leadott rendeléseket az ügyfélszolgálat kézzel rögzíti az MPL rendszerében –
                                                                            ehhez nincs automatikus kapcsolat.
                                                                        </span>
                                                                    @endif
                                                                </div>
                                                            @endif
                                                        @empty
                                                            <span class="ws-hint mt-0">Ehhez nincs telepítve futárszolgálat-integráció.</span>
                                                        @endforelse
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="ws-card">
                        <header class="ws-card-head">
                            <span class="ws-card-icon"><i class="fa-solid fa-address-card"></i></span>
                            <div class="ws-card-heading">
                                <h3 class="ws-card-title">Pénztár mezői</h3>
                                <span class="ws-card-lead">A bejelölt mezők is megjelennek a pénztárban.</span>
                            </div>
                        </header>
                        <div class="ws-toggle-grid">
                            @foreach([
                                'site_checkout_phone_enabled' => 'Telefonszám',
                                'site_checkout_company_enabled' => 'Cégnév',
                                'site_checkout_tax_number_enabled' => 'Adószám',
                                'site_checkout_billing_enabled' => 'Számlázási adatok',
                                'site_checkout_shipping_enabled' => 'Szállítási adatok',
                            ] as $wsFieldKey => $wsFieldLabel)
                                <div class="ws-check-item">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="{{ $wsFieldKey }}" name="{{ $wsFieldKey }}"
                                               @if($wsOn($wsFieldKey)) checked @endif>
                                        <label class="custom-control-label fw-600" for="{{ $wsFieldKey }}">{{ $wsFieldLabel }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="ws-card">
                        <header class="ws-card-head">
                            <span class="ws-card-icon"><i class="fa-solid fa-ticket"></i></span>
                            <div class="ws-card-heading"><h3 class="ws-card-title">Kuponok</h3></div>
                        </header>
                        <div class="ws-toggle-tile @if($wsOn('coupons_enabled')) is-on @endif">
                            @include($wsToggle, ['name' => 'coupons_enabled', 'label' => 'Kuponbeváltás', 'icon' => 'fa-ticket',
                                'hint' => 'Kikapcsolva a Kuponok menüpont eltűnik, és a pénztáron sem fut kupon-lekérdezés.', 'checked' => $wsOn('coupons_enabled')])
                        </div>
                        @if(!\Illuminate\Support\Facades\Route::has('admin.webshop.coupons.index'))
                            <div class="alert alert-warning mb-0 mt-2 py-2 px-3 small">
                                <i class="fa fa-exclamation-triangle mr-1"></i>
                                A kupon modul (commerce-coupons) nincs telepítve, a kapcsoló önmagában nem elég.
                            </div>
                        @endif
                    </div>
                </div>

                {{-- ===== SZÁMLÁZÁS ===== --}}
                <div class="tab-pane fade" id="ws-tab-invoicing" role="tabpanel">
                    <div class="ws-card">
                        <div class="ws-toggle-tile @if($invoicingEnabled) is-on @endif">
                            @include($wsToggle, ['name' => 'invoicing_enabled', 'label' => 'Számlázás', 'icon' => 'fa-file-invoice',
                                'hint' => 'Kikapcsolva a Számlák menüpont és a rendeléseknél a számlázási műveletek sem jelennek meg.', 'checked' => $invoicingEnabled])
                        </div>
                    </div>

                    <div id="invoicing-settings" data-ws-show-when="#invoicing_enabled">
                        <div class="ws-card">
                            <header class="ws-card-head">
                                <span class="ws-card-icon"><i class="fa-solid fa-plug"></i></span>
                                <div class="ws-card-heading"><h3 class="ws-card-title">Számlázó program</h3></div>
                            </header>
                            @if(empty($invoiceProviders))
                                <div class="alert alert-warning mb-0 py-2 px-3 small">
                                    <i class="fa fa-exclamation-triangle mr-1"></i>
                                    Nincs telepítve számlázó integráció, ezért a számlázás nem használható.
                                </div>
                            @else
                                <div class="ws-toggle-grid">
                                    @foreach($invoiceProviders as $providerCode => $providerMeta)
                                        <div class="ws-check-item">
                                            <div class="custom-control custom-radio">
                                                <input type="radio" class="custom-control-input"
                                                       id="invoicing_provider_{{ $providerCode }}"
                                                       name="invoicing_provider" value="{{ $providerCode }}"
                                                       @if($invoicingProvider === $providerCode) checked @endif>
                                                <label class="custom-control-label fw-600" for="invoicing_provider_{{ $providerCode }}">
                                                    {{ $providerMeta['name'] ?? $providerCode }}
                                                    @if(empty($providerMeta['active']))
                                                        <span class="badge badge-warning ml-1">nincs bekapcsolva</span>
                                                    @endif
                                                </label>
                                            </div>
                                            @if(!empty($providerMeta['settings_url']))
                                                <a href="{{ $providerMeta['settings_url'] }}" class="ws-check-link"><i class="fa fa-cog"></i> Beállítások</a>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>

                                @php $selectedProvider = $invoiceProviders[$invoicingProvider] ?? null; @endphp

                                @if($selectedProvider && empty($selectedProvider['active']))
                                    <div class="alert alert-warning mt-3 mb-0 py-2 px-3 small">
                                        <i class="fa fa-exclamation-triangle mr-1"></i>
                                        A kiválasztott integráció a saját beállítófelületén nincs bekapcsolva,
                                        ezért a számla kiállítása hibára fut.
                                    </div>
                                @endif
                            @endif
                        </div>

                        {{-- A számla tételei. Kikapcsolt számlázásnál a blokk rejtve van, de a DOM-ban ott marad,
                             tehát a kapcsolói a mentéssel továbbra is elmennek. Ez fontos: egy be nem küldött
                             checkbox megkülönböztethetetlen a kikapcsolttól, így a mentés némán kikapcsolná mind a
                             hármat. A rejtett invoice_lines_present mező a controller oldalán is bebiztosítja ezt. --}}
                        <div class="ws-card">
                            <header class="ws-card-head">
                                <span class="ws-card-icon"><i class="fa-solid fa-list-ol"></i></span>
                                <div class="ws-card-heading">
                                    <h3 class="ws-card-title">Számla tételei</h3>
                                    <span class="ws-card-lead">A termékek mellett ezek kerülnek a számlára. Amit kézzel viszel fel a számlázó programban, azt itt kapcsold ki, hogy ne legyen dupla tétel.</span>
                                </div>
                            </header>
                            <input type="hidden" name="invoice_lines_present" value="1">

                            <div class="ws-toggle-grid">
                                @foreach($invoiceExtraLines as $lineKey => $lineMeta)
                                    @php
                                        $lineEnabled = ($ws['invoice_line_' . $lineKey . '_enabled'] ?? 'true') === 'true';
                                        $lineName = $ws['invoice_line_' . $lineKey . '_name'] ?? '';
                                    @endphp
                                    <div class="ws-toggle-tile @if($lineEnabled) is-on @endif">
                                        @include($wsToggle, ['name' => 'invoice_line_' . $lineKey . '_enabled', 'label' => $lineMeta['admin_label'], 'icon' => 'fa-receipt',
                                            'checked' => $lineEnabled, 'inputClass' => 'js-invoice-line', 'attrs' => 'data-line="' . e($lineKey) . '"'])
                                        <div class="ws-toggle-sub js-invoice-line-name" data-line="{{ $lineKey }}" data-ws-show-when="#invoice_line_{{ $lineKey }}_enabled">
                                            <div class="form-group">
                                                <label for="invoice_line_{{ $lineKey }}_name">Tétel neve</label>
                                                <input type="text" class="form-control form-control-sm"
                                                       id="invoice_line_{{ $lineKey }}_name"
                                                       name="invoice_line_{{ $lineKey }}_name"
                                                       value="{{ $lineName }}"
                                                       placeholder="{{ $lineMeta['default_name'] }}">
                                                <span class="ws-hint">Pontosan így kerül a számlára. Üresen: „{{ $lineMeta['default_name'] }}”, a futárszolgálat nevével, illetve a kuponkóddal kiegészítve.</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="alert alert-warning mt-3 mb-0 py-2 px-3 small js-invoice-line-warning" style="display:none;">
                                <i class="fa fa-exclamation-triangle mr-1"></i>
                                Kikapcsolt tétel esetén a számla végösszege nem fogja kiadni a rendelés
                                végösszegét – a hiányzó tételt kézzel kell felvinni a számlázó programban.
                            </div>

                            <div class="form-group mt-4 mb-0" style="max-width: 420px">
                                <label for="invoice_vat_key">Áfakulcs a számlán</label>
                                <select class="form-control" id="invoice_vat_key" name="invoice_vat_key">
                                    @foreach($invoiceVatKeys as $vatKey => $vatLabel)
                                        <option value="{{ $vatKey }}" @if($invoiceVatKey === (string) $vatKey) selected @endif>
                                            {{ $vatLabel }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="ws-hint">
                                    A számla MINDEN tételére ez kerül, a termékekre is – a webshop nem tárol termékenkénti
                                    áfakulcsot. Alanyi adómentes boltnál tehát AAM-re állítsd, és az egész számla AAM lesz.
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="admin-save-box">
                <div class="admin-save-btn-container mx-auto">
                    <button type="submit" class="btn btn-primary fs-18 fw-600"><i class="fa fa-save"></i> Beállítások mentése</button>
                </div>
            </div>
        </form>
    </div>

    <link rel="stylesheet" href="/packages/webshop/admin/css/webshop-admin.css">
    <script src="/packages/webshop/admin/js/webshop-admin.js"></script>
    <script>
        WebshopAdmin.initToggleTiles('.ws-editor');
        WebshopAdmin.initSettingsTabs('.js-ws-settings-tabs', 'ws-settings-tab');

        // A számla extra tételei: ha bármelyik ki van kapcsolva, figyelmeztetünk, hogy a számla
        // végösszege nem fogja kiadni a rendelés végösszegét.
        (function () {
            function toggleInvoiceLineWarning() {
                var hasDisabledLine = false;
                document.querySelectorAll('.js-invoice-line').forEach(function (cb) {
                    if (!cb.checked) hasDisabledLine = true;
                });
                var notice = document.querySelector('.js-invoice-line-warning');
                if (notice) notice.style.display = hasDisabledLine ? '' : 'none';
            }

            document.querySelectorAll('.js-invoice-line').forEach(function (cb) {
                cb.addEventListener('change', toggleInvoiceLineWarning);
            });
            toggleInvoiceLineWarning();
        })();
    </script>
@endsection

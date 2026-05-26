@extends('admin.layouts.layout')
@section('title', 'Webshop beállítások')

@section('content')
    <div class="container mt-lg-4 mt-3 mb-150">
        @include('admin.webshop.partials.alerts')

        <div class="row"><div class="col-lg-12"><h2 class="header-box"><i class="fa fa-cog"></i> Webshop beállítások</h2></div></div>

        <form method="POST" action="{{ route('admin.webshop.settings.update') }}">
            @csrf
            <div class="row">
                <div class="col-lg-6 mb-3">
                    <h2 class="header-box product-info">Kategória beállítások</h2>
                    <div class="content-box bordered">
                        <div class="custom-control custom-switch mb-3">
                            <input type="checkbox" class="custom-control-input" id="category_icon_enabled" name="category_icon_enabled"
                                   @if(($ws['category_icon_enabled'] ?? 'false') === 'true') checked @endif>
                            <label class="custom-control-label" for="category_icon_enabled">Ikon feltöltés engedélyezése</label>
                        </div>
                        <div class="custom-control custom-switch mb-3">
                            <input type="checkbox" class="custom-control-input" id="category_parent_enabled" name="category_parent_enabled"
                                   @if(($ws['category_parent_enabled'] ?? 'false') === 'true') checked @endif>
                            <label class="custom-control-label" for="category_parent_enabled">Több szintű kategória / szülő kategória engedélyezése</label>
                        </div>
                        <div class="custom-control custom-switch mb-3">
                            <input type="checkbox" class="custom-control-input" id="category_related_enabled" name="category_related_enabled"
                                   @if(($ws['category_related_enabled'] ?? 'false') === 'true') checked @endif>
                            <label class="custom-control-label" for="category_related_enabled">Kapcsolódó kategóriák engedélyezése</label>
                        </div>
                        <div class="custom-control custom-switch mb-3">
                            <input type="checkbox" class="custom-control-input" id="category_product_card_properties_enabled" name="category_product_card_properties_enabled"
                                   @if(($ws['category_product_card_properties_enabled'] ?? 'false') === 'true') checked @endif>
                            <label class="custom-control-label" for="category_product_card_properties_enabled">Termékkártyán megjelenő tulajdonság kategóriák engedélyezése</label>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-3">
                    <h2 class="header-box product-info">Termék beállítások</h2>
                    <div class="content-box bordered">
                        <div class="custom-control custom-switch mb-3">
                            <input type="checkbox" class="custom-control-input" id="product_stock_enabled" name="product_stock_enabled"
                                   @if(($ws['product_stock_enabled'] ?? 'false') === 'true') checked @endif>
                            <label class="custom-control-label" for="product_stock_enabled">Készlet kezelés engedélyezése</label>
                        </div>
                        <div class="custom-control custom-switch mb-3">
                            <input type="checkbox" class="custom-control-input" id="product_related_enabled" name="product_related_enabled"
                                   @if(($ws['product_related_enabled'] ?? 'false') === 'true') checked @endif>
                            <label class="custom-control-label" for="product_related_enabled">Kapcsolódó termékek engedélyezése</label>
                        </div>
                        <div class="custom-control custom-switch mb-3">
                            <input type="checkbox" class="custom-control-input" id="admin_product_labels_enabled" name="admin_product_labels_enabled"
                                   @if(($ws['admin_product_labels_enabled'] ?? 'false') === 'true') checked @endif>
                            <label class="custom-control-label" for="admin_product_labels_enabled">Termék címkék kezelése engedélyezése</label>
                        </div>
                        <div class="custom-control custom-switch mb-3">
                            <input type="checkbox" class="custom-control-input" id="product_price_enabled" name="product_price_enabled"
                                   @if(($ws['product_price_enabled'] ?? 'false') === 'true') checked @endif>
                            <label class="custom-control-label" for="product_price_enabled">Ár és akciós ár engedélyezése</label>
                        </div>
                        <div class="custom-control custom-switch mb-3">
                            <input type="checkbox" class="custom-control-input" id="product_gallery_enabled" name="product_gallery_enabled"
                                   @if(($ws['product_gallery_enabled'] ?? 'false') === 'true') checked @endif>
                            <label class="custom-control-label" for="product_gallery_enabled">Galéria engedélyezése</label>
                        </div>
                        <div class="custom-control custom-switch mb-3">
                            <input type="checkbox" class="custom-control-input" id="product_variations_enabled" name="product_variations_enabled"
                                   @if(($ws['product_variations_enabled'] ?? 'false') === 'true') checked @endif>
                            <label class="custom-control-label" for="product_variations_enabled">Variációs termékek engedélyezése</label>
                        </div>
                    </div>
                </div>
            </div>

            <hr>

            <div class="row">
                <div class="col-lg-6 mb-3">
                    <h2 class="header-box product-info">Site Kategória beállítások</h2>
                    <div class="content-box bordered">
                        <div class="custom-control custom-switch mb-3">
                            <input type="checkbox" class="custom-control-input" id="site_category_view_switcher_enabled" name="site_category_view_switcher_enabled"
                                   @if(($ws['site_category_view_switcher_enabled'] ?? 'false') === 'true') checked @endif>
                            <label class="custom-control-label" for="site_category_view_switcher_enabled">Nézet váltó gomb látható-e</label>
                        </div>
                        <div class="form-group">
                            <label for="site_category_cards_per_row">Kártyák száma egy sorban</label>
                            <select class="form-control" id="site_category_cards_per_row" name="site_category_cards_per_row">
                                <option value="3" @if(($ws['site_category_cards_per_row'] ?? '3') == '3') selected @endif>3 kártya</option>
                                <option value="4" @if(($ws['site_category_cards_per_row'] ?? '3') == '4') selected @endif>4 kártya</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="site_product_list_default_view">Alapértelmezett terméklista nézet</label>
                            <select class="form-control" id="site_product_list_default_view" name="site_product_list_default_view">
                                <option value="card" @if(($ws['site_product_list_default_view'] ?? 'card') == 'card') selected @endif>Kártyás nézet</option>
                                <option value="table" @if(($ws['site_product_list_default_view'] ?? 'card') == 'table') selected @endif>Táblázatos nézet</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-3">
                    <h2 class="header-box product-info">Site Termék beállítások</h2>
                    <div class="content-box bordered">
                        <div class="custom-control custom-switch mb-3">
                            <input type="checkbox" class="custom-control-input" id="site_related_products_modal_enabled" name="site_related_products_modal_enabled"
                                   @if(($ws['site_related_products_modal_enabled'] ?? 'false') === 'true') checked @endif>
                            <label class="custom-control-label" for="site_related_products_modal_enabled">Kapcsolódó termékek modal felugrik-e</label>
                        </div>
                        <div class="custom-control custom-switch mb-3">
                            <input type="checkbox" class="custom-control-input" id="site_product_reviews_enabled" name="site_product_reviews_enabled"
                                   @if(($ws['site_product_reviews_enabled'] ?? 'false') === 'true') checked @endif>
                            <label class="custom-control-label" for="site_product_reviews_enabled">Lehessen véleményeket írni</label>
                        </div>
                        <div class="custom-control custom-switch mb-3">
                            <input type="checkbox" class="custom-control-input" id="site_product_prices_visible" name="site_product_prices_visible"
                                   @if(($ws['site_product_prices_visible'] ?? 'false') === 'true') checked @endif>
                            <label class="custom-control-label" for="site_product_prices_visible">Árak látszódnak-e</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 mb-3">
                    <h2 class="header-box product-info">Site Checkout beállítások</h2>
                    <div class="content-box bordered">
                        <div class="form-group">
                            <label for="site_checkout_mode">Checkout mód</label>
                            <select class="form-control" id="site_checkout_mode" name="site_checkout_mode">
                                <option value="order" @if(($ws['site_checkout_mode'] ?? 'order') == 'order') selected @endif>Rendelés leadása</option>
                                <option value="quote" @if(($ws['site_checkout_mode'] ?? 'order') == 'quote') selected @endif>Ajánlatkérés</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox mb-2">
                                    <input type="checkbox" class="custom-control-input" id="site_checkout_phone_enabled" name="site_checkout_phone_enabled"
                                           @if(($ws['site_checkout_phone_enabled'] ?? 'false') === 'true') checked @endif>
                                    <label class="custom-control-label" for="site_checkout_phone_enabled">Telefonszám</label>
                                </div>
                                <div class="custom-control custom-checkbox mb-2">
                                    <input type="checkbox" class="custom-control-input" id="site_checkout_company_enabled" name="site_checkout_company_enabled"
                                           @if(($ws['site_checkout_company_enabled'] ?? 'false') === 'true') checked @endif>
                                    <label class="custom-control-label" for="site_checkout_company_enabled">Cégnév</label>
                                </div>
                                <div class="custom-control custom-checkbox mb-2">
                                    <input type="checkbox" class="custom-control-input" id="site_checkout_tax_number_enabled" name="site_checkout_tax_number_enabled"
                                           @if(($ws['site_checkout_tax_number_enabled'] ?? 'false') === 'true') checked @endif>
                                    <label class="custom-control-label" for="site_checkout_tax_number_enabled">Adószám</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox mb-2">
                                    <input type="checkbox" class="custom-control-input" id="site_checkout_billing_enabled" name="site_checkout_billing_enabled"
                                           @if(($ws['site_checkout_billing_enabled'] ?? 'false') === 'true') checked @endif>
                                    <label class="custom-control-label" for="site_checkout_billing_enabled">Számlázási adatok</label>
                                </div>
                                <div class="custom-control custom-checkbox mb-2">
                                    <input type="checkbox" class="custom-control-input" id="site_checkout_shipping_enabled" name="site_checkout_shipping_enabled"
                                           @if(($ws['site_checkout_shipping_enabled'] ?? 'false') === 'true') checked @endif>
                                    <label class="custom-control-label" for="site_checkout_shipping_enabled">Szállítási adatok</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-3">
                    <h2 class="header-box product-info">Egyéb Site beállítások</h2>
                    <div class="content-box bordered">
                        <div class="custom-control custom-switch mb-3">
                            <input type="checkbox" class="custom-control-input" id="site_product_compare_enabled" name="site_product_compare_enabled"
                                   @if(($ws['site_product_compare_enabled'] ?? 'false') === 'true') checked @endif>
                            <label class="custom-control-label" for="site_product_compare_enabled">Termék összehasonlítás</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-3">
                <button type="submit" class="btn btn-primary fs-18 font-weight-bold"><i class="fa fa-save"></i> Beállítások mentése</button>
            </div>
        </form>
    </div>
    <link rel="stylesheet" href="/packages/webshop/admin/css/webshop-admin.css">
@endsection

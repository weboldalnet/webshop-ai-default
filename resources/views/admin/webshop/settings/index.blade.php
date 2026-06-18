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
                        <hr>
                        <div class="custom-control custom-switch mb-3">
                            <input type="checkbox" class="custom-control-input" id="category_sizing_enabled" name="category_sizing_enabled"
                                   @if(($ws['category_sizing_enabled'] ?? 'false') === 'true') checked @endif>
                            <label class="custom-control-label" for="category_sizing_enabled">Termék kategória méretezése</label>
                        </div>
                        <div class="custom-control custom-switch mb-3">
                            <input type="checkbox" class="custom-control-input" id="category_list_image_enabled" name="category_list_image_enabled"
                                   @if(($ws['category_list_image_enabled'] ?? 'false') === 'true') checked @endif>
                            <label class="custom-control-label" for="category_list_image_enabled">Termék kategória listakép</label>
                        </div>
                        <div class="custom-control custom-switch mb-3">
                            <input type="checkbox" class="custom-control-input" id="category_merchant_feed_enabled" name="category_merchant_feed_enabled"
                                   @if(($ws['category_merchant_feed_enabled'] ?? 'false') === 'true') checked @endif>
                            <label class="custom-control-label" for="category_merchant_feed_enabled">Google és Facebook merchant feed</label>
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
                        <hr>
                        <div class="custom-control custom-switch mb-3">
                            <input type="checkbox" class="custom-control-input" id="product_extra_gallery_enabled" name="product_extra_gallery_enabled"
                                   @if(($ws['product_extra_gallery_enabled'] ?? 'false') === 'true') checked @endif>
                            <label class="custom-control-label" for="product_extra_gallery_enabled">Extra látvány galéria</label>
                        </div>
                        <div class="custom-control custom-switch mb-3">
                            <input type="checkbox" class="custom-control-input" id="product_document_upload_enabled" name="product_document_upload_enabled"
                                   @if(($ws['product_document_upload_enabled'] ?? 'false') === 'true') checked @endif>
                            <label class="custom-control-label" for="product_document_upload_enabled">Dokumentum feltöltés</label>
                        </div>
                        <div class="custom-control custom-switch mb-3">
                            <input type="checkbox" class="custom-control-input" id="product_secondary_name_enabled" name="product_secondary_name_enabled"
                                   @if(($ws['product_secondary_name_enabled'] ?? 'false') === 'true') checked @endif>
                            <label class="custom-control-label" for="product_secondary_name_enabled">Termék másodlagos név</label>
                        </div>
                        <div class="custom-control custom-switch mb-3">
                            <input type="checkbox" class="custom-control-input" id="product_short_desc_instead_of_properties_enabled" name="product_short_desc_instead_of_properties_enabled"
                                   @if(($ws['product_short_desc_instead_of_properties_enabled'] ?? 'false') === 'true') checked @endif>
                            <label class="custom-control-label" for="product_short_desc_instead_of_properties_enabled">Rövid leírás tulajdonságok helyett</label>
                        </div>
                        <div class="custom-control custom-switch mb-3">
                            <input type="checkbox" class="custom-control-input" id="product_crm_id_enabled" name="product_crm_id_enabled"
                                   @if(($ws['product_crm_id_enabled'] ?? 'false') === 'true') checked @endif>
                            <label class="custom-control-label" for="product_crm_id_enabled">CRM azonosító megadása</label>
                        </div>

                        <div class="form-group">
                            <label for="admin_product_primary_image_mode">Termék elsődleges kép feltöltési módja</label>
                            <select class="form-control" id="admin_product_primary_image_mode" name="admin_product_primary_image_mode">
                                <option value="cropper" @if(($ws['admin_product_primary_image_mode'] ?? 'cropper') == 'cropper') selected @endif>Képmetsző (Cropper)</option>
                                <option value="simple" @if(($ws['admin_product_primary_image_mode'] ?? 'cropper') == 'simple') selected @endif>Egyszerű képfeltöltés</option>
                                <option value="simple_white" @if(($ws['admin_product_primary_image_mode'] ?? 'cropper') == 'simple_white') selected @endif>Egyszerű képfeltöltés fehér háttérrel</option>
                            </select>
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
                            <input type="checkbox" class="custom-control-input" id="site_home_page_editor_enabled" name="site_home_page_editor_enabled"
                                   @if(($ws['site_home_page_editor_enabled'] ?? 'false') === 'true') checked @endif>
                            <label class="custom-control-label" for="site_home_page_editor_enabled">Főoldal szerkesztő</label>
                        </div>
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

                        {{-- Rendelés leadása módban megjelenő extra beállítások --}}
                        <div id="checkout-order-settings" @if(($ws['site_checkout_mode'] ?? 'order') != 'order') style="display:none;" @endif>
                            <hr class="my-3">

                            {{-- Fizetési lehetőségek --}}
                            <div class="custom-control custom-switch mb-2">
                                <input type="checkbox" class="custom-control-input" id="site_checkout_payment_options_enabled" name="site_checkout_payment_options_enabled"
                                       @if(($ws['site_checkout_payment_options_enabled'] ?? 'false') === 'true') checked @endif>
                                <label class="custom-control-label fw-600" for="site_checkout_payment_options_enabled"><strong>Fizetési lehetőségek</strong></label>
                            </div>
                            <div id="checkout-payment-options-settings" @if(($ws['site_checkout_payment_options_enabled'] ?? 'false') !== 'true') style="display:none;" @endif>
                                <p class="text-muted small mb-2">Válaszd ki, mely fizetési módok jelenjenek meg a site oldalon:</p>
                                <div class="pl-3">
                                    <div class="custom-control custom-checkbox mb-2">
                                        <input type="checkbox" class="custom-control-input" id="site_checkout_payment_online_enabled" name="site_checkout_payment_online_enabled"
                                               @if(($ws['site_checkout_payment_online_enabled'] ?? 'false') === 'true') checked @endif>
                                        <label class="custom-control-label" for="site_checkout_payment_online_enabled">Online fizetés</label>
                                    </div>
                                    <div class="custom-control custom-checkbox mb-2">
                                        <input type="checkbox" class="custom-control-input" id="site_checkout_payment_cod_enabled" name="site_checkout_payment_cod_enabled"
                                               @if(($ws['site_checkout_payment_cod_enabled'] ?? 'false') === 'true') checked @endif>
                                        <label class="custom-control-label" for="site_checkout_payment_cod_enabled">Utánvét</label>
                                    </div>
                                    <div class="custom-control custom-checkbox mb-2">
                                        <input type="checkbox" class="custom-control-input" id="site_checkout_payment_bank_transfer_enabled" name="site_checkout_payment_bank_transfer_enabled"
                                               @if(($ws['site_checkout_payment_bank_transfer_enabled'] ?? 'false') === 'true') checked @endif>
                                        <label class="custom-control-label" for="site_checkout_payment_bank_transfer_enabled">Előre utalással</label>
                                    </div>
                                    <div class="custom-control custom-checkbox mb-2">
                                        <input type="checkbox" class="custom-control-input" id="site_checkout_payment_on_site_enabled" name="site_checkout_payment_on_site_enabled"
                                               @if(($ws['site_checkout_payment_on_site_enabled'] ?? 'false') === 'true') checked @endif>
                                        <label class="custom-control-label" for="site_checkout_payment_on_site_enabled">Fizetés a helyszínen <small class="text-muted">(személyes átvételnél jelenik meg)</small></label>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-3">

                            {{-- Szállítási lehetőségek --}}
                            <div class="custom-control custom-switch mb-2">
                                <input type="checkbox" class="custom-control-input" id="site_checkout_shipping_options_enabled" name="site_checkout_shipping_options_enabled"
                                       @if(($ws['site_checkout_shipping_options_enabled'] ?? 'false') === 'true') checked @endif>
                                <label class="custom-control-label fw-600" for="site_checkout_shipping_options_enabled"><strong>Szállítási lehetőségek</strong></label>
                            </div>
                            <div id="checkout-shipping-options-settings" @if(($ws['site_checkout_shipping_options_enabled'] ?? 'false') !== 'true') style="display:none;" @endif>
                                <p class="text-muted small mb-2">Válaszd ki, mely szállítási módok jelenjenek meg a site oldalon:</p>
                                <div class="pl-3">
                                    <div class="custom-control custom-checkbox mb-2">
                                        <input type="checkbox" class="custom-control-input" id="site_checkout_shipping_home_delivery_enabled" name="site_checkout_shipping_home_delivery_enabled"
                                               @if(($ws['site_checkout_shipping_home_delivery_enabled'] ?? 'false') === 'true') checked @endif>
                                        <label class="custom-control-label" for="site_checkout_shipping_home_delivery_enabled">Házhoz szállítás</label>
                                    </div>
                                    <div class="custom-control custom-checkbox mb-2">
                                        <input type="checkbox" class="custom-control-input" id="site_checkout_shipping_parcel_locker_enabled" name="site_checkout_shipping_parcel_locker_enabled"
                                               @if(($ws['site_checkout_shipping_parcel_locker_enabled'] ?? 'false') === 'true') checked @endif>
                                        <label class="custom-control-label" for="site_checkout_shipping_parcel_locker_enabled">Csomagpont automata</label>
                                    </div>
                                    <div class="custom-control custom-checkbox mb-2">
                                        <input type="checkbox" class="custom-control-input" id="site_checkout_shipping_pickup_enabled" name="site_checkout_shipping_pickup_enabled"
                                               @if(($ws['site_checkout_shipping_pickup_enabled'] ?? 'false') === 'true') checked @endif>
                                        <label class="custom-control-label" for="site_checkout_shipping_pickup_enabled">Személyes átvétel</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-3">
                        <p class="text-muted small mb-2">Kötelező és opcionális checkout mezők:</p>
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
    <script>
        (function() {
            function toggleCheckoutOrderSettings() {
                var mode = document.getElementById('site_checkout_mode').value;
                var block = document.getElementById('checkout-order-settings');
                if (block) block.style.display = (mode === 'order') ? '' : 'none';
            }
            function togglePaymentOptionsSettings() {
                var cb = document.getElementById('site_checkout_payment_options_enabled');
                var block = document.getElementById('checkout-payment-options-settings');
                if (block) block.style.display = cb && cb.checked ? '' : 'none';
            }
            function toggleShippingOptionsSettings() {
                var cb = document.getElementById('site_checkout_shipping_options_enabled');
                var block = document.getElementById('checkout-shipping-options-settings');
                if (block) block.style.display = cb && cb.checked ? '' : 'none';
            }

            document.addEventListener('DOMContentLoaded', function() {
                var modeSelect = document.getElementById('site_checkout_mode');
                if (modeSelect) modeSelect.addEventListener('change', toggleCheckoutOrderSettings);

                var payOptCb = document.getElementById('site_checkout_payment_options_enabled');
                if (payOptCb) payOptCb.addEventListener('change', togglePaymentOptionsSettings);

                var shipOptCb = document.getElementById('site_checkout_shipping_options_enabled');
                if (shipOptCb) shipOptCb.addEventListener('change', toggleShippingOptionsSettings);
            });
        })();
    </script>
@endsection

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

            <div class="row">
                <div class="col-lg-6 mb-3">
                    <h2 class="header-box product-info">Site beállítások <span class="badge badge-secondary">Előkészítés</span></h2>
                    <div class="content-box bordered">
                        <p class="text-muted mb-0"><i class="fa fa-info-circle"></i> A site oldali beállítások egy későbbi fejlesztési körben lesznek implementálva.</p>
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

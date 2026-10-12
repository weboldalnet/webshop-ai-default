@extends('site.layouts.layout')
@section('title', $product->name)

@section('content')
    @include('site.webshop.partials.sticky-categories', ['category' => $product->category])

    <div class="ws-page-container ws-products-show">
        <div class="container-xl container-fluid">
            @include('site.webshop.partials.breadcrumb', ['category' => $product->category, 'product' => $product])

            <div class="">
                <h1 class="h2 mb-0 font-weight-bold">{{ $product->name }}</h1>
                @if(($ws['product_secondary_name_enabled'] ?? 'false') === 'true' && $product->secondary_name)
                    <div class="h4 text-muted mt-1">{{ $product->secondary_name }}</div>
                @endif

            </div>

            <div class="row mt-2 ws-product-main">
                <!-- Kép blokk -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="ws-product-gallery card shadow-sm">
                        <div class="ws-main-image p-2 text-center gallery">
                            @if(\Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopSettingsService::getBool('admin_product_labels_enabled') && $product->label)
                                <div class="ws-product-label-badge badge position-absolute"
                                     style="background-color: {{ $product->label->bg_color }}; color: {{ $product->label->text_color }};
                    top: {{ (($ws['site_product_prices_visible'] ?? 'true') === 'true' && $product->sale_price && $product->discount_percentage > 0) ? '45px' : '10px' }};">
                                    {{ $product->label->name }}
                                </div>
                            @endif

                            @if($product->primary_image)
                                <a href="{{ $product->primary_image }}">
                                    <img src="{{ $product->primary_image }}" id="ws-main-img" alt="{{ $product->name }}" class="img-fluid" style="max-height: 100%;">
                                </a>
                            @else
                                <i class="fa fa-image fa-5x text-muted opacity-25"></i>
                            @endif

                            @if($product->defaultGalleryImages->isNotEmpty())
                                @foreach($product->defaultGalleryImages as $img)
                                    <a href="{{ $img->image }}"></a>
                                @endforeach
                            @endif
                        </div>
                        @if($product->defaultGalleryImages->isNotEmpty())
                            <div class="ws-thumbnails p-2 d-flex flex-wrap border-top">
                                <div class="ws-thumb p-1" style="width: 25%; cursor: pointer;">
                                    <img src="{{ $product->primary_image_thumb }}" class="img-fluid border rounded js-thumb active" data-src="{{ $product->primary_image }}">
                                </div>
                                @foreach($product->defaultGalleryImages as $img)
                                    <div class="ws-thumb p-1" style="width: 25%; cursor: pointer;">
                                        <img src="{{ $img->image_thumb }}" class="img-fluid border rounded js-thumb" data-src="{{ $img->image }}" alt="{{ $img->alt }}">
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Tulajdonságok blokk -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card shadow-sm h-100 border-0">
                        <div class="card-body">
                            {{-- Rövid leírás a tulajdonságok FÖLÖTT, saját címmel.

                                 SZÁNDÉKOSAN nincs beállítás mögé kötve: az adminban
                                 kitölthető mező eddig sehol nem jelent meg a boltban,
                                 tehát írás-only volt. A "Terméklistán rövid leírás..."
                                 kapcsoló a LISTA kártyáira vonatkozik, nem erre. --}}
                            @if($product->short_desc)
                                <h4 class="font-weight-bold mb-2 border-bottom pb-2">Rövid leírás</h4>
                                <div class="ws-product-short-desc text-muted mb-4">{{ $product->short_desc }}</div>
                            @endif

                            {{-- A címet CSAK akkor írjuk ki, ha van megjeleníthető tulajdonság: üres
                                 listánál eddig egy cím állt a semmi fölött. --}}
                            @if($product->productProperties->isNotEmpty())
                                <h4 class="font-weight-bold mb-3 border-bottom pb-2">Tulajdonságok</h4>
                            @endif
                            <div class="ws-product-properties">
                                @foreach($product->productProperties->groupBy('property_category_id') as $pcId => $pProps)
                                    @php $pc = $pProps->first()->propertyCategory; @endphp
                                    @if($pc)
                                        <div class="mb-lg-3 mb-0">
                                            <div class="font-weight-bold text-muted small">{{ $pc->name }}:</div>
                                            <div class="ws-prop-values">
                                                @foreach($pProps as $pp)
                                                    @if($pp->property)
                                                        {{-- A kategórialap a ";{slug}-f{id}" alakot érti (parseBeautyUrl).
                                                             A korábbi "?f[1][]=1" lekérdezés-paraméteres alakot SEM a szerver,
                                                             SEM a szűrő JS nem dolgozta fel, így a link odavitt a kategóriára,
                                                             de szűrés nélkül - és a böngésző Vissza gombja is emiatt viselkedett
                                                             furcsán. --}}
                                                        <a href="{{ route('site.webshop.categories.show', $product->category) }};{{ $pp->property->slug }}-f{{ $pp->property->id }}" class="badge badge-secondary mr-1">{{ $pp->property->name }}</a>
                                                    @elseif($pp->number_value !== null)
                                                        <span class="fw-600">{{ $pc->formatNumberValue($pp->number_value) }}</span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ár és kosár blokk -->
                <div class="col-lg-4 col-md-6 mx-auto mb-4">
                    <div class="card shadow-sm bg-light">
                        <div class="card-body d-flex flex-column">
                            <h4 class="font-weight-bold mb-4 border-bottom pb-2 text-primary">Vásárlás</h4>

                            @include('site.webshop.partials.product-price', [
                                'product' => $product,
                                'priceWrapClass' => 'mb-4',
                                'showPriceNote' => true,
                            ])

                            {{-- Készlet-állapot.

                                 A $wsStock null, ha a termék korlátlan (nincs
                                 készletkezelés, vagy a terméken nincs bekapcsolva) –
                                 ilyenkor semmit nem írunk ki, hogy a nem készletezett
                                 boltok látképe ne változzon. --}}
                            @php($wsStock = \Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopStockService::available($product))

                            @if($wsStock !== null)
                                <div class="ws-product-stock mb-3">
                                    @if($wsStock <= 0)
                                        <span class="badge badge-danger px-3 py-2 fs-14">
                                            <i class="fa fa-circle-xmark mr-1"></i> Elfogyott
                                        </span>
                                    @elseif($wsStock <= 5)
                                        <span class="badge badge-warning px-3 py-2 fs-14">
                                            <i class="fa fa-triangle-exclamation mr-1"></i>
                                            Utolsó {{ $wsStock }} db
                                        </span>
                                    @else
                                        <span class="badge badge-success px-3 py-2 fs-14">
                                            <i class="fa fa-circle-check mr-1"></i> Raktáron
                                        </span>
                                    @endif
                                </div>
                            @endif

                            @if($wsStock === null || $wsStock > 0)
                                <div class="form-group mb-4">
                                    <label for="quantity" class="font-weight-bold">Mennyiség:</label>
                                    <select id="quantity" class="form-control form-control w-100 js-quantity-select">
                                        {{-- Legfeljebb annyi választható, amennyi raktáron van --}}
                                        @php($wsMaxQuantity = $wsStock === null ? 10 : min(10, $wsStock))
                                        @for($i = 1; $i <= $wsMaxQuantity; $i++)
                                            <option value="{{ $i }}">{{ $i }} db</option>
                                        @endfor
                                    </select>
                                </div>
                            @endif

                            <div class="mt-auto">
                                @if($wsStock !== null && $wsStock <= 0)
                                    <button type="button" class="btn btn-secondary btn-lg btn-block font-weight-bold py-3 mb-2" disabled>
                                        <i class="fa fa-circle-xmark mr-2"></i> Jelenleg nem rendelhető
                                    </button>
                                @else
                                    <button type="button" class="btn btn-primary btn-lg btn-block font-weight-bold py-3 mb-2 js-add-to-cart" data-id="{{ $product->id }}" data-with-qty="true">
                                        <i class="fa fa-shopping-cart mr-2"></i> Kosárba rakom
                                    </button>
                                @endif

                                @if(($ws['site_product_compare_enabled'] ?? 'false') === 'true')
                                    <button type="button" class="btn btn-outline-info btn-block js-add-to-compare" data-id="{{ $product->id }}">
                                        <i class="fa fa-scale-balanced mr-2"></i> Összehasonlítás
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if(($ws['product_variations_enabled'] ?? 'false') === 'true')
                        @include('site.webshop.products.partials.variations')
                    @endif
                </div>
            </div>

        </div>{{-- /container: a galéria OLDAL SZÉLES, ezért ki kell lépnie belőle --}}

        {{-- MÁSODLAGOS GALÉRIA — oldal széles, vízszintesen görgethető sáv.

             Miért nem kártyában, egy rácsban, apró bélyegképekkel (ahogy korábban volt):
             a másodlagos galéria a termék hangulatképeit hordozza, azoknak nagynak kell
             lenniük, különben semmit nem adnak hozzá. Ezért a profi boltok mintája:
             a képek a lap teljes szélességében, egy magas filmszalagban futnak, és
             vízszintesen görgethetők — a rács ehhez képest minden képet összenyomna.

             A cím a rendes tartalom-konténerben marad, hogy a többi szekciócímmel
             egy vonalban álljon; csak maga a szalag fut ki a széléig.

             A .scroll-container / .scroll-wrapper / .scroll-row-list a projekt SAJÁT
             görgetőmintája (public/js/section.js), tehát a nyilak, a léptetés és a
             nyilak elrejtése a széleken külön JS nélkül működik. A section.js minden
             site-oldalon be van töltve, ezen is.

             KÉPMÉRET: itt szándékosan a NAGY kép megy ki ($img->image, 1000 px), nem a
             bélyegkép ($img->image_thumb, 150 px) — ekkora megjelenítésnél a bélyegkép
             látványosan pixeles lenne. A loading="lazy" tartja kordában a letöltést.

             A .gallery osztály a baguetteBox-nak szól (public/js/header.js indítja
             minden .gallery elemre), így a képek nagyításra ugyanúgy megnyílnak. --}}
        @if(($ws['product_extra_gallery_enabled'] ?? 'false') === 'true' && $product->secondaryGalleryImages->isNotEmpty())
            @php($wsGalleryTitle = trim($ws['product_extra_gallery_title'] ?? ''))

            <section class="ws-extra-gallery py-0 my-0">
                {{-- Cím nélkül a KONTÉNER SEM kerül ki, nem csak a szöveg: egy üres
                     címsor a saját alsó margójával és a konténer belső térközével
                     ugyanúgy hézagot hagyna a képsáv fölött, csak épp láthatatlanul.
                     Az admin így tudja cím nélkül is használni a galériát. --}}
                @if($wsGalleryTitle !== '')
                    <div class="container-xl container-fluid">
                        <h2 class="font-weight-bold mb-3 text-center ws-extra-gallery-title">
                            {{ $wsGalleryTitle }}
                        </h2>
                    </div>
                @endif

                <div class="scroll-container ws-extra-gallery-scroll">
                    <div class="scroll-wrapper">
                        <div class="scroll-row-list d-flex gallery">
                            @foreach($product->secondaryGalleryImages as $img)
                                <a href="{{ $img->image }}" class="ws-extra-gallery-item">
                                    <img src="{{ $img->image }}"
                                         alt="{{ $img->alt ?: $product->name }}"
                                         loading="lazy">
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <span class="scroll-left default-arrows carousel-arrows left" aria-label="Előző képek">
                        <i class="fa fa-arrow-left"></i>
                    </span>
                    <span class="scroll-right default-arrows carousel-arrows right" aria-label="További képek">
                        <i class="fa fa-arrow-right"></i>
                    </span>
                </div>
            </section>
        @endif

        <div class="container-xl container-fluid pb-5">

            <!-- Tab-page rész -->
            <div class="row mt-4">
                <div class="col-lg-12">
                    <div class="card shadow-sm border-0">
                        {{-- Kötelező termékinformációk (GPSR, élelmiszer, CLP …): a vásárlás előtt elérhetőnek kell lenniük --}}
                        @php($wsLegalHasInfo = \Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopProductLegalInfoService::hasInfo($product))
                        <div class="card-header bg-white p-0">
                            <ul class="nav nav-tabs ws-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active px-4 py-2 font-weight-bold fs-20" id="desc-tab" data-toggle="tab" href="#desc" role="tab">Leírás</a>
                                </li>
                                @if($wsLegalHasInfo)
                                    <li class="nav-item">
                                        <a class="nav-link px-4 py-2 font-weight-bold fs-20" id="legal-info-tab" data-toggle="tab" href="#legal-info" role="tab">Termékinformációk</a>
                                    </li>
                                @endif
                                {{-- A Vélemények fül GOMBJA. A tartalma (tab-pane) korábban is
                                     kirenderelődött, de gomb nélkül semmi nem tudta aktiválni,
                                     így a vélemények szekció elérhetetlen volt. --}}
                                @if(($ws['site_product_reviews_enabled'] ?? 'false') === 'true')
                                    <li class="nav-item">
                                        <a class="nav-link px-4 py-2 font-weight-bold fs-20" id="reviews-tab" data-toggle="tab" href="#reviews" role="tab">
                                            Vélemények
                                            @php($wsReviewCount = $product->reviews()->active()->count())
                                            @if($wsReviewCount)
                                                <span class="badge badge-secondary ml-1">{{ $wsReviewCount }}</span>
                                            @endif
                                        </a>
                                    </li>
                                @endif

                                @if(($ws['product_document_upload_enabled'] ?? 'false') === 'true' && $product->productDocuments->isNotEmpty())
                                    <li class="nav-item">
                                        <a class="nav-link px-4 py-2 font-weight-bold fs-20" id="docs-tab" data-toggle="tab" href="#docs" role="tab">Dokumentumok</a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                        <div class="card-body tab-content p-4">
                            <div class="tab-pane fade show active" id="desc" role="tabpanel">
                                {!! $product->description !!}
                            </div>
                            @if($wsLegalHasInfo)
                                <div class="tab-pane fade" id="legal-info" role="tabpanel">
                                    @include('site.webshop.products.partials.legal-info')
                                </div>
                            @endif
                            @if(($ws['site_product_reviews_enabled'] ?? 'false') === 'true')
                                <div class="tab-pane fade" id="reviews" role="tabpanel">
                                    @include('site.webshop.products.partials.reviews')
                                </div>
                            @endif
                            @if(($ws['product_document_upload_enabled'] ?? 'false') === 'true' && $product->productDocuments->isNotEmpty())
                                <div class="tab-pane fade" id="docs" role="tabpanel">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Dokumentum neve</th>
                                                    <th style="width: 150px"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($product->productDocuments as $doc)
                                                    <tr>
                                                        <td class="align-middle fw-600 fs-18">{{ $doc->name }}</td>
                                                        <td class="text-center">
                                                            <a href="{{ $doc->type === 'link' ? $doc->url : $doc->file_path }}" target="_blank" class="btn btn-primary btn-sm">
                                                                Megnyitás <i class="fa fa-external-link-alt ml-1"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @if($product->relatedProducts->isNotEmpty())
                <div class="row mt-5">
                    <div class="col-lg-12"><h3 class="font-weight-bold mb-4 border-bottom pb-2">Kapcsolódó termékek</h3></div>
                    @foreach($product->relatedProducts->take(4) as $relProd)
                        <div class="col-lg-3 col-md-6 mb-4">
                            @include('site.webshop.categories.partials.product-card', ['product' => $relProd, 'ws' => $ws])
                        </div>
                    @endforeach
                </div>
            @endif

            @if($similarProducts->isNotEmpty())
                <div class="row mt-5">
                    <div class="col-lg-12"><h3 class="font-weight-bold mb-4 border-bottom pb-2">Hasonló termékek</h3></div>
                    @foreach($similarProducts->take(4) as $simProd)
                        <div class="col-lg-3 col-md-6 mb-4">
                            @include('site.webshop.categories.partials.product-card', ['product' => $simProd, 'ws' => $ws])
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    @include('site.webshop.modals.review-form')

    @push('scripts')
        <script src="/packages/webshop/site/js/webshop-site.js"></script>
        <script type="application/ld+json">
            {!! (new \Weboldalnet\WebshopAiDefault\Services\Webshop\ProductSchemaService())->generateJson($product) !!}
        </script>
        @include('site.webshop.partials.tracking-scripts', ['pageType' => 'product'])
    @endpush
@endsection

@extends('admin.layouts.layout')
@section('title', 'Termék szerkesztése')

@section('content')
    @include('admin.commons.tinymce-img-upload', ['tinyHeight' => 300])

    @php
        /*
         * TERMÉKSZERKESZTŐ (újratervezve 2026-10-28). Két oszlop: balra a termék tartalma
         * szakaszonként, jobbra a kép, a besorolás, a készlet és a szakasz-navigáció. Minden
         * szakasz ugyanazon beállítás mögött jelenik meg, mint korábban (Webshop beállítások),
         * a mezőnevek és a JS-kötések változatlanok.
         */
        $wsSettings = \Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopSettingsService::class;
        $wsOn = fn (string $key) => ($ws[$key] ?? 'false') === 'true';

        $existingProps = $product->productProperties->groupBy('property_category_id');
        $wsPriceOn = $wsOn('product_price_enabled');
        $wsStockOn = $wsOn('product_stock_enabled');
        $wsGalleryOn = $wsOn('product_gallery_enabled');
        $wsExtraGalleryOn = $wsGalleryOn && $wsOn('product_extra_gallery_enabled');
        $wsDocumentsOn = $wsOn('product_document_upload_enabled');
        $wsRelatedOn = $wsOn('product_related_enabled');
        $wsVariationsOn = $wsOn('product_variations_enabled');
        $wsLabelsOn = $wsSettings::getBool('admin_product_labels_enabled');
        $wsPricesVisible = ($ws['site_product_prices_visible'] ?? 'true') === 'true';
        $wsImgWidth = $product->category->primary_image_width ?? config('webshop.primary_image.width');
        $wsImgHeight = $product->category->primary_image_height ?? config('webshop.primary_image.height');

        // a kategóriához rendelt tulajdonságcsoportok elöl, a többi összecsukva alattuk
        $wsDefaultPcs = $allPropertyCategories->filter(fn ($pc) => in_array($pc->id, $categoryPropertyCatIds));
        $wsOtherPcs = $allPropertyCategories->reject(fn ($pc) => in_array($pc->id, $categoryPropertyCatIds));
        $wsFilterTypes = ['checkbox' => 'több választás', 'radio' => 'egy választás', 'number' => 'szám'];

        $wsSections = array_filter([
            ['ws-sec-basic', 'fa-pen', 'Alapadatok', true],
            ['ws-sec-description', 'fa-align-left', 'Leírás', true],
            ['ws-sec-price', 'fa-tag', 'Ár és kiszerelés', $wsPriceOn],
            ['ws-sec-properties', 'fa-sliders', 'Tulajdonságok', true],
            ['ws-sec-gallery', 'fa-images', 'Galéria', $wsGalleryOn],
            ['ws-sec-documents', 'fa-file-lines', 'Dokumentumok', $wsDocumentsOn],
            ['ws-sec-relations', 'fa-link', $wsRelatedOn ? 'Kapcsolódó termékek' : 'Variációk', $wsRelatedOn || $wsVariationsOn],
            ['ws-sec-legal', 'fa-clipboard-list', 'Termékinformációk', true],
            ['ws-sec-seo', 'fa-magnifying-glass', 'SEO és megosztás', true],
        ], fn ($section) => $section[3]);
    @endphp

    <div class="container-fluid ws-editor mt-3 mb-150">
        @include('admin.webshop.partials.alerts')
        @include('admin.webshop.partials.form-errors')

        <div class="ws-editor-head">
            <div>
                <span class="ws-editor-kicker">Termék</span>
                <h2 class="ws-editor-title">{{ $product->name }}</h2>
                <div class="ws-chips">
                    @if($product->is_active)
                        <span class="ws-chip is-good"><i class="fa-solid fa-circle-check"></i> Aktív</span>
                    @else
                        <span class="ws-chip is-warn"><i class="fa-solid fa-eye-slash"></i> Inaktív</span>
                    @endif
                    @if($product->category)
                        <span class="ws-chip"><i class="fa-solid fa-folder"></i> {{ $product->category->name_singular }}</span>
                    @endif
                    @if($product->sku)
                        <span class="ws-chip"><i class="fa-solid fa-barcode"></i> {{ $product->sku }}</span>
                    @endif
                    @if($wsPriceOn && $product->price)
                        <span class="ws-chip @if($product->hasActiveSale()) is-accent @endif">
                            <i class="fa-solid fa-tag"></i>
                            {{ hufFormat($product->hasActiveSale() ? $product->sale_price : $product->price) }}
                            @if($product->hasActiveSale()) · akció @endif
                        </span>
                    @endif
                    @if($wsStockOn && $product->stock_enabled)
                        <span class="ws-chip @if((int) $product->stock_quantity <= 0) is-warn @endif">
                            <i class="fa-solid fa-boxes-stacked"></i> {{ (int) $product->stock_quantity }} db készleten
                        </span>
                    @endif
                </div>
            </div>
            <div class="ws-editor-head-actions">
                @if(\Illuminate\Support\Facades\Route::has('site.webshop.products.show') && $product->slug)
                    <a href="{{ route('site.webshop.products.show', $product) }}" target="_blank" rel="noopener" class="btn btn-outline-secondary">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i> Megtekintés
                    </a>
                @endif
                @if($wsOn('site_product_reviews_enabled'))
                    <a href="{{ route('admin.webshop.products.reviews.index', $product) }}" class="btn btn-outline-secondary">
                        <i class="fa-solid fa-star"></i> Vélemények
                    </a>
                @endif
                <a href="{{ route('admin.webshop.products.index') }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left"></i> Terméklista
                </a>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.webshop.products.update', $product) }}" enctype="multipart/form-data">
            @csrf @method('PUT')

            <div class="row">
                <div class="col-xl-8">

                    {{-- Alapadatok --}}
                    <div class="ws-card" id="ws-sec-basic">
                        <header class="ws-card-head">
                            <span class="ws-card-icon"><i class="fa-solid fa-pen"></i></span>
                            <div class="ws-card-heading"><h3 class="ws-card-title">Alapadatok</h3></div>
                        </header>
                        <div class="form-group">
                            <label for="name">Név <span class="ws-req">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $product->name) }}" required>
                        </div>
                        @if($wsOn('product_secondary_name_enabled'))
                            <div class="form-group">
                                <label for="secondary_name">Másodlagos név</label>
                                <input type="text" class="form-control" id="secondary_name" name="secondary_name" value="{{ old('secondary_name', $product->secondary_name) }}">
                                <span class="ws-hint">A termékkártyán, a keresőben és a termékoldalon is megjelenik.</span>
                            </div>
                        @endif
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="sku">Cikkszám (SKU)</label>
                                <input type="text" class="form-control" id="sku" name="sku" value="{{ old('sku', $product->sku) }}">
                            </div>
                            @if($wsOn('product_crm_id_enabled'))
                                <div class="form-group col-md-6">
                                    <label for="crm_identifier">CRM azonosító</label>
                                    <input type="text" class="form-control" id="crm_identifier" name="crm_identifier" value="{{ old('crm_identifier', $product->crm_identifier) }}">
                                    <span class="ws-hint">A termék azonosítója a külső rendszerben.</span>
                                </div>
                            @endif
                        </div>
                        <div class="form-group">
                            <label for="short_desc">Rövid leírás</label>
                            <textarea class="form-control" id="short_desc" name="short_desc" rows="4">{{ old('short_desc', $product->short_desc) }}</textarea>
                            <span class="ws-hint">A termékoldalon a név alatt látszik.</span>
                        </div>
                    </div>

                    {{-- Leírás --}}
                    <div class="ws-card" id="ws-sec-description">
                        <header class="ws-card-head">
                            <span class="ws-card-icon"><i class="fa-solid fa-align-left"></i></span>
                            <div class="ws-card-heading"><h3 class="ws-card-title">Leírás</h3></div>
                        </header>
                        <div class="form-group">
                            <textarea class="form-control js-tinymce" id="description" name="description" rows="6">{!! old('description', $product->description) !!}</textarea>
                        </div>
                    </div>

                    {{-- Ár és kiszerelés --}}
                    @if($wsPriceOn)
                        <div class="ws-card" id="ws-sec-price">
                            <header class="ws-card-head">
                                <span class="ws-card-icon"><i class="fa-solid fa-tag"></i></span>
                                <div class="ws-card-heading"><h3 class="ws-card-title">Ár és kiszerelés</h3></div>
                            </header>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="price">Ár <span class="font-weight-normal text-muted fs-14">(Ft)</span> @if($wsPricesVisible)<span class="ws-req">*</span>@endif</label>
                                    <input type="number" class="form-control" id="price" name="price" value="{{ $product->price }}"
                                           @if($wsPricesVisible) required @endif>
                                    <span class="ws-hint">Forintban, pl. 10000.</span>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="sale_price">Akciós ár <span class="font-weight-normal text-muted fs-14">(Ft)</span></label>
                                    <input type="number" class="form-control" id="sale_price" name="sale_price" value="{{ $product->sale_price }}">
                                    <span class="ws-hint">Üresen hagyva nincs akció.</span>
                                </div>
                            </div>
                            @if($product->hasActiveSale() && $product->sale_started_at)
                                <div class="alert alert-info fs-14 py-2">
                                    Akció kezdete: {{ $product->sale_started_at->format('Y. m. d. H:i') }} ·
                                    feltüntetett korábbi ár (30 napos legalacsonyabb): <strong>{{ hufFormat($product->referencePrice()) }}</strong>
                                    ({{ $product->discount_percentage }}% kedvezmény)
                                </div>
                            @elseif($product->sale_price && !$product->hasActiveSale())
                                <div class="alert alert-warning fs-14 py-2">
                                    Az akciós ár nem alacsonyabb az elmúlt 30 nap legalacsonyabb áránál
                                    ({{ hufFormat($product->referencePrice()) }}), ezért nem jelenik meg akcióként.
                                </div>
                            @endif
                            <span class="ws-hint mt-0 mb-3">
                                Akciónál a webshop áthúzott árként az akciót megelőző 30 nap legalacsonyabb eladási árát mutatja
                                (uniós Omnibusz-szabály), és ebből számolja a kedvezményt. Ha az akciós ár nem alacsonyabb ennél,
                                sima eladási árként jelenik meg.
                            </span>

                            <h4 class="ws-card-sub">Kiszerelés és egységár</h4>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="unit_quantity">Kiszerelés <span class="font-weight-normal text-muted fs-14">(nettó)</span></label>
                                    <input type="number" step="0.001" min="0" class="form-control" id="unit_quantity" name="unit_quantity"
                                           value="{{ old('unit_quantity', $product->unit_quantity) }}">
                                    <span class="ws-hint">A csomag nettó mennyisége, pl. 500 vagy 0,75.</span>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="unit_measure">Mértékegység</label>
                                    <select class="form-control" id="unit_measure" name="unit_measure">
                                        <option value="">– nincs egységár –</option>
                                        @foreach(\Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopUnitPriceService::MEASURES as $wsMeasureKey => $wsMeasure)
                                            <option value="{{ $wsMeasureKey }}" @if(old('unit_measure', $product->unit_measure) === $wsMeasureKey) selected @endif>{{ $wsMeasure['label'] }}</option>
                                        @endforeach
                                    </select>
                                    <span class="ws-hint">Tömegre, térfogatra, hosszra vagy területre mért terméknél kötelező (pl. 500 g → Ft/kg). Darabárasnál hagyd üresen.</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Tulajdonságok --}}
                    <div class="ws-card" id="ws-sec-properties">
                        <header class="ws-card-head">
                            <span class="ws-card-icon"><i class="fa-solid fa-sliders"></i></span>
                            <div class="ws-card-heading">
                                <h3 class="ws-card-title">Tulajdonságok</h3>
                                <span class="ws-card-lead">Ezek alapján szűrhetnek a vásárlók a terméklistában.</span>
                            </div>
                        </header>

                        @if($allPropertyCategories->isEmpty())
                            <div class="ws-empty-note">Még nincs tulajdonságcsoport. A Tulajdonságok menüpontban hozhatsz létre.</div>
                        @endif

                        @foreach([[$wsDefaultPcs, true], [$wsOtherPcs, false]] as [$wsGroupList, $isDefault])
                            @continue($wsGroupList->isEmpty())
                            @if(!$isDefault)
                                <div class="ws-prop-more">{{ $wsDefaultPcs->isEmpty() ? 'Tulajdonságcsoportok' : 'További csoportok' }}</div>
                            @endif
                            <div class="ws-prop-grid">
                                @foreach($wsGroupList as $pc)
                                    @php
                                        $pcProps = $existingProps->get($pc->id, collect());
                                        $selectedPropIds = $pcProps->pluck('property_id')->toArray();
                                        $wsOpen = $isDefault || $pcProps->isNotEmpty();
                                        $wsCount = $pc->isNumber()
                                            ? ($pcProps->first()->number_value ?? null)
                                            : (count($selectedPropIds) ?: null);
                                    @endphp
                                    <div class="ws-prop-tile @if($isDefault) is-default @endif">
                                        <button type="button" class="ws-prop-tile-head @if(!$wsOpen) collapsed @endif"
                                                data-toggle="collapse" data-target="#propGroup{{ $pc->id }}" aria-expanded="{{ $wsOpen ? 'true' : 'false' }}">
                                            {{ $pc->name }}
                                            <span class="ws-prop-type">{{ $wsFilterTypes[$pc->filter_type] ?? $pc->filter_type }}</span>
                                            @if($wsCount !== null)
                                                <span class="ws-prop-count" title="Kiválasztva">@unless($pc->isNumber())<i class="fa-solid fa-check"></i> @endunless{{ $wsCount }}{{ $pc->isNumber() && $pc->suffix ? ' ' . $pc->suffix : '' }}</span>
                                            @endif
                                            <i class="fa-solid fa-chevron-down @if($wsCount === null) ml-auto @endif"></i>
                                        </button>
                                        <div id="propGroup{{ $pc->id }}" class="collapse @if($wsOpen) show @endif">
                                            <div class="ws-prop-body">
                                                @if($pc->isNumber())
                                                    <div class="input-group">
                                                        <input type="number" class="form-control" name="properties[{{ $pc->id }}][number_value]" step="0.01"
                                                               value="{{ $pcProps->first()->number_value ?? '' }}">
                                                        @if($pc->suffix)
                                                            <div class="input-group-append"><span class="input-group-text">{{ $pc->suffix }}</span></div>
                                                        @endif
                                                    </div>
                                                @else
                                                    <div class="ws-option-list">
                                                        @foreach($pc->properties as $prop)
                                                            @if($pc->filter_type === 'radio')
                                                                <div class="custom-control custom-radio">
                                                                    <input type="radio" class="custom-control-input" id="prop{{ $prop->id }}" name="properties[{{ $pc->id }}][selected]" value="{{ $prop->id }}"
                                                                           @if(in_array($prop->id, $selectedPropIds)) checked @endif>
                                                                    <label class="custom-control-label" for="prop{{ $prop->id }}">{{ $prop->name }}</label>
                                                                </div>
                                                            @else
                                                                <div class="custom-control custom-checkbox">
                                                                    <input type="checkbox" class="custom-control-input" id="prop{{ $prop->id }}" name="properties[{{ $pc->id }}][selected][]" value="{{ $prop->id }}"
                                                                           @if(in_array($prop->id, $selectedPropIds)) checked @endif>
                                                                    <label class="custom-control-label" for="prop{{ $prop->id }}">{{ $prop->name }}</label>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>

                    {{-- Galéria (+ másodlagos galéria) --}}
                    @if($wsGalleryOn)
                        <div class="ws-card" id="ws-sec-gallery">
                            <header class="ws-card-head">
                                <span class="ws-card-icon"><i class="fa-solid fa-images"></i></span>
                                <div class="ws-card-heading">
                                    <h3 class="ws-card-title">Galéria</h3>
                                    <span class="ws-card-lead">A képek sorrendje húzással állítható, a kapcsolóval elrejthetők.</span>
                                </div>
                            </header>

                            @foreach(array_filter(['default' => true, 'secondary' => $wsExtraGalleryOn]) as $wsGalleryType => $wsUnused)
                                <div class="ws-gallery-block js-gallery-block">
                                    @if($wsGalleryType === 'secondary')
                                        <h4 class="ws-card-sub mt-0 pt-0 border-0">{{ $ws['product_extra_gallery_title'] ?? '' ?: 'Másodlagos galéria' }}</h4>
                                    @endif
                                    <div id="{{ $wsGalleryType === 'secondary' ? 'secondary-gallery-sortable' : 'gallery-sortable' }}"
                                         class="row js-gallery-sortable ws-gallery-list"
                                         data-empty="Még nincs kép ebben a galériában."
                                         data-url="{{ route('admin.webshop.products.gallery.sort') }}">
                                        @foreach(($wsGalleryType === 'secondary' ? $product->secondaryGalleryImages : $product->defaultGalleryImages)->sortBy('sort_order') as $img)
                                            @include('admin.webshop.products.partials.gallery-item', ['product' => $product, 'img' => $img])
                                        @endforeach
                                    </div>

                                    <div class="js-gallery-upload-container ws-upload-row mt-1">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fa-regular fa-images fs-18"></i></span>
                                            </div>
                                            <div class="custom-file">
                                                <input type="file"
                                                       class="custom-file-input js-gallery-upload js-custom-file-input"
                                                       data-url="{{ route('admin.webshop.products.gallery.store', $product) }}"
                                                       data-type="{{ $wsGalleryType }}"
                                                       accept=".jpg,.jpeg,.png,.webp"
                                                       multiple>
                                                <label class="custom-file-label text-muted">Képek kiválasztása</label>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-success js-gallery-upload-start" style="display:none">
                                            <i class="fa fa-upload"></i> Feltöltés
                                        </button>
                                        <div class="js-gallery-upload-status" style="display:none">
                                            <span class="spinner-border spinner-border-sm text-primary"></span> Feltöltés... (<span class="js-gallery-upload-count">0</span>/<span class="js-gallery-upload-total">0</span>)
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Dokumentumok — a .content-box osztályra a dokumentum-mentés JS-e köt --}}
                    @if($wsDocumentsOn)
                        <div class="ws-card content-box" id="ws-sec-documents">
                            <header class="ws-card-head">
                                <span class="ws-card-icon"><i class="fa-solid fa-file-lines"></i></span>
                                <div class="ws-card-heading">
                                    <h3 class="ws-card-title">Dokumentumok</h3>
                                    <span class="ws-card-lead">Letölthető fájlok és linkek a termékoldalon (pl. használati utasítás).</span>
                                </div>
                            </header>
                            <div class="table-responsive js-document-sortable" data-url="{{ route('admin.webshop.products.documents.sort') }}">
                                <table class="table table-sm ws-doc-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 40px"></th>
                                            <th>Név</th>
                                            <th>Típus</th>
                                            <th>Elérés</th>
                                            <th style="width: 80px">Aktív</th>
                                            <th style="width: 50px"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="js-sortable-container">
                                        @forelse($product->productDocuments->sortBy('sort_order') as $doc)
                                            <tr data-id="{{ $doc->id }}">
                                                <td class="text-center ws-drag-handle"><i class="fa fa-grip-vertical"></i></td>
                                                <td class="fw-600">{{ $doc->name }}</td>
                                                <td>{{ $doc->type === 'link' ? 'Link' : 'Fájl' }}</td>
                                                <td>
                                                    @if($doc->type === 'link')
                                                        <a href="{{ $doc->url }}" target="_blank" class="text-break">{{ $doc->url }}</a>
                                                    @else
                                                        <a href="{{ $doc->file_path }}" target="_blank">Fájl megnyitása</a>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input js-toggle-active" id="doc{{ $doc->id }}" data-id="{{ $doc->id }}" data-url="{{ route('admin.webshop.products.documents.toggle-active') }}" @if($doc->is_active) checked @endif>
                                                        <label class="custom-control-label" for="doc{{ $doc->id }}"></label>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-outline-danger js-delete-document" data-id="{{ $doc->id }}" data-url="{{ route('admin.webshop.products.documents.destroy', [$product, $doc]) }}" title="Törlés">
                                                        <i class="fa fa-trash-alt"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr class="js-empty-row"><td colspan="6" class="text-center text-muted py-3">Még nincs dokumentum.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="ws-doc-new">
                                <h4 class="fs-16 fw-600 mb-3">Új dokumentum</h4>
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label>Név</label>
                                        <input type="text" class="form-control js-new-doc-name" placeholder="Pl. Használati utasítás">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Típus</label>
                                        <select class="form-control js-new-doc-type">
                                            <option value="link">Külső link</option>
                                            <option value="file">Fájl feltöltés</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-5 js-doc-url-group">
                                        <label>URL</label>
                                        <input type="url" class="form-control js-new-doc-url" placeholder="https://...">
                                    </div>
                                    <div class="form-group col-md-5 js-doc-file-group" style="display:none">
                                        <label>Fájl</label>
                                        <input type="file" class="form-control-file js-new-doc-file">
                                    </div>
                                </div>
                                <button type="button" class="btn btn-primary js-save-document" data-url="{{ route('admin.webshop.products.documents.store', $product) }}">
                                    <i class="fa fa-plus"></i> Dokumentum mentése
                                </button>
                            </div>
                        </div>
                    @endif

                    {{-- Kapcsolódó termékek + variációk — a két oszlop EGYMÁSTÓL FÜGGETLEN: a variációk
                         a kapcsolódó termékek kikapcsolásakor is kezelhetők maradnak. --}}
                    @if($wsRelatedOn || $wsVariationsOn)
                        <div class="ws-card" id="ws-sec-relations">
                            <header class="ws-card-head">
                                <span class="ws-card-icon"><i class="fa-solid fa-link"></i></span>
                                <div class="ws-card-heading"><h3 class="ws-card-title">{{ $wsRelatedOn && $wsVariationsOn ? 'Kapcsolódó termékek és variációk' : ($wsRelatedOn ? 'Kapcsolódó termékek' : 'Variációk') }}</h3></div>
                            </header>
                            <div class="row">
                                @foreach(array_filter(['related' => $wsRelatedOn, 'variation' => $wsVariationsOn]) as $wsRelType => $wsUnused)
                                    @php
                                        $wsRelItems = $wsRelType === 'related' ? $product->relatedProducts : $product->variations;
                                        $wsRelInput = $wsRelType === 'related' ? 'related_product_ids[]' : 'variation_product_ids[]';
                                    @endphp
                                    <div class="{{ $wsRelatedOn && $wsVariationsOn ? 'col-lg-6' : 'col-12' }} ws-relation-col">
                                        @if($wsRelatedOn && $wsVariationsOn)
                                            <h4 class="fs-16 fw-600 mb-1">{{ $wsRelType === 'related' ? 'Kapcsolódó termékek' : 'Variációk' }}</h4>
                                        @endif
                                        <span class="ws-hint mt-0 mb-3">{{ $wsRelType === 'related'
                                            ? 'A termékoldal alján ajánlott termékek.'
                                            : 'Ugyanannak a terméknek más kiszerelése vagy változata.' }}</span>
                                        <div class="form-group mb-2">
                                            <label class="sr-only">Termék keresése</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control js-{{ $wsRelType }}-product-input" placeholder="Név vagy cikkszám…" autocomplete="off">
                                                <input type="hidden" class="js-{{ $wsRelType }}-product-id">
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-info js-add-{{ $wsRelType }}-product" disabled><i class="fa fa-plus"></i> Hozzáadás</button>
                                                </div>
                                            </div>
                                            <div class="ws-search-results js-{{ $wsRelType }}-product-results" style="display:none"></div>
                                        </div>
                                        <div class="ws-relation-list js-{{ $wsRelType }}-products-list" data-empty="{{ $wsRelType === 'related' ? 'Még nincs kapcsolódó termék.' : 'Még nincs variáció.' }}">
                                            @foreach($wsRelItems as $rel)
                                                <div class="ws-relation-row js-{{ $wsRelType }}-product-row d-flex align-items-center justify-content-between p-2 mb-1 border rounded bg-light" data-id="{{ $rel->id }}">
                                                    <div class="d-flex align-items-center">
                                                        <img src="{{ $rel->primary_image_thumb ?? $rel->primary_image }}" class="img-fluid mr-2" style="width:36px;height:36px;object-fit:cover">
                                                        <div>
                                                            <div class="fw-600 lh-1">{{ $rel->name }}</div>
                                                            <small class="text-muted">{{ $rel->category->name_singular ?? '' }} @if($rel->sku) | {{ $rel->sku }} @endif</small>
                                                        </div>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-link text-danger js-remove-{{ $wsRelType }}-product" title="Eltávolítás"><i class="fa fa-trash-alt"></i></button>
                                                    <input type="hidden" name="{{ $wsRelInput }}" value="{{ $rel->id }}">
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Kötelező termékinformációk (GPSR, élelmiszer, CLP …) --}}
                    @include('admin.webshop.products.partials.legal-info-fields')

                    {{-- SEO / megosztás — mindhárom mező elhagyható: üresen a termék saját adataiból
                         képződik a tartalék (WebshopSeoService). --}}
                    <div class="ws-card" id="ws-sec-seo">
                        <header class="ws-card-head">
                            <span class="ws-card-icon"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <div class="ws-card-heading">
                                <h3 class="ws-card-title">SEO és megosztás</h3>
                                <span class="ws-card-lead">Mindegyik elhagyható: üresen a termék saját adatai kerülnek a helyükre.</span>
                            </div>
                        </header>
                        <div class="row">
                            <div class="col-lg-7">
                                <div class="form-group">
                                    <label for="og_title">Böngészőcím</label>
                                    <input type="text" class="form-control" id="og_title" name="og_title" maxlength="60"
                                           value="{{ old('og_title', $product->og_title) }}" placeholder="Alapértelmezett: a termék neve">
                                    <span class="ws-hint">Legfeljebb 60 karakter. A cégnév automatikusan a végére kerül.</span>
                                </div>
                                <div class="form-group">
                                    <label for="og_description">Meta leírás</label>
                                    <textarea class="form-control" id="og_description" name="og_description" rows="3" maxlength="160"
                                              placeholder="Alapértelmezett: a rövid leírás első mondatai">{{ old('og_description', $product->og_description) }}</textarea>
                                    <span class="ws-hint">A Google találati listájában és a közösségi megosztásnál jelenik meg. Legfeljebb 160 karakter.</span>
                                </div>
                            </div>
                            <div class="col-lg-5">
                                @include('admin.webshop.partials.og-image-input', [
                                    'ogImage' => $product->og_img,
                                    'ogHint' => 'Üresen hagyva a termék elsődleges képe megy ki.',
                                ])
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    {{-- Elsődleges kép --}}
                    <div class="ws-card" id="ws-sec-media">
                        <header class="ws-card-head">
                            <span class="ws-card-icon"><i class="fa-solid fa-image"></i></span>
                            <div class="ws-card-heading">
                                <h3 class="ws-card-title">Elsődleges kép</h3>
                                <span class="ws-card-lead">{{ $wsImgWidth }} × {{ $wsImgHeight }} px, a kategória beállítása szerint.</span>
                            </div>
                        </header>
                        @if($wsSettings::get('admin_product_primary_image_mode', 'cropper') === 'cropper')
                            @include('admin.elements.commons.img-crop-object-input', [
                                'object' => $product,
                                'label' => 'Termékkép',
                                'variable' => 'primary_image',
                                'imgWidth' => $wsImgWidth,
                                'imgHeight' => $wsImgHeight,
                            ])
                        @else
                            @if($product->primary_image)
                                <img src="{{ $product->primary_image }}" class="ws-image-preview" alt="">
                            @endif
                            <div class="form-group">
                                <label for="primary_image">Kép feltöltése</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa-regular fa-file-image fs-18"></i></span>
                                    </div>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input js-custom-file-input" id="primary_image" name="primary_image" accept="image/*">
                                        <label class="custom-file-label text-muted" for="primary_image">Kép kiválasztása</label>
                                    </div>
                                </div>
                                <span class="ws-hint">Javasolt méret: {{ $wsImgWidth }} × {{ $wsImgHeight }} px.</span>
                            </div>
                        @endif
                    </div>

                    {{-- Besorolás --}}
                    <div class="ws-card" id="ws-sec-organize">
                        <header class="ws-card-head">
                            <span class="ws-card-icon"><i class="fa-solid fa-folder-tree"></i></span>
                            <div class="ws-card-heading"><h3 class="ws-card-title">Besorolás</h3></div>
                        </header>
                        <div class="form-group">
                            <label for="category_id">Kategória <span class="ws-req">*</span></label>
                            <select class="form-control" id="category_id" name="category_id" required>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" @if(old('category_id', $product->category_id) == $cat->id) selected @endif>{{ $cat->hierarchical_name }}</option>
                                @endforeach
                            </select>
                            <span class="ws-hint">A kategória adja a tulajdonságcsoportokat és a képméretet.</span>
                        </div>
                        @if($wsLabelsOn)
                            <div class="form-group">
                                <label for="label_id">Termékcímke</label>
                                <select class="form-control" id="label_id" name="label_id">
                                    <option value="">– nincs címke –</option>
                                    @foreach($labels as $label)
                                        <option value="{{ $label->id }}" @if(old('label_id', $product->label_id) == $label->id) selected @endif>{{ $label->name }}</option>
                                    @endforeach
                                </select>
                                <span class="ws-hint">A termékkártyán színes címkeként jelenik meg (pl. Újdonság).</span>
                            </div>
                        @endif
                    </div>

                    {{-- Készlet --}}
                    @if($wsStockOn)
                        <div class="ws-card" id="ws-sec-stock">
                            <header class="ws-card-head">
                                <span class="ws-card-icon"><i class="fa-solid fa-boxes-stacked"></i></span>
                                <div class="ws-card-heading"><h3 class="ws-card-title">Készlet</h3></div>
                            </header>
                            <div class="custom-control custom-switch mb-3">
                                <input type="checkbox" class="custom-control-input" id="stock_enabled" name="stock_enabled" @if(old('stock_enabled', $product->stock_enabled)) checked @endif>
                                <label class="custom-control-label" for="stock_enabled">Készletkezelés</label>
                            </div>
                            <div class="form-group" data-ws-show-when="#stock_enabled">
                                <label for="stock_quantity">Mennyiség <span class="font-weight-normal text-muted fs-14">(db)</span></label>
                                <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}">
                            </div>
                        </div>
                    @endif

                    <div class="ws-side-sticky d-none d-xl-block">
                        <nav class="ws-section-nav js-ws-section-nav" aria-label="Szakaszok">
                            <span class="ws-section-nav-title">Ugrás</span>
                            @foreach($wsSections as [$wsSecId, $wsSecIcon, $wsSecName])
                                <a href="#{{ $wsSecId }}" class="ws-section-link"><i class="fa-solid {{ $wsSecIcon }}"></i> {{ $wsSecName }}</a>
                            @endforeach
                        </nav>
                    </div>
                </div>
            </div>

            <div class="admin-save-box">
                <div class="admin-save-btn-container mx-auto">
                    <button type="submit" class="btn btn-primary fs-18 fw-600"><i class="fa fa-save"></i> Mentés</button>
                    <a href="{{ route('admin.webshop.products.index') }}" class="btn btn-secondary fs-18">Vissza</a>
                </div>
            </div>
        </form>
    </div>

    @include('admin.commons.img-cropper')

    @include('admin.webshop.products.partials.gallery-item-alt')

    <link rel="stylesheet" href="/packages/webshop/admin/css/webshop-admin.css">
    <script src="/packages/webshop/admin/js/webshop-admin.js"></script>
    <script>
        WebshopAdmin.initToggleActive();
        WebshopAdmin.initToggleTiles('.ws-editor');
        WebshopAdmin.initSectionNav('.js-ws-section-nav');

        @if($wsGalleryOn)
        $('.js-gallery-sortable').each(function() {
            WebshopAdmin.initSortable('#' + $(this).attr('id'), $(this).data('url'), null);
        });

        // A feltöltött kép a SAJÁT galériájába kerül: a két galéria egy szakaszban van, ezért a
        // galéria-blokkhoz (nem a szakaszhoz) keressük a konténert.
        $('.js-gallery-upload').each(function() {
            var containerId = '#' + $(this).closest('.js-gallery-block').find('.js-gallery-sortable').attr('id');
            WebshopAdmin.initGalleryUpload(this, containerId);
        });
        @endif

        @if($wsDocumentsOn)
        WebshopAdmin.initSortable('.js-document-sortable .js-sortable-container', '{{ route("admin.webshop.products.documents.sort") }}', null);
        WebshopAdmin.initDocumentActions();
        @endif

        WebshopAdmin.initProductRelationPicker({
            currentProductId: {{ $product->id }},
            searchUrl: '{{ route("admin.webshop.products.search") }}'
        });
    </script>
@endsection

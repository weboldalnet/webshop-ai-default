@php
    /*
     * KATEGÓRIA-SZERKESZTŐ (újratervezve 2026-10-28), az új és a meglévő kategóriáé is. Balra a
     * tartalom (alapadatok, listakép, tulajdonságcsoportok, kapcsolódó kategóriák, SEO), jobbra az
     * elhelyezés, a megjelenés és a szakasz-navigáció. A szakaszok ugyanazon Webshop beállítások
     * mögött jelennek meg, mint korábban; a mezőnevek változatlanok.
     */
    /** @var \Weboldalnet\WebshopAiDefault\Models\WebshopCategory $category */
    $isEdit = isset($category) && $category;
    $selectedPropCats = $isEdit ? $category->propertyCategories->pluck('id')->toArray() : [];
    $showOnCardIds = $isEdit ? $category->propertyCategories->where('pivot.show_on_product_card', true)->pluck('id')->toArray() : [];
    $selectedRelated = $isEdit ? $category->relatedCategories->pluck('id')->toArray() : [];

    $wsOn = fn (string $key) => ($ws[$key] ?? 'false') === 'true';
    $wsParentOn = $wsOn('category_parent_enabled');
    $wsIconOn = $wsOn('category_icon_enabled');
    $wsSizingOn = $wsOn('category_sizing_enabled');
    $wsListImageOn = $wsOn('category_list_image_enabled');
    $wsFeedOn = $wsOn('category_merchant_feed_enabled');
    $wsRelatedOn = $wsOn('category_related_enabled');
    $wsCardPropsOn = $wsOn('category_product_card_properties_enabled');
    $wsFilterTypes = ['checkbox' => 'több választás', 'radio' => 'egy választás', 'number' => 'szám'];
    $wsListMode = old('list_image_mode', $isEdit ? $category->list_image_mode : 'cropped_upload');

    $wsSections = array_filter([
        ['ws-sec-basic', 'fa-pen', 'Alapadatok', true],
        ['ws-sec-list-image', 'fa-image', 'Listakép', $wsListImageOn],
        ['ws-sec-properties', 'fa-sliders', 'Tulajdonságcsoportok', true],
        ['ws-sec-related', 'fa-link', 'Kapcsolódó kategóriák', $wsRelatedOn],
        ['ws-sec-seo', 'fa-magnifying-glass', 'SEO és megosztás', true],
    ], fn ($section) => $section[3]);
@endphp

<div class="ws-editor-head">
    <div>
        <span class="ws-editor-kicker">Kategória</span>
        <h2 class="ws-editor-title">{{ $isEdit ? $category->name_plural : 'Új kategória' }}</h2>
        @if($isEdit)
            <div class="ws-chips">
                @if($category->is_active)
                    <span class="ws-chip is-good"><i class="fa-solid fa-circle-check"></i> Aktív</span>
                @else
                    <span class="ws-chip is-warn"><i class="fa-solid fa-eye-slash"></i> Inaktív</span>
                @endif
                @if($wsParentOn && $category->parent)
                    <span class="ws-chip"><i class="fa-solid fa-folder-tree"></i> {{ $category->parent->name_singular }} alatt</span>
                @endif
                <span class="ws-chip"><i class="fa-solid fa-box"></i> {{ $category->products()->count() }} termék</span>
                @if($category->show_in_sticky_header)
                    <span class="ws-chip is-accent"><i class="fa-solid fa-thumbtack"></i> Fejlécben</span>
                @endif
            </div>
        @endif
    </div>
    <div class="ws-editor-head-actions">
        @if($isEdit && \Illuminate\Support\Facades\Route::has('site.webshop.categories.show') && $category->slug)
            <a href="{{ route('site.webshop.categories.show', $category) }}" target="_blank" rel="noopener" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> Megtekintés
            </a>
        @endif
        <a href="{{ route('admin.webshop.categories.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left"></i> Kategórialista
        </a>
    </div>
</div>

<div class="row">
    <div class="col-xl-8">

        {{-- Alapadatok --}}
        <div class="ws-card" id="ws-sec-basic">
            <header class="ws-card-head">
                <span class="ws-card-icon"><i class="fa-solid fa-pen"></i></span>
                <div class="ws-card-heading"><h3 class="ws-card-title">Alapadatok</h3></div>
            </header>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="name_singular">Név <span class="font-weight-normal text-muted fs-14">(egyes szám)</span> <span class="ws-req">*</span></label>
                    <input type="text" class="form-control" id="name_singular" name="name_singular" value="{{ old('name_singular', $isEdit ? $category->name_singular : '') }}" required>
                    <span class="ws-hint">Pl. Mosószer — a termék adatlapján és a menüben.</span>
                </div>
                <div class="form-group col-md-6">
                    <label for="name_plural">Név <span class="font-weight-normal text-muted fs-14">(többes szám)</span> <span class="ws-req">*</span></label>
                    <input type="text" class="form-control" id="name_plural" name="name_plural" value="{{ old('name_plural', $isEdit ? $category->name_plural : '') }}" required>
                    <span class="ws-hint">Pl. Mosószerek — a kategóriaoldal címe.</span>
                </div>
            </div>
            <div class="form-group">
                <label for="description">Leírás</label>
                <textarea class="form-control js-tinymce" id="description" name="description" rows="4">{{ old('description', $isEdit ? $category->description : '') }}</textarea>
            </div>
        </div>

        {{-- Listakép --}}
        @if($wsListImageOn)
            <div class="ws-card" id="ws-sec-list-image">
                <header class="ws-card-head">
                    <span class="ws-card-icon"><i class="fa-solid fa-image"></i></span>
                    <div class="ws-card-heading">
                        <h3 class="ws-card-title">Listakép</h3>
                        <span class="ws-card-lead">A kategória kártyájának képe a kategórialistán.</span>
                    </div>
                </header>
                <div class="form-group">
                    <label for="list_image_mode">Kép forrása</label>
                    <select class="form-control js-category-image-mode" id="list_image_mode" name="list_image_mode">
                        @if($wsIconOn)
                            <option value="icon" @if($wsListMode == 'icon') selected @endif>Az ikon jelenik meg</option>
                        @endif
                        <option value="product_image" @if($wsListMode == 'product_image') selected @endif>A kategóriához tartozó termékkép jelenik meg</option>
                        <option value="upload" @if($wsListMode == 'upload') selected @endif>Képfeltöltés</option>
                        <option value="cropped_upload" @if($wsListMode == 'cropped_upload') selected @endif>Képfeltöltés képmetszővel</option>
                    </select>
                </div>

                <div class="form-group js-mode-product_image" style="display:none">
                    <label for="list_image_product_search">Termék</label>
                    <input type="text" class="form-control js-cat-product-search" id="list_image_product_search" list="cat-products-list" placeholder="Keress a kategória termékei között…" value="{{ $isEdit && $category->listImageProduct ? $category->listImageProduct->name : '' }}">
                    <datalist id="cat-products-list">
                        @if($isEdit)
                            @foreach($category->products as $p)
                                <option value="{{ $p->name }}" data-id="{{ $p->id }}"></option>
                            @endforeach
                        @endif
                    </datalist>
                    <input type="hidden" name="list_image_product_id" class="js-cat-product-id" value="{{ old('list_image_product_id', $isEdit ? $category->list_image_product_id : '') }}">
                    <span class="ws-hint">Ennek a terméknek az elsődleges képe lesz a kategória képe.</span>
                </div>

                <div class="form-group js-mode-upload" style="display:none">
                    @if($isEdit && $category->list_image_path)
                        <div class="cip"><img class="w-50 d-block mb-2 mx-auto" src="{{ $category->list_image_path }}" alt=""></div>
                    @endif
                    <label for="list_image_upload" class="mb-1">Kép feltöltése</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa-regular fa-file-image fs-18"></i></span>
                        </div>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input js-custom-file-input" id="list_image_upload" name="list_image_upload" accept="image/*">
                            <label class="custom-file-label text-muted" for="list_image_upload">Kép</label>
                        </div>
                    </div>
                    <span class="ws-hint">Vágás nélkül, eredeti méretben kerül fel.</span>
                </div>

                <div class="js-mode-cropped_upload" style="display:none">
                    @php
                        $cropSizes = config('webshop.category_list_image_crop_sizes');
                    @endphp

                    {{-- Alapértelmezett 1-es szélességű képmetsző --}}
                    <div class="mb-3">
                        @if($isEdit && $category->list_image_cropped_path)
                            <img src="{{ $category->list_image_cropped_path }}" class="ws-image-preview mx-0" alt="">
                        @endif
                        @include('admin.elements.commons.img-crop-object-input', [
                            'object' => $isEdit ? $category : new \Weboldalnet\WebshopAiDefault\Models\WebshopCategory(),
                            'label' => 'Vágott kép (1 egység)',
                            'variable' => 'list_image_cropped_upload',
                            'fieldName' => 'list_image_cropped_upload',
                            'pathVariable' => 'list_image_cropped_path',
                            'imgWidth' => $cropSizes[1]['width'] ?? 400,
                            'imgHeight' => $cropSizes[1]['height'] ?? 300,
                        ])
                    </div>

                    {{-- Szélesebb képmetszők (csak akkor jelennek meg, ha card_width_units > 1) --}}
                    @for($i = 2; $i <= 4; $i++)
                        <div class="js-wide-crop js-wide-crop-{{ $i }}" style="display:none">
                            @if($isEdit && $category->list_image_cropped_path_wide)
                                <img src="{{ $category->list_image_cropped_path_wide }}" class="ws-image-preview mx-0" alt="">
                            @endif
                            @include('admin.elements.commons.img-crop-object-input', [
                                'object' =>  $isEdit ? $category : new \Weboldalnet\WebshopAiDefault\Models\WebshopCategory(),
                                'label' => 'Széles kép (' . $i . ' egység)',
                                'variable' => 'list_image_cropped_upload_wide',
                                'fieldName' => 'list_image_cropped_upload_wide',
                                'pathVariable' => 'list_image_cropped_path_wide',
                                'imgWidth' => $cropSizes[$i]['width'] ?? (400 * $i),
                                'imgHeight' => $cropSizes[$i]['height'] ?? 300,
                            ])
                        </div>
                    @endfor
                    @if($wsSizingOn)
                        <span class="ws-hint">Széles kártyánál (Kártya mérete) a széles kép is megjelenik.</span>
                    @endif
                </div>
            </div>
        @endif

        {{-- Tulajdonságcsoportok --}}
        <div class="ws-card" id="ws-sec-properties">
            <header class="ws-card-head">
                <span class="ws-card-icon"><i class="fa-solid fa-sliders"></i></span>
                <div class="ws-card-heading">
                    <h3 class="ws-card-title">Tulajdonságcsoportok</h3>
                    <span class="ws-card-lead">A bejelöltek a kategória termékeinél elöl, kinyitva jelennek meg, és ezek szerint lehet szűrni.</span>
                </div>
            </header>
            @if($propertyCategories->isEmpty())
                <div class="ws-empty-note">Még nincs tulajdonságcsoport. A Tulajdonságok menüpontban hozhatsz létre.</div>
            @else
                <div class="ws-toggle-grid">
                    @foreach($propertyCategories as $pc)
                        @php $wsPcChecked = in_array($pc->id, old('property_categories', $selectedPropCats)); @endphp
                        <div class="ws-check-item">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="pc{{ $pc->id }}" name="property_categories[]" value="{{ $pc->id }}"
                                       @if($wsPcChecked) checked @endif>
                                <label class="custom-control-label fw-600" for="pc{{ $pc->id }}">{{ $pc->name }}</label>
                            </div>
                            <span class="ws-prop-type">{{ $wsFilterTypes[$pc->filter_type] ?? $pc->filter_type }}</span>
                            @if($wsCardPropsOn)
                                <div class="ws-check-extra pt-1" data-ws-show-when="#pc{{ $pc->id }}">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="card{{ $pc->id }}" name="show_on_product_card[]" value="{{ $pc->id }}"
                                               @if(in_array($pc->id, old('show_on_product_card', $showOnCardIds))) checked @endif>
                                        <label class="custom-control-label fs-14 text-muted" for="card{{ $pc->id }}">Termékkártyán is</label>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Kapcsolódó kategóriák --}}
        @if($wsRelatedOn)
            <div class="ws-card" id="ws-sec-related">
                <header class="ws-card-head">
                    <span class="ws-card-icon"><i class="fa-solid fa-link"></i></span>
                    <div class="ws-card-heading">
                        <h3 class="ws-card-title">Kapcsolódó kategóriák</h3>
                        <span class="ws-card-lead">A kategóriaoldalon ajánlott további kategóriák.</span>
                    </div>
                </header>
                @if($allCategories->isEmpty())
                    <div class="ws-empty-note">Nincs másik kategória.</div>
                @else
                    <div class="ws-option-list is-wide">
                        @foreach($allCategories as $cat)
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="rel{{ $cat->id }}" name="related_categories[]" value="{{ $cat->id }}"
                                       @if(in_array($cat->id, old('related_categories', $selectedRelated))) checked @endif>
                                <label class="custom-control-label" for="rel{{ $cat->id }}">{{ $cat->name_singular }}</label>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        {{-- SEO / megosztás --}}
        <div class="ws-card" id="ws-sec-seo">
            <header class="ws-card-head">
                <span class="ws-card-icon"><i class="fa-solid fa-magnifying-glass"></i></span>
                <div class="ws-card-heading">
                    <h3 class="ws-card-title">SEO és megosztás</h3>
                    <span class="ws-card-lead">Mindegyik elhagyható: üresen a kategória saját adatai kerülnek a helyükre.</span>
                </div>
            </header>
            <div class="row">
                <div class="col-lg-7">
                    <div class="form-group">
                        <label for="og_title">Böngészőcím</label>
                        <input type="text" class="form-control" id="og_title" name="og_title" maxlength="60" value="{{ old('og_title', $isEdit ? $category->og_title : '') }}" placeholder="Alapértelmezett: kategória neve">
                        <span class="ws-hint">Legfeljebb 60 karakter. A cégnév automatikusan a végére kerül.</span>
                    </div>
                    <div class="form-group">
                        <label for="og_description">Meta leírás</label>
                        {{-- 160 karakter: ez a mező adja a Google találati listájában megjelenő meta
                             leírást is, a korábbi 120-as korlát ahhoz túl szűk volt. --}}
                        <textarea class="form-control" id="og_description" name="og_description" rows="3" maxlength="160"
                                  placeholder="Alapértelmezett: a kategória leírásának első mondatai">{{ old('og_description', $isEdit ? $category->og_description : '') }}</textarea>
                        <span class="ws-hint">A Google találati listájában és a közösségi megosztásnál jelenik meg. Legfeljebb 160 karakter.</span>
                    </div>
                </div>
                <div class="col-lg-5">
                    @include('admin.webshop.partials.og-image-input', [
                        'ogImage' => $isEdit ? $category->og_img : null,
                        'ogHint' => 'Üresen hagyva a kategória listaképe megy ki.',
                    ])
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        {{-- Elhelyezés --}}
        <div class="ws-card" id="ws-sec-placement">
            <header class="ws-card-head">
                <span class="ws-card-icon"><i class="fa-solid fa-folder-tree"></i></span>
                <div class="ws-card-heading"><h3 class="ws-card-title">Elhelyezés</h3></div>
            </header>
            @if($wsParentOn)
                <div class="form-group">
                    <label for="parent_id">Szülő kategória</label>
                    <select class="form-control" id="parent_id" name="parent_id">
                        <option value="">– nincs (főkategória) –</option>
                        @foreach($allCategories as $cat)
                            <option value="{{ $cat->id }}" @if(old('parent_id', $isEdit ? $category->parent_id : '') == $cat->id) selected @endif>{{ $cat->hierarchical_name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="show_in_sticky_header" name="show_in_sticky_header" value="1"
                       @if(old('show_in_sticky_header', $isEdit ? $category->show_in_sticky_header : false)) checked @endif>
                <label class="custom-control-label" for="show_in_sticky_header">Sticky fejlécben</label>
            </div>
            <span class="ws-hint">Bekapcsolva a webshop görgetéskor is látható kategóriasávjában szerepel.</span>
        </div>

        {{-- Ikon --}}
        @if($wsIconOn)
            <div class="ws-card" id="ws-sec-icon">
                <header class="ws-card-head">
                    <span class="ws-card-icon"><i class="fa-solid fa-icons"></i></span>
                    <div class="ws-card-heading"><h3 class="ws-card-title">Ikon</h3></div>
                </header>
                {{-- A központi ikonkönyvtárból választunk, nem fájlt töltünk fel:
                     így ugyanaz az ikon több helyen újrahasználható, és a
                     karbantartása egy helyen, az Ikonok oldalon történik.
                     A komponens a kiválasztott ikon URL-jét írja az "icon" mezőbe,
                     tehát a korábban FELTÖLTÖTT ikonok URL-je változatlanul él tovább. --}}
                @include('admin.elements.commons.icon-object-input', [
                    'variable' => 'icon',
                    'iconUrl' => $isEdit ? $category->icon : null,
                ])
                <span class="ws-hint">Új ikon feltöltése az <a href="/admin/icons" target="_blank">Ikonok</a> oldalon.</span>
            </div>
        @endif

        {{-- Megjelenés: kártyaméret + a termékképek vágási mérete --}}
        <div class="ws-card" id="ws-sec-display">
            <header class="ws-card-head">
                <span class="ws-card-icon"><i class="fa-solid fa-ruler-combined"></i></span>
                <div class="ws-card-heading"><h3 class="ws-card-title">Méretek</h3></div>
            </header>
            @if($wsSizingOn)
                <div class="form-group">
                    <label for="card_width_units">Kártya mérete</label>
                    <select class="form-control" id="card_width_units" name="card_width_units">
                        @for($i=1; $i<=4; $i++)
                            <option value="{{ $i }}" @if(old('card_width_units', $isEdit ? $category->card_width_units : 1) == $i) selected @endif>{{ $i }} egység széles</option>
                        @endfor
                    </select>
                    <span class="ws-hint">A kategórialistán ennyi egység széles a kategória kártyája.</span>
                </div>
            @endif
            <label class="d-block">Termékkép mérete <span class="font-weight-normal text-muted fs-14">(px)</span></label>
            <div class="form-row">
                <div class="col-6 form-group mb-0">
                    <label for="primary_image_width" class="sr-only">Szélesség</label>
                    <div class="input-group">
                        <input type="number" class="form-control" id="primary_image_width" name="primary_image_width" value="{{ old('primary_image_width', $isEdit ? $category->primary_image_width : config('webshop.primary_image.width')) }}">
                        <div class="input-group-append"><span class="input-group-text">sz.</span></div>
                    </div>
                </div>
                <div class="col-6 form-group mb-0">
                    <label for="primary_image_height" class="sr-only">Magasság</label>
                    <div class="input-group">
                        <input type="number" class="form-control" id="primary_image_height" name="primary_image_height" value="{{ old('primary_image_height', $isEdit ? $category->primary_image_height : config('webshop.primary_image.height')) }}">
                        <div class="input-group-append"><span class="input-group-text">m.</span></div>
                    </div>
                </div>
            </div>
            <span class="ws-hint">A kategória termékeinek elsődleges képe erre a méretre vágódik.</span>
        </div>

        {{-- Merchant feed --}}
        @if($wsFeedOn)
            <div class="ws-card" id="ws-sec-feed">
                <header class="ws-card-head">
                    <span class="ws-card-icon"><i class="fa-solid fa-rss"></i></span>
                    <div class="ws-card-heading"><h3 class="ws-card-title">Merchant feed</h3></div>
                </header>
                <div class="form-group">
                    <label for="google_merchant_id">Google Merchant ID</label>
                    <input type="text" class="form-control" id="google_merchant_id" name="google_merchant_id" value="{{ old('google_merchant_id', $isEdit ? $category->google_merchant_id : '') }}">
                </div>
                <div class="form-group">
                    <label for="facebook_merchant_id">Facebook Merchant ID</label>
                    <input type="text" class="form-control" id="facebook_merchant_id" name="facebook_merchant_id" value="{{ old('facebook_merchant_id', $isEdit ? $category->facebook_merchant_id : '') }}">
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

@if($wsListImageOn)
    <script>
        $(document).ready(function() {
            function updateImageMode() {
                var mode = $('.js-category-image-mode').val();
                $('.js-mode-product_image, .js-mode-upload, .js-mode-cropped_upload').hide();
                $('.js-mode-' + mode).show();
                updateWideCropVisibility();
            }

            function updateWideCropVisibility() {
                var units = $('#card_width_units').val();
                var mode = $('.js-category-image-mode').val();

                $('.js-wide-crop').hide().find('input, select, textarea').prop('disabled', true);

                if (mode === 'cropped_upload' && units > 1) {
                    $('.js-wide-crop-' + units).show().find('input, select, textarea').prop('disabled', false);
                }
            }

            $('.js-category-image-mode').on('change', updateImageMode);
            $('#card_width_units').on('change', updateWideCropVisibility);

            updateImageMode();

            $(document).on('input', '.js-cat-product-search', function () {
                var val = $(this).val();
                var $list = $('#cat-products-list');
                var $option = $list.find('option').filter(function() {
                    return $(this).val() === val;
                });
                if ($option.length) {
                    $('.js-cat-product-id').val($option.data('id'));
                } else {
                    $('.js-cat-product-id').val('');
                }
            });
        });
    </script>
@endif

@php
    /** @var \Weboldalnet\WebshopAiDefault\Models\WebshopCategory $category */
    $isEdit = isset($category) && $category;
    $selectedPropCats = $isEdit ? $category->propertyCategories->pluck('id')->toArray() : [];
    $showOnCardIds = $isEdit ? $category->propertyCategories->where('pivot.show_on_product_card', true)->pluck('id')->toArray() : [];
    $selectedRelated = $isEdit ? $category->relatedCategories->pluck('id')->toArray() : [];
@endphp

<div class="row">
    <div class="col-lg-6 mb-3">
        <h3 class="header-box product-info">Alapadatok</h3>
        <div class="content-box bordered">
            <div class="form-group">
                <label for="name_singular">Név (egyes szám) <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="name_singular" name="name_singular" value="{{ old('name_singular', $isEdit ? $category->name_singular : '') }}" required>
            </div>
            <div class="form-group">
                <label for="name_plural">Név (többes szám) <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="name_plural" name="name_plural" value="{{ old('name_plural', $isEdit ? $category->name_plural : '') }}" required>
            </div>
            <div class="form-group">
                <label for="description">Leírás</label>
                <textarea class="form-control js-tinymce" id="description" name="description" rows="4">{{ old('description', $isEdit ? $category->description : '') }}</textarea>
            </div>
            <hr>
            <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="show_in_sticky_header" name="show_in_sticky_header" value="1"
                       @if(old('show_in_sticky_header', $isEdit ? $category->show_in_sticky_header : false)) checked @endif>
                <label class="custom-control-label" for="show_in_sticky_header">Megjelenik a sticky fejlécben</label>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-3">
        <h3 class="header-box product-info"><i class="fa-solid fa-code"></i> SEO / OG adatok</h3>
        <div class="content-box bordered">
            @if($isEdit && $category->og_img)
                <div class="mb-2"><img src="{{ $category->og_img }}" class="img-fluid" style="max-height:150px"></div>
            @endif
            <div class="form-group">
                <label for="og_img">Megosztási kép (1200x630)</label>
                <input type="file" class="form-control-file" id="og_img" name="og_img" accept=".jpg,.jpeg,.png">
            </div>
            <div class="form-group">
                <label for="og_title">OG Cím</label>
                <input type="text" class="form-control" id="og_title" name="og_title" maxlength="60" value="{{ old('og_title', $isEdit ? $category->og_title : '') }}" placeholder="Alapértelmezett: kategória neve">
            </div>
            <div class="form-group">
                <label for="og_description">OG Leírás</label>
                <textarea class="form-control" id="og_description" name="og_description" maxlength="120">{{ old('og_description', $isEdit ? $category->og_description : '') }}</textarea>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-3">
        <h3 class="header-box product-info">Termékkép méretek (metszéshez)</h3>
        <div class="content-box bordered">
            <button type="button" class="btn btn-sm btn-outline-info mb-3" data-toggle="collapse" data-target="#imageSizesCollapse">
                <i class="fa-solid fa-arrows-left-right"></i> Méretek beállítása
            </button>
            <div class="collapse" id="imageSizesCollapse">
                <div class="form-row">
                    <div class="col-md-6 form-group">
                        <label for="primary_image_width">Szélesség (px)</label>
                        <input type="number" class="form-control" id="primary_image_width" name="primary_image_width" value="{{ old('primary_image_width', $isEdit ? $category->primary_image_width : config('webshop.primary_image.width')) }}">
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="primary_image_height">Magasság (px)</label>
                        <input type="number" class="form-control" id="primary_image_height" name="primary_image_height" value="{{ old('primary_image_height', $isEdit ? $category->primary_image_height : config('webshop.primary_image.height')) }}">
                    </div>
                </div>
                <small class="form-text text-muted">A kategóriába tartozó termékek elsődleges képének vágási arányait határozza meg.</small>
            </div>
        </div>
    </div>

    @if(($ws['category_parent_enabled'] ?? 'false') === 'true')
        <div class="col-lg-6 mb-3">
            <h3 class="header-box product-info">Szülő kategória</h3>
            <div class="content-box bordered">
                <div class="form-group">
                    <label for="parent_id">Szülő kategória</label>
                    <select class="form-control" id="parent_id" name="parent_id">
                        <option value="">-- Nincs --</option>
                        @foreach($allCategories as $cat)
                            <option value="{{ $cat->id }}" @if(old('parent_id', $isEdit ? $category->parent_id : '') == $cat->id) selected @endif>{{ $cat->hierarchical_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    @endif

    @if(($ws['category_icon_enabled'] ?? 'false') === 'true')
        <div class="col-lg-6 mb-3">
            <h3 class="header-box product-info">Ikon</h3>
            <div class="content-box bordered">
                @if($isEdit && $category->icon)
                    <div class="mb-2"><img src="{{ $category->icon }}" style="max-height:60px"></div>
                @endif
                <div class="form-group">
                    <label for="icon_file">Ikon (SVG/PNG)</label>
                    <input type="file" class="form-control-file" id="icon_file" name="icon_file" accept=".svg,.png">
                </div>
            </div>
        </div>
    @endif


    @if(($ws['category_sizing_enabled'] ?? 'false') === 'true')
        <div class="col-lg-6 mb-3">
            <h3 class="header-box product-info">Kategória kártya mérete</h3>
            <div class="content-box bordered">
                <div class="form-group">
                    <label for="card_width_units">Kártya szélessége (egység)</label>
                    <select class="form-control" id="card_width_units" name="card_width_units">
                        @for($i=1; $i<=4; $i++)
                            <option value="{{ $i }}" @if(old('card_width_units', $isEdit ? $category->card_width_units : 1) == $i) selected @endif>{{ $i }} egység</option>
                        @endfor
                    </select>
                    <small class="form-text text-muted">A kategória listaoldalon ennyi egység széles lesz a kategória kártyája.</small>
                </div>
            </div>
        </div>
    @endif

    @if(($ws['category_list_image_enabled'] ?? 'false') === 'true')
        <div class="col-lg-6 mb-3">
            <h3 class="header-box product-info">Kategória listakép</h3>
            <div class="content-box bordered">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label for="list_image_mode">Kategória kép kiválasztása</label>
                            <select class="form-control js-category-image-mode" id="list_image_mode" name="list_image_mode">
                                @if(($ws['category_icon_enabled'] ?? 'false') === 'true')
                                    <option value="icon" @if(old('list_image_mode', $isEdit ? $category->list_image_mode : 'cropped_upload') == 'icon') selected @endif>Az ikon jelenik meg</option>
                                @endif
                                <option value="product_image" @if(old('list_image_mode', $isEdit ? $category->list_image_mode : 'cropped_upload') == 'product_image') selected @endif>A kategóriához tartozó termékkép jelenik meg</option>
                                <option value="upload" @if(old('list_image_mode', $isEdit ? $category->list_image_mode : 'cropped_upload') == 'upload') selected @endif>Képfeltöltés</option>
                                <option value="cropped_upload" @if(old('list_image_mode', $isEdit ? $category->list_image_mode : 'cropped_upload') == 'cropped_upload') selected @endif>Képfeltöltés képmetszővel</option>
                            </select>
                        </div>

                        <div class="form-group js-mode-product_image" style="display:none">
                            <label for="list_image_product_id">Válaszd ki a terméket</label>
                            <input type="text" class="form-control js-cat-product-search" list="cat-products-list" placeholder="Keress a kategória termékei között..." value="{{ $isEdit && $category->listImageProduct ? $category->listImageProduct->name : '' }}">
                            <datalist id="cat-products-list">
                                @if($isEdit)
                                    @foreach($category->products as $p)
                                        <option value="{{ $p->name }}" data-id="{{ $p->id }}"></option>
                                    @endforeach
                                @endif
                            </datalist>
                            <input type="hidden" name="list_image_product_id" class="js-cat-product-id" value="{{ old('list_image_product_id', $isEdit ? $category->list_image_product_id : '') }}">
                        </div>

                        <div class="form-group js-mode-upload" style="display:none">
                            @if($isEdit && $category->list_image_path)
                                <div class="mb-2"><img src="{{ $category->list_image_path }}" class="img-fluid" style="max-height: 150px"></div>
                            @endif
                            <label for="list_image_upload">Kép feltöltése</label>
                            <input type="file" class="form-control-file" id="list_image_upload" name="list_image_upload" accept="image/*">
                        </div>

                        <div class="form-group js-mode-cropped_upload" style="display:none">
                            @php
                                $cropSizes = config('webshop.category_list_image_crop_sizes');
                            @endphp

                            {{-- Alapértelmezett 1-es szélességű képmetsző --}}
                            <div class="mb-4">
                                @if($category->list_image_cropped_path)
                                    <img src="{{ $category->list_image_cropped_path }}" class="img-fluid mb-2 mx-auto" style="max-height: 150px">
                                @endif
                                @include('admin.elements.commons.img-crop-object-input', [
                                    'object' => $category,
                                    'label' => 'Vágott kép feltöltése (1 egység szélesség)',
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
                                    @if($category->list_image_cropped_path_wide)
                                        <img src="{{ $category->list_image_cropped_path_wide }}" class="img-fluid mb-2" style="max-height: 150px">
                                    @endif
                                    @include('admin.elements.commons.img-crop-object-input', [
                                        'object' => $category,
                                        'label' => 'Vágott kép feltöltése (' . $i . ' egység szélesség)',
                                        'variable' => 'list_image_cropped_upload_wide',
                                        'fieldName' => 'list_image_cropped_upload_wide',
                                        'pathVariable' => 'list_image_cropped_path_wide',
                                        'imgWidth' => $cropSizes[$i]['width'] ?? (400 * $i),
                                        'imgHeight' => $cropSizes[$i]['height'] ?? 300,
                                    ])
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(($ws['category_merchant_feed_enabled'] ?? 'false') === 'true')
        <div class="col-lg-6 mb-3">
            <h3 class="header-box product-info">Merchant Feed</h3>
            <div class="content-box bordered">
                <div class="form-group">
                    <label for="google_merchant_id">Google Merchant ID</label>
                    <input type="text" class="form-control" id="google_merchant_id" name="google_merchant_id" value="{{ old('google_merchant_id', $isEdit ? $category->google_merchant_id : '') }}">
                </div>
                <div class="form-group">
                    <label for="facebook_merchant_id">Facebook Merchant ID</label>
                    <input type="text" class="form-control" id="facebook_merchant_id" name="facebook_merchant_id" value="{{ old('facebook_merchant_id', $isEdit ? $category->facebook_merchant_id : '') }}">
                </div>
            </div>
        </div>
    @endif


</div>



<div class="row">
    <div class="col-lg-6 mb-3">
        <h3 class="header-box product-info">Tulajdonság kategóriák</h3>
        <div class="content-box bordered">
            @foreach($propertyCategories as $pc)
                <div class="custom-control custom-checkbox mb-1">
                    <input type="checkbox" class="custom-control-input" id="pc{{ $pc->id }}" name="property_categories[]" value="{{ $pc->id }}"
                           @if(in_array($pc->id, old('property_categories', $selectedPropCats))) checked @endif>
                    <label class="custom-control-label" for="pc{{ $pc->id }}">{{ $pc->name }} <span class="badge badge-info">{{ $pc->filter_type }}</span></label>
                </div>
                @if(($ws['category_product_card_properties_enabled'] ?? 'false') === 'true')
                    <div class="ml-4 mb-2">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="card{{ $pc->id }}" name="show_on_product_card[]" value="{{ $pc->id }}"
                                   @if(in_array($pc->id, old('show_on_product_card', $showOnCardIds))) checked @endif>
                            <label class="custom-control-label text-muted" for="card{{ $pc->id }}"><small>Megjelenik termékkártyán</small></label>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    @if(($ws['category_related_enabled'] ?? 'false') === 'true')
    <div class="col-lg-6 mb-3">
        <h3 class="header-box product-info">Kapcsolódó kategóriák</h3>
        <div class="content-box bordered">
            @foreach($allCategories as $cat)
                <div class="custom-control custom-checkbox mb-1">
                    <input type="checkbox" class="custom-control-input" id="rel{{ $cat->id }}" name="related_categories[]" value="{{ $cat->id }}"
                           @if(in_array($cat->id, old('related_categories', $selectedRelated))) checked @endif>
                    <label class="custom-control-label" for="rel{{ $cat->id }}">{{ $cat->name_singular }}</label>
                </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

@if(($ws['category_list_image_enabled'] ?? 'false') === 'true')
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

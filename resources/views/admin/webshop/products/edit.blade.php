@extends('admin.layouts.layout')
@section('title', $product->name . ' szerkesztése')

@section('content')
    @php
        $existingProps = $product->productProperties->groupBy('property_category_id');
    @endphp

    <div class="container mt-3 mb-150">
        @include('admin.webshop.partials.alerts')
        @include('admin.webshop.partials.form-errors')

        <form method="POST" action="{{ route('admin.webshop.products.update', $product) }}" enctype="multipart/form-data">
            @csrf @method('PUT')

            <div class="row">
                {{-- Alapadatok --}}
                <div class="col-lg-6 mb-3">
                    <h3 class="header-box product-info">Alapadatok</h3>
                    <div class="content-box bordered">
                        <div class="form-group">
                            <label for="name">Név <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $product->name) }}" required>
                        </div>
                        <div class="form-group">
                            <label for="category_id">Kategória <span class="text-danger">*</span></label>
                            <select class="form-control" id="category_id" name="category_id" required>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" @if(old('category_id', $product->category_id) == $cat->id) selected @endif>{{ $cat->hierarchical_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if(\Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopSettingsService::getBool('admin_product_labels_enabled'))
                            <div class="form-group">
                                <label for="label_id">Termék címke</label>
                                <select class="form-control" id="label_id" name="label_id">
                                    <option value="">-- Nincs címke --</option>
                                    @foreach($labels as $label)
                                        <option value="{{ $label->id }}" @if(old('label_id', $product->label_id) == $label->id) selected @endif>{{ $label->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="form-group">
                            <label for="sku">SKU</label>
                            <input type="text" class="form-control" id="sku" name="sku" value="{{ old('sku', $product->sku) }}">
                        </div>
                        <div class="form-group">
                            <label for="description">Leírás</label>
                            <textarea class="form-control js-tinymce" id="description" name="description" rows="6">{{ old('description', $product->description) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Kép --}}
                <div class="col-lg-6 mb-3">
                    <h3 class="header-box product-info">Elsődleges kép</h3>
                    <div class="content-box bordered">
                        @if(\Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopSettingsService::get('admin_product_primary_image_mode', 'cropper') === 'cropper')
                            @include('admin.elements.commons.img-crop-object-input', [
                                        'object' => $product,
                                        'label' => 'Elsődleges kép',
                                        'variable' => 'primary_image',
                                        'imgWidth' => $product->category->primary_image_width ?? config('webshop.primary_image.width'),
                                        'imgHeight' => $product->category->primary_image_height ?? config('webshop.primary_image.height'),
                                    ])
                        @else
                            @if($product->primary_image)
                                <div class="mb-2"><img src="{{ $product->primary_image }}" class="img-fluid ws-img-preview mx-auto d-block" style="max-height: 200px;"></div>
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
                                <div class="lh-12 mt-1">
                                    <span class="text-muted">Javasolt méret: {{ $product->category->primary_image_width ?? config('webshop.primary_image.width') }} x {{ $product->category->primary_image_height ?? config('webshop.primary_image.height') }}</span>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Árak --}}
                    @if(($ws['product_price_enabled'] ?? 'false') === 'true')
                        <h3 class="header-box product-info mt-3">Árak</h3>
                        <div class="content-box bordered">
                            <div class="form-group">
                                <label for="price">
                                    Ár (Ft)
                                    @if(($ws['site_product_prices_visible'] ?? 'true') === 'true')
                                        <span class="text-danger">*</span>
                                    @endif
                                </label>
                                <input type="number"
                                       class="form-control"
                                       id="price"
                                       name="price"
                                       value="{{ $product->price }}"
                                       aria-describedby="priceHelp"
                                       @if(($ws['site_product_prices_visible'] ?? 'true') === 'true') required @endif
                                >
                                <small id="priceHelp" class="form-text text-muted">Ár megadása Ft-ban. Például: 10000</small>
                            </div>
                            <div class="form-group">
                                <label for="sale_price">Akciós ár (Ft)</label>
                                <input type="number"
                                       class="form-control"
                                       id="sale_price"
                                       name="sale_price"
                                       value="{{ $product->sale_price }}"
                                >
                            </div>
                        </div>
                    @endif

                    {{-- Készlet --}}
                    @if(($ws['product_stock_enabled'] ?? 'false') === 'true')
                        <h3 class="header-box product-info mt-3">Készlet</h3>
                        <div class="content-box bordered">
                            <div class="custom-control custom-switch mb-2">
                                <input type="checkbox" class="custom-control-input" id="stock_enabled" name="stock_enabled" @if(old('stock_enabled', $product->stock_enabled)) checked @endif>
                                <label class="custom-control-label" for="stock_enabled">Készletkezelés engedélyezve</label>
                            </div>
                            <div class="form-group">
                                <label for="stock_quantity">Készlet mennyiség</label>
                                <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}">
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Tulajdonságok --}}
            <div class="row">
                <div class="col-lg-12 mb-3">
                    <h3 class="header-box product-info">Tulajdonságok</h3>
                    <div class="content-box bordered">
                        <div class="row">
                        @foreach($allPropertyCategories as $pc)
                            @php
                                $isDefault = in_array($pc->id, $categoryPropertyCatIds);
                                $pcProps = $existingProps->get($pc->id, collect());
                            @endphp
                            <div class="col-xl-4 col-md-6">
                                <div class="ws-property-group {{ !$isDefault ? 'ws-collapsed' : '' }}">
                                    <h5 class="ws-property-header {{ !$isDefault ? 'js-collapse-toggle collapsed' : '' }}" data-toggle="{{ !$isDefault ? 'collapse' : '' }}" data-target="#propGroup{{ $pc->id }}">
                                        {{ $pc->name }} <span class="badge badge-info">{{ $pc->filter_type }}</span>
                                        @if(!$isDefault) <i class="fa fa-chevron-down float-right"></i> @endif
                                    </h5>
                                    <div id="propGroup{{ $pc->id }}" class="{{ !$isDefault ? 'collapse' : '' }}">
                                        @if($pc->isNumber())
                                            <div class="form-group">
                                                <div class="input-group">
                                                    <input type="number" class="form-control" name="properties[{{ $pc->id }}][number_value]" step="0.01"
                                                           value="{{ $pcProps->first()->number_value ?? '' }}">
                                                    @if($pc->suffix)
                                                        <div class="input-group-append"><span class="input-group-text">{{ $pc->suffix }}</span></div>
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            @php $selectedPropIds = $pcProps->pluck('property_id')->toArray(); @endphp
                                            @foreach($pc->properties as $prop)
                                                @if($pc->filter_type === 'radio')
                                                    <div class="custom-control custom-radio mb-1">
                                                        <input type="radio" class="custom-control-input" id="prop{{ $prop->id }}" name="properties[{{ $pc->id }}][selected]" value="{{ $prop->id }}"
                                                               @if(in_array($prop->id, $selectedPropIds)) checked @endif>
                                                        <label class="custom-control-label" for="prop{{ $prop->id }}">{{ $prop->name }}</label>
                                                    </div>
                                                @else
                                                    <div class="custom-control custom-checkbox mb-1">
                                                        <input type="checkbox" class="custom-control-input" id="prop{{ $prop->id }}" name="properties[{{ $pc->id }}][selected][]" value="{{ $prop->id }}"
                                                               @if(in_array($prop->id, $selectedPropIds)) checked @endif>
                                                        <label class="custom-control-label" for="prop{{ $prop->id }}">{{ $prop->name }}</label>
                                                    </div>
                                                @endif
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Galéria --}}
            @if(($ws['product_gallery_enabled'] ?? 'false') === 'true')
            <div class="row">
                <div class="col-lg-12 mb-3">
                    <h3 class="header-box product-info">Galéria</h3>
                    <div class="content-box bordered">
                        <div id="gallery-sortable" class="row mb-3">
                            @foreach($product->galleryImages->sortBy('sort_order') as $img)
                                @include('admin.webshop.products.partials.gallery-item', ['product' => $product, 'img' => $img])
                            @endforeach
                        </div>
                        <hr>

                        <h5 class="fw-600">Új képek feltöltése</h5>
                        <div class="js-gallery-upload-container mw-400">
{{--                            <input type="file" class="form-control-file mr-2 mb-1 js-gallery-upload"--}}
{{--                                   data-url="{{ route('admin.webshop.products.gallery.store', $product) }}"--}}
{{--                                   accept=".jpg,.jpeg,.png,.webp" multiple>--}}
                            <div class="input-group mb-1">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa-regular fa-images fs-18"></i></span>
                                </div>
                                <div class="custom-file">
                                    <input type="file"
                                           class="custom-file-input js-gallery-upload js-custom-file-input"
                                           data-url="{{ route('admin.webshop.products.gallery.store', $product) }}"
                                           accept=".jpg,.jpeg,.png,.webp"
                                           multiple
                                    >
                                    <label class="custom-file-label text-muted">Kép kiválasztása</label>
                                </div>
                            </div>

                            <button type="button" class="btn btn-sm btn-success fs-16 js-gallery-upload-start mb-1" style="display:none">
                                <i class="fa fa-upload"></i> Kijelölt képek feltöltése
                            </button>
                            <div class="js-gallery-upload-status ml-2" style="display:none">
                                <span class="spinner-border spinner-border-sm text-primary"></span> Feltöltés... (<span class="js-gallery-upload-count">0</span>/<span class="js-gallery-upload-total">0</span>)
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Kapcsolódó termékek --}}
            @if(($ws['product_related_enabled'] ?? 'false') === 'true')
            <div class="row">
                <div class="col-lg-6 mb-3">
                    <h3 class="header-box product-info">Kapcsolódó termékek</h3>
                    <div class="content-box bordered">
                        <div class="form-group mb-2">
                            <label>Termék hozzáadása (név vagy SKU alapján)</label>
                            <div class="input-group">
                                <input type="text" class="form-control js-related-product-input" placeholder="Keressen terméket..." autocomplete="off">
                                <input type="hidden" class="js-related-product-id">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-info js-add-related-product" disabled><i class="fa fa-plus"></i> Hozzáadás</button>
                                </div>
                            </div>
                            <div class="ws-search-results js-related-product-results" style="display:none"></div>
                        </div>

                        <div class="js-related-products-list">
                            @foreach($product->relatedProducts as $rel)
                                <div class="ws-relation-row js-related-product-row d-flex align-items-center justify-content-between p-2 mb-1 border rounded bg-light" data-id="{{ $rel->id }}">
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $rel->primary_image_thumb ?? $rel->primary_image }}" class="img-fluid mr-2" style="width:30px;height:30px;object-fit:cover">
                                        <div>
                                            <div class="fw-600 lh-1">{{ $rel->name }}</div>
                                            <small class="text-muted">{{ $rel->category->name_singular ?? '' }} @if($rel->sku) | {{ $rel->sku }} @endif</small>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-link text-danger js-remove-related-product"><i class="fa fa-trash-alt"></i></button>
                                    <input type="hidden" name="related_product_ids[]" value="{{ $rel->id }}">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Variációk --}}
                @if(($ws['product_variations_enabled'] ?? 'false') === 'true')
                <div class="col-lg-6 mb-3">
                    <h3 class="header-box product-info">Variációs termékek</h3>
                    <div class="content-box bordered">
                        <div class="form-group mb-2">
                            <label>Variáció hozzáadása (név vagy SKU alapján)</label>
                            <div class="input-group">
                                <input type="text" class="form-control js-variation-product-input" placeholder="Keressen variációt..." autocomplete="off">
                                <input type="hidden" class="js-variation-product-id">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-info js-add-variation-product" disabled><i class="fa fa-plus"></i> Hozzáadás</button>
                                </div>
                            </div>
                            <div class="ws-search-results js-variation-product-results" style="display:none"></div>
                        </div>

                        <div class="js-variation-products-list">
                            @foreach($product->variations as $var)
                                <div class="ws-relation-row js-variation-product-row d-flex align-items-center justify-content-between p-2 mb-1 border rounded bg-light" data-id="{{ $var->id }}">
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $var->primary_image_thumb ?? $var->primary_image }}" class="img-fluid mr-2" style="width:30px;height:30px;object-fit:cover">
                                        <div>
                                            <div class="fw-600 lh-1">{{ $var->name }}</div>
                                            <small class="text-muted">{{ $var->category->name_singular ?? '' }} @if($var->sku) | {{ $var->sku }} @endif</small>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-link text-danger js-remove-variation-product"><i class="fa fa-trash-alt"></i></button>
                                    <input type="hidden" name="variation_product_ids[]" value="{{ $var->id }}">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>
            @endif

            <div class="admin-save-box" style="left: 0">
                <div class="admin-save-btn-container mx-auto">
                    <button type="submit" class="btn btn-primary fs-18 font-weight-bold"><i class="fa fa-save"></i> Mentés</button>
                    <a href="{{ route('admin.webshop.products.index') }}" class="btn btn-secondary">Vissza</a>
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
        @if(($ws['product_gallery_enabled'] ?? 'false') === 'true')
        WebshopAdmin.initSortable('#gallery-sortable', '{{ route("admin.webshop.products.gallery.sort") }}', null);
        WebshopAdmin.initGalleryUpload('.js-gallery-upload', '#gallery-sortable');
        @endif

        WebshopAdmin.initProductRelationPicker({
            currentProductId: {{ $product->id }},
            searchUrl: '{{ route("admin.webshop.products.search") }}'
        });
    </script>
@endsection

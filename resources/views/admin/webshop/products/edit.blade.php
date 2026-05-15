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
                        @if($product->primary_image)
                            <div class="mb-2"><img src="{{ $product->primary_image }}" class="img-fluid ws-img-preview"></div>
                        @endif
                        <div class="form-group">
                            <input type="file" class="form-control-file" id="primary_image" name="primary_image" accept=".jpg,.jpeg,.png,.webp">
                        </div>
                    </div>

                    {{-- Árak --}}
                    @if(($ws['product_price_enabled'] ?? 'false') === 'true')
                        <h3 class="header-box product-info mt-3">Árak</h3>
                        <div class="content-box bordered">
                            <div class="form-group">
                                <label for="price">Ár (Ft)</label>
                                <input type="number" class="form-control" id="price" name="price" step="0.01" value="{{ old('price', $product->price) }}">
                            </div>
                            <div class="form-group">
                                <label for="sale_price">Akciós ár (Ft)</label>
                                <input type="number" class="form-control" id="sale_price" name="sale_price" step="0.01" value="{{ old('sale_price', $product->sale_price) }}">
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
                        @foreach($allPropertyCategories as $pc)
                            @php
                                $isDefault = in_array($pc->id, $categoryPropertyCatIds);
                                $pcProps = $existingProps->get($pc->id, collect());
                            @endphp
                            <div class="ws-property-group mb-3 {{ !$isDefault ? 'ws-collapsed' : '' }}">
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
                        @endforeach
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
                                <div class="col-md-3 col-6 mb-2 ws-gallery-item">
                                    <input type="hidden" class="js-sort-id" value="{{ $img->id }}">
                                    <img src="{{ $img->image }}" class="img-fluid rounded mb-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input js-toggle-active" id="gal{{ $img->id }}" data-id="{{ $img->id }}" data-url="{{ route('admin.webshop.products.gallery.toggle-active') }}" @if($img->is_active) checked @endif>
                                            <label class="custom-control-label" for="gal{{ $img->id }}"></label>
                                        </div>
                                        <form method="POST" action="{{ route('admin.webshop.products.gallery.destroy', [$product, $img]) }}" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Törlés?')"><i class="fa fa-trash-alt"></i></button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="content-box bordered mt-2">
                        <h6>Új kép feltöltése</h6>
                        <form method="POST" action="{{ route('admin.webshop.products.gallery.store', $product) }}" enctype="multipart/form-data" class="form-inline">
                            @csrf
                            <input type="file" class="form-control-file mr-2" name="gallery_image" accept=".jpg,.jpeg,.png,.webp" required>
                            <input type="text" class="form-control mr-2" name="alt" placeholder="Alt szöveg">
                            <button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> Feltöltés</button>
                        </form>
                    </div>
                </div>
            </div>
            @endif

            {{-- Kapcsolódó termékek --}}
            @if(($ws['product_related_enabled'] ?? 'false') === 'true')
            <div class="row">
                <div class="col-lg-6 mb-3">
                    <h3 class="header-box product-info">Kapcsolódó termékek</h3>
                    <div class="content-box bordered" style="max-height:300px;overflow-y:auto">
                        @php $relatedIds = $product->relatedProducts->pluck('id')->toArray(); @endphp
                        @foreach($allProducts as $p)
                            <div class="custom-control custom-checkbox mb-1">
                                <input type="checkbox" class="custom-control-input" id="rel{{ $p->id }}" name="related_products[]" value="{{ $p->id }}" @if(in_array($p->id, $relatedIds)) checked @endif>
                                <label class="custom-control-label" for="rel{{ $p->id }}">{{ $p->name }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Variációk --}}
                @if(($ws['product_variations_enabled'] ?? 'false') === 'true')
                <div class="col-lg-6 mb-3">
                    <h3 class="header-box product-info">Variációs termékek</h3>
                    <div class="content-box bordered" style="max-height:300px;overflow-y:auto">
                        @php $variationIds = $product->variations->pluck('id')->toArray(); @endphp
                        @foreach($allProducts as $p)
                            <div class="custom-control custom-checkbox mb-1">
                                <input type="checkbox" class="custom-control-input" id="var{{ $p->id }}" name="variations[]" value="{{ $p->id }}" @if(in_array($p->id, $variationIds)) checked @endif>
                                <label class="custom-control-label" for="var{{ $p->id }}">{{ $p->name }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            @endif

            <div class="text-center mt-3">
                <button type="submit" class="btn btn-primary fs-18 font-weight-bold"><i class="fa fa-save"></i> Mentés</button>
                <a href="{{ route('admin.webshop.products.index') }}" class="btn btn-secondary">Vissza</a>
            </div>
        </form>
    </div>

    <link rel="stylesheet" href="/packages/webshop/admin/css/webshop-admin.css">
    <script src="/packages/webshop/admin/js/webshop-admin.js"></script>
    <script>
        WebshopAdmin.initToggleActive();
        @if(($ws['product_gallery_enabled'] ?? 'false') === 'true')
        WebshopAdmin.initSortable('#gallery-sortable', '{{ route("admin.webshop.products.gallery.sort") }}');
        @endif
    </script>
@endsection

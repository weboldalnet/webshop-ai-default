@extends('site.layouts.layout')
@section('title', $product->name)

@section('content')
    @include('site.webshop.partials.sticky-categories', ['category' => $product->category])

    <div class="ws-page-container ws-products-show">
        <div class="container-xl container-fluid pb-5">
            @include('site.webshop.partials.breadcrumb', ['category' => $product->category, 'product' => $product])

            <div class="">
                <h1 class="h2 mb-0 font-weight-bold">{{ $product->name }}</h1>
            </div>

            <div class="row mt-2 ws-product-main">
                <!-- Kép blokk -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="ws-product-gallery card shadow-sm">
                        <div class="ws-main-image p-3 text-center gallery">
                            @if($product->primary_image)
                                <a href="{{ $product->primary_image }}">
                                    <img src="{{ $product->primary_image }}" id="ws-main-img" alt="{{ $product->name }}" class="img-fluid" style="max-height: 100%;">
                                </a>
                            @else
                                <i class="fa fa-image fa-5x text-muted opacity-25"></i>
                            @endif

                            @if($product->galleryImages->isNotEmpty())
                                @foreach($product->galleryImages as $img)
                                    <a href="{{ $img->image }}"></a>
                                @endforeach
                            @endif
                        </div>
                        @if($product->galleryImages->isNotEmpty())
                            <div class="ws-thumbnails p-2 d-flex flex-wrap border-top">
                                <div class="ws-thumb p-1" style="width: 25%; cursor: pointer;">
                                    <img src="{{ $product->primary_image }}" class="img-fluid border rounded js-thumb active" data-src="{{ $product->primary_image }}">
                                </div>
                                @foreach($product->galleryImages as $img)
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
                            <h4 class="font-weight-bold mb-3 border-bottom pb-2">Tulajdonságok</h4>
                            <div class="ws-product-properties">
                                @foreach($product->productProperties->groupBy('property.property_category_id') as $pcId => $pProps)
                                    @php $pc = $pProps->first()->property->propertyCategory ?? $pProps->first()->propertyCategory; @endphp
                                    @if($pc)
                                        <div class="mb-lg-3 mb-0">
                                            <div class="font-weight-bold text-muted small">{{ $pc->name }}:</div>
                                            <div class="ws-prop-values">
                                                @foreach($pProps as $pp)
                                                    @if($pp->property)
                                                        <a href="{{ route('site.webshop.categories.show', $product->category) }}?f[{{ $pc->id }}][]={{ $pp->property->id }}" class="badge badge-secondary mr-1">{{ $pp->property->name }}</a>
                                                    @elseif($pp->number_value !== null)
                                                        <span class="font-weight-bold">{{ (float)$pp->number_value }}{{ $pc->suffix }}</span>
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

                            <div class="mb-4">
                                @include('site.webshop.partials.product-price', ['product' => $product])
                            </div>

                            <div class="form-group mb-4">
                                <label for="quantity" class="font-weight-bold">Mennyiség:</label>
                                <select id="quantity" class="form-control form-control w-100 js-quantity-select">
                                    @for($i=1; $i<=10; $i++)
                                        <option value="{{ $i }}">{{ $i }} db</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="mt-auto">
                                <button type="button" class="btn btn-primary btn-lg btn-block font-weight-bold py-3 mb-2 js-add-to-cart" data-id="{{ $product->id }}" data-with-qty="true">
                                    <i class="fa fa-shopping-cart mr-2"></i> Kosárba rakom
                                </button>

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

            <!-- Tab-page rész -->
            <div class="row mt-4">
                <div class="col-lg-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white p-0">
                            <ul class="nav nav-tabs ws-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active px-4 py-2 font-weight-bold fs-20" id="desc-tab" data-toggle="tab" href="#desc" role="tab">Leírás</a>
                                </li>
                                @if(($ws['site_product_reviews_enabled'] ?? 'false') === 'true')
                                    <li class="nav-item">
                                        <a class="nav-link px-4 py-2 font-weight-bold fs-20" id="reviews-tab" data-toggle="tab" href="#reviews" role="tab">Vélemények ({{ $product->reviews()->active()->count() }})</a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                        <div class="card-body tab-content p-4">
                            <div class="tab-pane fade show active" id="desc" role="tabpanel">
                                {!! $product->description !!}
                            </div>
                            @if(($ws['site_product_reviews_enabled'] ?? 'false') === 'true')
                                <div class="tab-pane fade" id="reviews" role="tabpanel">
                                    @include('site.webshop.products.partials.reviews')
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
    @endpush
@endsection

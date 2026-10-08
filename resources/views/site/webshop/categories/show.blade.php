@extends('site.layouts.layout')
@section('title', $category->name_singular)

@section('content')
    @include('site.webshop.partials.sticky-categories')

    <div class="ws-page-container ws-categories-show">
        <div class="container-xl container-fluid pb-5">
            @include('site.webshop.partials.breadcrumb', ['category' => $category])

            @php($wsHasFilters = $wsHasFilters ?? true)

            <div class="row mt-4">
                {{-- Sidebar szűrők – csak akkor, ha van egyáltalán megjeleníthető szűrő.
                     Szűrő nélküli kategóriában (pl. csak néhány, tulajdonság nélküli termék)
                     egy üres „Szűrők" doboz maradna a bal oldalon, a terméklista pedig
                     ok nélkül szűkülne a sor háromnegyedére. --}}
                @if($wsHasFilters)
                    <div class="col-lg-3 ws-filter-sidebar">
                        @include('site.webshop.categories.partials.filter-sidebar')
                    </div>
                @endif

                <!-- Terméklista rész -->
                <div class="{{ $wsHasFilters ? 'col-lg-9' : 'col-12' }} ws-product-list-wrapper">
                    <div class="row mb-3 align-items-center ws-toolbar">
                        <div class="col-md-12 d-flex justify-content-between align-items-center flex-wrap">
                            <h1 class="h3 font-weight-bold mb-0">{{ $category->name_plural }}</h1>

                            <div class="d-flex align-items-center mt-2 mt-md-0">
                                @if(($ws['site_category_view_switcher_enabled'] ?? 'false') === 'true')
                                    <div class="btn-group btn-group-sm mr-2 ws-view-switcher" role="group">
                                        <button type="button" class="btn btn-outline-dark @if(($ws['site_product_list_default_view'] ?? 'card') == 'card') active @endif js-view-mode" data-mode="card" title="Kártyás nézet"><i class="fa fa-th-large"></i></button>
                                        <button type="button" class="btn btn-outline-dark @if(($ws['site_product_list_default_view'] ?? 'card') == 'table') active @endif js-view-mode" data-mode="table" title="Táblázatos nézet"><i class="fa fa-list"></i></button>
                                    </div>
                                @endif

                                <select class="form-control form-control-sm w-auto mr-2 js-sort-select">
                                    <option value="newest" @if(request('sort') == 'newest') selected @endif>Legújabbak elöl</option>
                                    <option value="price_asc" @if(request('sort') == 'price_asc') selected @endif>Ár szerint növekvő</option>
                                    <option value="price_desc" @if(request('sort') == 'price_desc') selected @endif>Ár szerint csökkenő</option>
                                    <option value="name_asc" @if(request('sort') == 'name_asc') selected @endif>Név szerint (A-Z)</option>
                                    <option value="name_desc" @if(request('sort') == 'name_desc') selected @endif>Név szerint (Z-A)</option>
                                </select>

                                <select class="form-control form-control-sm w-auto js-per-page-select px-1">
                                    <option value="30" @if(request('per_page') == '30') selected @endif>30/oldal</option>
                                    <option value="60" @if(request('per_page') == '60') selected @endif>60/oldal</option>
                                    <option value="90" @if(request('per_page') == '90') selected @endif>90/oldal</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div id="product-list-container">
                        <div class="text-center p-5">
                            <i class="fa fa-spinner fa-spin fa-3x"></i>
                        </div>
                    </div>

                    <div id="pagination-container" class="mt-4">
                    </div>
                </div>
            </div>

            {{-- Kapcsolódó kategóriák.

                 Ez a blokk eddig HIÁNYZOTT: az admin mentette a kapcsolatot
                 (category_related_enabled), de a bolt sehol nem mutatta meg, így a
                 beállítás látszólag nem csinált semmit.

                 Csak AKTÍV kapcsolt kategóriákat mutatunk – egy inaktív kategória
                 oldala 404-et adna. --}}
            @if(($ws['category_related_enabled'] ?? 'false') === 'true')
                @php($wsRelatedCats = $category->relatedCategories()->where('is_active', true)->orderBy('sort_order')->get())

                @if($wsRelatedCats->isNotEmpty())
                    <div class="row mt-5">
                        <div class="col-lg-12">
                            <h2 class="h4 font-weight-bold mb-3">Kapcsolódó kategóriák</h2>
                            <div class="ws-related-categories d-flex flex-wrap">
                                @foreach($wsRelatedCats as $wsRelCat)
                                    <a href="{{ route('site.webshop.categories.show', $wsRelCat) }}"
                                       class="ws-related-category d-inline-flex align-items-center mr-2 mb-2 px-3 py-2 bg-white border rounded shadow-sm">
                                        @if($wsRelCat->icon)
                                            <img src="{{ $wsRelCat->icon }}" alt="" class="ws-related-category-icon mr-2">
                                        @endif
                                        <span class="font-weight-bold">{{ $wsRelCat->name_singular }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            @if($category->description)
                <div class="row mt-5">
                    <div class="col-lg-12">
                        <div class="ws-category-description-box p-lg-4 p-3 bg-white border rounded shadow-sm">
                            <h2 class="h4 font-weight-bold mb-lg-3 mb-2 text-dark">{{ $category->name_singular }}</h2>
                            <div class="ws-category-description text-muted fs-16">
                                {!! $category->description !!}
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script src="/packages/webshop/site/js/webshop-site.js"></script>
        <script>
            $(function() {
                WebshopSite.initProductList('{{ route('site.webshop.categories.products', $category) }}', {
                    category_key: '{{ $category->getRouteKey() }}',
                    view_mode: '{{ $ws['site_product_list_default_view'] ?? 'card' }}',
                    sort: '{{ request('sort', 'newest') }}',
                    per_page: '{{ request('per_page', '30') }}'
                });
            });
        </script>
    @endpush
@endsection

@extends('site.layouts.layout')
@section('title', $category->name_singular)

@section('content')
    @include('site.webshop.partials.sticky-categories')

    <div class="ws-page-container ws-categories-show">
        <div class="container-xl container-fluid pb-5">
            @include('site.webshop.partials.breadcrumb', ['category' => $category])

            <div class="row mt-4">
                <!-- Sidebar szűrők -->
                <div class="col-lg-3 ws-filter-sidebar">
                    @include('site.webshop.categories.partials.filter-sidebar')
                </div>

                <!-- Terméklista rész -->
                <div class="col-lg-9 ws-product-list-wrapper">
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
                                    <option value="newest">Legújabbak elöl</option>
                                    <option value="price_asc">Ár szerint növekvő</option>
                                    <option value="price_desc">Ár szerint csökkenő</option>
                                    <option value="name_asc">Név szerint (A-Z)</option>
                                    <option value="name_desc">Név szerint (Z-A)</option>
                                </select>

                                <select class="form-control form-control-sm w-auto js-per-page-select px-1">
                                    <option value="30">30/oldal</option>
                                    <option value="60">60/oldal</option>
                                    <option value="90">90/oldal</option>
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
        </div>
    </div>

    @push('scripts')
        <script src="/packages/webshop/site/js/webshop-site.js"></script>
        <script>
            $(function() {
                WebshopSite.initProductList('{{ route('site.webshop.categories.products', $category) }}');
            });
        </script>
    @endpush
@endsection

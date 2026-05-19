<?php
    /** @var \Weboldalnet\WebshopAiDefault\Models\WebshopProduct $prod */
?>

@extends('admin.layouts.layout')
@section('title', 'Termékek')

@section('content')
    <div class="container mt-lg-4 mt-3 mb-150">
        @include('admin.webshop.partials.alerts')

        <div class="row"><div class="col-lg-12"><h2 class="header-box"><i class="fa fa-box"></i> Termékek</h2></div></div>

        <div class="row mt-2 mb-2">
            <div class="col-lg-12 text-center">
                <a href="{{ route('admin.webshop.products.create') }}" class="btn btn-primary fs-18 font-weight-bold"><i class="fa fa-plus"></i> Új termék</a>
            </div>
        </div>

        <div class="content-box bordered mb-3">
            <form method="GET" action="{{ route('admin.webshop.products.index') }}" class="row align-items-end">
                <div class="col-lg-3 col-md-6 mb-2">
                    <label>Keresés</label>
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Név...">
                </div>
                <div class="col-lg-3 col-md-6 mb-2">
                    <label>Kategória</label>
                    <select name="category_id" class="form-control">
                        <option value="">Mind</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @if(request('category_id') == $cat->id) selected @endif>{{ $cat->name_singular }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-6 mb-2">
                    <label>Státusz</label>
                    <select name="is_active" class="form-control">
                        <option value="">Mind</option>
                        <option value="1" @if(request('is_active')==='1') selected @endif>Aktív</option>
                        <option value="0" @if(request('is_active')==='0') selected @endif>Inaktív</option>
                    </select>
                </div>
                <div class="col-lg-4 col-md-12 mb-2">
                    <button type="submit" class="btn btn-dark"><i class="fa fa-search"></i> Szűrés</button>
                    <a href="{{ route('admin.webshop.products.index') }}" class="btn btn-outline-secondary">Törlés</a>
                </div>
            </form>
        </div>

        <div class="content-box table-responsive">
            <table class="table table-hover">
                <thead class="thead-dark">
                <tr>
                    <th style="width:40px"><i class="fa fa-arrows-alt"></i></th>
                    <th class="py-1"><i class="fa fa-image fs-20"></i></th>
                    <th>Név</th>
                    <th>Kategória</th>
                    @if(($ws['product_variations_enabled'] ?? 'false') === 'true')<th>Variáció</th>@endif
                    @if(($ws['product_related_enabled'] ?? 'false') === 'true')<th>Kapcs.t</th>@endif
                    @if(($ws['product_price_enabled'] ?? 'false') === 'true')<th>Ár</th>@endif
                    <th>Aktív</th>
                    <th><i class="fa fa-pen"></i></th>
                </tr>
                </thead>
                <tbody id="sortable-list">
                @foreach($products as $prod)
                    <tr>
                        <td class="ws-drag-handle"><i class="fa fa-grip-vertical text-muted"></i><input type="hidden" class="js-sort-id" value="{{ $prod->id }}"></td>
                        <td class="py-1" style="max-width: 50px"><img src="{{ $prod->primary_image_thumb ?? $prod->primary_image }}" class="img-fluid" style="max-width: 50px; max-height: 50px;"></td>
                        <td class="font-weight-bold">{{ $prod->name }}</td>
                        <td>{{ $prod->category->name_singular ?? '-' }}</td>
                        @if(($ws['product_variations_enabled'] ?? 'false') === 'true')
                            <td>
                                @if($prod->variations_count > 0)
                                    <span class="badge badge-info fs-14">{{ $prod->variations_count }}</span>
                                @else
                                    -
                                @endif
                            </td>
                        @endif
                        @if(($ws['product_related_enabled'] ?? 'false') === 'true')
                            <td>
                                @if($prod->related_products_count > 0)
                                    <span class="badge badge-info fs-14">{{ $prod->related_products_count }}</span>
                                @else
                                    -
                                @endif
                            </td>
                        @endif
                        @if(($ws['product_price_enabled'] ?? 'false') === 'true')
                            <td class="py-1 lh-12">
                                @if($prod->sale_price)
                                    <span class="text-muted small" style="text-decoration: line-through;">{{ $prod->price ? hufFormat($prod->price) : '-' }}</span>
                                    <p class="mb-0 text-danger fw-600">{{hufFormat($prod->sale_price)}}</p>
                                @else
                                    <span>{{ $prod->price ? hufFormat($prod->price) : '-' }}</span>
                                @endif
                            </td>
                        @endif
                        <td>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input js-toggle-active" id="active{{ $prod->id }}" data-id="{{ $prod->id }}" data-url="{{ route('admin.webshop.products.toggle-active') }}" @if($prod->is_active) checked @endif>
                                <label class="custom-control-label" for="active{{ $prod->id }}"></label>
                            </div>
                        </td>
                        <td class="ws-nowrap">
                            @if(($ws['site_product_reviews_enabled'] ?? 'false') === 'true')
                                <a href="{{ route('admin.webshop.products.reviews.index', $prod) }}" class="btn btn-sm {{ $prod->reviews_count > 0 ? 'btn-info' : 'btn-outline-info' }} mr-1" title="Vélemények">
                                    <i class="fa fa-comment"></i> @if($prod->reviews_count > 0)<span class="badge badge-light ml-1">{{ $prod->reviews_count }}</span>@endif
                                </a>
                            @endif
                            <a href="{{ route('admin.webshop.products.edit', $prod) }}" class="btn btn-sm btn-primary"><i class="fa fa-pen"></i></a>
                            <button type="button" class="btn btn-sm btn-danger js-delete-btn" data-url="{{ route('admin.webshop.products.destroy', $prod) }}"><i class="fa fa-trash-alt"></i></button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @include('admin.webshop.modals.delete-confirm')
    <link rel="stylesheet" href="/packages/webshop/admin/css/webshop-admin.css">
    <script src="/packages/webshop/admin/js/webshop-admin.js"></script>
    <script>
        WebshopAdmin.initSortable('#sortable-list', '{{ route("admin.webshop.products.sort") }}');
        WebshopAdmin.initToggleActive();
        WebshopAdmin.initDeleteConfirm();
    </script>
@endsection

@extends('site.layouts.layout')
@section('title', 'Termék összehasonlítás')

@section('content')
    @include('site.webshop.partials.sticky-categories')

    <div class="ws-page-container ws-compare-index">
        <div class="container-xl container-fluid pb-5">
            <div class="row mt-4">
                <div class="col-lg-12">
                    <h1 class="mb-4 font-weight-bold"><i class="fa fa-scale-balanced mr-2"></i> Termékek összehasonlítása</h1>
                </div>
            </div>

            <div class="content-box table-responsive shadow-sm rounded">
                <table class="table table-bordered ws-compare-table bg-white">
                    <thead>
                    <tr>
                        <th class="bg-light" style="width: 200px;"></th>
                        @foreach($products as $product)
                            <th class="text-center">
                                <div class="mb-2 mx-auto" style="height: 140px; max-width: 140px;">
                                    @if($product->primary_image)
                                        <img src="{{ $product->primary_image }}" class="img-fluid h-100" style="object-fit: contain;">
                                    @else
                                        <i class="fa fa-image fa-3x text-muted opacity-25"></i>
                                    @endif
                                </div>
                                <h5 class="fs-18 font-weight-bold">{{ $product->name }}</h5>
                                <button type="button" class="btn btn-sm btn-link text-danger js-remove-compare-item" data-id="{{ $product->id }}" data-reload="true">
                                    <i class="fa fa-trash-can mr-1"></i> Törlés
                                </button>
                            </th>
                        @endforeach
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($propertyCategories as $pcId => $pc)
                        <tr>
                            <th class="bg-light">{{ $pc->name }}</th>
                            @foreach($products as $product)
                                @php
                                    $pProps = $product->productProperties->where('property_category_id', $pcId);
                                @endphp
                                <td>
                                    @forelse($pProps as $pp)
                                        @if($pp->property)
                                            <div>{{ $pp->property->name }}</div>
                                        @elseif($pp->number_value !== null)
                                            <div>{{ (float)$pp->number_value }}{{ $pc->suffix }}</div>
                                        @endif
                                    @empty
                                        <span class="text-muted">-</span>
                                    @endforelse
                                </td>
                            @endforeach
                        </tr>
                    @endforeach

                    @if(($ws['site_product_prices_visible'] ?? 'true') === 'true')
                        <tr>
                            <th class="bg-light">Ár</th>
                            @foreach($products as $product)
                                <td class="font-weight-bold">
                                    @if($product->sale_price)
                                        <div class="text-muted small" style="text-decoration: line-through;">{{ hufFormat($product->price) }}</div>
                                        <div class="text-danger h5">{{ hufFormat($product->sale_price) }}</div>
                                    @else
                                        <div class="h5">{{ hufFormat($product->price) }}</div>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endif

                    <tr>
                        <th class="bg-light">Művelet</th>
                        @foreach($products as $product)
                            <td class="text-center">
                                <button type="button" class="btn btn-primary btn-block js-add-to-cart ws-nowrap fs-16" data-id="{{ $product->id }}">
                                    <i class="fa fa-shopping-cart mr-2"></i> Kosárba
                                </button>
                            </td>
                        @endforeach
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="/packages/webshop/site/js/webshop-site.js"></script>
    @endpush
@endsection

<?php
    /** @var \Weboldalnet\WebshopAiDefault\Models\WebshopProduct $product */
?>
<div class="table-responsive">
    <table class="table ws-product-table align-middle">
        <thead class="thead-light">
        <tr>
            <th>Kép</th>
            <th>Terméknév</th>
            @php
                $cardProps = $products->first() ? $products->first()->category->propertyCategories()
                    ->wherePivot('show_on_product_card', true)
                    ->orderByPivot('sort_order')
                    ->get() : [];
            @endphp
            @foreach($cardProps as $pc)
                <th>{{ $pc->name }}</th>
            @endforeach
            @if(($ws['site_product_prices_visible'] ?? 'true') === 'true')
                <th>Ár</th>
            @endif
            <th class="text-right">Akció</th>
        </tr>
        </thead>
        <tbody>
        @foreach($products as $product)
            <tr>
                <td style="width: 90px;" class="p-1">
                    <a href="{{ route('site.webshop.products.show', $product) }}">
                        @if($product->primary_image)
                            <img src="{{ $product->primary_image_thumb ?? $product->primary_image }}" alt="{{ $product->name }}" class="" style="max-height: 82px;">
                        @else
                            <i class="fa fa-image text-muted"></i>
                        @endif
                    </a>
                </td>
                <td>
                    <a href="{{ route('site.webshop.products.show', $product) }}" class="text-dark font-weight-bold">{{ $product->name }}</a>
                </td>
                @foreach($cardProps as $pc)
                    <td class="ws-product-table-property">
                        @php
                            $pProps = $product->productProperties()->where('property_category_id', $pc->id)->get();
                        @endphp
                        @foreach($pProps as $pp)
                            @if($pp->property)
                                <div class="small">{{ $pp->property->name }}</div>
                            @elseif($pp->number_value !== null)
                                <div class="small">{{ (float)$pp->number_value }}{{ $pc->suffix }}</div>
                            @endif
                        @endforeach
                    </td>
                @endforeach
                @if(($ws['site_product_prices_visible'] ?? 'true') === 'true')
                    <td class="ws-nowrap">
                        @if($product->sale_price)
                            <div class="text-muted small" style="text-decoration: line-through;">{{ hufFormat($product->price) }}</div>
                            <div class="text-danger font-weight-bold">{{ hufFormat($product->sale_price) }}</div>
                        @else
                            <div class="font-weight-bold">{{ hufFormat($product->price) }}</div>
                        @endif
                    </td>
                @endif
                <td class="text-right ws-nowrap">
                    @if(($ws['site_product_compare_enabled'] ?? 'false') === 'true')
                        <button type="button" class="btn btn-sm btn-outline-info js-add-to-compare" data-id="{{ $product->id }}" title="Összehasonlítás">
                            <i class="fa fa-scale-balanced"></i>
                        </button>
                    @endif
                    <button type="button" class="btn btn-sm btn-primary js-add-to-cart" data-id="{{ $product->id }}">
                        <i class="fa fa-shopping-cart"></i> Kosárba
                    </button>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

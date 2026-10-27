@php
    /*
     | Kártyakép (#b6fa1b34).
     |
     | A width/height a kategória beállított vágási méretéből jön (ebből menti a képet a
     | WebshopProductController is), config-tartalékkal. Enélkül a böngésző csak a kép
     | letöltése után tudja, mennyi helyet hagyjon — ez adja a layout-ugrást (CLS).
     |
     | A lusta betöltés CSAK a lista alsóbb kártyáin indokolt. Az ELSŐ kártya képe jellemzően
     | az LCP-elem: lusta képként a böngésző későn kezdi tölteni, és ez önmagában másodperceket
     | tesz az LCP-hez (a jegy mérése: 11,04 s). Az első kártya ezért eleve töltődik, nagy
     | prioritással. Az első kártyát a hívó jelöli a `$wsElsoKartya` paraméterrel.
     */
    $wsKepSzelesseg = (int) ($product->category->primary_image_width ?? config('webshop.primary_image.width', 500));
    $wsKepMagassag = (int) ($product->category->primary_image_height ?? config('webshop.primary_image.height', 500));
    $wsElso = (bool) ($wsElsoKartya ?? false);
@endphp
<div class="card ws-product-card h-100 shadow-sm position-relative">
    @if(($ws['site_product_prices_visible'] ?? 'true') === 'true' && $product->sale_price && $product->discount_percentage > 0)
        <div class="ws-product-sale-badge badge badge-danger position-absolute">
            -{{ $product->discount_percentage }}%
        </div>
    @endif
    @if(\Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopSettingsService::getBool('admin_product_labels_enabled') && $product->label)
        <div class="ws-product-label-badge badge position-absolute"
             style="background-color: {{ $product->label->bg_color }}; color: {{ $product->label->text_color }};
                    top: {{ (($ws['site_product_prices_visible'] ?? 'true') === 'true' && $product->sale_price && $product->discount_percentage > 0) ? '45px' : '10px' }};">
            {{ $product->label->name }}
        </div>
    @endif
    <a href="{{ route('site.webshop.products.show', $product) }}" class="ws-product-card-img text-center bg-light d-flex align-items-center justify-content-center overflow-hidden" style="height: 220px;">
        @if($product->primary_image)
            <img src="{{ $product->primary_image }}" alt="{{ $product->name }}"
                 width="{{ $wsKepSzelesseg }}" height="{{ $wsKepMagassag }}"
                 @if($wsElso) fetchpriority="high" @else loading="lazy" @endif
                 class="img-fluid" style="max-height: 100%;">
        @else
            <i class="fa fa-image text-dark opacity-25 fs-30"></i>
        @endif
    </a>
    <div class="card-body d-flex py-3 flex-column">
        @if(($ws['product_secondary_name_enabled'] ?? 'false') === 'true' && $product->secondary_name)
            <h5 class="ws-product-card-title sec-title font-weight-bold mb-0">
                <a href="{{ route('site.webshop.products.show', $product) }}"
                   class="text-dark text-decoration-none">
                    {{ $product->name }}
                </a>
            </h5>
            <div class="ws-product-card-sec-title fs-14 text-muted mb-1">{{ $product->secondary_name }}</div>
        @else
            <h5 class="ws-product-card-title font-weight-bold">
                <a href="{{ route('site.webshop.products.show', $product) }}"
                   class="text-dark text-decoration-none">
                    {{ $product->name }}
                </a>
            </h5>
        @endif

        {{-- Változat-jelzés.

             A terméklistában a variációcsoportból csak a képviselő jelenik meg,
             ezért itt jelezzük, hogy a terméknek további változatai vannak –
             enélkül a vásárló nem tudná, hogy van miből válogatni.

             A variations_count a lista-lekérdezés withCount()-jából jön; ha az
             nem futott le (más hívó), a blokk egyszerűen kimarad. --}}
        @php
            $wsCardStockBadge = \Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopStockService::available($product);
        @endphp

        @if($wsCardStockBadge !== null && $wsCardStockBadge > 0 && $wsCardStockBadge <= 5)
            <div class="mb-2">
                <span class="badge badge-warning">Utolsó {{ $wsCardStockBadge }} db</span>
            </div>
        @endif

        @if(($product->variations_count ?? 0) > 0)
            <div class="ws-product-card-variations mb-2">
                <span class="badge badge-pill badge-light border">
                    <i class="fa fa-layer-group mr-1"></i>
                    {{ $product->variations_count + 1 }} változat
                </span>
            </div>
        @endif

        <div class="ws-product-card-badges mb-2">
            @if(($ws['product_short_desc_instead_of_properties_enabled'] ?? 'false') === 'true' && $product->short_desc)
                <div class="fs-14 text-muted mb-1 lh-12">
                    {{ \Illuminate\Support\Str::limit($product->short_desc, 100) }}
                </div>
            @else
                @php
                    /*
                       AKTÍV szűrés — ugyanaz, mint a terméklapon.

                       Az adminban kikapcsolt tulajdonság-kategória és a kikapcsolt
                       ÉRTÉK is megjelent itt, mert ez a nézet a terméklaptól
                       függetlenül, saját lekérdezéssel dolgozik: a terméklapi
                       eager load szűrése nem ér el idáig.

                       A szám értéket hordozó sornak nincs property_id-ja, azt tehát
                       az aktív-feltétel nem szabad hogy kizárja.

                       Egyetlen lekérdezés megy az összes megjelenítendő kategóriára,
                       nem kategóriánként egy: a terméklista kártyánként hívja ezt a
                       nézetet, ott az N+1 gyorsan összeadódik.
                    */
                    $cardProps = $product->category->propertyCategories()
                        ->where('webshop_property_categories.is_active', true)
                        ->wherePivot('show_on_product_card', true)
                        ->orderByPivot('sort_order')
                        ->get();

                    $cardPropValues = $cardProps->isEmpty()
                        ? collect()
                        : $product->productProperties()
                            ->whereIn('property_category_id', $cardProps->pluck('id'))
                            ->where(function ($q) {
                                $q->whereHas('property', fn($p) => $p->where('is_active', true))
                                    ->orWhereNull('property_id');
                            })
                            ->with('property')
                            ->get()
                            ->groupBy('property_category_id');
                @endphp
                @foreach($cardProps as $pc)
                    @foreach($cardPropValues->get($pc->id, collect()) as $pp)
                        @if($pp->property)
                            <span class="badge badge-secondary mb-1" title="{{ $pc->name }}">{{ $pp->property->name }}</span>
                        @elseif($pp->number_value !== null)
                            <span class="badge badge-info mb-1" title="{{ $pc->name }}">{{ $pc->formatNumberValue($pp->number_value) }}</span>
                        @endif
                    @endforeach
                @endforeach
            @endif
        </div>

        <div class="mt-auto pt-2 border-top d-flex flex-wrap justify-content-between align-items-end">
            @include('site.webshop.partials.product-price', [
                'product' => $product,
                'priceWrapClass' => 'mr-2',
            ])

            <div class="ws-product-card-actions ws-nowrap">
                @if(($ws['site_product_compare_enabled'] ?? 'false') === 'true')
                    <button type="button" class="btn btn-sm btn-outline-info js-add-to-compare" data-id="{{ $product->id }}" title="Összehasonlítás">
                        <i class="fa fa-scale-balanced"></i>
                    </button>
                @endif
                {{-- Elfogyott terméket ne lehessen kosárba tenni. A $wsCardStock
                     null, ha a termék korlátlan – akkor minden marad a régiben. --}}
                @php
                    $wsCardStock = \Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopStockService::available($product);
                @endphp

                @if($wsCardStock !== null && $wsCardStock <= 0)
                    <button type="button" class="btn btn-sm btn-secondary" disabled>
                        <i class="fa fa-circle-xmark"></i> Elfogyott
                    </button>
                @else
                    <button type="button" class="btn btn-sm btn-primary js-add-to-cart" data-id="{{ $product->id }}">
                        <i class="fa fa-shopping-cart"></i> Kosárba
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

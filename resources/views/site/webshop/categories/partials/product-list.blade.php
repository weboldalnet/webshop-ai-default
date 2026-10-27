@if($products->isEmpty())
    <div class="alert alert-info">Nincs a megadott feltételeknek megfelelő termék a kategóriában.</div>
@else
    @if($viewMode === 'table')
        @include('site.webshop.categories.partials.product-table', ['products' => $products, 'ws' => $ws])
    @else
        <div class="row ws-product-list">
            @php
                $colClass = ($ws['site_category_cards_per_row'] ?? '3') == '4' ? 'col-lg-3' : 'col-lg-4';

                /* Ha nincs bal oldali szűrő-oszlop, a lista a teljes sorszélességet kapja,
                   ezért nagy képernyőn négy termék is elfér egy sorban. A kisebb töréspontok
                   a beállítás szerinti értéket tartják. */
                if (!($wsHasFilters ?? true)) {
                    $colClass .= ' col-xl-3';
                }
            @endphp
            @foreach($products as $product)
                <div class="{{ $colClass }} col-md-4 col-sm-6 mb-4">
                    {{-- Az ELSŐ kártya képe jellemzően az LCP-elem, ezért az nem lehet lusta
                         betöltésű (#b6fa1b34) — a kártya ebből a jelzésből dönt. --}}
                    @include('site.webshop.categories.partials.product-card', [
                        'product' => $product,
                        'ws' => $ws,
                        'wsElsoKartya' => $loop->first,
                    ])
                </div>
            @endforeach
        </div>
    @endif
@endif

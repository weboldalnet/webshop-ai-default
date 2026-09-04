{{--
    Egy nyitóoldali blokk szekciója.

    A szekció a konténeren KÍVÜL van, és a konténer kerül BELE: így a blokk
    háttérszíne teljes oldalszélességben fut, miközben a tartalom a szokásos
    rácsban marad. (A háttér a .container-en nem érne ki a lap széléig.)

    A típus -> nézet leképezés a modell TYPES konstansából jön (view kulcs),
    mert két típus (kézzel válogatott és kategóriás termékek) UGYANAZT a nézetet
    használja. A view()->exists() védőréteg azért kell, hogy egy adatbázisban
    maradt, kivezetett típus ne vigye 500-ba az egész nyitóoldalt.
--}}
@php
    $wsView = $block->view_name ? 'site.webshop.home.partials.' . $block->view_name : null;
    $wsVanNezet = $wsView && view()->exists($wsView);

    if (!$wsVanNezet) {
        \Illuminate\Support\Facades\Log::warning('Ismeretlen nyitóoldali blokktípus – a blokk kimaradt.', [
            'block_id' => $block->id,
            'type' => $block->type,
        ]);
    }

    // A színek a mentéskor HEX-re vannak szűrve, ezért mehetnek inline stílusba
    $wsBgStyle = $block->bg_color ? 'background-color: ' . $block->bg_color . ';' : '';
    $wsTitleStyle = $block->title_color ? 'color: ' . $block->title_color . ';' : '';
@endphp

@if($wsVanNezet)
    <section class="ws-home-block ws-home-block-{{ $block->type }} py-lg-4 py-3"
             @if($wsBgStyle) style="{{ $wsBgStyle }}" @endif>
        <div class="container-xl container-fluid">
            @if($block->title)
                <h2 class="h3 font-weight-bold mb-3" @if($wsTitleStyle) style="{{ $wsTitleStyle }}" @endif>
                    {{ $block->title }}
                </h2>
            @endif

            @include($wsView, ['block' => $block, 'items' => $items])

            {{-- Gombok: ugyanaz a partial, amit a hero-k is használnak.
                 A $buttons/$btnColor/$extraClass változókat a Blade a szülő
                 scope-ból örökli, ezért itt kell őket beállítani. --}}
            @include('site.elements.items.buttons-2', [
                'buttons' => $block->buttons ?? [],
                'btnColor' => $block->btn_color ?: 'main',
                'extraClass' => $block->btn_align ?: 'justify-content-start',
            ])
        </div>
    </section>
@endif

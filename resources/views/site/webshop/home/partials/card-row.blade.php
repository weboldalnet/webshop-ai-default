{{--
    Kártyasor — az EGYETLEN hely, ahol a görgethető / többsoros elrendezés eldől.

    Paraméterek:
      $block    – a blokk (a layout mezője dönt)
      $items    – a kártyák adatai
      $cardView – a kártya nézetének neve
      $cardVar  – a kártya által várt változó neve ('product' vagy 'cat')

    A görgethető változat markupja SZÁNDÉKOSAN bit-azonos a bevált
    site.elements.services-tab-section megoldással: a public/js/section.js
    magától rákötődik a .scroll-container-re, oszloponként léptet, és a
    végeken elhalványítja a megfelelő nyilat. Új JS és új CSS nem kell hozzá.
--}}
@php
    $wsScroll = $block->isScroll();

    /* Görgethetőnél progresszív oszlopszélesség: mobilon kilóg a következő
       kártya széle, ez jelzi, hogy oldalra húzható. Többsorosnál a
       kategóriaoldal bevált oszlopai. */
    $wsCol = $wsScroll
        ? 'col-lg-3 col-md-5 col-sm-7 col-10 mb-lg-4 mb-3'
        : ((($ws['site_category_cards_per_row'] ?? '3') == '4' ? 'col-lg-3' : 'col-lg-4') . ' col-md-4 col-sm-6 mb-4');
@endphp

@if($wsScroll)
    {{-- position-relative KÖTELEZŐ: a .scroll-container üres szabály a SCSS-ben,
         ezért ki is esik a fordított CSS-ből – önmagában nem pozicionál. A nyilak
         viszont position:absolute-ok (left/right: -16px), tehát pozicionált ős
         nélkül az oldal tetejéhez tapadnának. --}}
    <div class="scroll-container position-relative">
        <div class="scroll-wrapper">
            {{-- py-3: az overflow-x:auto a függőleges tengelyt is levágja, a
                 kártya hover-emelkedése és árnyéka enélkül csonkolódna. --}}
            <div class="row scroll-row-list py-3">
                @foreach($items as $wsItem)
                    <div class="{{ $wsCol }}">
                        @include($cardView, [$cardVar => $wsItem])
                    </div>
                @endforeach
            </div>
        </div>

        <span class="scroll-left default-arrows carousel-arrows left outer">
            <i class="fa-solid fa-arrow-left"></i>
        </span>
        <span class="scroll-right default-arrows carousel-arrows right outer">
            <i class="fa-solid fa-arrow-right"></i>
        </span>
    </div>
@else
    <div class="row">
        @foreach($items as $wsItem)
            <div class="{{ $wsCol }}">
                @include($cardView, [$cardVar => $wsItem])
            </div>
        @endforeach
    </div>
@endif

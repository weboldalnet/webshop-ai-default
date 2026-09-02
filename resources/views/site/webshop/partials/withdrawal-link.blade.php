{{--
    Elállás link a láblécbe, a hozzá tartozó modallal együtt.

    A lábléc minden oldalon megjelenik, ezért a modal a webshop teljes területén
    elérhető. Mind a négy lábléc-változat ezt az egy partialt hívja, hogy a link
    és a modal egy helyen legyen karbantartva.

    Paraméterek (láblécenként eltérő elrendezés miatt):
      $linkClass – a link elrendezési osztályai
      $iconClass – az ikon színosztálya
--}}
@if(Route::has('site.webshop.withdrawals.lookup'))
    <a class="css-withdrawal {{ $linkClass ?? 'd-block py-1' }}" href="#"
       data-toggle="modal" data-target="#wsWithdrawalModal">
        <i class="fa-solid fa-rotate-left {{ $iconClass ?? '' }} mr-1"></i> Elállás a vásárlástól
    </a>

    @include('webshop::site.webshop.modals.withdrawal')
@endif

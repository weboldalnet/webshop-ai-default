{{--
    Pénztár / 6. blokk: tételes összesítő és a rendelés leadása.

    Az összegek a szállítási és a fizetési mód választásakor élőben frissülnek;
    a számolás a partials/scripts.blade.php wsUpdateSummary() függvényében van.
    A szállítási díjakat a szerver számolta ki (WebshopCheckoutController), méghozzá
    ugyanazzal a hívással, amivel a rendelés is készül – így a kiírt és a
    ténylegesen felszámított összeg nem térhet el.
--}}
<div class="card shadow-sm border-0">
    <div class="card-header bg-dark text-white font-weight-bold h5">Összegzés</div>
    <div class="card-body">
        <div class="row">
            {{-- Bal oldal: a tételes bontás --}}
            @if($wsPricesVisible)
                <div class="col-lg-6">
                    <div class="d-flex justify-content-between py-2">
                        <span>Kosár érték</span>
                        <span class="font-weight-bold">{{ hufFormat($total) }}</span>
                    </div>

                    @if($wsShippingEnabled)
                        <div class="d-flex justify-content-between py-2 border-top">
                            <span>Szállítási díj</span>
                            <span class="font-weight-bold" id="ws-sum-shipping">&mdash;</span>
                        </div>
                    @endif

                    {{-- Az utánvét-felár csak akkor jelenik meg, ha van ilyen díj
                         beállítva ÉS az utánvétes fizetés van kiválasztva.

                         A rejtést d-none/d-flex osztálycserével kell megoldani, NEM a
                         hidden attribútummal és nem is inline display:none-nal: a
                         Bootstrap .d-flex szabálya !important, ezért mindkettőt
                         felülírná, és a sor a rejtés ellenére látszana. --}}
                    <div class="d-none justify-content-between py-2 border-top" id="ws-sum-cod-row">
                        <span>Utánvét költsége</span>
                        <span class="font-weight-bold" id="ws-sum-cod">&mdash;</span>
                    </div>

                    <div class="d-flex justify-content-between py-3 border-top h4 mb-0">
                        <span class="font-weight-bold">Összesen fizetendő</span>
                        <span class="text-primary font-weight-bold" id="ws-sum-total">{{ hufFormat($total) }}</span>
                    </div>
                </div>
            @endif

            {{-- Jobb oldal: elfogadások és a rendelés leadása.
                 A belső flex-oszlop teszi lehetővé, hogy a gomb a doboz aljára
                 kerüljön akkor is, ha a bal oldali bontás magasabb. A h-100 itt
                 azért biztonságos, mert a .col egyetlen gyereke – nincs mellette
                 fejléc, amivel túllógna. --}}
            <div class="{{ $wsPricesVisible ? 'col-lg-6' : 'col-12' }}">
                <div class="d-flex flex-column h-100">
                    <div>
                        @if(($ws['site_checkout_tos_enabled'] ?? 'false') === 'true')
                            <div class="custom-control custom-checkbox mt-3 mt-lg-0">
                                <input type="checkbox" class="custom-control-input @error('accept_tos') is-invalid @enderror" id="accept_tos" name="accept_tos" value="1" {{ old('accept_tos') ? 'checked' : '' }} required>
                                <label class="custom-control-label" for="accept_tos">
                                    {{ $ws['site_checkout_tos_label'] ?? 'Elfogadom az Általános Szerződési Feltételeket' }}
                                    @php $tosLink = $ws['site_checkout_tos_url'] ?? ($ws['site_checkout_tos_path'] ?? null); @endphp
                                    @if($tosLink)
                                        (<a href="{{ $tosLink }}" target="_blank">Megnyitás</a>)
                                    @endif
                                </label>
                                @error('accept_tos') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                        @endif

                        @if(($ws['site_checkout_privacy_enabled'] ?? 'false') === 'true')
                            <div class="custom-control custom-checkbox mt-2">
                                <input type="checkbox" class="custom-control-input @error('accept_privacy') is-invalid @enderror" id="accept_privacy" name="accept_privacy" value="1" {{ old('accept_privacy') ? 'checked' : '' }} required>
                                <label class="custom-control-label" for="accept_privacy">
                                    {{ $ws['site_checkout_privacy_label'] ?? 'Elfogadom az Adatvédelmi tájékoztatót' }}
                                    @php $privLink = $ws['site_checkout_privacy_url'] ?? ($ws['site_checkout_privacy_path'] ?? null); @endphp
                                    @if($privLink)
                                        (<a href="{{ $privLink }}" target="_blank">Megnyitás</a>)
                                    @endif
                                </label>
                                @error('accept_privacy') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                        @endif
                    </div>

                    <div class="mt-auto pt-3">
                        <button type="submit" class="btn btn-success btn-lg btn-block font-weight-bold py-3 shadow mb-0">
                            <i class="fa {{ ($ws['site_checkout_mode'] ?? 'order') == 'quote' ? 'fa-paper-plane' : 'fa-check-circle' }} mr-2"></i>
                            {{ ($ws['site_checkout_mode'] ?? 'order') == 'quote' ? 'Ajánlatkérés elküldése' : 'Rendelés leadása' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

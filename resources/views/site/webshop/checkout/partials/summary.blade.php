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

                <div class="col-lg-12 mx-auto">
                    @if($wsPricesVisible)
                    <div class="d-flex flex-wrap justify-content-between py-2">
                        <span>Kosár érték</span>
                        {{-- id kell hozzá: mennyiség-változtatáskor JS-ből frissül --}}
                        <span class="font-weight-bold ws-nowrap" id="ws-sum-items">{{ hufFormat($total) }}</span>
                    </div>

                    @if($wsShippingEnabled)
                        <div class="d-flex flex-wrap justify-content-between py-2 border-top">
                            <span>Szállítási díj</span>
                            <span class="font-weight-bold ws-nowrap" id="ws-sum-shipping">&mdash;</span>
                        </div>
                    @endif

                    {{-- Az utánvét-felár csak akkor jelenik meg, ha van ilyen díj
                         beállítva ÉS az utánvétes fizetés van kiválasztva.

                         A rejtést d-none/d-flex osztálycserével kell megoldani, NEM a
                         hidden attribútummal és nem is inline display:none-nal: a
                         Bootstrap .d-flex szabálya !important, ezért mindkettőt
                         felülírná, és a sor a rejtés ellenére látszana. --}}
                    <div class="d-none flex-wrap justify-content-between py-2 border-top" id="ws-sum-cod-row">
                        <span>Utánvét költsége</span>
                        <span class="font-weight-bold ws-nowrap" id="ws-sum-cod">&mdash;</span>
                    </div>

                    {{-- Kuponkedvezmény. Ugyanaz az osztálycserés rejtés, mint az
                         utánvétnél: a Bootstrap .d-flex !important szabálya a
                         hidden attribútumot és az inline display:none-t is felülírná. --}}
                    <div class="d-none flex-wrap justify-content-between py-2 border-top" id="ws-sum-coupon-row">
                        <span>Kuponkedvezmény<span class="text-muted small ml-1" id="ws-sum-coupon-code"></span></span>
                        <span class="font-weight-bold text-success" id="ws-sum-coupon">&mdash;</span>
                    </div>

                    <div class="d-flex flex-wrap justify-content-between py-3 border-top h4 mb-0">
                        <span class="font-weight-bold">Összesen fizetendő</span>
                        <span class="text-primary font-weight-bold ws-nowrap" id="ws-sum-total">{{ hufFormat($total) }}</span>
                    </div>
                @endif

                @php
                    /*
                     | A hozzájárulások felirata és dokumentum-linkje. A `??` ITT NEM ELÉG: az admin
                     | űrlap az üresen hagyott mezőt ÜRES STRINGKÉNT menti, az pedig nem null, tehát
                     | a `??` megállt rajta. Emiatt a feltöltött PDF linkje nem jelent meg a pénztárban
                     | (a külső URL mező üres volt), és az üres felirat-mező sem esett vissza az
                     | alapértelmezett szövegre. Hibajegy: #3b64d88c.
                     */
                    $wsSzoveg = static fn ($ertek, $alap) => trim((string) ($ertek ?? '')) !== '' ? trim((string) $ertek) : $alap;

                    $tosLabel = $wsSzoveg($ws['site_checkout_tos_label'] ?? null, 'Elfogadom az Általános Szerződési Feltételeket');
                    $tosLink = $wsSzoveg($ws['site_checkout_tos_url'] ?? null, $wsSzoveg($ws['site_checkout_tos_path'] ?? null, ''));

                    $privLabel = $wsSzoveg($ws['site_checkout_privacy_label'] ?? null, 'Elfogadom az Adatvédelmi tájékoztatót');
                    $privLink = $wsSzoveg($ws['site_checkout_privacy_url'] ?? null, $wsSzoveg($ws['site_checkout_privacy_path'] ?? null, ''));
                @endphp

                <div class="d-flex flex-column">
                    <div>
                        @if(($ws['site_checkout_tos_enabled'] ?? 'false') === 'true')
                            <div class="custom-control custom-checkbox mt-3 mt-lg-0">
                                <input type="checkbox" class="custom-control-input @error('accept_tos') is-invalid @enderror" id="accept_tos" name="accept_tos" value="1" {{ old('accept_tos') ? 'checked' : '' }} required>
                                {{-- A felirat marad a jelölőnégyzet kattintási felülete; a dokumentum
                                     linkje külön áll, különben a szövegre kattintás a PDF-et nyitná
                                     meg a pipálás helyett. --}}
                                <label class="custom-control-label" for="accept_tos">
                                    {{ $tosLabel }}
                                    @if($tosLink !== '')
                                        (<a href="{{ $tosLink }}" target="_blank" rel="noopener">Megnyitás</a>)
                                    @endif
                                </label>
                                @error('accept_tos') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                        @endif

                        @if(($ws['site_checkout_privacy_enabled'] ?? 'false') === 'true')
                            <div class="custom-control custom-checkbox mt-2">
                                <input type="checkbox" class="custom-control-input @error('accept_privacy') is-invalid @enderror" id="accept_privacy" name="accept_privacy" value="1" {{ old('accept_privacy') ? 'checked' : '' }} required>
                                <label class="custom-control-label" for="accept_privacy">
                                    {{ $privLabel }}
                                    @if($privLink !== '')
                                        (<a href="{{ $privLink }}" target="_blank" rel="noopener">Megnyitás</a>)
                                    @endif
                                </label>
                                @error('accept_privacy') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                        @endif
                    </div>

                    <div class="mt-auto pt-3">
                        {{-- A felirat jogszabályi követelmény (45/2014. Korm. rendelet): rendelésnél a gombon
                             csak a „megrendelés fizetési kötelezettséggel” vagy azzal egyértelműen egyenértékű
                             szöveg állhat – ennek hiányában a vásárlót a szerződés NEM köti. Ne írd át
                             „Rendelés leadása”-ra vagy hasonlóra. Az ajánlatkérés nem fizetős megrendelés. --}}
                        <button type="submit" class="btn btn-success btn-lg btn-block font-weight-bold py-3 shadow mb-0">
                            <i class="fa {{ ($ws['site_checkout_mode'] ?? 'order') == 'quote' ? 'fa-paper-plane' : 'fa-check-circle' }} mr-2"></i>
                            {{ ($ws['site_checkout_mode'] ?? 'order') == 'quote' ? 'Ajánlatkérés elküldése' : 'Megrendelés fizetési kötelezettséggel' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

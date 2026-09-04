{{--
    Pénztár / 3. blokk: szállítási adatok.

    Két rész: a kézbesítés módja (csoportosítva) és a szállítási cím.
    A cím személyes átvételnél és átvevőpontos módoknál elrejtődik – ezt a
    lap alján lévő wsUpdateShippingAddress() intézi (partials/scripts.blade.php).
--}}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-primary text-white font-weight-bold h5">Szállítási adatok</div>
    <div class="card-body">
        @if($wsShippingEnabled)
            @error('shipping_method') <div class="alert alert-danger py-1 mb-2">{{ $message }}</div> @enderror

            {{-- A vásárló előbb a kézbesítés módját választja, és csak azon belül a
                 futárszolgálatot – így nem egy hosszú, vegyes listából kell
                 választania. Egytagú csoportnál nincs alválasztás. --}}
            @php
                $wsSelectedMethod = old('shipping_method', array_key_first($shippingMethods));
                $wsSelectedGroup = null;
                foreach (($shippingGroups ?? []) as $wsKind => $wsGroup) {
                    if (array_key_exists($wsSelectedMethod, $wsGroup['methods'])) {
                        $wsSelectedGroup = $wsKind;
                        break;
                    }
                }
            @endphp

            <div class="form-group ws-shipping-groups">
                @foreach(($shippingGroups ?? []) as $wsKind => $wsGroup)
                    <div class="custom-control custom-radio mb-2">
                        <input type="radio" name="shipping_group" id="sg_{{ $wsKind }}" value="{{ $wsKind }}"
                               class="custom-control-input js-ws-shipping-group"
                               {{ $wsSelectedGroup === $wsKind ? 'checked' : '' }}>
                        <label class="custom-control-label fw-600" for="sg_{{ $wsKind }}">{{ $wsGroup['label'] }}</label>
                    </div>

                    <div class="js-ws-shipping-methods pl-4 mb-3" data-group="{{ $wsKind }}"
                         @if($wsSelectedGroup !== $wsKind || count($wsGroup['methods']) < 2) style="display:none;" @endif>
                        @foreach($wsGroup['methods'] as $code => $label)
                            <div class="custom-control custom-radio mb-1">
                                <input type="radio" name="shipping_method" id="sm_{{ $code }}" value="{{ $code }}"
                                    class="custom-control-input ws-shipping-radio @error('shipping_method') is-invalid @enderror"
                                    {{ $wsSelectedMethod === $code ? 'checked' : '' }}>
                                <label class="custom-control-label" for="sm_{{ $code }}">
                                    {{ $label }}
                                    @if($wsPricesVisible && isset($shippingRates[$code]))
                                        <span class="text-muted small ml-1">({{ $shippingRates[$code] > 0 ? hufFormat($shippingRates[$code]) : 'ingyenes' }})</span>
                                    @endif
                                </label>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>

            <script>
                (function () {
                    var groups = document.querySelectorAll('.js-ws-shipping-group');
                    if (!groups.length) {
                        return;
                    }

                    function applyGroup(kind) {
                        var current = null;

                        document.querySelectorAll('.js-ws-shipping-methods').forEach(function (block) {
                            var isCurrent = block.getAttribute('data-group') === kind;
                            var radios = block.querySelectorAll('input[name="shipping_method"]');

                            // Egytagú csoportnál nincs mit választani, ezért nem is mutatjuk
                            block.style.display = (isCurrent && radios.length > 1) ? 'block' : 'none';

                            if (isCurrent) {
                                current = block;
                            }
                        });

                        if (!current) {
                            return;
                        }

                        var already = current.querySelector('input[name="shipping_method"]:checked');
                        var target = already || current.querySelector('input[name="shipping_method"]');

                        if (target) {
                            target.checked = true;
                            // A programozott beállítás nem vált ki change eseményt, a többi
                            // logika (cím elrejtése, átvevőpont-választó, helyszíni fizetés,
                            // összesítő) viszont erre figyel – ezért kézzel küldjük el.
                            target.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    }

                    groups.forEach(function (input) {
                        input.addEventListener('change', function () {
                            applyGroup(input.value);
                        });
                    });

                    var checked = document.querySelector('.js-ws-shipping-group:checked');
                    if (checked) {
                        applyGroup(checked.value);
                    }
                })();
            </script>

            {{-- Átvevőpont-választók: mindegyik csak akkor jelenik meg,
                 ha az adott mód elérhető és ki van választva. --}}
            @if(isset($glsParcelShopCode) && array_key_exists($glsParcelShopCode, $shippingMethods))
                @include('site.webshop.checkout.partials.gls-parcel-shop')
            @endif
            @if(isset($foxpostCode) && array_key_exists($foxpostCode, $shippingMethods))
                @include('site.webshop.checkout.partials.foxpost-apm')
            @endif
        @endif

        @if($wsAddressEnabled)
            <div id="ws-shipping-address-block" class="{{ $wsShippingEnabled ? 'mt-4' : '' }}">
                @if($wsShippingEnabled)
                    <h5 class="border-bottom pb-2 font-weight-bold">Szállítási cím</h5>
                @endif

                <div class="row">
                    <div class="col-md-3 form-group">
                        <label>Irsz. <span class="text-danger">*</span></label>
                        <input type="text" name="shipping[zip]" id="shipping_zip" class="form-control ws-shipping-address-input @error('shipping.zip') is-invalid @enderror" value="{{ old('shipping.zip') }}">
                        @error('shipping.zip') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-9 form-group">
                        <label>Város <span class="text-danger">*</span></label>
                        <input type="text" name="shipping[city]" id="shipping_city" class="form-control ws-shipping-address-input @error('shipping.city') is-invalid @enderror" value="{{ old('shipping.city') }}">
                        @error('shipping.city') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-12 form-group mb-0">
                        <label>Utca, házszám <span class="text-danger">*</span></label>
                        <input type="text" name="shipping[address]" id="shipping_address" class="form-control ws-shipping-address-input @error('shipping.address') is-invalid @enderror" value="{{ old('shipping.address') }}">
                        @error('shipping.address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

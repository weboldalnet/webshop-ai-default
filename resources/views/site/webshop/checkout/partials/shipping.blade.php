{{--
    Pénztár / 3. blokk: szállítási mód (csoportosítva) és az átvevőpont-választók.

    A bal oszlopban, a személyes adatok alatt áll; a szállítási cím külön doboz a jobb oszlop
    tetején (shipping-address.blade.php). Személyes átvételnél és átvevőpontos módoknál az a
    doboz elrejtődik – ezt a lap alján lévő
    wsUpdateShippingAddress() intézi (partials/scripts.blade.php).
--}}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-primary text-white font-weight-bold h5">Szállítási mód</div>
    <div class="card-body">
        @if($wsShippingEnabled)
            @error('shipping_method') <div class="alert alert-danger py-1 mb-2">{{ $message }}</div> @enderror

            {{-- A vásárló előbb a kézbesítés módját választja, és csak azon belül a
                 futárszolgálatot – így nem egy hosszú, vegyes listából kell
                 választania. Egytagú csoportnál nincs alválasztás. --}}
            @php
                // Az alapértelmezett mód a CSOPORTOK sorrendjét kövesse (házhoz szállítás →
                // csomagpont → személyes átvétel), ne a providerek regisztrációs sorrendjét:
                // utóbbinál a személyes átvétel került előre, és az volt előre kiválasztva.
                $wsDefaultMethod = null;
                foreach (($shippingGroups ?? []) as $wsGroup) {
                    if (!empty($wsGroup['methods'])) {
                        $wsDefaultMethod = array_key_first($wsGroup['methods']);
                        break;
                    }
                }

                $wsSelectedMethod = old('shipping_method', $wsDefaultMethod ?? array_key_first($shippingMethods));
                $wsSelectedGroup = null;
                foreach (($shippingGroups ?? []) as $wsKind => $wsGroup) {
                    if (array_key_exists($wsSelectedMethod, $wsGroup['methods'])) {
                        $wsSelectedGroup = $wsKind;
                        break;
                    }
                }
            @endphp

            <div class="form-group mb-0 ws-shipping-groups">
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
    </div>
</div>

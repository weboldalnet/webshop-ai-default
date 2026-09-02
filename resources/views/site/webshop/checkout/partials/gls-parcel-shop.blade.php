{{--
    GLS csomagpont-választó.

    A GLS saját beágyazható térképes komponensét használjuk (gls-dpm), ami
    hitelesítést nem igényel. A kiválasztott pont ADATAIN FELÜL az azonosítóját
    is elmentjük – e nélkül a címke nem generálható, mert a GLS az azonosító
    alapján tudja, melyik pontra megy a csomag.
--}}
<div id="gls-parcel-shop-wrapper" class="ws-gls-parcel-shop mt-3" style="display: none;">
    <h5 class="mb-2">GLS csomagpont választása <span class="text-danger">*</span></h5>

    <div class="ws-gls-map-holder">
        <gls-dpm country="{{ strtolower($glsCountry ?? 'hu') }}"
                 dropoffpoints-only
                 id="gls-parcel-map"
                 class="d-block position-relative w-100"
                 style="min-height: 420px; height: 100%; max-height: 520px; z-index: 1"
        ></gls-dpm>
    </div>

    <div class="mt-2">
        <input type="text" id="glsSelectedShop" class="form-control"
               placeholder="Válassz GLS csomagpontot a térképen!"
               readonly tabindex="-1" value="">

        {{-- Ezek mennek a rendeléssel. A parcel_shop_id a címkéhez kell. --}}
        <input type="hidden" name="shipping[parcel_shop_id]" id="glsShopId" value="">
        <input type="hidden" name="shipping[parcel_shop_name]" id="glsShopName" value="">
        <input type="hidden" name="shipping[parcel_shop_country]" id="glsShopCountry" value="">
        <input type="hidden" name="shipping[parcel_shop_zip]" id="glsShopZip" value="">
        <input type="hidden" name="shipping[parcel_shop_city]" id="glsShopCity" value="">
        <input type="hidden" name="shipping[parcel_shop_address]" id="glsShopAddress" value="">

        <div id="glsShopError" class="text-danger small mt-1 d-none">
            A csomagpontos szállításhoz ki kell választani egy átvevőpontot.
        </div>
    </div>
</div>

<script type="module" src="/js/gls/gls-dpm.min.js"></script>
<script>
    (function () {
        var wrapper = document.getElementById('gls-parcel-shop-wrapper');
        var map = document.getElementById('gls-parcel-map');
        if (!wrapper || !map) {
            return;
        }

        function setValue(id, value) {
            var el = document.getElementById(id);
            if (el) {
                el.value = value == null ? '' : value;
            }
        }

        map.addEventListener('change', function (e) {
            var detail = e.detail || {};
            var contact = detail.contact || {};

            // A GLS komponens verziónként eltérő kulcson adja az azonosítót,
            // ezért több lehetséges mezőt is megnézünk.
            var shopId = detail.id || detail.pclshopid || detail.parcelShopId || contact.id || '';

            setValue('glsShopId', shopId);
            setValue('glsShopName', detail.name);
            setValue('glsShopCountry', contact.countryCode);
            setValue('glsShopZip', contact.postalCode);
            setValue('glsShopCity', contact.city);
            setValue('glsShopAddress', contact.address);

            var label = [
                contact.postalCode,
                contact.city,
                contact.address
            ].filter(Boolean).join(' ');

            setValue('glsSelectedShop', detail.name ? (detail.name + ' — ' + label) : label);

            var err = document.getElementById('glsShopError');
            if (err && shopId) {
                err.classList.add('d-none');
            }

            if (!shopId) {
                console.warn('gls-dpm: a kiválasztott ponthoz nem érkezett azonosító', detail);
            }
        });

        // A választó csak akkor látszik, ha a GLS csomagpontos mód van kiválasztva
        function toggle() {
            var selected = document.querySelector('input[name="shipping_method"]:checked');
            var isParcelShop = selected && selected.value === '{{ $glsParcelShopCode ?? 'gls_parcel_shop' }}';
            wrapper.style.display = isParcelShop ? 'block' : 'none';
        }

        document.querySelectorAll('input[name="shipping_method"]').forEach(function (input) {
            input.addEventListener('change', toggle);
        });

        toggle();
    })();
</script>

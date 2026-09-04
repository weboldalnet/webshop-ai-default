{{--
    GLS csomagpont-választó.

    A GLS saját beágyazható térképes komponensét használjuk (gls-dpm), ami
    hitelesítést nem igényel. A kiválasztott pont ADATAIN FELÜL az azonosítóját
    is elmentjük – e nélkül a címke nem generálható, mert a GLS az azonosító
    alapján tudja, melyik pontra megy a csomag.
--}}
<div id="gls-parcel-shop-wrapper" class="ws-gls-parcel-shop mt-3" style="display: none;">
    <h5 class="mb-2">GLS csomagpont választása <span class="text-danger">*</span></h5>

    <style>
        /*
            A gls-dpm zárt shadow DOM-ba renderel, és a belső tartalma végig
            százalékos magasságokra épül. A százalékos magasság viszont NEM oldódik
            fel min-height ellenében – ezért a "min-height + height:100%" párossal
            a komponens 0 px magasan, láthatatlanul jött létre. A hostnak definit
            magasság kell.
        */
        .ws-gls-map-holder gls-dpm {
            display: block;
            position: relative;
            width: 100%;
            height: 480px;
            z-index: 1;
        }

        @media (max-width: 575.98px) {
            .ws-gls-map-holder gls-dpm {
                height: 380px;
            }
        }
    </style>

    {{-- A gls-dpm elemet szándékosan NEM írjuk ki ide: JS hozza létre, amikor
         a konténer már látható. A miértje a lenti ensureMap()-nél. --}}
    <div class="ws-gls-map-holder"></div>

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
        var holder = wrapper ? wrapper.querySelector('.ws-gls-map-holder') : null;
        if (!wrapper || !holder) {
            return;
        }

        var parcelShopCode = '{{ $glsParcelShopCode ?? 'gls_parcel_shop' }}';
        var country = '{{ strtolower($glsCountry ?? 'hu') }}';
        var map = null;

        function setValue(id, value) {
            var el = document.getElementById(id);
            if (el) {
                el.value = value == null ? '' : value;
            }
        }

        function onShopSelected(e) {
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
        }

        /*
            A komponenst CSAK akkor hozzuk létre, amikor a konténer már látható.

            A gls-dpm a Leafletre épül, az pedig induláskor egyszer kiméri a
            konténerét, és a méretet később nem számolja újra (a window resize
            esemény sem hozza helyre). Rejtett, 0 px-es konténerben indítva a
            térkép egyetlen csempével és csomagpontok nélkül maradt – pontosan
            ez okozta, hogy a térkép "nem nyílt meg".
        */
        function ensureMap() {
            if (map) {
                map.open = true;
                return;
            }

            map = document.createElement('gls-dpm');
            map.setAttribute('country', country);
            map.setAttribute('dropoffpoints-only', '');
            map.id = 'gls-parcel-map';
            map.addEventListener('change', onShopSelected);
            holder.appendChild(map);

            // Az open beállítása csak a custom element regisztrációja után
            // érvényes – korábban egy sima saját property lenne, amit az
            // upgrade felülírna. A rAF pedig azt biztosítja, hogy a méret
            // már ki legyen számolva, mire a térkép elindul.
            customElements.whenDefined('gls-dpm').then(function () {
                requestAnimationFrame(function () {
                    map.open = true;
                });
            });
        }

        // A választó csak akkor látszik, ha a GLS csomagpontos mód van kiválasztva
        function toggle() {
            var selected = document.querySelector('input[name="shipping_method"]:checked');
            var isParcelShop = selected && selected.value === parcelShopCode;

            wrapper.style.display = isParcelShop ? 'block' : 'none';

            if (isParcelShop) {
                ensureMap();
            }
        }

        document.querySelectorAll('input[name="shipping_method"]').forEach(function (input) {
            input.addEventListener('change', toggle);
        });

        toggle();
    })();
</script>

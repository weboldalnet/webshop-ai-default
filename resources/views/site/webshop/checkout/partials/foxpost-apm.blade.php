{{--
    FoxPost csomagautomata-választó.

    A FoxPost nem ad beágyazható komponenst, ezért saját, kereshető listát
    használunk: a csomag szerver oldali végpontja adja az automatákat, így a több
    ezer elemű lista nem kerül be a böngészőbe.

    A kiválasztott automata azonosítója ugyanabba a mezőbe kerül, mint a GLS-nél
    (shipping[parcel_shop_id]) – így a pénztár egységesen kezeli az átvevőpontokat.
--}}
<div id="foxpost-apm-wrapper" class="ws-foxpost-apm mt-3" style="display: none;">
    <h5 class="mb-2">FoxPost automata választása <span class="text-danger">*</span></h5>

    <div class="form-group mb-2">
        <input type="text" id="foxpostApmSearch" class="form-control"
               placeholder="Keresés irányítószámra, városra vagy névre…" autocomplete="off">
    </div>

    <div id="foxpostApmList" class="border rounded"
         style="max-height: 260px; overflow-y: auto;"></div>

    <div class="mt-2">
        <input type="text" id="foxpostSelectedApm" class="form-control"
               placeholder="Válassz automatát a listából!" readonly tabindex="-1" value="">

        {{-- Ez megy a rendeléssel: a címkéhez ez az azonosító kell. --}}
        <input type="hidden" name="shipping[parcel_shop_id]" id="foxpostApmId" value="">
        <input type="hidden" name="shipping[parcel_shop_name]" id="foxpostApmName" value="">

        <div id="foxpostApmError" class="text-danger small mt-1 d-none">
            A csomagautomatás szállításhoz ki kell választani egy automatát.
        </div>
    </div>
</div>

<script>
    (function () {
        var wrapper = document.getElementById('foxpost-apm-wrapper');
        if (!wrapper) {
            return;
        }

        var searchInput = document.getElementById('foxpostApmSearch');
        var list = document.getElementById('foxpostApmList');
        var selected = document.getElementById('foxpostSelectedApm');
        var idField = document.getElementById('foxpostApmId');
        var nameField = document.getElementById('foxpostApmName');
        var errorBox = document.getElementById('foxpostApmError');
        var foxpostCode = '{{ $foxpostCode ?? 'foxpost' }}';
        var searchTimer = null;
        var loaded = false;

        function render(items) {
            if (!items.length) {
                list.innerHTML = '<div class="p-3 text-muted small">Nincs találat.</div>';
                return;
            }

            var html = '';
            items.forEach(function (apm) {
                html += '<button type="button" class="btn btn-link text-left d-block w-100 px-3 py-2 border-bottom js-foxpost-apm"'
                    + ' data-id="' + apm.id + '"'
                    + ' data-name="' + (apm.name || '').replace(/"/g, '&quot;') + '"'
                    + ' data-label="' + ((apm.zip || '') + ' ' + (apm.city || '') + ', ' + (apm.address || '')).replace(/"/g, '&quot;') + '">'
                    + '<strong>' + (apm.name || apm.id) + '</strong><br>'
                    + '<small class="text-muted">' + (apm.zip || '') + ' ' + (apm.city || '') + ', ' + (apm.address || '') + '</small>'
                    + '</button>';
            });
            list.innerHTML = html;
        }

        function load(term) {
            list.innerHTML = '<div class="p-3 text-muted small">Betöltés…</div>';

            fetch('{{ route('commerce.foxpost.apms') }}?q=' + encodeURIComponent(term || ''), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function (r) { return r.json(); })
            .then(function (data) { render(data.items || []); })
            .catch(function () {
                list.innerHTML = '<div class="p-3 text-danger small">Az automaták betöltése nem sikerült.</div>';
            });
        }

        list.addEventListener('click', function (e) {
            var btn = e.target.closest('.js-foxpost-apm');
            if (!btn) {
                return;
            }

            idField.value = btn.getAttribute('data-id');
            nameField.value = btn.getAttribute('data-name');
            selected.value = btn.getAttribute('data-name') + ' — ' + btn.getAttribute('data-label');
            errorBox.classList.add('d-none');
        });

        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () { load(searchInput.value); }, 300);
        });

        // A választó csak akkor látszik, ha a FoxPost automatás mód van kiválasztva
        function toggle() {
            var checked = document.querySelector('input[name="shipping_method"]:checked');
            var isFoxpost = checked && checked.value === foxpostCode;
            wrapper.style.display = isFoxpost ? 'block' : 'none';

            // A listát csak akkor töltjük be, ha tényleg kell – felesleges kérés nélkül.
            if (isFoxpost && !loaded) {
                loaded = true;
                load('');
            }
        }

        document.querySelectorAll('input[name="shipping_method"]').forEach(function (input) {
            input.addEventListener('change', toggle);
        });

        toggle();
    })();
</script>

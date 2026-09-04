{{-- Pénztár: a lap összes dinamikus viselkedése egy helyen --}}
<script src="/packages/webshop/site/js/webshop-site.js"></script>

@php($__scripts = \Weboldalnet\WebshopAiDefault\Models\WebshopTrackingScript::byPage('checkout')->active()->ordered()->get())
@foreach($__scripts as $__s)
    {!! $__s->script !!}
@endforeach

<script>
    $(function() {
        WebshopSite.initCheckout();

        @if($onSitePaymentEnabled)
        // "Fizetés a helyszínen" dinamikus megjelenítés személyes átvételnél
        function wsUpdateOnSitePayment() {
            var selectedShipping = $('input[name="shipping_method"]:checked').val();
            var onSiteOption = $('#ws-on-site-payment-option');
            var onSiteRadio = $('#pm_on_site');

            if (selectedShipping === 'pickup') {
                onSiteOption.show();
            } else {
                onSiteOption.hide();
                if (onSiteRadio.is(':checked')) {
                    // Ha a helyszíni fizetés volt kiválasztva, de már nem személyes
                    // átvétel, töröljük. A change kiváltása azért kell, hogy az
                    // összesítő is kövesse a fizetési mód cseréjét.
                    $('input[name="payment_method"]').first().prop('checked', true).trigger('change');
                }
            }
        }
        $('input.ws-shipping-radio').on('change', wsUpdateOnSitePayment);
        // Oldal betöltésekor is ellenőrizzük
        wsUpdateOnSitePayment();
        @endif

        // Szállítási cím elrejtése személyes átvétel és átvevőpont esetén
        function wsUpdateShippingAddress() {
            var selectedShipping = $('input[name="shipping_method"]:checked').val();
            var shippingBlock = $('#ws-shipping-address-block');
            var billingSameBlock = $('#billingSameAsShipping').closest('.custom-control');
            var billingCollapse = $('.js-billing-collapse');

            // Provider-független: minden átvevőpontos módnál a csomag az
            // átvevőpontra megy, nem a vásárló címére.
            var parcelShopCodes = @json($parcelShopCodes ?? []);
            var noOwnAddress = (selectedShipping === 'pickup')
                || (parcelShopCodes.indexOf(selectedShipping) !== -1);

            if (noOwnAddress) {
                // Nincs saját szállítási cím, kötelező a számlázási cím
                if (shippingBlock.length) {
                    shippingBlock.hide();
                    shippingBlock.find('.ws-shipping-address-input').prop('required', false);
                }
                billingSameBlock.hide();
                $('#billingSameAsShipping').prop('checked', false);
                billingCollapse.addClass('show');
                billingCollapse.find('.js-billing-required').prop('required', true);
            } else {
                // Házhozszállítás: van szállítási cím, választható az azonos számlázási cím
                if (shippingBlock.length) {
                    shippingBlock.show();
                    shippingBlock.find('.ws-shipping-address-input').prop('required', true);
                }
                billingSameBlock.show();
                if ($('#billingSameAsShipping').is(':checked')) {
                    billingCollapse.removeClass('show');
                    billingCollapse.find('.js-billing-required').prop('required', false);
                } else {
                    billingCollapse.addClass('show');
                    billingCollapse.find('.js-billing-required').prop('required', true);
                }
            }
        }
        $('input.ws-shipping-radio').on('change', wsUpdateShippingAddress);
        wsUpdateShippingAddress();

        @if($wsPricesVisible)
        /*
            Tételes összesítő.

            A díjakat a szerver számolta ki minden szállítási módra, ugyanazzal a
            hívással, amivel a rendelés is készül (WebshopCommerceService::
            calculateShippingCost) – így a vásárlónak kiírt és a ténylegesen
            felszámított összeg nem térhet el egymástól.
        */
        var wsShippingRates = @json($shippingRates ?? []);
        var wsCodFee = {{ (float) ($codFee ?? 0) }};
        var wsCartTotal = {{ (float) $total }};

        // A hufFormat() PHP-helper párja: ezres tagolás szóközzel
        function wsHuf(amount) {
            return Math.round(amount).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' Ft';
        }

        function wsUpdateSummary() {
            var shippingCode = $('input[name="shipping_method"]:checked').val();
            var paymentCode = $('input[name="payment_method"]:checked').val();

            var shipping = (shippingCode && wsShippingRates[shippingCode] != null)
                ? Number(wsShippingRates[shippingCode])
                : 0;
            var cod = (paymentCode === 'cod') ? wsCodFee : 0;

            $('#ws-sum-shipping').text(shipping > 0 ? wsHuf(shipping) : 'Ingyenes');

            $('#ws-sum-cod').text(wsHuf(cod));
            // Osztálycsere, nem hidden/display:none – a Bootstrap .d-flex
            // !important szabálya azokat felülírná, és a sor mégis látszana.
            $('#ws-sum-cod-row')
                .toggleClass('d-flex', cod > 0)
                .toggleClass('d-none', cod <= 0);

            $('#ws-sum-total').text(wsHuf(wsCartTotal + shipping + cod));
        }

        // Delegált kötés: a szállítási mód rádiói csoportváltáskor is cserélődhetnek
        $(document).on('change', 'input[name="shipping_method"], input[name="payment_method"]', wsUpdateSummary);
        wsUpdateSummary();
        @endif
    });
</script>

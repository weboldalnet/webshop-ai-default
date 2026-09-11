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

        // Az aktivált kupon kedvezménye. A böngésző SOSEM számolja:
        // a szerver adja meg aktiváláskor, itt csak megjelenítjük és levonjuk.
        var wsCouponDiscount = 0;

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

            $('#ws-sum-coupon').text('-' + wsHuf(wsCouponDiscount));
            $('#ws-sum-coupon-row')
                .toggleClass('d-flex', wsCouponDiscount > 0)
                .toggleClass('d-none', wsCouponDiscount <= 0);

            $('#ws-sum-total').text(wsHuf(wsCartTotal + shipping + cod - wsCouponDiscount));
        }

        // Delegált kötés: a szállítási mód rádiói csoportváltáskor is cserélődhetnek
        $(document).on('change', 'input[name="shipping_method"], input[name="payment_method"]', wsUpdateSummary);
        wsUpdateSummary();

        /*
            Kosár-mennyiség változás a pénztáron.

            A webshop-site.js már NEM tölti újra az oldalt (az kitörölte volna a
            kitöltött űrlapot), hanem ezt az eseményt küldi az új összegekkel.
            A kupon kedvezményét is a szerver adja: a kosár változásával az
            összege is változik, és akár érvénytelenné is válhat.
        */
        $(document).on('ws:cart-updated', function (esemeny, valasz) {
            wsCartTotal = Number(valasz.total) || 0;
            $('#ws-sum-items').text(wsHuf(wsCartTotal));

            if (valasz.coupon) {
                wsCouponDiscount = Number(valasz.coupon.discount) || 0;

                if (valasz.coupon.code) {
                    $('#ws-sum-coupon-code').text('(' + valasz.coupon.code + ')');
                }

                // A kosár módosítása érvénytelenné tehette a kupont
                if (valasz.coupon.message) {
                    var $error = $('#ws-coupon-error');
                    $error.text(valasz.coupon.message).removeClass('d-none');
                    $('#ws-coupon-success').addClass('d-none');
                }
            }

            wsUpdateSummary();
        });

        /*
            Kuponbeváltás.

            A kód elküldése után a SZERVER adja vissza a kedvezményt; siker esetén
            modal ugrik fel, hiba esetén a dobozon belüli kompakt alert jelenik meg.
            Itt csak megjelenítünk – a rendelés végösszegét a szerver ugyanabból
            a forrásból számolja újra, ezért a kettő nem tud elcsúszni.
        */
        var $wsCouponInput = $('#ws-coupon-code');

        if ($wsCouponInput.length) {
            var $wsCouponButton = $('#ws-coupon-apply');
            var $wsCouponError = $('#ws-coupon-error');
            var $wsCouponSuccess = $('#ws-coupon-success');

            function wsHideCouponMessages() {
                $wsCouponError.addClass('d-none').text('');
                $wsCouponSuccess.addClass('d-none').text('');
            }

            function wsApplyCoupon() {
                var kod = ($wsCouponInput.val() || '').trim();
                wsHideCouponMessages();

                if (!kod) {
                    $wsCouponError.removeClass('d-none').text('Add meg a kuponkódot.');
                    return;
                }

                $wsCouponButton.prop('disabled', true);

                $.post($wsCouponInput.data('url'), { code: kod })
                    .done(function (valasz) {
                        if (valasz.success) {
                            wsCouponDiscount = Number(valasz.discount) || 0;
                            $('#ws-sum-coupon-code').text(' (' + valasz.coupon_code + ')');
                            wsUpdateSummary();

                            $wsCouponSuccess.removeClass('d-none').text(
                                'Aktiválva: ' + valasz.coupon_name + ' — ' + wsHuf(wsCouponDiscount) + ' kedvezmény.'
                            );

                            $('#ws-coupon-modal-name').text(valasz.coupon_name || '');
                            $('#ws-coupon-modal-amount').text(wsHuf(wsCouponDiscount));
                            $('#wsCouponModal').modal('show');
                        } else {
                            // A szerver a sikertelen beváltásnál a session-t is törli,
                            // ezért a korábbi kedvezményt itt is elvesszük.
                            wsCouponDiscount = 0;
                            wsUpdateSummary();
                            $wsCouponError.removeClass('d-none').text(valasz.message || 'A kupon nem aktiválható.');
                        }
                    })
                    .fail(function () {
                        $wsCouponError.removeClass('d-none')
                            .text('A kupon ellenőrzése nem sikerült. Kérjük, próbáld újra.');
                    })
                    .always(function () {
                        $wsCouponButton.prop('disabled', false);
                    });
            }

            $wsCouponButton.on('click', wsApplyCoupon);

            // Enterre is aktiváljon, de NE küldje el a rendelés-űrlapot
            $wsCouponInput.on('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    wsApplyCoupon();
                }
            });
        }
        @endif
    });
</script>

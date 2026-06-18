@extends('site.layouts.layout')
@section('title', ($ws['site_checkout_mode'] ?? 'order') == 'quote' ? 'Ajánlatkérés' : 'Rendelés leadása')

@section('content')
    @include('site.webshop.partials.sticky-categories')

    <div class="ws-page-container ws-checkout-index">
        <div class="container-xl container-fluid pb-5 mt-5">
            <div class="row">
                <div class="col-lg-12">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif
                    @if(session('warning'))
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            {{ session('warning') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <h1 class="mb-4 font-weight-bold border-bottom pb-2">
                        <i class="fa {{ ($ws['site_checkout_mode'] ?? 'order') == 'quote' ? 'fa-file-invoice' : 'fa-cart-check' }} mr-2"></i>
                        {{ ($ws['site_checkout_mode'] ?? 'order') == 'quote' ? 'Ajánlatkérés' : 'Pénztár' }}
                    </h1>
                </div>
            </div>

            <form method="POST" action="{{ route('site.webshop.checkout.store') }}" class="row">
                @csrf

                <!-- Bal oldal: Kosár termékek -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-dark text-white font-weight-bold h5">A kosár tartalma</div>
                        <div class="card-body">
                            @foreach($items as $productId => $item)
                                <div class="mb-3 pb-3 border-bottom ws-checkout-item-box">
                                    <div class="d-flex align-items-center ws-checkout-item" data-id="{{ $productId }}">
                                        <div class="mr-3" style="width: 80px; height: 80px;">
                                            @if($item['image'])
                                                <img src="{{ $item['image_thumb'] ?? $item['image'] }}" class="rounded border"  style="width: 80px; height: 80px; object-fit: cover">
                                            @else
                                                <div class="bg-light text-center p-2 rounded"><i class="fa fa-image text-muted"></i></div>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1">
                                            <h5 class="fs-16 font-weight-bold mb-1">
                                                {{ $item['name'] }}
                                                @if($item['sec_name'])
                                                    @if(($ws['site_product_prices_visible'] ?? 'true') === 'false') <br> @endif
                                                    <span class="font-weight-light fs-14">{{ $item['sec_name'] }}</span>
                                                @endif
                                            </h5>
                                            @if(($ws['site_product_prices_visible'] ?? 'true') === 'true')
                                                <div class="text-muted small lh-12">Egységár: {{ hufFormat($item['price']) }}</div>
                                            @endif
                                            @if(($ws['site_product_prices_visible'] ?? 'true') === 'true')
                                                <div class="d-md-none d-block font-weight-bold fs-16 mt-1" style="min-width: 100px;">
                                                    {{ hufFormat($item['quantity'] * $item['price']) }}
                                                </div>
                                            @endif

                                            <div class="d-md-flex d-none align-items-center mt-2">
                                                <div class="input-group input-group-sm" style="width: 100px;">
                                                    <div class="input-group-prepend">
                                                        <button class="btn btn-outline-secondary js-qty-minus" type="button" data-id="{{ $productId }}">-</button>
                                                    </div>
                                                    <input type="text" class="form-control text-center js-qty-input" value="{{ $item['quantity'] }}" readonly data-id="{{ $productId }}">
                                                    <div class="input-group-append">
                                                        <button class="btn btn-outline-secondary js-qty-plus" type="button" data-id="{{ $productId }}">+</button>
                                                    </div>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-link text-danger ml-3 js-remove-cart-item ws-nowrap" data-id="{{ $productId }}" data-reload="true">
                                                    <i class="fa fa-trash-alt mr-1"></i> Törlés
                                                </button>
                                            </div>
                                        </div>
                                        @if(($ws['site_product_prices_visible'] ?? 'true') === 'true')
                                            <div class="d-md-block d-none text-right font-weight-bold ml-2 fs-16" style="min-width: 100px;">
                                                {{ hufFormat($item['quantity'] * $item['price']) }}
                                            </div>
                                        @endif
                                    </div>

                                    <div class="d-md-none d-flex justify-content-between align-items-center mt-2">
                                        <div class="input-group input-group-sm" style="width: 100px;">
                                            <div class="input-group-prepend">
                                                <button class="btn btn-outline-secondary js-qty-minus" type="button" data-id="{{ $productId }}">-</button>
                                            </div>
                                            <input type="text" class="form-control text-center js-qty-input" value="{{ $item['quantity'] }}" readonly data-id="{{ $productId }}">
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary js-qty-plus" type="button" data-id="{{ $productId }}">+</button>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-link text-danger ml-3 js-remove-cart-item ws-nowrap" data-id="{{ $productId }}" data-reload="true">
                                            <i class="fa fa-trash-alt mr-1"></i> Törlés
                                        </button>
                                    </div>
                                </div>
                            @endforeach

                            @if(($ws['site_product_prices_visible'] ?? 'true') === 'true')
                                <div class="d-flex flex-sm-row flex-column justify-content-between align-items-center mt-4 pt-3 border-top h4">
                                    <span class="font-weight-bold">Összesen fizetendő:</span>
                                    <span class="text-primary font-weight-bold js-cart-total">{{ hufFormat($total) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Jobb oldal: Ügyfél adatok -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-primary text-white font-weight-bold h5">Személyes adatok</div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="name">Név <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="email">E-mail cím <span class="text-danger">*</span></label>
                                    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            @if(($ws['site_checkout_phone_enabled'] ?? 'false') === 'true')
                                <div class="form-group">
                                    <label for="phone">Telefonszám <span class="text-danger">*</span></label>
                                    <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" required>
                                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            @endif

                            @if(($ws['site_checkout_company_enabled'] ?? 'false') === 'true')
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label for="company">Cégnév <span class="text-danger">*</span></label>
                                        <input type="text" name="company" id="company" class="form-control @error('company') is-invalid @enderror" value="{{ old('company') }}" required>
                                        @error('company') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    @if(($ws['site_checkout_tax_number_enabled'] ?? 'false') === 'true')
                                        <div class="col-md-6 form-group">
                                            <label for="tax_number">Adószám @if(($ws['site_checkout_tax_number_enabled'] ?? 'false') === 'true') <span class="text-danger">*</span> @endif</label>
                                            <input type="text" name="tax_number" id="tax_number" class="form-control @error('tax_number') is-invalid @enderror" value="{{ old('tax_number') }}" required>
                                            @error('tax_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    @endif
                                </div>
                            @endif

                            @if(($ws['site_checkout_billing_enabled'] ?? 'false') === 'true')
                                <div class="d-flex flex-sm-row flex-column justify-content-between align-items-sm-center align-items-start mt-4 border-bottom pb-2 mb-3">
                                    <h5 class="m-0 font-weight-bold">Számlázási adatok</h5>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" name="billing_same_as_shipping" class="custom-control-input js-billing-same-as-shipping" id="billingSameAsShipping" value="1" {{ old('billing_same_as_shipping', '1') == '1' ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="billingSameAsShipping">Megegyezik a szállítással</label>
                                    </div>
                                </div>

                                <div class="collapse {{ old('billing_same_as_shipping', '1') == '1' ? '' : 'show' }} js-billing-collapse">
                                    <div class="row">
                                        <div class="col-md-12 form-group">
                                            <label>Számlázási név <span class="text-danger">*</span></label>
                                            <input type="text" name="billing[name]" class="form-control js-billing-required @error('billing.name') is-invalid @enderror" value="{{ old('billing.name') }}" {{ old('billing_same_as_shipping', '1') == '1' ? '' : 'required' }}>
                                            @error('billing.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-3 form-group">
                                            <label>Irsz. <span class="text-danger">*</span></label>
                                            <input type="text" name="billing[zip]" class="form-control js-billing-required @error('billing.zip') is-invalid @enderror" value="{{ old('billing.zip') }}" {{ old('billing_same_as_shipping', '1') == '1' ? '' : 'required' }}>
                                            @error('billing.zip') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-9 form-group">
                                            <label>Város <span class="text-danger">*</span></label>
                                            <input type="text" name="billing[city]" class="form-control js-billing-required @error('billing.city') is-invalid @enderror" value="{{ old('billing.city') }}" {{ old('billing_same_as_shipping', '1') == '1' ? '' : 'required' }}>
                                            @error('billing.city') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-12 form-group">
                                            <label>Utca, házszám <span class="text-danger">*</span></label>
                                            <input type="text" name="billing[address]" class="form-control js-billing-required @error('billing.address') is-invalid @enderror" value="{{ old('billing.address') }}" {{ old('billing_same_as_shipping', '1') == '1' ? '' : 'required' }}>
                                            @error('billing.address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if(!$isQuoteMode)
                                @if(!empty($shippingMethods))
                                    <h5 class="mt-4 border-bottom pb-2 font-weight-bold">Szállítási mód</h5>
                                    @error('shipping_method') <div class="alert alert-danger py-1 mb-2">{{ $message }}</div> @enderror
                                    <div class="form-group">
                                        @foreach($shippingMethods as $code => $label)
                                            <div class="custom-control custom-radio mb-2">
                                                <input type="radio" name="shipping_method" id="sm_{{ $code }}" value="{{ $code }}"
                                                    class="custom-control-input ws-shipping-radio @error('shipping_method') is-invalid @enderror"
                                                    {{ old('shipping_method', array_key_first($shippingMethods)) === $code ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="sm_{{ $code }}">{{ $label }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            @endif

                            @if(($ws['site_checkout_shipping_enabled'] ?? 'false') === 'true')
                                <div id="ws-shipping-address-block">
                                    <h5 class="mt-4 border-bottom pb-2 font-weight-bold">Szállítási adatok</h5>
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
                                        <div class="col-md-12 form-group">
                                            <label>Utca, házszám <span class="text-danger">*</span></label>
                                            <input type="text" name="shipping[address]" id="shipping_address" class="form-control ws-shipping-address-input @error('shipping.address') is-invalid @enderror" value="{{ old('shipping.address') }}">
                                            @error('shipping.address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if(!$isQuoteMode)
                                @if(!empty($paymentMethods))
                                    <h5 class="mt-4 border-bottom pb-2 font-weight-bold">Fizetési mód</h5>
                                    @error('payment_method') <div class="alert alert-danger py-1 mb-2">{{ $message }}</div> @enderror
                                    <div class="form-group" id="ws-payment-methods-group">
                                        @foreach($paymentMethods as $code => $label)
                                            <div class="custom-control custom-radio mb-2">
                                                <input type="radio" name="payment_method" id="pm_{{ $code }}" value="{{ $code }}"
                                                    class="custom-control-input @error('payment_method') is-invalid @enderror"
                                                    {{ old('payment_method', array_key_first($paymentMethods)) === $code ? 'checked' : '' }} required>
                                                <label class="custom-control-label" for="pm_{{ $code }}">{{ $label }}</label>
                                            </div>
                                        @endforeach
                                        @if($onSitePaymentEnabled)
                                            <div class="custom-control custom-radio mb-2" id="ws-on-site-payment-option" style="display:none;">
                                                <input type="radio" name="payment_method" id="pm_on_site" value="on_site"
                                                    class="custom-control-input @error('payment_method') is-invalid @enderror"
                                                    {{ old('payment_method') === 'on_site' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="pm_on_site">Fizetés a helyszínen</label>
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <div class="alert alert-warning mt-3">Jelenleg nincs elérhető fizetési mód.</div>
                                @endif
                            @endif

                            <div class="form-group mt-3">
                                <label for="note">Megjegyzés</label>
                                <textarea name="note" id="note" class="form-control" rows="3">{{ old('note') }}</textarea>
                            </div>

                            @if(($ws['site_checkout_tos_enabled'] ?? 'false') === 'true')
                                <div class="custom-control custom-checkbox mt-3">
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

                            <button type="submit" class="btn btn-success btn-lg btn-block mt-4 font-weight-bold py-3 shadow">
                                <i class="fa {{ ($ws['site_checkout_mode'] ?? 'order') == 'quote' ? 'fa-paper-plane' : 'fa-check-circle' }} mr-2"></i>
                                {{ ($ws['site_checkout_mode'] ?? 'order') == 'quote' ? 'Ajánlatkérés elküldése' : 'Rendelés leadása' }}
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
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
                            // Ha a helyszíni fizetés volt kiválasztva, de már nem személyes átvétel, töröljük
                            $('input[name="payment_method"]').first().prop('checked', true);
                        }
                    }
                }
                $('input.ws-shipping-radio').on('change', wsUpdateOnSitePayment);
                // Oldal betöltésekor is ellenőrizzük
                wsUpdateOnSitePayment();
                @endif

                // Szállítási adatok elrejtése személyes átvétel esetén
                function wsUpdateShippingAddress() {
                    var selectedShipping = $('input[name="shipping_method"]:checked').val();
                    var shippingBlock = $('#ws-shipping-address-block');
                    var billingSameBlock = $('#billingSameAsShipping').closest('.custom-control');
                    var billingCollapse = $('.js-billing-collapse');

                    if (selectedShipping === 'pickup') {
                        // Személyes átvétel: nincs szállítási cím, kötelező a számlázási cím
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
            });
        </script>
    @endpush
@endsection

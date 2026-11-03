{{--
    Pénztár / számlázási adatok — saját doboz a jobb oszlopban, a szállítási cím alatt.

    A „Megegyezik a szállítással” jelölőtől függően nyílik ki (webshop-site.js initCheckout()).
    Személyes átvételnél és csomagpontnál nincs saját szállítási cím: ilyenkor a jelölő
    eltűnik, a mezők kötelezők (scripts.blade.php › wsUpdateShippingAddress()).
    Bejelentkezett vásárlónál a mezők a fiókból töltődnek ki ($checkoutPrefill).
--}}
@php
    $wsPrefill = $checkoutPrefill ?? [];
    $wsSameDefault = array_key_exists('billing_same_as_shipping', $wsPrefill) ? ($wsPrefill['billing_same_as_shipping'] ? '1' : '0') : '1';
    $wsSame = old('billing_same_as_shipping', old('_token') ? null : $wsSameDefault) == '1';
    $wsSavedBilling = $wsPrefill['billing_addresses'] ?? [];
@endphp
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-primary text-white font-weight-bold h5">{{ __('webshop::checkout.billing.title') }}</div>
    <div class="card-body">
        <div class="custom-control custom-checkbox">
            <input type="checkbox" name="billing_same_as_shipping" class="custom-control-input js-billing-same-as-shipping" id="billingSameAsShipping" value="1" {{ $wsSame ? 'checked' : '' }}>
            <label class="custom-control-label" for="billingSameAsShipping">{{ __('webshop::checkout.billing.same_as_shipping') }}</label>
        </div>

        <div class="collapse {{ $wsSame ? '' : 'show' }} js-billing-collapse">
            {{-- a felső térköz csak a látható jelölő alatt kell; a szkript veszi le, ha a jelölő rejtve van --}}
            <div class="pt-3 js-billing-fields">
                @if(count($wsSavedBilling) > 1)
                    <div class="form-group">
                        <label for="ws_saved_billing">{{ __('webshop::checkout.billing.saved') }}</label>
                        <select id="ws_saved_billing" class="form-control js-ws-saved-address" data-target="billing">
                            @foreach($wsSavedBilling as $wsSaved)
                                <option value="{{ $wsSaved['id'] }}" data-address='@json($wsSaved)'>
                                    {{ $wsSaved['name'] }} – {{ $wsSaved['zip'] }} {{ $wsSaved['city'] }}, {{ $wsSaved['address'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="row">
                    <div class="col-md-12 form-group">
                        <label for="billing_name">{{ __('webshop::checkout.billing.name') }} <span class="text-danger">*</span></label>
                        <input type="text" name="billing[name]" id="billing_name" class="form-control js-billing-required @error('billing.name') is-invalid @enderror" value="{{ old('billing.name', $wsPrefill['billing']['name'] ?? '') }}" {{ $wsSame ? '' : 'required' }}>
                        @error('billing.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="billing_zip">{{ __('webshop::checkout.address.zip') }} <span class="text-danger">*</span></label>
                        <input type="text" name="billing[zip]" id="billing_zip" class="form-control js-billing-required @error('billing.zip') is-invalid @enderror" value="{{ old('billing.zip', $wsPrefill['billing']['zip'] ?? '') }}" {{ $wsSame ? '' : 'required' }}>
                        @error('billing.zip') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-9 form-group">
                        <label for="billing_city">{{ __('webshop::checkout.address.city') }} <span class="text-danger">*</span></label>
                        <input type="text" name="billing[city]" id="billing_city" class="form-control js-billing-required @error('billing.city') is-invalid @enderror" value="{{ old('billing.city', $wsPrefill['billing']['city'] ?? '') }}" {{ $wsSame ? '' : 'required' }}>
                        @error('billing.city') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-12 form-group mb-0">
                        <label for="billing_address">{{ __('webshop::checkout.address.street') }} <span class="text-danger">*</span></label>
                        <input type="text" name="billing[address]" id="billing_address" class="form-control js-billing-required @error('billing.address') is-invalid @enderror" value="{{ old('billing.address', $wsPrefill['billing']['address'] ?? '') }}" {{ $wsSame ? '' : 'required' }}>
                        @error('billing.address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        @include('site.webshop.checkout.partials.save-addresses')
    </div>
</div>

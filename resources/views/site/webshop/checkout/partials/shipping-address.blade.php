{{--
    Pénztár / szállítási cím — saját doboz a jobb oszlop tetején, alatta a számlázási adatok.

    Személyes átvételnél és átvevőpontos módoknál nincs saját szállítási cím: ilyenkor a
    wsUpdateShippingAddress() (partials/scripts.blade.php) a #ws-shipping-address-block
    dobozt elrejti, és a mezők nem kötelezők.
    Bejelentkezett vásárlónál (commerce-users) a mezők a fiókból töltődnek ki ($checkoutPrefill).
--}}
@php
    $wsSavedShipping = $checkoutPrefill['shipping_addresses'] ?? [];
@endphp
<div id="ws-shipping-address-block" class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-primary text-white font-weight-bold h5">{{ __('webshop::checkout.shipping_address.title') }}</div>
    <div class="card-body">
        {{-- Bejelentkezett vásárló mentett címei: választáskor kitölti a mezőket --}}
        @if(count($wsSavedShipping) > 1)
            <div class="form-group">
                <label for="ws_saved_shipping">{{ __('webshop::checkout.shipping_address.saved') }}</label>
                <select id="ws_saved_shipping" class="form-control js-ws-saved-address" data-target="shipping">
                    @foreach($wsSavedShipping as $wsSaved)
                        <option value="{{ $wsSaved['id'] }}" data-address='@json($wsSaved)'>
                            {{ $wsSaved['zip'] }} {{ $wsSaved['city'] }}, {{ $wsSaved['address'] }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="row">
            <div class="col-md-3 form-group">
                <label for="shipping_zip">{{ __('webshop::checkout.address.zip') }} <span class="text-danger">*</span></label>
                <input type="text" name="shipping[zip]" id="shipping_zip" class="form-control ws-shipping-address-input @error('shipping.zip') is-invalid @enderror" value="{{ old('shipping.zip', $checkoutPrefill['shipping']['zip'] ?? '') }}">
                @error('shipping.zip') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-9 form-group">
                <label for="shipping_city">{{ __('webshop::checkout.address.city') }} <span class="text-danger">*</span></label>
                <input type="text" name="shipping[city]" id="shipping_city" class="form-control ws-shipping-address-input @error('shipping.city') is-invalid @enderror" value="{{ old('shipping.city', $checkoutPrefill['shipping']['city'] ?? '') }}">
                @error('shipping.city') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-12 form-group mb-0">
                <label for="shipping_address">{{ __('webshop::checkout.address.street') }} <span class="text-danger">*</span></label>
                <input type="text" name="shipping[address]" id="shipping_address" class="form-control ws-shipping-address-input @error('shipping.address') is-invalid @enderror" value="{{ old('shipping.address', $checkoutPrefill['shipping']['address'] ?? '') }}">
                @error('shipping.address') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>
</div>

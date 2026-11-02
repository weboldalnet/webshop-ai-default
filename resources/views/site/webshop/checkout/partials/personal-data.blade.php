{{--
    Pénztár / 2. blokk: személyes adatok.

    Ide tartozik a vevő azonosítása (név, e-mail, telefon, cégadatok) és a
    számlázási cím is – az utóbbi a "Megegyezik a szállítással" jelölőtől
    függően nyílik ki (a logika a webshop-site.js initCheckout()-jában van).

    Bejelentkezett vásárlónál (commerce-users) a mezők a fiókból töltődnek ki
    ($checkoutPrefill), az e-mail-cím a fiók címe és nem írható.
--}}
@php
    $wsPrefill = $checkoutPrefill ?? [];
    $wsLoggedIn = !empty($commerceUser);
    $wsSameDefault = array_key_exists('billing_same_as_shipping', $wsPrefill) ? ($wsPrefill['billing_same_as_shipping'] ? '1' : '0') : '1';
    $wsSame = old('billing_same_as_shipping', old('_token') ? null : $wsSameDefault) == '1';
    $wsSavedBilling = $wsPrefill['billing_addresses'] ?? [];
@endphp
<div class="card shadow-sm border-0 h-100">
    <div class="card-header bg-primary text-white font-weight-bold h5">Személyes adatok</div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 form-group">
                <label for="name">Név <span class="text-danger">*</span></label>
                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $wsPrefill['name'] ?? '') }}" required>
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6 form-group">
                <label for="email">E-mail cím <span class="text-danger">*</span></label>
                @if($wsLoggedIn)
                    <input type="email" name="email" id="email" class="form-control" value="{{ $commerceUser->email }}" readonly>
                    <small class="form-text text-muted">A fiókod e-mail-címe; a rendelés ide kötődik.</small>
                @else
                    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                @endif
            </div>
        </div>

        @if(($ws['site_checkout_phone_enabled'] ?? 'false') === 'true')
            <div class="form-group">
                <label for="phone">Telefonszám <span class="text-danger">*</span></label>
                <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $wsPrefill['phone'] ?? '') }}" required>
                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        @endif

        @if(($ws['site_checkout_company_enabled'] ?? 'false') === 'true')
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="company">Cégnév <span class="text-danger">*</span></label>
                    <input type="text" name="company" id="company" class="form-control @error('company') is-invalid @enderror" value="{{ old('company', $wsPrefill['company'] ?? '') }}" required>
                    @error('company') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                @if(($ws['site_checkout_tax_number_enabled'] ?? 'false') === 'true')
                    <div class="col-md-6 form-group">
                        <label for="tax_number">Adószám <span class="text-danger">*</span></label>
                        <input type="text" name="tax_number" id="tax_number" class="form-control @error('tax_number') is-invalid @enderror" value="{{ old('tax_number', $wsPrefill['tax_number'] ?? '') }}" required>
                        @error('tax_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                @endif
            </div>
        @endif

        @if(($ws['site_checkout_billing_enabled'] ?? 'false') === 'true')
            <div class="d-flex flex-sm-row flex-column justify-content-between align-items-sm-center align-items-start mt-4 border-bottom pb-2 mb-3">
                <h5 class="m-0 font-weight-bold">Számlázási adatok</h5>
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" name="billing_same_as_shipping" class="custom-control-input js-billing-same-as-shipping" id="billingSameAsShipping" value="1" {{ $wsSame ? 'checked' : '' }}>
                    <label class="custom-control-label" for="billingSameAsShipping">Megegyezik a szállítással</label>
                </div>
            </div>

            <div class="collapse {{ $wsSame ? '' : 'show' }} js-billing-collapse">
                @if(count($wsSavedBilling) > 1)
                    <div class="form-group">
                        <label for="ws_saved_billing">Mentett számlázási címeim</label>
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
                        <label>Számlázási név <span class="text-danger">*</span></label>
                        <input type="text" name="billing[name]" id="billing_name" class="form-control js-billing-required @error('billing.name') is-invalid @enderror" value="{{ old('billing.name', $wsPrefill['billing']['name'] ?? '') }}" {{ $wsSame ? '' : 'required' }}>
                        @error('billing.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Irsz. <span class="text-danger">*</span></label>
                        <input type="text" name="billing[zip]" id="billing_zip" class="form-control js-billing-required @error('billing.zip') is-invalid @enderror" value="{{ old('billing.zip', $wsPrefill['billing']['zip'] ?? '') }}" {{ $wsSame ? '' : 'required' }}>
                        @error('billing.zip') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-9 form-group">
                        <label>Város <span class="text-danger">*</span></label>
                        <input type="text" name="billing[city]" id="billing_city" class="form-control js-billing-required @error('billing.city') is-invalid @enderror" value="{{ old('billing.city', $wsPrefill['billing']['city'] ?? '') }}" {{ $wsSame ? '' : 'required' }}>
                        @error('billing.city') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-12 form-group mb-0">
                        <label>Utca, házszám <span class="text-danger">*</span></label>
                        <input type="text" name="billing[address]" id="billing_address" class="form-control js-billing-required @error('billing.address') is-invalid @enderror" value="{{ old('billing.address', $wsPrefill['billing']['address'] ?? '') }}" {{ $wsSame ? '' : 'required' }}>
                        @error('billing.address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        @endif

        {{-- Bejelentkezve: a pénztárban megadott címek mentése a fiókba (a meglévők nem duplázódnak) --}}
        @if($wsLoggedIn && !empty($wsPrefill['addresses_enabled']))
            <div class="custom-control custom-checkbox mt-4">
                <input type="checkbox" name="save_addresses" value="1" class="custom-control-input" id="saveAddresses"
                       {{ old('save_addresses', old('_token') ? null : (empty($wsPrefill['has_addresses']) ? '1' : null)) ? 'checked' : '' }}>
                <label class="custom-control-label" for="saveAddresses">A megadott címeimet mentsd el a fiókomba</label>
            </div>
        @endif
    </div>
</div>

@if(!empty($wsPrefill['shipping_addresses']) || !empty($wsPrefill['billing_addresses']))
    <script>
        // Mentett cím választása: a megfelelő pénztári mezőket tölti ki (shipping[...] / billing[...]).
        document.addEventListener('change', function (e) {
            var select = e.target.closest('.js-ws-saved-address');
            if (!select) {
                return;
            }
            var option = select.options[select.selectedIndex];
            var data = JSON.parse(option.getAttribute('data-address') || '{}');
            var target = select.getAttribute('data-target');
            ['name', 'zip', 'city', 'address'].forEach(function (field) {
                var input = document.getElementById(target + '_' + field);
                if (input && data[field] !== undefined && data[field] !== null) {
                    input.value = data[field];
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                }
            });
            if (target === 'billing') {
                ['company', 'tax_number'].forEach(function (field) {
                    var input = document.getElementById(field);
                    if (input && data[field]) {
                        input.value = data[field];
                    }
                });
            }
        });
    </script>
@endif

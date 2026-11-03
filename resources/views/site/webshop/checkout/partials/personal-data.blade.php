{{--
    Pénztár / 2. blokk: személyes adatok.

    Ide tartozik a vevő azonosítása (név, e-mail, telefon, cégadatok). Alatta a szállítási
    mód doboza áll; a számlázási cím saját dobozban van a jobb oszlopban (billing.blade.php).

    Bejelentkezett vásárlónál (commerce-users) a mezők a fiókból töltődnek ki
    ($checkoutPrefill), az e-mail-cím a fiók címe és nem írható.
--}}
@php
    $wsPrefill = $checkoutPrefill ?? [];
    $wsLoggedIn = !empty($commerceUser);
@endphp
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-primary text-white font-weight-bold h5">Személyes adatok</div>
    {{-- pb-1: az utolsó mezőcsoport saját 16 px-ével együtt alul is 20 px, mint felül --}}
    <div class="card-body pb-1">
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

        {{-- Számlázás nélkül a címmentés itt jelenik meg; különben a számlázási doboz alján --}}
        @if(($ws['site_checkout_billing_enabled'] ?? 'false') !== 'true')
            @include('site.webshop.checkout.partials.save-addresses', ['wsSaveMb' => 'mb-3'])
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

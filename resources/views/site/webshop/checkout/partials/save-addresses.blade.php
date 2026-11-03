{{--
    Bejelentkezett vásárló: a pénztár címei a fiókba (WebshopUserService::saveCheckoutAddresses()).
    Amíg nincs mentett címe, az első rendelés címei maguktól mentődnek — ezt csak jelezzük;
    ha már van, az új címek mentése a jelölővel kérhető. Csomagpont és személyes átvétel
    helye nem kerül a címek közé.
    $wsSaveMb: alsó margó (a személyes adatok dobozában mb-3, mert ott a doboz alja pb-1).
--}}
@php
    $wsPrefill = $checkoutPrefill ?? [];
    $wsSaveMb = $wsSaveMb ?? 'mb-0';
@endphp
@if(!empty($commerceUser) && !empty($wsPrefill['addresses_enabled']))
    @if(!empty($wsPrefill['has_addresses']))
        <div class="custom-control custom-checkbox mt-4 {{ $wsSaveMb }}">
            <input type="checkbox" name="save_addresses" value="1" class="custom-control-input" id="saveAddresses" {{ old('save_addresses') ? 'checked' : '' }}>
            <label class="custom-control-label" for="saveAddresses">{{ __('webshop::checkout.account.save_new_addresses') }}</label>
        </div>
    @else
        <p class="small text-muted mt-4 {{ $wsSaveMb }}">
            <i class="fa-solid fa-circle-info mr-1"></i> {{ __('webshop::checkout.account.addresses_autosave') }}
        </p>
    @endif
@endif

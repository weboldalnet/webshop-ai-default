{{--
    Pénztár / 2. blokk: személyes adatok.

    Ide tartozik a vevő azonosítása (név, e-mail, telefon, cégadatok) és a
    számlázási cím is – az utóbbi a "Megegyezik a szállítással" jelölőtől
    függően nyílik ki (a logika a webshop-site.js initCheckout()-jában van).
--}}
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
                        <label for="tax_number">Adószám <span class="text-danger">*</span></label>
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
                    <div class="col-md-12 form-group mb-0">
                        <label>Utca, házszám <span class="text-danger">*</span></label>
                        <input type="text" name="billing[address]" class="form-control js-billing-required @error('billing.address') is-invalid @enderror" value="{{ old('billing.address') }}" {{ old('billing_same_as_shipping', '1') == '1' ? '' : 'required' }}>
                        @error('billing.address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

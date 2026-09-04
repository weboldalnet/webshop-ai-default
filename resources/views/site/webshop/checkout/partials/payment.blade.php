{{--
    Pénztár / 4. blokk: fizetési lehetőségek.

    A "Fizetés a helyszínen" opció rejtve indul, és csak személyes átvételnél
    jelenik meg – ezt a wsUpdateOnSitePayment() kapcsolgatja
    (partials/scripts.blade.php).
--}}
<div class="card shadow-sm border-0">
    <div class="card-header bg-primary text-white font-weight-bold h5">Fizetési lehetőségek</div>
    <div class="card-body">
        @if(!empty($paymentMethods))
            @error('payment_method') <div class="alert alert-danger py-1 mb-2">{{ $message }}</div> @enderror

            <div class="form-group mb-0" id="ws-payment-methods-group">
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
            <div class="alert alert-warning mb-0">Jelenleg nincs elérhető fizetési mód.</div>
        @endif
    </div>
</div>

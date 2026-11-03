{{-- Pénztár / 5. blokk: megjegyzés, teljes szélességben --}}
<div class="card shadow-sm border-0 h-100">
    <div class="card-header bg-primary text-white font-weight-bold h5">{{ __('webshop::common.note') }}</div>
    <div class="card-body">
        <div class="form-group mb-0">
            <label for="note" class="sr-only">{{ __('webshop::common.note') }}</label>
            <textarea name="note" id="note" class="form-control" rows="3"
                      placeholder="{{ __('webshop::checkout.note.placeholder') }}">{{ old('note') }}</textarea>
        </div>
    </div>
</div>

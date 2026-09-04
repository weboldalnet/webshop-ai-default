{{-- Pénztár / 5. blokk: megjegyzés, teljes szélességben --}}
<div class="card shadow-sm border-0">
    <div class="card-header bg-primary text-white font-weight-bold h5">Megjegyzés</div>
    <div class="card-body">
        <div class="form-group mb-0">
            <label for="note" class="sr-only">Megjegyzés</label>
            <textarea name="note" id="note" class="form-control" rows="3"
                      placeholder="Ha bármit szeretnél jelezni a rendeléssel kapcsolatban, ide írhatod.">{{ old('note') }}</textarea>
        </div>
    </div>
</div>

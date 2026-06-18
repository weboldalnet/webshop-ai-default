<div class="row mt-3">
    <div class="col-lg-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-light py-2">
                <h5 class="mb-0 font-weight-bold">{{ $label }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.webshop.extra-settings.custom-contents.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="type" value="{{ $type }}">
                    <input type="hidden" name="checkout_mode" value="{{ $checkout_mode }}">
                    <input type="hidden" name="payment_method" value="{{ $payment_method }}">
                    <input type="hidden" name="shipping_method" value="{{ $shipping_method }}">

                    <div class="form-group">
                        <label>Cím / Tárgy</label>
                        <input type="text" name="title" class="form-control" value="{{ $content->title ?? '' }}">
                    </div>

                    <div class="form-group">
                        <label>Tartalom</label>
                        <textarea name="content" class="form-control js-tinymce" rows="5">{{ $content->content ?? '' }}</textarea>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="active_{{ $checkout_mode }}_{{ $payment_method }}_{{ $shipping_method }}" name="is_active" value="1" @if($content->is_active ?? true) checked @endif>
                            <label class="custom-control-label" for="active_{{ $checkout_mode }}_{{ $payment_method }}_{{ $shipping_method }}">Aktív</label>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Mentés</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

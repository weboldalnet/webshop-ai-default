<div class="ws-cart-wrapper position-fixed" style="bottom: 20px; right: 20px; z-index: 1050;">
    <div class="dropdown">
        <button class="btn btn-primary rounded-circle shadow ws-cart-button p-0" type="button" data-toggle="dropdown" style="width: 60px; height: 60px; position: relative;">
            <i class="fa fa-shopping-cart fa-lg"></i>
            <span class="badge badge-danger position-absolute js-cart-count" style="bottom: 0; right: 0; transform: translate(25%, 25%); border-radius: 50%; padding: 5px 8px;">
                {{ $cartCount ?? 0 }}
            </span>
        </button>
        <div class="dropdown-menu dropdown-menu-right p-3 shadow-lg js-cart-dropdown" style="min-width: 320px; max-height: 80vh; overflow-y: auto;">
            <div class="text-center p-4">
                <i class="fa fa-spinner fa-spin"></i> Betöltés...
            </div>
        </div>
    </div>
</div>

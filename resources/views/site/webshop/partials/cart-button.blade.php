<div class="ws-global-btn-item ws-cart-wrapper" style="">
    <div class="dropdown">
        <button class="btn btn-primary ws-cart-button" type="button" data-toggle="dropdown">
            <i class="fa fa-shopping-cart"></i>
            <span class="badge badge-danger position-absolute js-cart-count">
                {{ $cartCount ?? 0 }}
            </span>
        </button>
        <div class="dropdown-menu dropdown-menu-right px-lg-3 px-2 py-2 shadow-lg js-cart-dropdown" style="min-width: 320px; max-height: 80vh; overflow-y: auto;">
            <div class="text-center p-4">
                <i class="fa fa-spinner fa-spin"></i> Betöltés...
            </div>
        </div>
    </div>
</div>

@if(($ws['site_product_compare_enabled'] ?? 'false') === 'true')
<div class="ws-global-btn-item  ws-compare-wrapper" style="">
    <div class="dropdown">
        <button class="btn btn-info ws-compare-button" type="button" data-toggle="dropdown">
            <i class="fa fa-scale-balanced"></i>
            <span class="badge badge-warning position-absolute js-compare-count">
                {{ $compareCount ?? 0 }}
            </span>
        </button>
        <div class="dropdown-menu dropdown-menu-right px-lg-3 px-2 py-2 shadow-lg js-compare-dropdown" style="min-width: 320px; max-height: 80vh; overflow-y: auto;">
            <div class="text-center p-4">
                <i class="fa fa-spinner fa-spin"></i> Betöltés...
            </div>
        </div>
    </div>
</div>
@endif

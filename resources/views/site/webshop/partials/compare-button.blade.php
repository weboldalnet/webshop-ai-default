@if(($ws['site_product_compare_enabled'] ?? 'false') === 'true')
<div class="ws-compare-wrapper position-fixed" style="bottom: 90px; right: 20px; z-index: 1050;">
    <div class="dropdown">
        <button class="btn btn-info rounded-circle shadow ws-compare-button p-0" type="button" data-toggle="dropdown" style="width: 60px; height: 60px; position: relative;">
            <i class="fa fa-scale-balanced fa-lg"></i>
            <span class="badge badge-warning position-absolute js-compare-count" style="bottom: 0; right: 0; transform: translate(25%, 25%); border-radius: 50%; padding: 5px 8px;">
                {{ $compareCount ?? 0 }}
            </span>
        </button>
        <div class="dropdown-menu dropdown-menu-right p-3 shadow-lg js-compare-dropdown" style="min-width: 320px; max-height: 80vh; overflow-y: auto;">
            <div class="text-center p-4">
                <i class="fa fa-spinner fa-spin"></i> Betöltés...
            </div>
        </div>
    </div>
</div>
@endif

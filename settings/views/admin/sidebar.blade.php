
<div class="mb-1">
    <a class="menu-point collapsed @if(str_contains($url, 'webshop')) active @endif"
       data-toggle="collapse" href="#webshopCollapse" role="button"
    >
        <span><i class="fa-solid fa-cart-shopping mr-1"></i>Webshop</span>
        <i class="fa-solid fa-chevron-down"></i>
    </a>
    <div class="collapse collapse-box @if(str_contains($url, 'webshop')) show @endif " id="webshopCollapse">
        <div class="collapse-menu-points">
            @php
                $wsCommerce = \Weboldalnet\WebshopAiDefault\Services\Webshop\Commerce\WebshopCommerceService::class;

                // Számlázás: a webshop beállításokban lévő főkapcsoló dönt, a
                // kiválasztott integráció beállítólinkje pedig a commerce-core
                // provider-nyilvántartásából jön – így egy új számlázó csomag
                // bekötéséhez ezt a menüt nem kell módosítani.
                $wsInvoicingEnabled = $wsCommerce::isInvoicingEnabled();
                $wsInvoiceProvider = $wsInvoicingEnabled ? $wsCommerce::getInvoiceProviderMeta() : null;

                // Fizetési és szállítási integrációk beállításai: csak azoké
                // jelenik meg, amelyek a pénztárban be vannak kapcsolva.
                $wsIntegrationLinks = $wsCommerce::getIntegrationSettingsLinks();
            @endphp

            <a href="/webshop/orders" class="fw-800">Rendelések</a>
            @if($wsInvoicingEnabled)
                <a href="/webshop/invoices">Számlák</a>
            @endif
            @if(class_exists(\Weboldalnet\CommerceCore\Models\Shipment::class))
                <a href="/webshop/shipments">Szállítmányok</a>
            @endif

            <a href="/webshop/withdrawals">Elállások</a>

            <hr class="d-block w-fill my-1 mx-2">

            <a href="/webshop/categories">Kategóriák</a>
            <a href="/webshop/property-categories">Tulajdonságok</a>
            <a href="/webshop/products">Termékek</a>
            @if(\Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopSettingsService::getBool('admin_product_labels_enabled'))
                <a href="/webshop/labels">Címkék</a>
            @endif

            <a href="{{ route('admin.webshop.extra-settings.index') }}" class="fw-800">Webshop beállítások</a>

            @if($user->is_super)
                <hr class="d-block w-fill my-1 mx-2">
                <a href="/webshop/settings" class="fw-800">Beállítások</a>
                @foreach($wsIntegrationLinks['payment'] as $wsLink)
                    <a href="{{ $wsLink['url'] }}">{{ $wsLink['name'] }}</a>
                @endforeach
                @if($wsInvoiceProvider && !empty($wsInvoiceProvider['settings_url']))
                    <a href="{{ $wsInvoiceProvider['settings_url'] }}">{{ $wsInvoiceProvider['name'] }}</a>
                @endif
                @foreach($wsIntegrationLinks['shipping'] as $wsLink)
                    <a href="{{ $wsLink['url'] }}">{{ $wsLink['name'] }}</a>
                @endforeach
            @endif
        </div>
    </div>
</div>

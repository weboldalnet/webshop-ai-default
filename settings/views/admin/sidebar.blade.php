
<div class="mb-1">
    <a class="menu-point collapsed @if(str_contains($url, 'webshop')) active @endif"
       data-toggle="collapse" href="#webshopCollapse" role="button"
    >
        <span><i class="fa-solid fa-cart-shopping mr-1"></i>Webshop</span>
        <i class="fa-solid fa-chevron-down"></i>
    </a>
    <div class="collapse collapse-box @if(str_contains($url, 'webshop')) show @endif " id="webshopCollapse">
        <div class="collapse-menu-points">
            <a href="/webshop/orders" class="fw-800">Rendelések</a>
            @if(class_exists(\Weboldalnet\CommerceSzamlazzhu\Services\SzamlazzhuSettingsService::class) && \Weboldalnet\CommerceSzamlazzhu\Services\SzamlazzhuSettingsService::getBool('enabled'))
                <a href="/webshop/invoices">Számlák</a>
            @endif
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
                @if(class_exists(\Weboldalnet\CommerceSzamlazzhu\Services\SzamlazzhuSettingsService::class))
                    <a href="/webshop/szamlazzhu/settings">Számlázz.hu</a>
                @endif
            @endif
        </div>
    </div>
</div>

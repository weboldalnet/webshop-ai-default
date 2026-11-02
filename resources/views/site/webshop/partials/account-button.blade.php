{{-- Fiók-gomb a fejléc gombsorában — csak bekapcsolt vásárlói fiókoknál (Webshop beállítások ›
     Vásárlói fiókok) és telepített felhasználó modulnál (weboldalnet/commerce-users).
     A projekt mobil fejléce is ezt húzza be a kosár mellé. --}}
@if(\Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopUserService::isEnabled())
    <div class="ws-global-btn-item ws-account-wrapper">
        @include('commerce-users::site.partials.account-button', ['btnClass' => 'btn btn-primary ws-account-button'])
    </div>
@endif

{{-- Fiók-gomb a fejléc gombsorában — csak bekapcsolt vásárlói fiókoknál (Webshop beállítások ›
     Vásárlói fiókok) és telepített felhasználó modulnál (weboldalnet/commerce-users).
     A kosártól jobbra áll, formája a kosár-gombé (.ws-account-button a webshop-site.css-ben), színe a
     btn-secondary — a projekt témája felülírhatja. A projekt mobil fejléce is ezt húzza be a kosár után. --}}
@if(\Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopUserService::isEnabled())
    <div class="ws-global-btn-item ws-account-wrapper">
        @include('commerce-users::site.partials.account-button', ['btnClass' => 'btn btn-secondary ws-account-button'])
    </div>
@endif

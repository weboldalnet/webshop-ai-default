<div class="ws-reviews-box">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="font-weight-bold mb-0">Vásárlói vélemények</h5>
        <button type="button" class="btn btn-dark" data-toggle="modal" data-target="#reviewModal">
            <i class="fa fa-pen-to-square mr-2"></i> Vélemény írása
        </button>
    </div>

    {{-- Kötelező tájékoztatás a vélemények eredetéről (Fttv., Omnibusz). A szöveg a tényleges
         beállításból készül, így nem állíthat többet, mint amit a rendszer ellenőriz. --}}
    <p class="text-muted small mb-4 ws-reviews-disclosure">
        <i class="fa fa-circle-info mr-1"></i>
        {{ \Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopReviewService::disclosureText() }}
    </p>

    <div class="ws-reviews-list">
        @forelse($product->reviews()->active()->latestFirst()->get() as $review)
            <div class="ws-review-item border-bottom pb-3 mb-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <span class="font-weight-bold text-primary">{{ $review->name }}</span>
                        @if($review->is_verified)
                            {{-- A szín a csomag CSS-ében (`.ws-verified-badge`), nem inline: így a projektek
                                 arculatra tudják húzni `!important` nélkül. A Bootstrap `badge-success` azért
                                 nem jó, mert kis betűnél csak ~3,1:1 kontrasztot ad. --}}
                            <span class="badge ws-verified-badge ml-2 align-middle"><i class="fa fa-check mr-1"></i>Ellenőrzött vásárló</span>
                        @endif
                    </div>
                    <div class="text-muted small">{{ $review->created_at->format('Y.m.d.') }}</div>
                </div>
                <div class="ws-rating my-2 text-warning">
                    @for($i=1; $i<=5; $i++)
                        <i class="fa{{ $i <= $review->rating ? '-solid' : '-regular' }} fa-star"></i>
                    @endfor
                </div>
                <div class="ws-review-text">{{ $review->review }}</div>
            </div>
        @empty
            <div class="text-center p-4 text-muted border rounded bg-light">
                Még nem érkezett vélemény ehhez a termékhez. Legyen Ön az első!
            </div>
        @endforelse
    </div>
</div>

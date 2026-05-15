<div class="ws-reviews-box">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="font-weight-bold mb-0">Vásárlói vélemények</h5>
        <button type="button" class="btn btn-dark" data-toggle="modal" data-target="#reviewModal">
            <i class="fa fa-pen-to-square mr-2"></i> Vélemény írása
        </button>
    </div>

    <div class="ws-reviews-list">
        @forelse($product->reviews()->active()->latestFirst()->get() as $review)
            <div class="ws-review-item border-bottom pb-3 mb-3">
                <div class="d-flex justify-content-between">
                    <div class="font-weight-bold text-primary">{{ $review->name }}</div>
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

<div class="modal fade" id="relatedProductsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold">A termék a kosárba került!</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <p class="font-weight-bold h5 mb-4">Ezek a termékek is érdekelhetik:</p>
                <div class="row">
                    @foreach($product->relatedProducts as $rel)
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 shadow-sm border-0 bg-light text-center p-2">
                                <div class="mb-2" style="height: 100px;">
                                    @if($rel->primary_image)
                                        <img src="{{ $rel->primary_image }}" class="img-fluid h-100" style="object-fit: contain;">
                                    @else
                                        <i class="fa fa-image fa-2x text-muted opacity-25"></i>
                                    @endif
                                </div>
                                <h6 class="font-weight-bold small mb-2" style="height: 40px; overflow: hidden;">{{ $rel->name }}</h6>
                                @if(($ws['site_product_prices_visible'] ?? 'true') === 'true')
                                    <div class="font-weight-bold text-primary mb-2 small">{{ hufFormat($rel->sale_price ?? $rel->price) }}</div>
                                @endif
                                <button type="button" class="btn btn-sm btn-primary btn-block js-add-to-cart" data-id="{{ $rel->id }}">
                                    <i class="fa fa-shopping-cart"></i> Kosárba
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary font-weight-bold" data-dismiss="modal">Vásárlás folytatása</button>
                <a href="{{ route('site.webshop.checkout.index') }}" class="btn btn-primary font-weight-bold">Tovább a pénztárhoz</a>
            </div>
        </div>
    </div>
</div>

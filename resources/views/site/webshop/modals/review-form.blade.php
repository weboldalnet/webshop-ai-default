<div class="modal fade" id="reviewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title font-weight-bold">Vélemény írása</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="ws-review-form">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <div class="modal-body p-4">
                    <div class="form-group">
                        <label class="font-weight-bold">Értékelés <span class="text-danger">*</span></label>
                        <div class="ws-rating-input h4 text-warning">
                            <i class="fa-regular fa-star js-star-input" data-rating="1" style="cursor: pointer;"></i>
                            <i class="fa-regular fa-star js-star-input" data-rating="2" style="cursor: pointer;"></i>
                            <i class="fa-regular fa-star js-star-input" data-rating="3" style="cursor: pointer;"></i>
                            <i class="fa-regular fa-star js-star-input" data-rating="4" style="cursor: pointer;"></i>
                            <i class="fa-regular fa-star js-star-input" data-rating="5" style="cursor: pointer;"></i>
                            <input type="hidden" name="rating" id="ws-rating-value" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="review_name" class="font-weight-bold">Az Ön neve <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="review_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="review_text" class="font-weight-bold">Vélemény <span class="text-danger">*</span></label>
                        <textarea name="review" id="review_text" class="form-control" rows="5" required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary font-weight-bold" data-dismiss="modal">Mégse</button>
                    <button type="submit" class="btn btn-primary font-weight-bold px-4">Küldés <i class="fa fa-paper-plane ml-2"></i></button>
                </div>
            </form>
        </div>
    </div>
</div>


{{-- Alt szöveg modal --}}
<div class="modal fade" id="galleryAltModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Alt szöveg szerkesztése</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="js-gallery-alt-id">
                <div class="form-group">
                    <label for="js-gallery-alt-input">Alt szöveg</label>
                    <input type="text" class="form-control" id="js-gallery-alt-input">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Mégse</button>
                <button type="button" class="btn btn-primary js-save-gallery-alt" data-url="{{ route('admin.webshop.products.gallery.update-alt') }}">Mentés</button>
            </div>
        </div>
    </div>
</div>
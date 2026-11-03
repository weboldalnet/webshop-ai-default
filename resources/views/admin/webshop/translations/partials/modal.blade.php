{{-- Az AI-fordítás ablaka (a core „Fordítás más nyelvre” ablakának mintájára); a törzsét és a gombjait a
     webshop-translations.js tölti lépésenként: beállítások (dialog) → futás (folyamatjelző) → riport. --}}
<div class="modal fade wn-modal ws-editor" id="wsTranslationModal" tabindex="-1" role="dialog"
     aria-labelledby="wsTranslationModalTitle" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="wsTranslationModalTitle"><i class="fa-solid fa-language"></i> Fordítás AI-val</h5>
                <button type="button" class="close js-tr-modal-close" data-dismiss="modal" aria-label="Bezárás">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body js-tr-modal-body"></div>
            <div class="modal-footer js-tr-modal-footer"></div>
        </div>
    </div>
</div>

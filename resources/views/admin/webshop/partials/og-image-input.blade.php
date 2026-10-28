{{-- Megosztási (OG) kép a termék- és a kategória-szerkesztőben — a core OG-blokkjának képmetszős input-groupja
     (admin.elements.og), hogy ugyanúgy nézzen ki és működjön, mint a többi képmezőnél. A képmetsző 1200 × 630-ra vág,
     a feltöltött fájl az eddigi "og_img" néven érkezik (a mentés változatlan; a szerver is erre a méretre igazít).
     Paraméterek: $ogImage (a mentett kép URL-je vagy null), $ogHint (mi megy ki, ha üres). A lapon kell lennie az
     admin.commons.img-cropper include-nak. --}}
<div class="form-group">
    @if(!empty($ogImage))
        <div class="cip"><img class="w-50 d-block mb-2 mx-auto" src="{{ $ogImage }}" alt=""></div>
    @endif
    <label for="og_img" class="mb-1">Megosztási kép</label>
    <div class="input-group">
        <div class="input-group-prepend">
            <span class="input-group-text"><i class="fa-regular fa-file-image fs-18"></i></span>
        </div>
        <div class="custom-file">
            <input type="file"
                   class="custom-file-input js-crop"
                   accept=".jpg,.jpeg,.png"
                   id="og_img"
                   name="og_img"
                   data-width="1200"
                   data-height="630"
                   data-fix-aspect="1"
            >
            <label class="custom-file-label text-muted">Kép</label>
        </div>
    </div>
    <p class="cropper-validator d-none"><i class="fa fa-exclamation-triangle"></i> Nem megfelelő képméret! A kép túl kicsi!</p>
    <span class="ws-hint">Minimális képméret: 1200 × 630. {{ $ogHint ?? '' }}</span>
</div>

{{--
    Banner blokk: három képváltozat (asztali / tablet / mobil) és egy link.

    A metszőknek SZÁNDÉKOSAN nincs data-height attribútumuk: enélkül a cropper
    nem rögzít képarányt, tehát szabad magasságú a vágás. A szélességek a modell
    IMAGE_SIZES konstansából jönnek – configból nem jöhetnének, mert cache-elt
    confignál a csomag merge-elt kulcsai kimaradnak.
--}}
<div class="form-row">
    @foreach($imageSizes as $mezo => $meret)
        @php($jelenlegi = $block->{$mezo})
        <div class="col-lg-4 form-group">
            <label class="font-weight-bold">{{ $meret['label'] }} ({{ $meret['width'] }} px széles)</label>

            @if($jelenlegi)
                <div class="cip mb-2">
                    <img src="{{ $jelenlegi }}" class="w-100 d-block border rounded" alt="">
                </div>
                <div class="custom-control custom-checkbox mb-2">
                    <input type="checkbox" class="custom-control-input"
                           id="hb-{{ $mezo }}-del-{{ $block->id }}" name="{{ $mezo }}_delete" value="1">
                    <label class="custom-control-label text-danger" for="hb-{{ $mezo }}-del-{{ $block->id }}">
                        Kép eltávolítása
                    </label>
                </div>
            @endif

            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fa-regular fa-file-image fs-18"></i></span>
                </div>
                <div class="custom-file">
                    <input type="file"
                           class="custom-file-input js-crop"
                           accept=".jpg,.jpeg,.png"
                           id="hb-{{ $mezo }}-{{ $block->id }}"
                           name="{{ $mezo }}"
                           data-width="{{ $meret['width'] }}">
                    <label class="custom-file-label text-muted">Kép kiválasztása</label>
                </div>
            </div>
            <p class="cropper-validator d-none">
                <i class="fa fa-exclamation-triangle"></i> Nem megfelelő képméret! A kép túl kicsi!
            </p>
            <span class="text-muted fs-14">Minimális szélesség: {{ $meret['width'] }} px, magasság szabad.</span>
        </div>
    @endforeach
</div>

<div class="form-row">
    <div class="col-md-8 form-group">
        <label for="hb-banner-link-{{ $block->id }}">Link (nem kötelező)</label>
        <input type="text" class="form-control" id="hb-banner-link-{{ $block->id }}"
               name="link_url" value="{{ $block->link_url }}" placeholder="/webshop/kategoria/pelda-c1">
        <span class="text-muted fs-14">Megadva a teljes banner kattinthatóvá válik.</span>
    </div>
    <div class="col-md-4 form-group">
        <label for="hb-banner-alt-{{ $block->id }}">Kép alternatív szövege</label>
        <input type="text" class="form-control" id="hb-banner-alt-{{ $block->id }}"
               name="img_alt" value="{{ $block->img_alt }}">
    </div>
</div>

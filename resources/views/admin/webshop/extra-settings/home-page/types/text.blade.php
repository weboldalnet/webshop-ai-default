{{--
    Szöveg blokk.

    Az osztály SZÁNDÉKOSAN js-home-tinymce, NEM js-tinymce: a megosztott
    tinymce include a .js-tinymce-re oldalbetöltéskor köt, a mező viszont
    összecsukott (display:none) collapse-ban van, ahol a szerkesztő 0 magasan
    jönne létre. Ezért lustán, a collapse kinyitásakor indul (lásd scripts.blade.php).

    Az érték {!! !!}-lel megy ki: {{ }} minden mentésnél újra escape-elné a HTML-t.
--}}
<div class="form-group">
    <label for="hb-content-{{ $block->id }}">Tartalom</label>
    <textarea class="form-control js-home-tinymce"
              id="hb-content-{{ $block->id }}"
              name="content">{!! $block->setting('content', '') !!}</textarea>
</div>

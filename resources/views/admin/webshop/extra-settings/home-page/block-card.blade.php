{{--
    Egy nyitóoldali blokk kártyája: mindig látszó fejlécsor + kinyitható törzs.

    A törzs SAJÁT <form>-ban van, blokkonként külön mentéssel. Nincs autosave:
    a banner fájlmezőit minden mentés kiürítené, és a metszőben lévő,
    még el nem küldött kép elveszne.
--}}
@php
    $hbOpen = (int) ($openBlockId ?? 0) === (int) $block->id;
    $hbCollapseId = 'hb-body-' . $block->id;
@endphp

<div class="content-box bordered mb-3 js-home-block" data-block="{{ $block->id }}">
    <input type="hidden" class="js-hb-id" value="{{ $block->id }}">

    <div class="d-flex align-items-center flex-wrap">
        <span class="js-hb-handle mr-2 text-muted" style="cursor: move;" title="Húzd az átrendezéshez">
            <i class="fa fa-grip-vertical"></i>
        </span>

        <span class="badge badge-light border mr-2">
            <i class="fa {{ $block->type_icon }} mr-1"></i>{{ $block->type_label }}
        </span>

        <span class="font-weight-bold mr-auto">
            {{ $block->title ?: '(nincs címsor)' }}
        </span>

        <div class="custom-control custom-switch mr-3">
            <input type="checkbox" class="custom-control-input js-toggle-active"
                   id="hb-active-{{ $block->id }}"
                   data-id="{{ $block->id }}"
                   data-url="{{ route('admin.webshop.extra-settings.home-page.blocks.toggle-active') }}"
                   @if($block->is_active) checked @endif>
            <label class="custom-control-label" for="hb-active-{{ $block->id }}">Aktív</label>
        </div>

        <button type="button" class="btn btn-sm btn-outline-primary mr-2" data-toggle="collapse"
                data-target="#{{ $hbCollapseId }}">
            <i class="fa fa-pen mr-1"></i> Szerkesztés
        </button>

        {{-- Nem beágyazott form és NEM natív confirm(): a megosztott modal kezeli --}}
        <button type="button" class="btn btn-sm btn-outline-danger js-delete-btn"
                data-url="{{ route('admin.webshop.extra-settings.home-page.blocks.destroy', $block->id) }}">
            <i class="fa fa-trash-alt"></i>
        </button>
    </div>

    <div class="collapse mt-3 @if($hbOpen) show @endif" id="{{ $hbCollapseId }}">
        <form action="{{ route('admin.webshop.extra-settings.home-page.blocks.update', $block->id) }}"
              method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="hb-title-{{ $block->id }}">Blokk címsora</label>
                <input type="text" class="form-control" id="hb-title-{{ $block->id }}"
                       name="title" value="{{ $block->title }}">
                <span class="text-muted fs-14">Üresen hagyva a blokk címsor nélkül jelenik meg.</span>
            </div>

            @if($block->hasLayoutChoice())
                <div class="form-group">
                    <label for="hb-layout-{{ $block->id }}">Elrendezés</label>
                    <select class="form-control" id="hb-layout-{{ $block->id }}" name="layout" style="max-width: 320px;">
                        @foreach($layouts as $layoutKey => $layoutLabel)
                            <option value="{{ $layoutKey }}" @if($block->layout === $layoutKey) selected @endif>{{ $layoutLabel }}</option>
                        @endforeach
                    </select>
                    <span class="text-muted fs-14">
                        Görgethetőnél a kártyák egy sorban maradnak, nyilakkal léptethetők — minden képernyőméreten.
                    </span>
                </div>
            @endif

            @include('admin.webshop.extra-settings.home-page.types.' . str_replace('_', '-', $block->type), [
                'block' => $block,
                'items' => $items,
            ])

            {{-- Megjelenés: a háttér a site-on TELJES oldalszélességben fut --}}
            <hr class="mt-0">
            <div class="form-row">
                <div class="col-md-3 form-group">
                    <label for="hb-bg-{{ $block->id }}">Blokk háttérszíne</label>
                    <div class="input-group">
                        <input type="text" class="form-control js-hb-color-text" id="hb-bg-{{ $block->id }}"
                               name="bg_color" value="{{ $block->bg_color }}" placeholder="#ffffff">
                        <div class="input-group-append">
                            <input type="color" class="form-control js-hb-color-pick" style="width: 46px; padding: 4px;"
                                   value="{{ $block->bg_color ?: '#ffffff' }}"
                                   data-target="#hb-bg-{{ $block->id }}" title="Szín választása">
                            <button type="button" class="btn btn-outline-secondary js-hb-color-clear"
                                    data-target="#hb-bg-{{ $block->id }}" title="Szín törlése">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <span class="text-muted fs-14">Üresen: nincs egyedi háttér.</span>
                </div>

                <div class="col-md-3 form-group">
                    <label for="hb-title-color-{{ $block->id }}">Címsor színe</label>
                    <div class="input-group">
                        <input type="text" class="form-control js-hb-color-text" id="hb-title-color-{{ $block->id }}"
                               name="title_color" value="{{ $block->title_color }}" placeholder="#000000">
                        <div class="input-group-append">
                            <input type="color" class="form-control js-hb-color-pick" style="width: 46px; padding: 4px;"
                                   value="{{ $block->title_color ?: '#000000' }}"
                                   data-target="#hb-title-color-{{ $block->id }}" title="Szín választása">
                            <button type="button" class="btn btn-outline-secondary js-hb-color-clear"
                                    data-target="#hb-title-color-{{ $block->id }}" title="Szín törlése">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 form-group">
                    <label for="hb-btn-color-{{ $block->id }}">Gombok színe</label>
                    <select class="form-control" id="hb-btn-color-{{ $block->id }}" name="btn_color">
                        @foreach($btnColors as $colorKey => $colorLabel)
                            <option value="{{ $colorKey }}" @if(($block->btn_color ?: 'main') === $colorKey) selected @endif>{{ $colorLabel }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 form-group">
                    <label for="hb-btn-align-{{ $block->id }}">Gombok igazítása</label>
                    <select class="form-control" id="hb-btn-align-{{ $block->id }}" name="btn_align">
                        @foreach($btnAligns as $alignKey => $alignLabel)
                            <option value="{{ $alignKey }}" @if(($block->btn_align ?: 'justify-content-start') === $alignKey) selected @endif>{{ $alignLabel }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Gombok: a projekt hero-inál is használt szerkesztő.
                 A $variable adja a mezőnevek előtagját (hb[btn][primary][…]),
                 a $sectionData pedig a jelenlegi értékeket. --}}
            @include('admin.elements.includes.button-multi', [
                'hasButton' => true,
                'variable' => 'hb_' . $block->id,
                'sectionData' => ['btn' => $block->buttons ?? []],
            ])

            <div class="text-right">
                <button type="submit" class="btn btn-primary font-weight-bold">
                    <i class="fa fa-save mr-1"></i> Blokk mentése
                </button>
            </div>
        </form>
    </div>
</div>

{{--
    Egy kategória a mobil fiókban, rekurzívan.

    Gyerek nélküli kategória: sima link.
    Gyerekes kategória: a NÉV maga is link (a szülő kategória oldala elérhető
    maradjon), mellette egy külön nyitó gomb a Bootstrap collapse-hoz. Ha az egész
    sor lenne a collapse-kapcsoló, a szülő kategóriára nem lehetne rákattintani.

    Elvárt változók: $wsNode (kategória), $wsLevel (mélység)
--}}
@php
    $wsHasChildren = $wsNode->children->isNotEmpty();
    $wsCollapseId = 'wsCatDrawer' . $wsNode->id;
@endphp

<div class="ws-cat-drawer-item ws-cat-drawer-level-{{ min($wsLevel, 2) }}">
    <div class="d-flex align-items-stretch">
        <a href="{{ route('site.webshop.categories.show', $wsNode) }}" class="ws-cat-drawer-link">
            {{ $wsNode->name_singular }}
        </a>

        @if($wsHasChildren)
            <button type="button" class="ws-cat-drawer-toggle collapsed"
                    data-toggle="collapse" data-target="#{{ $wsCollapseId }}"
                    aria-expanded="false" aria-controls="{{ $wsCollapseId }}"
                    aria-label="{{ $wsNode->name_singular }} alkategóriái">
                <i class="fa fa-angle-down"></i>
            </button>
        @endif
    </div>

    @if($wsHasChildren)
        <div class="collapse" id="{{ $wsCollapseId }}">
            @foreach($wsNode->children as $wsChild)
                @include('site.webshop.partials.category-menu-mobile-node', [
                    'wsNode' => $wsChild,
                    'wsLevel' => $wsLevel + 1,
                ])
            @endforeach
        </div>
    @endif
</div>

{{-- A tulajdonságlista sorai: az oldal első betöltésekor és a keresés / mentés / törlés utáni AJAX-frissítéskor is
     ez renderelődik (WebshopPropertyController::index). --}}
@forelse($properties as $prop)
    <tr data-id="{{ $prop->id }}">
        <td class="ws-drag-handle"><i class="fa fa-grip-vertical text-muted"></i><input type="hidden" class="js-sort-id" value="{{ $prop->id }}"></td>
        <td class="fw-600">{{ $prop->name }}</td>
        <td class="text-muted">{{ $usage[$prop->id] ?? 0 }} termék</td>
        <td>
            <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input js-toggle-active" id="active{{ $prop->id }}" data-id="{{ $prop->id }}" data-url="{{ route('admin.webshop.properties.toggle-active') }}" @if($prop->is_active) checked @endif>
                <label class="custom-control-label" for="active{{ $prop->id }}"></label>
            </div>
        </td>
        <td class="ws-nowrap text-right">
            <button type="button" class="btn btn-sm btn-outline-primary js-edit-property" title="Szerkesztés"
                    data-name="{{ $prop->name }}"
                    data-url="{{ route('admin.webshop.properties.update', [$propertyCategory, $prop]) }}">
                <i class="fa fa-pen"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger js-delete-btn" title="Törlés"
                    data-url="{{ route('admin.webshop.properties.destroy', [$propertyCategory, $prop]) }}">
                <i class="fa fa-trash-alt"></i>
            </button>
        </td>
    </tr>
@empty
    <tr class="js-empty-row">
        <td colspan="5" class="text-center text-muted py-4">
            @if($search !== '')
                Nincs találat erre: „{{ $search }}”.
            @else
                Még nincs tulajdonság ebben a csoportban. Az „Új tulajdonság” gombbal vehetsz fel.
            @endif
        </td>
    </tr>
@endforelse

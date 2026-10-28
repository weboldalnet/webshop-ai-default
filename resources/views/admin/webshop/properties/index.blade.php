@extends('admin.layouts.layout')
@section('title', 'Tulajdonságok')

@section('content')
    @php
        /*
         * TULAJDONSÁGOK EGY CSOPORTON BELÜL — AJAX-os lista (2026-10-28): élő keresés, felvétel és szerkesztés
         * modalban („Mentés és új”: a modal nyitva marad, üres mezővel), törlés megerősítés után, oldalbetöltés
         * nélkül. A sorok: properties/partials/rows (a controller AJAX-ra is ezt adja vissza).
         */
        $wsTypeLabels = ['checkbox' => 'Több választás', 'radio' => 'Egy választás'];
    @endphp

    <div class="container-fluid ws-editor mt-3 mb-150" style="max-width: 1000px">
        @include('admin.webshop.partials.alerts')

        <div class="ws-editor-head">
            <div>
                <span class="ws-editor-kicker">Tulajdonságcsoport</span>
                <h2 class="ws-editor-title">{{ $propertyCategory->name }}</h2>
                <div class="ws-chips">
                    <span class="ws-chip"><i class="fa-solid fa-list-check"></i> {{ $wsTypeLabels[$propertyCategory->filter_type] ?? $propertyCategory->filter_type }}</span>
                    <span class="ws-chip"><i class="fa-solid fa-tags"></i> <span class="js-property-total">{{ $total }}</span> tulajdonság</span>
                    @if($propertyCategory->filter_enabled)
                        <span class="ws-chip is-good"><i class="fa-solid fa-filter"></i> Szűrőként megjelenik</span>
                    @else
                        <span class="ws-chip"><i class="fa-solid fa-filter-circle-xmark"></i> Szűrőként nem jelenik meg</span>
                    @endif
                </div>
            </div>
            <div class="ws-editor-head-actions">
                <a href="{{ route('admin.webshop.property-categories.edit', $propertyCategory) }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-gear"></i> Csoport beállításai
                </a>
                <a href="{{ route('admin.webshop.property-categories.index') }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left"></i> Tulajdonságcsoportok
                </a>
            </div>
        </div>

        <div class="ws-card">
            <div class="ws-list-toolbar">
                <div class="input-group ws-list-search">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    </div>
                    <input type="search" class="form-control js-property-search" value="{{ $search }}" placeholder="Keresés név szerint…" aria-label="Keresés név szerint" autocomplete="off">
                </div>
                <span class="ws-hint mt-0 js-property-result" @if($search === '') style="display:none" @endif>{{ $properties->count() }} találat</span>
                <button type="button" class="btn btn-primary fw-600 ml-auto js-new-property">
                    <i class="fa fa-plus"></i> Új tulajdonság
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-hover ws-list-table js-property-table @if($search !== '') is-searching @endif">
                    <thead>
                    <tr>
                        <th style="width:40px"></th>
                        <th>Név</th>
                        <th style="width:130px">Termékek</th>
                        <th style="width:90px">Aktív</th>
                        <th style="width:100px"></th>
                    </tr>
                    </thead>
                    <tbody id="sortable-list" class="js-property-rows">
                        @include('admin.webshop.properties.partials.rows')
                    </tbody>
                </table>
            </div>
            <span class="ws-hint">A sorrend húzással állítható (keresés közben nem) — ebben a sorrendben jelennek meg a termékszerkesztőben és a szűrőben.</span>
        </div>
    </div>

    {{-- Felvétel / szerkesztés --}}
    <div class="modal fade ws-editor" id="propertyModal" tabindex="-1" role="dialog" aria-labelledby="propertyModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <form class="modal-content js-property-form" novalidate data-store-url="{{ route('admin.webshop.properties.store', $propertyCategory) }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="propertyModalTitle"><span class="js-property-modal-title">Új tulajdonság</span> <small class="text-muted">· {{ $propertyCategory->name }}</small></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Bezárás"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-success py-2 px-3 fs-14 js-property-saved" style="display:none"></div>
                    <div class="form-group mb-0">
                        <label for="property_name">Név <span class="ws-req">*</span></label>
                        <input type="text" class="form-control js-property-name" id="property_name" name="name" maxlength="255" autocomplete="off" required>
                        <div class="invalid-feedback js-property-error"></div>
                        <span class="ws-hint">A termékszerkesztőben és a webshop szűrőjében ez a felirat jelenik meg.</span>
                        <span class="ws-hint js-property-enter-hint"><i class="fa-regular fa-keyboard"></i> Enter: mentés és új tulajdonság.</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Bezárás</button>
                    <button type="button" class="btn btn-outline-primary js-property-save-new"><i class="fa fa-plus"></i> Mentés és új</button>
                    <button type="button" class="btn btn-primary js-property-save"><i class="fa fa-save"></i> Mentés</button>
                </div>
            </form>
        </div>
    </div>

    @include('admin.webshop.modals.delete-confirm')
    <link rel="stylesheet" href="/packages/webshop/admin/css/webshop-admin.css">
    <script src="/packages/webshop/admin/js/webshop-admin.js"></script>
    <script>
        WebshopAdmin.initSortable('#sortable-list', '{{ route("admin.webshop.properties.sort") }}');
        WebshopAdmin.initToggleActive();
        WebshopAdmin.initDeleteConfirm();
        WebshopAdmin.initPropertyList({
            listUrl: '{{ route("admin.webshop.properties.index", $propertyCategory) }}'
        });
    </script>
@endsection

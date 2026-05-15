@extends('admin.layouts.layout')
@section('title', $propertyCategory->name . ' - Tulajdonságok')

@section('content')
    <div class="container mt-lg-4 mt-3 mb-150">
        @include('admin.webshop.partials.alerts')

        <div class="row"><div class="col-lg-12"><h2 class="header-box"><i class="fa fa-list"></i> {{ $propertyCategory->name }} - Tulajdonságok</h2></div></div>

        <div class="row mt-2 mb-2">
            <div class="col-lg-12 text-center">
                <a href="{{ route('admin.webshop.properties.create', $propertyCategory) }}" class="btn btn-primary fs-18 font-weight-bold"><i class="fa fa-plus"></i> Új tulajdonság</a>
                <a href="{{ route('admin.webshop.property-categories.index') }}" class="btn btn-secondary">Vissza a kategóriákhoz</a>
            </div>
        </div>

        <div class="content-box bordered mb-3">
            <form method="GET" action="{{ route('admin.webshop.properties.index', $propertyCategory) }}" class="row align-items-end">
                <div class="col-lg-4 mb-2">
                    <label>Keresés</label>
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Név...">
                </div>
                <div class="col-lg-4 mb-2">
                    <button type="submit" class="btn btn-dark"><i class="fa fa-search"></i> Szűrés</button>
                </div>
            </form>
        </div>

        <div class="content-box table-responsive">
            <table class="table table-hover">
                <thead class="thead-dark">
                <tr>
                    <th style="width:40px"><i class="fa fa-arrows-alt"></i></th>
                    <th>Név</th>
                    <th>Aktív</th>
                    <th><i class="fa fa-pen"></i></th>
                </tr>
                </thead>
                <tbody id="sortable-list">
                @foreach($properties as $prop)
                    <tr>
                        <td class="ws-drag-handle"><i class="fa fa-grip-vertical text-muted"></i><input type="hidden" class="js-sort-id" value="{{ $prop->id }}"></td>
                        <td class="font-weight-bold">{{ $prop->name }}</td>
                        <td>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input js-toggle-active" id="active{{ $prop->id }}" data-id="{{ $prop->id }}" data-url="{{ route('admin.webshop.properties.toggle-active') }}" @if($prop->is_active) checked @endif>
                                <label class="custom-control-label" for="active{{ $prop->id }}"></label>
                            </div>
                        </td>
                        <td class="ws-nowrap">
                            <a href="{{ route('admin.webshop.properties.edit', [$propertyCategory, $prop]) }}" class="btn btn-sm btn-primary"><i class="fa fa-pen"></i></a>
                            <button type="button" class="btn btn-sm btn-danger js-delete-btn" data-url="{{ route('admin.webshop.properties.destroy', [$propertyCategory, $prop]) }}"><i class="fa fa-trash-alt"></i></button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @include('admin.webshop.modals.delete-confirm')
    <link rel="stylesheet" href="/packages/webshop/admin/css/webshop-admin.css">
    <script src="/packages/webshop/admin/js/webshop-admin.js"></script>
    <script>
        WebshopAdmin.initSortable('#sortable-list', '{{ route("admin.webshop.properties.sort") }}');
        WebshopAdmin.initToggleActive();
        WebshopAdmin.initDeleteConfirm();
    </script>
@endsection

@extends('admin.layouts.layout')
@section('title', 'Tulajdonság kategóriák')

@section('content')
    <div class="container mt-lg-4 mt-3 mb-150">
        @include('admin.webshop.partials.alerts')

        <div class="row">
            <div class="col-lg-12">
                <h2 class="header-box"><i class="fa fa-tags"></i> Tulajdonság kategóriák</h2>
            </div>
        </div>

        <div class="row mt-2 mb-2">
            <div class="col-lg-12 text-center">
                <a href="{{ route('admin.webshop.property-categories.create') }}" class="btn btn-primary fs-18 font-weight-bold">
                    <i class="fa fa-plus"></i> Új tulajdonság kategória
                </a>
            </div>
        </div>

        <div class="content-box bordered mb-3">
            <form method="GET" action="{{ route('admin.webshop.property-categories.index') }}" class="row align-items-end">
                <div class="col-lg-3 col-md-6 mb-2">
                    <label>Keresés</label>
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Név...">
                </div>
                <div class="col-lg-2 col-md-6 mb-2">
                    <label>Státusz</label>
                    <select name="is_active" class="form-control">
                        <option value="">Mind</option>
                        <option value="1" @if(request('is_active')==='1') selected @endif>Aktív</option>
                        <option value="0" @if(request('is_active')==='0') selected @endif>Inaktív</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-6 mb-2">
                    <label>Szűrő</label>
                    <select name="filter_enabled" class="form-control">
                        <option value="">Mind</option>
                        <option value="1" @if(request('filter_enabled')==='1') selected @endif>Igen</option>
                        <option value="0" @if(request('filter_enabled')==='0') selected @endif>Nem</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-6 mb-2">
                    <label>Típus</label>
                    <select name="filter_type" class="form-control">
                        <option value="">Mind</option>
                        <option value="checkbox" @if(request('filter_type')==='checkbox') selected @endif>Checkbox</option>
                        <option value="radio" @if(request('filter_type')==='radio') selected @endif>Radio</option>
                        <option value="number" @if(request('filter_type')==='number') selected @endif>Number</option>
                    </select>
                </div>
                <div class="col-lg-3 col-md-12 mb-2">
                    <button type="submit" class="btn btn-dark"><i class="fa fa-search"></i> Szűrés</button>
                    <a href="{{ route('admin.webshop.property-categories.index') }}" class="btn btn-outline-secondary">Törlés</a>
                </div>
            </form>
        </div>

        <div class="content-box table-responsive">
            <table class="table table-hover">
                <thead class="thead-dark">
                <tr>
                    <th style="width:40px"><i class="fa fa-arrows-alt"></i></th>
                    <th>Név</th>
                    <th>Típus</th>
                    <th>Szűrő</th>
                    <th>Suffix</th>
                    <th>Aktív</th>
                    <th>Tulajdonságok</th>
                    <th><i class="fa fa-pen"></i></th>
                </tr>
                </thead>
                <tbody id="sortable-list">
                @foreach($propertyCategories as $pc)
                    <tr>
                        <td class="ws-drag-handle"><i class="fa fa-grip-vertical text-muted"></i>
                            <input type="hidden" class="js-sort-id" value="{{ $pc->id }}">
                        </td>
                        <td class="font-weight-bold">{{ $pc->name }}</td>
                        <td><span class="badge badge-info">{{ $pc->filter_type }}</span></td>
                        <td>@if($pc->filter_enabled)<span class="badge badge-success">Igen</span>@else<span class="badge badge-secondary">Nem</span>@endif</td>
                        <td>{{ $pc->suffix ?? '-' }}</td>
                        <td>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input js-toggle-active" id="active{{ $pc->id }}" data-id="{{ $pc->id }}" data-url="{{ route('admin.webshop.property-categories.toggle-active') }}" @if($pc->is_active) checked @endif>
                                <label class="custom-control-label" for="active{{ $pc->id }}"></label>
                            </div>
                        </td>
                        <td>
                            @if(!$pc->isNumber())
                                <a href="{{ route('admin.webshop.properties.index', $pc) }}" class="btn btn-sm btn-outline-primary"><i class="fa fa-list"></i> Tulajdonságok</a>
                            @else <span class="text-muted">-</span> @endif
                        </td>
                        <td class="ws-nowrap">
                            <a href="{{ route('admin.webshop.property-categories.edit', $pc) }}" class="btn btn-sm btn-primary"><i class="fa fa-pen"></i></a>
                            <button type="button" class="btn btn-sm btn-danger js-delete-btn" data-url="{{ route('admin.webshop.property-categories.destroy', $pc) }}"><i class="fa fa-trash-alt"></i></button>
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
        WebshopAdmin.initSortable('#sortable-list', '{{ route("admin.webshop.property-categories.sort") }}');
        WebshopAdmin.initToggleActive();
        WebshopAdmin.initDeleteConfirm();
    </script>
@endsection

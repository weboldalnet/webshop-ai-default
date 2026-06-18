@extends('admin.layouts.layout')
@section('title', 'Kategóriák')

@section('content')
    <div class="container mt-lg-4 mt-3 mb-150">
        @include('admin.webshop.partials.alerts')

        <div class="row"><div class="col-lg-12"><h2 class="header-box"><i class="fa fa-folder"></i> Kategóriák</h2></div></div>

        <div class="row mt-2 mb-2">
            <div class="col-lg-12 text-center">
                <a href="{{ route('admin.webshop.categories.create') }}" class="btn btn-primary fs-18 font-weight-bold"><i class="fa fa-plus"></i> Új kategória</a>
            </div>
        </div>

        <div class="content-box bordered mb-3">
            <form method="GET" action="{{ route('admin.webshop.categories.index') }}" class="row align-items-end">
                <div class="col-lg-4 col-md-6 mb-2">
                    <label>Keresés</label>
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Név...">
                </div>
                <div class="col-lg-3 col-md-6 mb-2">
                    <label>Státusz</label>
                    <select name="is_active" class="form-control">
                        <option value="">Mind</option>
                        <option value="1" @if(request('is_active')==='1') selected @endif>Aktív</option>
                        <option value="0" @if(request('is_active')==='0') selected @endif>Inaktív</option>
                    </select>
                </div>
                <div class="col-lg-3 col-md-12 mb-2">
                    <button type="submit" class="btn btn-dark"><i class="fa fa-search"></i> Szűrés</button>
                    <a href="{{ route('admin.webshop.categories.index') }}" class="btn btn-outline-secondary">Törlés</a>
                </div>
            </form>
        </div>

        <div class="content-box table-responsive">
            <table class="table table-hover">
                <thead class="thead-dark">
                <tr>
                    <th style="width:40px"><i class="fa fa-arrows-alt"></i></th>
                    <th>Kép</th>
                    <th>Név</th>
                    @if($ws['category_parent_enabled'] ?? false)
                        <th>Szülő</th>
                    @endif
                    <th>Aktív</th>
                    <th><i class="fa fa-pen"></i></th>
                </tr>
                </thead>
                <tbody id="sortable-list">
                @foreach($categories as $cat)
                    <tr>
                        <td class="ws-drag-handle"><i class="fa fa-grip-vertical text-muted"></i><input type="hidden" class="js-sort-id" value="{{ $cat->id }}"></td>
                        <td>
                            @if($cat->getListImageUrl())
                                <img src="{{ $cat->getListImageUrl() }}" alt="kép" style="max-height: 40px; max-width: 40px; border-radius: 4px;">
                            @else
                                -
                            @endif
                        </td>
                        <td class="font-weight-bold">{{ $cat->name_singular }}</td>
                        @if($ws['category_parent_enabled'] ?? false)
                            <td>{{ $cat->parent ? $cat->parent->name_singular : '-' }}</td>
                        @endif
                        <td>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input js-toggle-active" id="active{{ $cat->id }}" data-id="{{ $cat->id }}" data-url="{{ route('admin.webshop.categories.toggle-active') }}" @if($cat->is_active) checked @endif>
                                <label class="custom-control-label" for="active{{ $cat->id }}"></label>
                            </div>
                        </td>
                        <td class="ws-nowrap">
                            <a href="{{ route('admin.webshop.categories.edit', $cat) }}" class="btn btn-sm btn-primary"><i class="fa fa-pen"></i></a>
                            <button type="button" class="btn btn-sm btn-danger js-delete-btn" data-url="{{ route('admin.webshop.categories.destroy', $cat) }}"><i class="fa fa-trash-alt"></i></button>
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
        WebshopAdmin.initSortable('#sortable-list', '{{ route("admin.webshop.categories.sort") }}');
        WebshopAdmin.initToggleActive();
        WebshopAdmin.initDeleteConfirm();
    </script>
@endsection

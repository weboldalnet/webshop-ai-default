@extends('admin.layouts.layout')
@section('title', 'Kategória szerkesztése')

@section('content')
    {{-- A Leírás mező js-tinymce osztályú: a szerkesztő betöltése nélkül sima szövegdoboz maradt. --}}
    @include('admin.commons.tinymce-img-upload', ['tinyHeight' => 260])

    <div class="container-fluid ws-editor mt-3 mb-150">
        @include('admin.webshop.partials.alerts')
        @include('admin.webshop.partials.form-errors')
        <form method="POST" action="{{ route('admin.webshop.categories.update', $category) }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            @include('admin.webshop.categories.form')

            <div class="admin-save-box">
                <div class="admin-save-btn-container mx-auto">
                    <button type="submit" class="btn btn-primary fs-18 fw-600"><i class="fa fa-save"></i> Mentés</button>
                    <a href="{{ route('admin.webshop.categories.index') }}" class="btn btn-secondary fs-18">Vissza</a>
                </div>
            </div>
        </form>
    </div>
    <link rel="stylesheet" href="/packages/webshop/admin/css/webshop-admin.css">
    <script src="/packages/webshop/admin/js/webshop-admin.js"></script>
    <script>
        WebshopAdmin.initToggleTiles('.ws-editor');
        WebshopAdmin.initSectionNav('.js-ws-section-nav');
    </script>

    @include('admin.commons.img-cropper')
@endsection

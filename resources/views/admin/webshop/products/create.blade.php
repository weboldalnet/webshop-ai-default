@extends('admin.layouts.layout')
@section('title', 'Új termék')

@section('content')
    {{-- A "Leírás" mező js-tinymce osztályú, de a szerkesztő betöltése eddig csak az edit
         oldalon volt meg — itt sima szövegdoboz maradt, a formázás nélkül beírt szöveg
         bekezdések nélkül mentődött. --}}
    @include('admin.commons.tinymce-img-upload', ['tinyHeight' => 300])

    <div class="container-fluid ws-editor mt-3 mb-150" style="max-width: 860px">
        @include('admin.webshop.partials.alerts')
        @include('admin.webshop.partials.form-errors')

        <div class="ws-editor-head">
            <div>
                <span class="ws-editor-kicker">Termék</span>
                <h2 class="ws-editor-title mb-1">Új termék</h2>
                <span class="ws-hint mt-0">Az alapadatok után a teljes szerkesztő nyílik meg: ár, tulajdonságok, képek.</span>
            </div>
            <div class="ws-editor-head-actions">
                <a href="{{ route('admin.webshop.products.index') }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left"></i> Terméklista
                </a>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.webshop.products.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="ws-card">
                <header class="ws-card-head">
                    <span class="ws-card-icon"><i class="fa-solid fa-pen"></i></span>
                    <div class="ws-card-heading"><h3 class="ws-card-title">Alapadatok</h3></div>
                </header>
                <div class="form-row">
                    <div class="form-group col-md-7">
                        <label for="name">Név <span class="ws-req">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                    </div>
                    <div class="form-group col-md-5">
                        <label for="category_id">Kategória <span class="ws-req">*</span></label>
                        <select class="form-control" id="category_id" name="category_id" required>
                            <option value="">– válassz –</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" @if(old('category_id') == $cat->id) selected @endif>{{ $cat->hierarchical_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="description">Leírás</label>
                    <textarea class="form-control js-tinymce" id="description" name="description" rows="4">{{ old('description') }}</textarea>
                </div>

                @if(\Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopSettingsService::get('admin_product_primary_image_mode', 'cropper') !== 'cropper')
                    <div class="form-group">
                        <label for="primary_image">Elsődleges kép</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fa-regular fa-file-image fs-18"></i></span>
                            </div>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input js-custom-file-input" id="primary_image" name="primary_image" accept="image/*">
                                <label class="custom-file-label text-muted" for="primary_image">Kép kiválasztása</label>
                            </div>
                        </div>
                        <span class="ws-hint">Alapértelmezett méret: {{ config('webshop.primary_image.width') }} × {{ config('webshop.primary_image.height') }} px.</span>
                    </div>
                @else
                    <span class="ws-hint">Az elsődleges képet a létrehozás után, a kategória képméretére vágva töltheted fel.</span>
                @endif
            </div>

            <div class="admin-save-box">
                <div class="admin-save-btn-container mx-auto">
                    <button type="submit" class="btn btn-primary fs-18 fw-600"><i class="fa fa-save"></i> Létrehozás</button>
                    <a href="{{ route('admin.webshop.products.index') }}" class="btn btn-secondary fs-18">Vissza</a>
                </div>
            </div>
        </form>
    </div>

    <link rel="stylesheet" href="/packages/webshop/admin/css/webshop-admin.css">
@endsection

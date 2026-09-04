@extends('admin.layouts.layout')
@section('title', 'Webshop nyitóoldal')

@section('content')
    @php
        $hpEnabled = ($ws['site_home_page_enabled'] ?? 'false') === 'true';
    @endphp

    <div class="container mt-lg-4 mt-3 mb-150">
        <div class="row">
            <div class="col-lg-12">
                <h2 class="header-box my-2"><i class="fa fa-home"></i> Webshop nyitóoldal</h2>
            </div>
        </div>

        @include('admin.webshop.partials.alerts')

        {{-- Főkapcsoló és SEO mezők: a blokkoktól független űrlap --}}
        <form action="{{ route('admin.webshop.extra-settings.home-page.store') }}" method="POST">
            @csrf
            <div class="row mt-4">
                <div class="col-lg-12">
                    <h3 class="header-box product-info mb-1">Alapbeállítások</h3>
                    <div class="content-box bordered mb-3">
                        <div class="custom-control custom-switch mb-3">
                            <input type="checkbox" class="custom-control-input" id="site_home_page_enabled"
                                   name="site_home_page_enabled" @if($hpEnabled) checked @endif>
                            <label class="custom-control-label" for="site_home_page_enabled">
                                Egyedi webshop nyitóoldal
                            </label>
                            <span class="text-muted fs-14 d-block">
                                Bekapcsolva a <code>/webshop</code> a lenti blokkokat mutatja a kategórialista helyett.
                                Ha egyetlen aktív blokk sincs, automatikusan a kategórialista jelenik meg.
                            </span>
                        </div>

                        <div class="row">
                            <div class="col-lg-6 form-group">
                                <label for="site_home_page_h1">Oldal címsora (H1)</label>
                                <input type="text" class="form-control" id="site_home_page_h1" name="site_home_page_h1"
                                       value="{{ $ws['site_home_page_h1'] ?? '' }}">
                                <span class="text-muted fs-14">Üresen hagyva nem jelenik meg címsor.</span>
                            </div>
                            <div class="col-lg-6 form-group">
                                <label for="site_home_page_meta_title">Böngészőcím (meta title)</label>
                                <input type="text" class="form-control" id="site_home_page_meta_title" name="site_home_page_meta_title"
                                       value="{{ $ws['site_home_page_meta_title'] ?? '' }}">
                            </div>
                            <div class="col-12 form-group mb-0">
                                <label for="site_home_page_meta_description">Meta leírás</label>
                                <textarea class="form-control" rows="2" id="site_home_page_meta_description"
                                          name="site_home_page_meta_description">{{ $ws['site_home_page_meta_description'] ?? '' }}</textarea>
                            </div>
                        </div>

                        <div class="text-center mt-3">
                            <button type="submit" class="btn btn-primary font-weight-bold">
                                <i class="fa fa-save"></i> Alapbeállítások mentése
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        {{-- Blokklista --}}
        <div class="row">
            <div class="col-lg-12">
                <h3 class="header-box product-info mb-1">Tartalmi blokkok</h3>

                @if(empty($blocks))
                    <div class="content-box bordered mb-3">
                        <p class="text-muted mb-0">Még nincs egyetlen blokk sem. Adj hozzá egyet lentebb.</p>
                    </div>
                @else
                    <div id="ws-home-blocks">
                        @foreach($blocks as $entry)
                            @include('admin.webshop.extra-settings.home-page.block-card', [
                                'block' => $entry['block'],
                                'items' => $entry['items'],
                            ])
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Új blokk --}}
        <form action="{{ route('admin.webshop.extra-settings.home-page.blocks.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-lg-12">
                    <h3 class="header-box product-info mb-1">Új blokk hozzáadása</h3>
                    <div class="content-box bordered mb-3">
                        <div class="form-row align-items-end">
                            <div class="col-md-4 form-group mb-md-0">
                                <label for="new_block_type">Blokk típusa</label>
                                <select class="form-control" id="new_block_type" name="type">
                                    @foreach($types as $typeKey => $typeMeta)
                                        <option value="{{ $typeKey }}">{{ $typeMeta['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5 form-group mb-md-0">
                                <label for="new_block_title">Címsor (nem kötelező)</label>
                                <input type="text" class="form-control" id="new_block_title" name="title">
                            </div>
                            <div class="col-md-3 form-group mb-0">
                                <button type="submit" class="btn btn-success btn-block font-weight-bold">
                                    <i class="fa fa-plus"></i> Hozzáadás
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div class="text-center mt-4">
            <a href="{{ route('admin.webshop.extra-settings.index') }}" class="btn btn-outline-secondary">
                <i class="fa fa-arrow-left mr-1"></i> Vissza az extra beállításokhoz
            </a>
        </div>
    </div>

    {{-- A törlő modal MINDEN űrlapon kívül: saját <form>-ot tartalmaz, beágyazva
         a böngésző eldobná, és a blokk mentése törléssé válhatna. --}}
    @include('admin.webshop.modals.delete-confirm')
    @include('admin.commons.img-cropper', ['galleryPicker' => false])
    @include('admin.commons.tinymce', ['tinyHeight' => 260])

    <link rel="stylesheet" href="/packages/webshop/admin/css/webshop-admin.css">
    {{-- A webshop-admin.js NINCS globálisan az admin layoutban, minden oldal maga
         húzza be. Enélkül a WebshopAdmin hiányzik, és a drag&drop némán nem működik. --}}
    <script src="/packages/webshop/admin/js/webshop-admin.js"></script>

    @include('admin.webshop.extra-settings.home-page.scripts')
@endsection

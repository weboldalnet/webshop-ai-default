@extends('admin.layouts.layout')
@section('title', 'Webshop extra beállítások')

@section('content')
    <div class="container mt-lg-4 mt-3 mb-150">
        <div class="row">
            <div class="col-lg-12">
                <h2 class="header-box"><i class="fa fa-cog"></i> Webshop extra beállítások</h2>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card h-100 shadow-sm border-0 text-center py-4">
                    <div class="card-body">
                        <div class="fs-28 text-primary mb-3"><i class="fa fa-envelope"></i></div>
                        <h4 class="font-weight-bold">Email szerkesztése</h4>
                        <p class="text-muted small">Rendelés visszaigazoló emailek szövegezése.</p>
                        <a href="{{ route('admin.webshop.extra-settings.custom-contents.index', ['type' => 'email']) }}" class="btn btn-outline-primary btn-sm stretched-link">Megnyitás</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card h-100 shadow-sm border-0 text-center py-4">
                    <div class="card-body">
                        <div class="fs-28 text-success mb-3"><i class="fa fa-check-circle"></i></div>
                        <h4 class="font-weight-bold">Köszönjük oldal</h4>
                        <p class="text-muted small">Sikeres rendelés utáni oldal szövegezése.</p>
                        <a href="{{ route('admin.webshop.extra-settings.custom-contents.index', ['type' => 'thank_you']) }}" class="btn btn-outline-success btn-sm stretched-link">Megnyitás</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card h-100 shadow-sm border-0 text-center py-4">
                    <div class="card-body">
                        <div class="fs-28 text-info mb-3"><i class="fa fa-file-contract"></i></div>
                        <h4 class="font-weight-bold">Dokumentumok</h4>
                        <p class="text-muted small">ÁSZF és Adatvédelmi tájékoztató kezelése.</p>
                        <a href="{{ route('admin.webshop.extra-settings.documents.index') }}" class="btn btn-outline-info btn-sm stretched-link">Megnyitás</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card h-100 shadow-sm border-0 text-center py-4">
                    <div class="card-body">
                        <div class="fs-28 text-warning mb-3"><i class="fa fa-code"></i></div>
                        <h4 class="font-weight-bold">Mérési scriptek</h4>
                        <p class="text-muted small">Google Analytics, Facebook Pixel stb. kódok.</p>
                        <a href="{{ route('admin.webshop.extra-settings.scripts.index') }}" class="btn btn-outline-warning btn-sm stretched-link">Megnyitás</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

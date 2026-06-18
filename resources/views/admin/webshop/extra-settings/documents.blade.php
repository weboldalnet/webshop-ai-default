@extends('admin.layouts.layout')
@section('title', 'Dokumentumok szerkesztése')

@section('content')
    <div class="container mt-lg-4 mt-3 mb-150">
        <div class="row">
            <div class="col-lg-12">
                <h2 class="header-box"><i class="fa fa-file-contract"></i> Dokumentumok szerkesztése</h2>
            </div>
        </div>

        @include('admin.webshop.partials.alerts')

        <form action="{{ route('admin.webshop.extra-settings.documents.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            {{-- ÁSZF --}}
            <div class="row mt-4">
                <div class="col-lg-12">
                    <h3 class="header-box product-info">Általános Szerződési Feltételek (ÁSZF)</h3>
                    <div class="content-box bordered">
                        <div class="custom-control custom-switch mb-3">
                            <input type="checkbox" class="custom-control-input" id="site_checkout_tos_enabled" name="site_checkout_tos_enabled" value="1" @if(($ws['site_checkout_tos_enabled'] ?? 'false') === 'true') checked @endif>
                            <label class="custom-control-label" for="site_checkout_tos_enabled">Megjelenik a checkout oldalon</label>
                        </div>
                        <div class="form-group">
                            <label for="tos_label">Megjelenő szöveg (label)</label>
                            <input type="text" class="form-control" id="tos_label" name="tos_label" value="{{ $ws['site_checkout_tos_label'] ?? 'Elfogadom az Általános Szerződési Feltételeket' }}">
                        </div>
                        <div class="form-group">
                            <label for="tos_url">Külső URL</label>
                            <input type="text" class="form-control" id="tos_url" name="tos_url" value="{{ $ws['site_checkout_tos_url'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label for="tos_file">Fájl feltöltése</label>
                            @if(isset($ws['site_checkout_tos_path']))
                                <div class="mb-2 small"><a href="{{ $ws['site_checkout_tos_path'] }}" target="_blank"><i class="fa fa-file-pdf"></i> Jelenlegi fájl megnyitása</a></div>
                            @endif
                            <input type="file" class="form-control-file" id="tos_file" name="tos_file" accept=".pdf,.doc,.docx">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Adatvédelem --}}
            <div class="row mt-4">
                <div class="col-lg-12">
                    <h3 class="header-box product-info">Adatvédelmi tájékoztató</h3>
                    <div class="content-box bordered">
                        <div class="custom-control custom-switch mb-3">
                            <input type="checkbox" class="custom-control-input" id="site_checkout_privacy_enabled" name="site_checkout_privacy_enabled" value="1" @if(($ws['site_checkout_privacy_enabled'] ?? 'false') === 'true') checked @endif>
                            <label class="custom-control-label" for="site_checkout_privacy_enabled">Megjelenik a checkout oldalon</label>
                        </div>
                        <div class="form-group">
                            <label for="privacy_label">Megjelenő szöveg (label)</label>
                            <input type="text" class="form-control" id="privacy_label" name="privacy_label" value="{{ $ws['site_checkout_privacy_label'] ?? 'Elfogadom az Adatvédelmi tájékoztatót' }}">
                        </div>
                        <div class="form-group">
                            <label for="privacy_url">Külső URL</label>
                            <input type="text" class="form-control" id="privacy_url" name="privacy_url" value="{{ $ws['site_checkout_privacy_url'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label for="privacy_file">Fájl feltöltése</label>
                            @if(isset($ws['site_checkout_privacy_path']))
                                <div class="mb-2 small"><a href="{{ $ws['site_checkout_privacy_path'] }}" target="_blank"><i class="fa fa-file-pdf"></i> Jelenlegi fájl megnyitása</a></div>
                            @endif
                            <input type="file" class="form-control-file" id="privacy_file" name="privacy_file" accept=".pdf,.doc,.docx">
                        </div>
                    </div>
                </div>
            </div>

            <div class="admin-save-box" style="left: 0">
                <div class="admin-save-btn-container mx-auto">
                    <button type="submit" class="btn btn-primary fs-18 font-weight-bold"><i class="fa fa-save"></i> Mentés</button>
                    <a href="{{ route('admin.webshop.extra-settings.index') }}" class="btn btn-secondary">Vissza</a>
                </div>
            </div>
        </form>
    </div>
@endsection

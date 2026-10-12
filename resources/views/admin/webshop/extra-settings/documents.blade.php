@extends('admin.layouts.layout')
@section('title', 'Dokumentumok szerkesztése')

@section('content')
    <div class="container mt-lg-4 mt-3 mb-150">
        <div class="row">
            <div class="col-lg-12">
                <h2 class="header-box my-2"><i class="fa fa-file-contract"></i> Dokumentumok szerkesztése</h2>
            </div>
        </div>

        @include('admin.webshop.partials.alerts')

        <form action="{{ route('admin.webshop.extra-settings.documents.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Eladó adatai: az e-kereskedelmi törvény szerint folyamatosan elérhetővé kell tenni, és a
                 rendelés-visszaigazoló levél, valamint az elállási nyilatkozatminta is ezeket használja.
                 Ami üresen marad, ott a Kapcsolat beállítások (cím, e-mail, telefon) adatai jelennek meg. --}}
            @php
                $wsSellerFields = \Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopLegalInfoService::SELLER_FIELDS;
                $wsSellerMissing = \Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopLegalInfoService::missingSellerFields();
            @endphp
            <div class="row mt-4">
                <div class="col-lg-12">
                    <h3 class="header-box product-info mb-1">Eladó adatai</h3>
                    <div class="content-box bordered mb-3">
                        @if(!empty($wsSellerMissing))
                            <div class="alert alert-warning small">
                                <strong>Hiányzó kötelező adat:</strong> {{ implode(', ', $wsSellerMissing) }}.
                                Ezeket a vásárlók számára folyamatosan elérhetővé kell tenni, és a rendelés-visszaigazoló
                                levélben is szerepelnek.
                            </div>
                        @endif
                        <div class="row">
                            @foreach($wsSellerFields as $wsField => $wsLabel)
                                @php $wsKey = \Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopLegalInfoService::settingKey($wsField); @endphp
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="{{ $wsKey }}">{{ $wsLabel }}</label>
                                        <input type="text" class="form-control" id="{{ $wsKey }}" name="{{ $wsKey }}"
                                               value="{{ $ws[$wsKey] ?? '' }}">
                                        @if(in_array($wsField, ['seat', 'email', 'phone'], true))
                                            <span class="text-muted fs-14">Üresen hagyva a Kapcsolat beállítások adata jelenik meg.</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- ÁSZF --}}
            <div class="row mt-4">
                <div class="col-lg-12">
                    <h3 class="header-box product-info mb-1">Általános Szerződési Feltételek (ÁSZF)</h3>
                    <div class="content-box bordered mb-3">
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
                        {{-- A szerződést tartós adathordozón kell visszaigazolni – egy link ennek nem felel meg,
                             a PDF-melléklet igen. Csak feltöltött PDF csatolható (külső URL nem). --}}
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="site_order_mail_attach_tos" name="site_order_mail_attach_tos" value="1"
                                   @if(($ws['site_order_mail_attach_tos'] ?? 'true') === 'true') checked @endif>
                            <label class="custom-control-label" for="site_order_mail_attach_tos">
                                Csatolás a rendelés-visszaigazoló levélhez (PDF esetén)
                            </label>
                        </div>
                        <span class="text-muted fs-14">
                            A szerződési feltételeket tartós adathordozón kell átadni a vásárlónak; egy weboldal-link ennek
                            nem felel meg, a levélhez csatolt PDF igen.
                        </span>
                    </div>
                </div>
            </div>

            {{-- Adatvédelem --}}
            <div class="row mt-4">
                <div class="col-lg-12">
                    <h3 class="header-box product-info mb-1">Adatvédelmi tájékoztató</h3>
                    <div class="content-box bordered mb-3">
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

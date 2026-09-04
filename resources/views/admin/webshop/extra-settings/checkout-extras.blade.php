@extends('admin.layouts.layout')
@section('title', 'Pénztár kiegészítések')

@section('content')
    <div class="container mt-lg-4 mt-3 mb-150">
        <div class="row">
            <div class="col-lg-12">
                <h2 class="header-box my-2"><i class="fa fa-cart-plus"></i> Pénztár kiegészítések</h2>
            </div>
        </div>

        @include('admin.webshop.partials.alerts')

        <form action="{{ route('admin.webshop.extra-settings.checkout-extras.store') }}" method="POST">
            @csrf

            {{-- Kérdőív --}}
            <div class="row mt-4">
                <div class="col-lg-12">
                    <h3 class="header-box product-info mb-1">Kérdőív a pénztárban</h3>
                    <div class="content-box bordered mb-3">
                        @if($qaSupported)
                            <div class="form-group mb-0">
                                <label for="site_checkout_qa_id">Megjelenő kérdőív</label>
                                <select class="form-control" id="site_checkout_qa_id" name="site_checkout_qa_id" style="max-width: 420px;">
                                    <option value="">— Nincs kérdőív —</option>
                                    @foreach($qaList as $qaId => $qaName)
                                        <option value="{{ $qaId }}" @if((string)($ws['site_checkout_qa_id'] ?? '') === (string)$qaId) selected @endif>{{ $qaName }}</option>
                                    @endforeach
                                </select>
                                <span class="text-muted fs-14">
                                    A kiválasztott kérdőív a Megjegyzés doboz fölött jelenik meg a pénztárban, a válaszok pedig
                                    a rendeléshez mentődnek, és a rendelés adatlapján is megjelennek.
                                    Kérdőívet a <a href="/qa-list" target="_blank">Kérdőívek</a> oldalon lehet létrehozni.
                                </span>
                            </div>
                        @else
                            <p class="text-muted mb-0">Ebben a projektben nincs kérdőív modul, ezért ez a beállítás nem érhető el.</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Értesítő dobozok --}}
            <div class="row">
                <div class="col-lg-6">
                    <h3 class="header-box product-info mb-1">Értesítés a kosár doboz fölött</h3>
                    <div class="content-box bordered mb-3">
                        <div class="form-group">
                            <label for="site_checkout_notice_top_type">Doboz színe</label>
                            <select class="form-control" id="site_checkout_notice_top_type" name="site_checkout_notice_top_type" style="max-width: 260px;">
                                @foreach($noticeTypes as $typeKey => $typeLabel)
                                    <option value="{{ $typeKey }}" @if(($ws['site_checkout_notice_top_type'] ?? 'info') === $typeKey) selected @endif>{{ $typeLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <label for="site_checkout_notice_top_content">Tartalom</label>
                            <textarea class="form-control js-tinymce" id="site_checkout_notice_top_content"
                                      name="site_checkout_notice_top_content">{!! $ws['site_checkout_notice_top_content'] ?? '' !!}</textarea>
                            <span class="text-muted fs-14">Üresen hagyva nem jelenik meg semmi.</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <h3 class="header-box product-info mb-1">Értesítés az Összegzés doboz fölött</h3>
                    <div class="content-box bordered mb-3">
                        <div class="form-group">
                            <label for="site_checkout_notice_bottom_type">Doboz színe</label>
                            <select class="form-control" id="site_checkout_notice_bottom_type" name="site_checkout_notice_bottom_type" style="max-width: 260px;">
                                @foreach($noticeTypes as $typeKey => $typeLabel)
                                    <option value="{{ $typeKey }}" @if(($ws['site_checkout_notice_bottom_type'] ?? 'info') === $typeKey) selected @endif>{{ $typeLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <label for="site_checkout_notice_bottom_content">Tartalom</label>
                            <textarea class="form-control js-tinymce" id="site_checkout_notice_bottom_content"
                                      name="site_checkout_notice_bottom_content">{!! $ws['site_checkout_notice_bottom_content'] ?? '' !!}</textarea>
                            <span class="text-muted fs-14">A rendelés leadása gomb dobozának tetején jelenik meg.</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-3">
                <button type="submit" class="btn btn-primary fs-18 font-weight-bold"><i class="fa fa-save"></i> Mentés</button>
            </div>
        </form>
    </div>

    @include('admin.commons.tinymce', ['tinyHeight' => 220])
@endsection

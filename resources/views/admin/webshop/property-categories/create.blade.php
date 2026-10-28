@extends('admin.layouts.layout')
@section('title', 'Új tulajdonságcsoport')

@section('content')
    <div class="container-fluid ws-editor mt-3 mb-150" style="max-width: 900px">
        @include('admin.webshop.partials.alerts')
        @include('admin.webshop.partials.form-errors')

        <div class="ws-editor-head">
            <div>
                <span class="ws-editor-kicker">Tulajdonságcsoport</span>
                <h2 class="ws-editor-title mb-0">Új tulajdonságcsoport</h2>
            </div>
            <div class="ws-editor-head-actions">
                <a href="{{ route('admin.webshop.property-categories.index') }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left"></i> Tulajdonságcsoportok
                </a>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.webshop.property-categories.store') }}">
            @csrf
            @include('admin.webshop.property-categories.form')

            <div class="admin-save-box">
                <div class="admin-save-btn-container mx-auto">
                    <button type="submit" class="btn btn-primary fs-18 fw-600"><i class="fa fa-save"></i> Mentés</button>
                    <a href="{{ route('admin.webshop.property-categories.index') }}" class="btn btn-secondary fs-18">Vissza</a>
                </div>
            </div>
        </form>
    </div>
    <link rel="stylesheet" href="/packages/webshop/admin/css/webshop-admin.css">
    <script src="/packages/webshop/admin/js/webshop-admin.js"></script>
    <script>WebshopAdmin.initFilterType();</script>
@endsection

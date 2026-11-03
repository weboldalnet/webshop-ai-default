@extends('admin.layouts.layout')
@section('title', 'AI-fordítás riportja')

@section('content')
    <div class="container-fluid ws-editor mt-3 mb-150" style="max-width: 1100px">
        <div class="ws-editor-head">
            <div>
                <span class="ws-editor-kicker">Fordítások · AI-futás #{{ $run->id }}</span>
                <h2 class="ws-editor-title">{{ $run->scope_label }}</h2>
            </div>
            <div class="ws-editor-head-actions">
                <a href="{{ route('admin.webshop.translations.index') }}" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left"></i> Fordítások</a>
            </div>
        </div>
        <div class="ws-card js-tr-report" data-restore-run-url="{{ route('admin.webshop.translations.runs.restore', $run) }}">
            @include('admin.webshop.translations.partials.report')
        </div>
    </div>

    <link rel="stylesheet" href="/packages/webshop/admin/css/webshop-admin.css">
    <script src="/packages/webshop/admin/js/webshop-translations.js?v={{ @filemtime(public_path('packages/webshop/admin/js/webshop-translations.js')) }}"></script>
    <script>WebshopTranslations.initReportActions(document.querySelector('.js-tr-report'));</script>
@endsection

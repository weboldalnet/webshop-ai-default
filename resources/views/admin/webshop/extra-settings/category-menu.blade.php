{{--
    Egyedi kategória menüsor szerkesztője.

    A teljes kategóriafa kirajzolódik, kategóriánként egy kapcsolóval. A kapcsoló a
    show_in_sticky_header oszlopot írja – ugyanazt, amit a kategória szerkesztő
    űrlapján a "Megjelenik a sticky fejlécben" mező.

    Nincs mentés gomb: a kapcsoló AJAX-szal azonnal ment (ugyanaz a minta, mint a
    nyitóoldal blokkjainál).
--}}
@extends('admin.layouts.layout')
@section('title', 'Kategória menüsor')

@section('content')
    <div class="container mt-lg-4 mt-3 mb-150">
        @includeIf('admin.webshop.partials.alerts')

        <div class="row">
            <div class="col-lg-12">
                <h2 class="header-box my-2"><i class="fa fa-sitemap"></i> Kategória menüsor</h2>
            </div>
        </div>

        <div class="content-box bordered mb-3">
            <p class="text-muted fs-14 mb-2">
                Itt állítod be, mely kategóriák jelenjenek meg az egyedi menüsorban.
                A sorrend a <a href="{{ route('admin.webshop.categories.index') }}">Kategóriák</a>
                oldalon állítható be, itt csak megjelenik.
            </p>
            <p class="text-muted fs-14 mb-0">
                Asztali nézetben a legfelső szintű kategóriák alkotják a sávot, föléjük húzva
                nyílik le a többi szint. Mobilon egy „Kategóriák” gomb nyitja meg ugyanezt a fát.
            </p>
        </div>

        @if(empty($categoryTree))
            <div class="content-box bordered">
                <div class="alert alert-warning mb-0">
                    <i class="fa fa-exclamation-triangle mr-1"></i>
                    Még nincs egyetlen kategória sem.
                    <a href="{{ route('admin.webshop.categories.create') }}">Hozz létre egyet</a>,
                    hogy legyen mit a menüsorba tenni.
                </div>
            </div>
        @else
            <div class="content-box bordered mb-3">
                @include('admin.webshop.extra-settings.partials.category-menu-nodes', [
                    'nodes' => $categoryTree,
                ])
            </div>
        @endif

        <div class="text-center mt-3">
            <a href="{{ route('admin.webshop.extra-settings.index') }}" class="btn btn-secondary font-weight-bold">
                Vissza
            </a>
        </div>
    </div>

    <link rel="stylesheet" href="/packages/webshop/admin/css/webshop-admin.css">
    <script src="/packages/webshop/admin/js/webshop-admin.js"></script>
    <script>
        // A megosztott aktív-kapcsoló segéd: id + is_active POST, toast-tal.
        WebshopAdmin.initToggleActive();
    </script>
@endsection

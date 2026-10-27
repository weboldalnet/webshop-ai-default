@extends('site.layouts.layout')

{{-- A nyitóoldal meta adatai a Webshop > Nyitóoldal beállításokból jönnek. A `$metaDescription`
     korábban át volt adva a nézetnek, de SEHOL nem jelent meg: a mező ki volt tölthető az
     adminban, a lapon mégis üres maradt a meta description. --}}
@php
    $wsSeo = \Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopSeoService::shopHome();
@endphp
@section('title', $wsSeo['title'])
@section('description', $metaDescription ?: $wsSeo['description'])
@section('og_image', $wsSeo['image'])

@section('content')
    @include('site.webshop.partials.sticky-categories')
    @include('site.webshop.partials.flash-messages')

    {{-- A blokkok SZÁNDÉKOSAN nincsenek közös konténerben: mindegyik maga hozza
         a sajátját, hogy a háttérszíne teljes oldalszélességben futhasson. --}}
    <div class="ws-page-container ws-home-index pb-5">
        @if($h1)
            <div class="container-xl container-fluid pt-4">
                <div class="row">
                    <div class="col-lg-12">
                        <h1 class="h2 mb-lg-4 mb-3 font-weight-bold">{{ $h1 }}</h1>
                    </div>
                </div>
            </div>
        @endif

        @foreach($blocks as $entry)
            @include('site.webshop.home.partials.block', [
                'block' => $entry['block'],
                'items' => $entry['items'],
            ])
        @endforeach
    </div>

    @push('scripts')
        <script src="/packages/webshop/site/js/webshop-site.js"></script>
        @include('site.webshop.partials.tracking-scripts', ['pageType' => 'homepage'])
    @endpush
@endsection

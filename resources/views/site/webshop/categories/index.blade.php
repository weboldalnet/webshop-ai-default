@extends('site.layouts.layout')

{{-- Ez a nézet KÉT dolgot szolgál ki: a webshop nyitóoldalát (/webshop) és egy
     szülőkategória alkategória-listáját. A SEO adatokat ezért a szolgáltatás választja szét:
     szülőkategóriánál annak saját mezői, gyökérszinten a Webshop > Nyitóoldal meta mezői,
     azok híján a tényleges kategóriakínálatból képzett leírás. --}}
@php
    $wsSeo = \Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopSeoService::shopHome($parentCategory ?? null);
@endphp
@section('title', $wsSeo['title'])
@section('description', $wsSeo['description'])
@section('og_image', $wsSeo['image'])

@section('content')
    @include('site.webshop.partials.sticky-categories')
    @include('site.webshop.partials.flash-messages')

    <div class="ws-page-container ws-categories-index">
        <div class="container-xl container-fluid pb-5">
            @isset($parentCategory)
                @include('site.webshop.partials.breadcrumb', ['category' => $parentCategory ?? null])
            @endisset
            <div class="row mt-4">
                <div class="col-lg-12">
                    <h1 class="h2 mb-4 font-weight-bold">{{ isset($parentCategory) ? $parentCategory->name_plural : 'Válasszon kategóriát' }}</h1>
                </div>
            </div>

            <div class="row">
                @foreach($categories as $cat)
                    <div class="{{ $cat->getCardColumnClass() }} col-md-4 col-sm-6 mb-4">
                        @include('site.webshop.categories.partials.category-card', ['cat' => $cat])
                    </div>
                @endforeach
            </div>

            @isset($parentCategory)
                @if($parentCategory->description)
                    <div class="row mt-5">
                        <div class="col-lg-12">
                            <div class="ws-category-description-box p-lg-4 p-3 bg-white border rounded shadow-sm">
                                <h2 class="h4 font-weight-bold mb-lg-3 mb-2 text-dark">{{ $parentCategory->name_singular }}</h2>
                                <div class="ws-category-description text-muted fs-16">
                                    {!! $parentCategory->description !!}
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endisset
        </div>
    </div>

    @push('scripts')
        <script src="/packages/webshop/site/js/webshop-site.js"></script>
        @include('site.webshop.partials.tracking-scripts', ['pageType' => 'product_list'])
    @endpush
@endsection

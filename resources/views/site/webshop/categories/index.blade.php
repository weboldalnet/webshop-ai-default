@extends('site.layouts.layout')
@section('title', isset($parentCategory) ? $parentCategory->name_singular : 'Webshop kategóriák')

@section('content')
    @include('site.webshop.partials.sticky-categories')

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
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    @include('site.webshop.categories.partials.category-card', ['cat' => $cat])
                </div>
            @endforeach
        </div>
    </div>

@endsection

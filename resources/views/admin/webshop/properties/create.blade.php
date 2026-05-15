@extends('admin.layouts.layout')
@section('title', 'Új tulajdonság - ' . $propertyCategory->name)

@section('content')
    <div class="container mw-600 mt-3 mb-150">
        @include('admin.webshop.partials.alerts')
        @include('admin.webshop.partials.form-errors')
        <div class="row"><div class="col-lg-12"><h3 class="header-box product-info">Új tulajdonság - {{ $propertyCategory->name }}</h3></div></div>
        <div class="content-box bordered">
            <form method="POST" action="{{ route('admin.webshop.properties.store', $propertyCategory) }}">
                @csrf
                @include('admin.webshop.properties.form')
                <div class="text-center mt-3">
                    <button type="submit" class="btn btn-primary fs-18 font-weight-bold"><i class="fa fa-save"></i> Mentés</button>
                    <a href="{{ route('admin.webshop.properties.index', $propertyCategory) }}" class="btn btn-secondary">Vissza</a>
                </div>
            </form>
        </div>
    </div>
@endsection

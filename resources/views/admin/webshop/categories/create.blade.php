@extends('admin.layouts.layout')
@section('title', 'Új kategória')

@section('content')
    <div class="container mt-3 mb-150">
        @include('admin.webshop.partials.alerts')
        @include('admin.webshop.partials.form-errors')
        <form method="POST" action="{{ route('admin.webshop.categories.store') }}" enctype="multipart/form-data">
            @csrf
            @include('admin.webshop.categories.form')
            <div class="text-center mt-3">
                <button type="submit" class="btn btn-primary fs-18 font-weight-bold"><i class="fa fa-save"></i> Mentés</button>
                <a href="{{ route('admin.webshop.categories.index') }}" class="btn btn-secondary">Vissza</a>
            </div>
        </form>
    </div>
    <link rel="stylesheet" href="/packages/webshop/admin/css/webshop-admin.css">
@endsection

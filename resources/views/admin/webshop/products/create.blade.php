@extends('admin.layouts.layout')
@section('title', 'Új termék')

@section('content')
    <div class="container mw-600 mt-3 mb-150">
        @include('admin.webshop.partials.alerts')
        @include('admin.webshop.partials.form-errors')

        <div class="row"><div class="col-lg-12"><h3 class="header-box product-info"><i class="fa fa-box"></i> Új termék</h3></div></div>
        <div class="content-box bordered">
            <form method="POST" action="{{ route('admin.webshop.products.store') }}">
                @csrf
                <div class="form-group">
                    <label for="name">Név <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                </div>
                <div class="form-group">
                    <label for="category_id">Kategória <span class="text-danger">*</span></label>
                    <select class="form-control" id="category_id" name="category_id" required>
                        <option value="">-- Válassz --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @if(old('category_id') == $cat->id) selected @endif>{{ $cat->hierarchical_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="description">Leírás</label>
                    <textarea class="form-control js-tinymce" id="description" name="description" rows="4">{{ old('description') }}</textarea>
                </div>
                <div class="text-center mt-3">
                    <button type="submit" class="btn btn-primary fs-18 font-weight-bold"><i class="fa fa-save"></i> Létrehozás</button>
                    <a href="{{ route('admin.webshop.products.index') }}" class="btn btn-secondary">Vissza</a>
                </div>
            </form>
        </div>
    </div>
@endsection

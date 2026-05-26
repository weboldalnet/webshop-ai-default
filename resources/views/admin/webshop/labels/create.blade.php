@extends('admin.layouts.layout')
@section('title', 'Új termék címke létrehozása')

@section('content')
    <div class="container mt-lg-4 mt-3 mb-150">
        @include('admin.webshop.partials.alerts')

        <div class="row"><div class="col-lg-12"><h2 class="header-box">Új termék címke létrehozása</h2></div></div>

        <form method="POST" action="{{ route('admin.webshop.labels.store') }}">
            @csrf
            <div class="row">
                <div class="col-lg-6 mb-3">
                    <h2 class="header-box product-info">Címke adatai</h2>
                    <div class="content-box bordered">
                        <div class="form-group">
                            <label for="name">Név <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="bg_color">Háttérszín</label>
                                    <input type="color" class="form-control" id="bg_color" name="bg_color" value="{{ old('bg_color', '#000000') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="text_color">Szövegszín</label>
                                    <input type="color" class="form-control" id="text_color" name="text_color" value="{{ old('text_color', '#ffffff') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-3">
                    <h2 class="header-box product-info">Előnézet</h2>
                    <div class="content-box bordered text-center py-5">
                        <div id="label-preview" style="display: inline-block; padding: 10px 20px; font-size: 20px; font-weight: bold; border-radius: 4px; background-color: #000000; color: #ffffff;">
                            Címke minta
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-3">
                <a href="{{ route('admin.webshop.labels.index') }}" class="btn btn-secondary font-weight-bold">Vissza</a>
                <button type="submit" class="btn btn-primary font-weight-bold ml-2">Címke mentése</button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const nameInput = document.getElementById('name');
            const bgInput = document.getElementById('bg_color');
            const textInput = document.getElementById('text_color');
            const preview = document.getElementById('label-preview');

            function updatePreview() {
                preview.textContent = nameInput.value || 'Címke minta';
                preview.style.backgroundColor = bgInput.value;
                preview.style.color = textInput.value;
            }

            nameInput.addEventListener('input', updatePreview);
            bgInput.addEventListener('input', updatePreview);
            textInput.addEventListener('input', updatePreview);

            updatePreview();
        });
    </script>
@endsection

@extends('admin.layouts.layout')
@section('title', isset($product) ? 'Vélemények: ' . $product->name : 'Összes vélemény')

@section('content')
    <div class="container mt-lg-4 mt-3 mb-150">
        @include('admin.webshop.partials.alerts')

        <div class="row">
            <div class="col-lg-12">
                <h2 class="header-box">
                    <i class="fa fa-comments"></i> {{ isset($product) ? 'Vélemények: ' . $product->name : 'Összes vélemény' }}
                    <a href="{{ route('admin.webshop.products.index') }}" class="btn btn-outline-dark btn-sm float-right"><i class="fa fa-arrow-left"></i> Vissza</a>
                </h2>
            </div>
        </div>

        <div class="content-box table-responsive mt-3">
            <table class="table table-hover">
                <thead class="thead-dark">
                <tr>
                    @if(!isset($product))
                        <th>Termék</th>
                    @endif
                    <th>Név</th>
                    <th>Értékelés</th>
                    <th>Vélemény</th>
                    <th>Dátum</th>
                    <th>Aktív</th>
                    <th><i class="fa fa-trash-alt"></i></th>
                </tr>
                </thead>
                <tbody>
                @forelse($reviews as $review)
                    @php $currentProduct = $product ?? $review->product; @endphp
                    <tr>
                        @if(!isset($product))
                            <td class="font-weight-bold">
                                @if($currentProduct)
                                    <a href="{{ route('admin.webshop.products.reviews.index', $currentProduct) }}">{{ $currentProduct->name }}</a>
                                @else
                                    <span class="text-muted">Törölt termék</span>
                                @endif
                            </td>
                        @endif
                        <td class="font-weight-bold">{{ $review->name }}</td>
                        <td>
                            @for($i=1; $i<=5; $i++)
                                <i class="fa{{ $i <= $review->rating ? '-solid' : '-regular' }} fa-star text-warning"></i>
                            @endfor
                        </td>
                        <td>{{ $review->review }}</td>
                        <td>{{ $review->created_at->format('Y.m.d. H:i') }}</td>
                        <td>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input js-toggle-active" id="active{{ $review->id }}" data-id="{{ $review->id }}" data-url="{{ route('admin.webshop.products.reviews.toggle-active', $currentProduct ?? 0) }}" @if($review->is_active) checked @endif>
                                <label class="custom-control-label" for="active{{ $review->id }}"></label>
                            </div>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger js-delete-btn" data-url="{{ route('admin.webshop.products.reviews.destroy', [$currentProduct ?? 0, $review]) }}"><i class="fa fa-trash-alt"></i></button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ !isset($product) ? 7 : 6 }}" class="text-center text-muted">Nincsenek vélemények.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $reviews->links() }}
        </div>
    </div>

    @include('admin.webshop.modals.delete-confirm')
    <link rel="stylesheet" href="/packages/webshop/admin/css/webshop-admin.css">
    <script src="/packages/webshop/admin/js/webshop-admin.js"></script>
    <script>
        WebshopAdmin.initToggleActive();
        WebshopAdmin.initDeleteConfirm();
    </script>
@endsection

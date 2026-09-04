@extends('site.layouts.layout')
@section('title', 'Rendelés részletei')

@section('content')
    <div class="ws-page-container ws-checkout-success bg-light py-5">
        <div class="container-xl container-fluid">

            {{-- Köszönő fejléc --}}
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card shadow-sm border-0 mb-4 text-center py-5">
                        <div class="card-body">
                            <div class="mb-4">
                                <i class="fa fa-check-circle fs-30 text-success"></i>
                            </div>

                            @if($customContent && $customContent->title)
                                <h1 class="font-weight-bold mb-3">{{ $customContent->title }}</h1>
                            @else
                                <h1 class="font-weight-bold mb-3">Köszönjük a rendelését!</h1>
                            @endif

                            @if($customContent && $customContent->content)
                                <div class="mb-4 fs-18">
                                    {!! $customContent->content !!}
                                </div>
                            @endif

                            <p class="lead mb-0">
                                A rendelés azonosítója:
                                <span class="font-weight-bold text-primary">{{ $order->order_number }}</span>
                            </p>

                            @if(!$customContent || !$customContent->content)
                                <p class="text-muted mb-0 mt-3">Hamarosan felvesszük Önnel a kapcsolatot a megadott elérhetőségeken.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Részletek --}}
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    @include('site.webshop.checkout.partials.order-overview')
                    @include('site.webshop.checkout.partials.order-addresses')
                    @include('site.webshop.checkout.partials.order-items')
                    @include('site.webshop.checkout.partials.order-extras')

                    <div class="text-center">
                        <a href="{{ route('site.webshop.categories.index') }}" class="btn btn-primary btn-lg px-5 font-weight-bold">
                            Vissza a webshophoz
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
        @php($__scripts = \Weboldalnet\WebshopAiDefault\Models\WebshopTrackingScript::byPage('thank_you')->active()->ordered()->get())
        @foreach($__scripts as $__s)
            {!! $__s->script !!}
        @endforeach
    @endpush
@endsection

@extends('site.layouts.layout')
@section('title', 'Sikeres rendelés')

@section('content')
    <div class="ws-page-container ws-checkout-success">
        <div class="container-xl container-fluid pb-5 mt-5 text-center">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0 py-5">
                        <div class="card-body">
                            <div class="mb-4">
                                <i class="fa fa-check-circle fa-5x text-success"></i>
                            </div>
                            <h1 class="font-weight-bold mb-3">Köszönjük a rendelését!</h1>
                            <p class="lead mb-4">A rendelés azonosítója: <span class="font-weight-bold text-primary">{{ $order->order_number }}</span></p>
                            <p class="text-muted mb-5">Hamarosan felvesszük Önnel a kapcsolatot a megadott elérhetőségeken.</p>
                            
                            <a href="{{ route('site.webshop.categories.index') }}" class="btn btn-primary btn-lg px-5 font-weight-bold">
                                Vissza a főoldalra
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

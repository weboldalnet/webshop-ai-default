@extends('site.layouts.layout')
@section('title', 'Sikeres rendelés')

@section('content')
    <div class="ws-page-container ws-checkout-success">
        <div class="container-xl container-fluid pb-5 mt-5 text-center">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow-sm border border-grey py-5">
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

                            <p class="lead mb-4">A rendelés azonosítója: <span class="font-weight-bold text-primary">{{ $order->order_number }}</span></p>

                            @if(!$customContent || !$customContent->content)
                                <p class="text-muted mb-5">Hamarosan felvesszük Önnel a kapcsolatot a megadott elérhetőségeken.</p>
                            @endif

                            <div class="mt-4 mb-5 text-left mx-auto" style="max-width:800px;">
                                <h4 class="font-weight-bold border-bottom pb-2 mb-3">Termékek</h4>
                                <div class="table-responsive">
                                    <table class="table ">
                                        <thead>
                                        <tr>
                                            <th></th>
                                            <th>Termék</th>
                                            @if($showPrices && $order->total_price > 0)
                                                <th class="text-right">Egységár</th>
                                            @endif
                                            <th class="text-center">Mennyiség</th>
                                            @if($showPrices && $order->total_price > 0)
                                                <th class="text-right">Sorösszeg</th>
                                            @endif
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($order->items as $item)
                                            <tr>
                                                <td class="py-1" style="width: 100px;">
                                                    @if($item->product && $item->product->primary_image_thumb)
                                                        <img src="{{ $item->product->primary_image_thumb }}" class=""
                                                             style=" width: 80px;">
                                                    @endif
                                                </td>
                                                <td class="align-middle">
                                                    <span class="fw-600">{{ $item->product_name }}</span>
                                                    @if($item['sec_name'])
                                                        @if(($ws['site_product_prices_visible'] ?? 'true') === 'false') <br> @endif
                                                        <span class="font-weight-light fs-14">{{ $item['sec_name'] }}</span>
                                                    @endif
                                                </td>
                                                @if($showPrices && $order->total_price > 0)
                                                    <td class="text-right align-middle ws-nowrap">{{ number_format($item->unit_price, 0, '.', ' ') }} {{ $order->currency }}</td>
                                                @endif
                                                <td class="text-center align-middle">{{ $item->quantity }} db</td>
                                                @if($showPrices && $order->total_price > 0)
                                                    <td class="text-right align-middle ws-nowrap fw-600">{{ number_format($item->total_price, 0, '.', ' ') }} {{ $order->currency }}</td>
                                                @endif
                                            </tr>
                                        @endforeach
                                        </tbody>
                                        @if($showPrices && $order->total_price > 0)
                                            <tfoot class="">
                                            <tr class="font-weight-bold fs-18 d-none d-lg-table-row">
                                                <td colspan="{{ $showPrices ? 4 : 3 }}" class="text-right">Összesen:</td>
                                                <td class="text-right text-primary ws-nowrap">{{ number_format($order->total_price, 0, '.', ' ') }} {{ $order->currency }}</td>
                                            </tr>
                                            </tfoot>
                                        @endif
                                    </table>
                                </div>

                                @if($showPrices && $order->total_price > 0)
                                    <div class="font-weight-bold fs-20 mt-3 d-lg-none d-block">
                                        <span>Összesen: </span> <span class="text-primary ws-nowrap">{{ number_format($order->total_price, 0, '.', ' ') }} {{ $order->currency }}</span>
                                    </div>
                                @endif
                            </div>

                            <a href="{{ route('site.webshop.categories.index') }}" class="btn btn-primary btn-lg px-5 font-weight-bold">
                                Vissza a főoldalra
                            </a>
                        </div>
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

@extends('site.layouts.layout')
@section('title', __('webshop::withdrawal.title'))

@section('content')
    @include('site.webshop.partials.sticky-categories')

    <div class="ws-page-container ws-withdrawal-page">
        <div class="container-xl container-fluid pb-5 mt-5">
            <div class="row justify-content-center">
                <div class="col-lg-9">

                    @if(session('withdrawal_success'))
                        <div class="alert alert-success" role="alert">
                            <h5 class="font-weight-bold mb-1"><i class="fa fa-check-circle mr-2"></i>{{ __('webshop::withdrawal.form.success_title') }}</h5>
                            {{ session('withdrawal_success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <ul class="mb-0 pl-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <h1 class="h3 font-weight-bold mb-1">{{ __('webshop::withdrawal.title') }}</h1>
                    <p class="text-muted">
                        {{ __('webshop::common.order_number') }}: <strong>{{ $order->order_number }}</strong>
                        @if($order->created_at)
                            &middot; {{ WsFormat::date($order->created_at) }}
                        @endif
                    </p>

                    {{-- Ha minden tételről beérkezett már nyilatkozat (pl. közvetlenül a beküldés után),
                         nincs mit kiválasztani – űrlap helyett csak a sikerüzenet marad. --}}
                    @if(array_sum($remaining) > 0)
                    {{-- A beküldés aláírt URL-re megy (a controller ellenőrzi) – a sima route() itt
                         nem működne, mert az elállási oldal csak aláírt linkkel érhető el. --}}
                    <form action="{{ $storeUrl }}"
                          method="POST" class="mt-4">
                        @csrf

                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white font-weight-bold">
                                {{ __('webshop::withdrawal.form.products_title') }}
                            </div>
                            <div class="card-body">
                                <p class="text-muted small">
                                    {{ __('webshop::withdrawal.form.products_hint') }}
                                </p>

                                @foreach($order->items as $item)
                                    @php $max = $remaining[$item->id] ?? 0; @endphp
                                    <div class="d-flex align-items-center justify-content-between border-bottom py-2 @if($max < 1) text-muted @endif">
                                        <div class="pr-3">
                                            <span class="font-weight-600">{{ $item->product_name }}</span>
                                            <br>
                                            <small class="text-muted">
                                                {{ __('webshop::withdrawal.form.ordered_quantity', ['count' => $item->quantity]) }}
                                                @if($showPrices)
                                                    &middot; {{ __('webshop::withdrawal.form.unit_price', ['price' => WsFormat::money($item->unit_price)]) }}
                                                @endif
                                                @if($max < $item->quantity)
                                                    {{-- Korábbi kérelem miatt kevesebb maradt --}}
                                                    <br><span class="text-danger">{{ __('webshop::withdrawal.form.already_withdrawn', ['count' => $item->quantity - $max]) }}</span>
                                                @endif
                                            </small>
                                        </div>
                                        <div style="min-width: 120px;">
                                            @if($max > 0)
                                                <select name="quantities[{{ $item->id }}]" class="form-control form-control-sm">
                                                    @for($i = $max; $i >= 0; $i--)
                                                        <option value="{{ $i }}"
                                                                @if((int) old('quantities.' . $item->id, $max) === $i) selected @endif>
                                                            {{ $i }} {{ __('webshop::common.pcs') }}
                                                        </option>
                                                    @endfor
                                                </select>
                                            @else
                                                <span class="badge badge-secondary">{{ __('webshop::withdrawal.form.not_withdrawable') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Indoklás NEM kérhető kötelezően: a fogyasztó 14 napon belül indokolás nélkül
                             elállhat (45/2014. Korm. rendelet). A mező csak lehetőség. --}}
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white font-weight-bold">
                                {{ __('webshop::common.note') }} <span class="font-weight-normal text-muted">{{ __('webshop::withdrawal.form.optional') }}</span>
                            </div>
                            <div class="card-body">
                                <div class="form-group mb-0">
                                    <label for="ws-withdrawal-reason" class="sr-only">{{ __('webshop::common.note') }}</label>
                                    <textarea name="reason" id="ws-withdrawal-reason" rows="4"
                                              class="form-control @error('reason') is-invalid @enderror"
                                              placeholder="{{ __('webshop::withdrawal.form.reason_placeholder') }}">{{ old('reason') }}</textarea>
                                    @error('reason')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        {{ __('webshop::withdrawal.form.reason_help') }}
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="text-center">
                            {{-- Megerősítő lépés: az elállás egyoldalú nyilatkozat, nem kérelem. --}}
                            <button type="submit" class="btn btn-primary btn-lg font-weight-bold px-5">
                                {{ __('webshop::withdrawal.form.submit') }} <i class="fa fa-check ml-2"></i>
                            </button>
                            <p class="text-muted small mt-2 mb-0">
                                {!! __('webshop::withdrawal.form.confirmation_info', ['email' => '<strong>' . e($order->customer_email) . '</strong>']) !!}
                            </p>
                        </div>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

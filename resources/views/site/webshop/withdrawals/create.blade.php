@extends('site.layouts.layout')
@section('title', 'Elállás a vásárlástól')

@section('content')
    @include('site.webshop.partials.sticky-categories')

    <div class="ws-page-container ws-withdrawal-page">
        <div class="container-xl container-fluid pb-5 mt-5">
            <div class="row justify-content-center">
                <div class="col-lg-9">

                    @if(session('withdrawal_success'))
                        <div class="alert alert-success" role="alert">
                            <h5 class="font-weight-bold mb-1"><i class="fa fa-check-circle mr-2"></i>Elállási nyilatkozat elküldve</h5>
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

                    <h1 class="h3 font-weight-bold mb-1">Elállás a vásárlástól</h1>
                    <p class="text-muted">
                        Rendelésszám: <strong>{{ $order->order_number }}</strong>
                        @if($order->created_at)
                            &middot; {{ $order->created_at->format('Y.m.d.') }}
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
                                Mely termékektől szeretnél elállni?
                            </div>
                            <div class="card-body">
                                <p class="text-muted small">
                                    Alapértelmezetten a teljes rendeléstől állsz el. Ha csak egyes termékektől
                                    szeretnél, állítsd át a mennyiséget – a 0 azt jelenti, hogy azt a terméket megtartod.
                                </p>

                                @foreach($order->items as $item)
                                    @php $max = $remaining[$item->id] ?? 0; @endphp
                                    <div class="d-flex align-items-center justify-content-between border-bottom py-2 @if($max < 1) text-muted @endif">
                                        <div class="pr-3">
                                            <span class="font-weight-600">{{ $item->product_name }}</span>
                                            <br>
                                            <small class="text-muted">
                                                Rendelt mennyiség: {{ $item->quantity }} db
                                                @if($showPrices)
                                                    &middot; {{ number_format($item->unit_price, 0, ',', ' ') }} Ft / db
                                                @endif
                                                @if($max < $item->quantity)
                                                    {{-- Korábbi kérelem miatt kevesebb maradt --}}
                                                    <br><span class="text-danger">Már elállt belőle: {{ $item->quantity - $max }} db</span>
                                                @endif
                                            </small>
                                        </div>
                                        <div style="min-width: 120px;">
                                            @if($max > 0)
                                                <select name="quantities[{{ $item->id }}]" class="form-control form-control-sm">
                                                    @for($i = $max; $i >= 0; $i--)
                                                        <option value="{{ $i }}"
                                                                @if((int) old('quantities.' . $item->id, $max) === $i) selected @endif>
                                                            {{ $i }} db
                                                        </option>
                                                    @endfor
                                                </select>
                                            @else
                                                <span class="badge badge-secondary">Nem elállható</span>
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
                                Megjegyzés <span class="font-weight-normal text-muted">(nem kötelező)</span>
                            </div>
                            <div class="card-body">
                                <div class="form-group mb-0">
                                    <label for="ws-withdrawal-reason" class="sr-only">Megjegyzés</label>
                                    <textarea name="reason" id="ws-withdrawal-reason" rows="4"
                                              class="form-control @error('reason') is-invalid @enderror"
                                              placeholder="Ha szeretnéd, megírhatod, miért állsz el – ez nem feltétele az elállásnak.">{{ old('reason') }}</textarea>
                                    @error('reason')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        A vásárlástól a termék átvételétől számított 14 napon belül indoklás nélkül elállhatsz.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="text-center">
                            {{-- Megerősítő lépés: az elállás egyoldalú nyilatkozat, nem kérelem. --}}
                            <button type="submit" class="btn btn-primary btn-lg font-weight-bold px-5">
                                Elállás megerősítése <i class="fa fa-check ml-2"></i>
                            </button>
                            <p class="text-muted small mt-2 mb-0">
                                A nyilatkozat beérkezéséről – annak tartalmával, dátumával és időpontjával –
                                visszaigazoló e-mailt küldünk a(z) <strong>{{ $order->customer_email }}</strong> címre.
                            </p>
                        </div>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

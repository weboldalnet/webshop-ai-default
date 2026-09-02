@extends('admin.layouts.layout')
@section('title', 'Elállás – ' . $withdrawal->order_number)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="header-box my-2">
                <div>
                    <h1 class="mb-0">Elállási kérelem</h1>
                    <p class="text-muted small mb-0">{{ $withdrawal->order_number }}</p>
                </div>
            </div>

            @include('admin.webshop.partials.alerts')

            <div class="mb-3">
                <a href="{{ route('admin.webshop.withdrawals.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fa fa-arrow-left mr-1"></i> Vissza a listához
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Bal oldal: a rendelés rövid adatlapja --}}
        <div class="col-lg-5">
            <div class="header-box product-info mb-1">
                <span><i class="fa fa-shopping-cart mr-1"></i> Rendelés adatai</span>
                @if($order)
                    <a href="{{ route('admin.webshop.orders.edit', $order) }}" class="btn btn-sm btn-light">
                        <i class="fa fa-external-link-alt mr-1"></i> Megnyitás
                    </a>
                @endif
            </div>
            <div class="content-box bordered mb-3">
                @if($order)
                    <dl class="row mb-2">
                        <dt class="col-5 small text-muted">Rendelésszám:</dt>
                        <dd class="col-7 small font-weight-bold">{{ $order->order_number }}</dd>

                        <dt class="col-5 small text-muted">Dátum:</dt>
                        <dd class="col-7 small">{{ $order->created_at ? $order->created_at->format('Y.m.d H:i') : '—' }}</dd>

                        <dt class="col-5 small text-muted">Státusz:</dt>
                        <dd class="col-7 small">{{ $order->status_label }}</dd>

                        <dt class="col-5 small text-muted">Fizetés:</dt>
                        <dd class="col-7 small">
                            {{ $order->payment_status_label }}
                            @if($order->payment_method)
                                <br><span class="text-muted">{{ \Weboldalnet\WebshopAiDefault\Services\Webshop\Commerce\WebshopCommerceService::getPaymentMethodLabel($order->payment_method) }}</span>
                            @endif
                        </dd>

                        <dt class="col-5 small text-muted">Szállítás:</dt>
                        <dd class="col-7 small">
                            {{ $order->shipping_status_label }}
                            @if($order->shipping_method)
                                <br><span class="text-muted">{{ \Weboldalnet\WebshopAiDefault\Services\Webshop\Commerce\WebshopCommerceService::getShippingMethodLabel($order->shipping_method) }}</span>
                            @endif
                        </dd>

                        <dt class="col-5 small text-muted">Vevő:</dt>
                        <dd class="col-7 small">
                            {{ $order->customer_name }}<br>
                            <span class="text-muted">{{ $order->customer_email }}</span>
                            @if($order->customer_phone)
                                <br><span class="text-muted">{{ $order->customer_phone }}</span>
                            @endif
                        </dd>

                        @if($showPrices)
                            <dt class="col-5 small text-muted">Végösszeg:</dt>
                            <dd class="col-7 small font-weight-bold">{{ number_format($order->total_price, 0, ',', ' ') }} Ft</dd>
                        @endif
                    </dl>

                    <p class="fw-600 small mb-1">Rendelt tételek</p>
                    <table class="table table-sm mb-0">
                        <tbody>
                            @foreach($order->items as $item)
                                <tr>
                                    <td class="small py-1">{{ $item->product_name }}</td>
                                    <td class="small py-1 text-center" style="width: 60px;">{{ $item->quantity }} db</td>
                                    @if($showPrices)
                                        <td class="small py-1 text-right" style="width: 100px;">
                                            {{ number_format($item->total_price, 0, ',', ' ') }} Ft
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-muted mb-0">
                        A kérelemhez tartozó rendelés már nem található (törölve lett).
                        A kérelem adatai alább változatlanul megmaradtak.
                    </p>
                @endif
            </div>
        </div>

        {{-- Jobb oldal: az elállási kérelem --}}
        <div class="col-lg-7">
            <div class="header-box product-info mb-1">
                <span><i class="fa fa-undo mr-1"></i> Elállási kérelem</span>
                <span class="badge badge-{{ $withdrawal->status_badge }} px-2 py-1">{{ $withdrawal->status_label }}</span>
            </div>
            <div class="content-box bordered mb-3">
                <dl class="row mb-3">
                    <dt class="col-4 small text-muted">Beérkezett:</dt>
                    <dd class="col-8 small">{{ $withdrawal->created_at ? $withdrawal->created_at->format('Y.m.d H:i') : '—' }}</dd>

                    <dt class="col-4 small text-muted">Vevő:</dt>
                    <dd class="col-8 small">
                        {{ $withdrawal->customer_name }}<br>
                        <span class="text-muted">{{ $withdrawal->customer_email }}</span>
                    </dd>

                    <dt class="col-4 small text-muted">Terjedelem:</dt>
                    <dd class="col-8 small">
                        @if($withdrawal->is_full)
                            <span class="badge badge-dark px-2 py-1">Teljes rendelés</span>
                        @else
                            Részleges ({{ $withdrawal->total_quantity }} db)
                        @endif
                    </dd>
                </dl>

                <p class="fw-600 small mb-1">Az elállással érintett termékek</p>
                <table class="table table-sm table-bordered mb-3">
                    <thead>
                        <tr>
                            <th class="small">Termék</th>
                            <th class="small text-center" style="width: 70px;">Mennyiség</th>
                            @if($showPrices)
                                <th class="small text-right" style="width: 110px;">Egységár</th>
                                <th class="small text-right" style="width: 110px;">Összesen</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($withdrawal->items as $item)
                            <tr>
                                <td class="small py-1">{{ $item->product_name }}</td>
                                <td class="small py-1 text-center">{{ $item->quantity }} db</td>
                                @if($showPrices)
                                    <td class="small py-1 text-right">{{ number_format($item->unit_price, 0, ',', ' ') }} Ft</td>
                                    <td class="small py-1 text-right">{{ number_format($item->total_price, 0, ',', ' ') }} Ft</td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                    @if($showPrices)
                        <tfoot>
                            <tr>
                                <td colspan="3" class="small text-right font-weight-bold">Érintett érték összesen:</td>
                                <td class="small text-right font-weight-bold">
                                    {{ number_format($withdrawal->total_amount, 0, ',', ' ') }} Ft
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>

                <p class="fw-600 small mb-1">A vásárló indoklása</p>
                <div class="border rounded p-2 mb-3 bg-light">
                    <p class="mb-0 small ws-pre-line">{{ $withdrawal->reason }}</p>
                </div>

                <form action="{{ route('admin.webshop.withdrawals.update', $withdrawal) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label class="fw-600 small">Státusz</label>
                                <select name="status" class="form-control">
                                    @foreach($statusLabels as $code => $label)
                                        <option value="{{ $code }}" @if($withdrawal->status === $code) selected @endif>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="form-group">
                                <label class="fw-600 small">Belső megjegyzés</label>
                                <textarea name="admin_note" class="form-control" rows="3"
                                          placeholder="Csak az adminban látszik">{{ $withdrawal->admin_note }}</textarea>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary font-weight-bold">
                        <i class="fa fa-save mr-1"></i> Mentés
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="/packages/webshop/admin/css/webshop-admin.css">
@endsection

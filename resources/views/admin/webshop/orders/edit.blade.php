@extends('admin.layouts.layout')
@section('title', 'Rendelés #' . $order->order_number)

@section('content')
    <div class="container mt-3 mb-150">
        @include('admin.webshop.partials.alerts')
        @include('admin.webshop.partials.form-errors')

        <div class="row">
            <div class="col-lg-6 mb-3">
                <h3 class="header-box product-info"><i class="fa fa-shopping-cart"></i> Rendelés #{{ $order->order_number }}</h3>
                <div class="content-box bordered">
                    <form method="POST" action="{{ route('admin.webshop.orders.update', $order) }}">
                        @csrf @method('PUT')

                        <div class="form-group">
                            <label for="status">Státusz <span class="text-danger">*</span></label>
                            <select class="form-control" id="status" name="status" required>
                                @foreach(\Weboldalnet\WebshopAiDefault\Models\WebshopOrder::STATUSES as $key => $label)
                                    <option value="{{ $key }}" @if(old('status', $order->status) === $key) selected @endif>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_completed" name="is_completed"
                                       @if(old('is_completed', $order->is_completed)) checked @endif>
                                <label class="custom-control-label" for="is_completed">Teljesítve</label>
                            </div>
                            @if($order->completed_at)
                                <small class="text-muted">Teljesítés dátuma: {{ $order->completed_at->format('Y.m.d H:i') }}</small>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="admin_note">Admin megjegyzés</label>
                            <textarea class="form-control" id="admin_note" name="admin_note" rows="3">{{ old('admin_note', $order->admin_note) }}</textarea>
                        </div>

                        <div class="text-center mt-3">
                            <button type="submit" class="btn btn-primary fs-18 font-weight-bold"><i class="fa fa-save"></i> Mentés</button>
                            <a href="{{ route('admin.webshop.orders.index') }}" class="btn btn-secondary">Vissza</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lg-6 mb-3">
                <h3 class="header-box product-info">Vevő adatok</h3>
                <div class="content-box bordered">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><td class="font-weight-bold">Név:</td><td>{{ $order->customer_name ?? '-' }}</td></tr>
                        <tr><td class="font-weight-bold">Email:</td><td>{{ $order->customer_email ?? '-' }}</td></tr>
                        <tr><td class="font-weight-bold">Telefon:</td><td>{{ $order->customer_phone ?? '-' }}</td></tr>
                        <tr><td class="font-weight-bold">Összeg:</td><td>{{ number_format($order->total_price, 0, ',', ' ') }} {{ $order->currency }}</td></tr>
                        <tr><td class="font-weight-bold">Létrehozva:</td><td>{{ $order->created_at->format('Y.m.d H:i') }}</td></tr>
                    </table>
                </div>

                <h3 class="header-box product-info mt-3">Rendelési tételek</h3>
                <div class="content-box bordered">
                    @if($order->items->count())
                        <table class="table table-sm">
                            <thead>
                            <tr><th>Termék</th><th>Mennyiség</th><th>Egységár</th><th>Összesen</th></tr>
                            </thead>
                            <tbody>
                            @foreach($order->items as $item)
                                <tr>
                                    <td>{{ $item->product_name }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ number_format($item->unit_price, 0, ',', ' ') }} {{ $order->currency }}</td>
                                    <td>{{ number_format($item->total_price, 0, ',', ' ') }} {{ $order->currency }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted mb-0">Nincs rendelési tétel.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <link rel="stylesheet" href="/packages/webshop/admin/css/webshop-admin.css">
@endsection

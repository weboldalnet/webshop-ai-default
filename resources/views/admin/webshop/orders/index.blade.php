@extends('admin.layouts.layout')
@section('title', 'Rendelések')

@section('content')
    <div class="container mt-lg-4 mt-3 mb-150">
        @include('admin.webshop.partials.alerts')

        <div class="row"><div class="col-lg-12"><h2 class="header-box"><i class="fa fa-shopping-cart"></i> Rendelések</h2></div></div>

        <div class="content-box bordered mb-3">
            <form method="GET" action="{{ route('admin.webshop.orders.index') }}" class="row align-items-end">
                <div class="col-lg-3 col-md-6 mb-2">
                    <label>Keresés</label>
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Rendelésszám, név, email...">
                </div>
                <div class="col-lg-2 col-md-6 mb-2">
                    <label>Státusz</label>
                    <select name="status" class="form-control">
                        <option value="">Mind</option>
                        @foreach(\Weboldalnet\WebshopAiDefault\Models\WebshopOrder::STATUSES as $key => $label)
                            <option value="{{ $key }}" @if(request('status') === $key) selected @endif>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-6 mb-2">
                    <label>Teljesített</label>
                    <select name="is_completed" class="form-control">
                        <option value="">Mind</option>
                        <option value="1" @if(request('is_completed')==='1') selected @endif>Igen</option>
                        <option value="0" @if(request('is_completed')==='0') selected @endif>Nem</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-6 mb-2">
                    <label>Dátumtól</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-lg-2 col-md-6 mb-2">
                    <label>Dátumig</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-lg-1 col-md-12 mb-2">
                    <button type="submit" class="btn btn-dark"><i class="fa fa-search"></i></button>
                </div>
            </form>
        </div>

        <div class="content-box table-responsive">
            <table class="table table-hover">
                <thead class="thead-dark">
                <tr>
                    <th>Rendelésszám</th>
                    <th>Vevő</th>
                    <th>Státusz</th>
                    <th>Összeg</th>
                    <th>Teljesített</th>
                    <th>Dátum</th>
                    <th><i class="fa fa-pen"></i></th>
                </tr>
                </thead>
                <tbody>
                @foreach($orders as $order)
                    <tr>
                        <td class="font-weight-bold">{{ $order->order_number }}</td>
                        <td>{{ $order->customer_name }}<br><small class="text-muted">{{ $order->customer_email }}</small></td>
                        <td><span class="badge badge-{{ $order->status === 'completed' ? 'success' : ($order->status === 'cancelled' ? 'danger' : 'warning') }}">{{ $order->status_label }}</span></td>
                        <td>{{ number_format($order->total_price, 0, ',', ' ') }} {{ $order->currency }}</td>
                        <td>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input js-toggle-completed" id="completed{{ $order->id }}" data-id="{{ $order->id }}" data-url="{{ route('admin.webshop.orders.toggle-completed') }}" @if($order->is_completed) checked @endif>
                                <label class="custom-control-label" for="completed{{ $order->id }}"></label>
                            </div>
                        </td>
                        <td>{{ $order->created_at->format('Y.m.d H:i') }}</td>
                        <td class="ws-nowrap">
                            <a href="{{ route('admin.webshop.orders.edit', $order) }}" class="btn btn-sm btn-primary"><i class="fa fa-pen"></i></a>
                            <button type="button" class="btn btn-sm btn-danger js-delete-btn" data-url="{{ route('admin.webshop.orders.destroy', $order) }}"><i class="fa fa-trash-alt"></i></button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @include('admin.webshop.modals.delete-confirm')
    <link rel="stylesheet" href="/packages/webshop/admin/css/webshop-admin.css">
    <script src="/packages/webshop/admin/js/webshop-admin.js"></script>
    <script>
        WebshopAdmin.initToggleCompleted();
        WebshopAdmin.initDeleteConfirm();
    </script>
@endsection

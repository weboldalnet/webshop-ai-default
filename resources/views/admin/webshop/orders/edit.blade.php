@extends('admin.layouts.layout')
@section('title', 'Rendelés #' . $order->order_number)

@section('content')
    <div class="container-fluid mt-lg-4 mt-3 mb-150">
        @include('admin.webshop.partials.alerts')
        @include('admin.webshop.partials.form-errors')

        <div class="row">
            <div class="col-lg-12">
                <h2 class="header-box"><i class="fa fa-shopping-cart"></i> Rendelés #{{ $order->order_number }}</h2>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.webshop.orders.update', $order) }}">
            @csrf @method('PUT')

            <div class="row">
                <!-- Bal oszlop -->
                <div class="col-lg-6">
                    <!-- Rendelés alapadatok -->
                    <h3 class="header-box product-info mb-3">Rendelés alapadatok</h3>
                    <div class="content-box bordered mb-4">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Típus</label>
                                <select name="type" class="form-control">
                                    @foreach(\Weboldalnet\WebshopAiDefault\Models\WebshopOrder::TYPES as $key => $label)
                                        <option value="{{ $key }}" @if(old('type', $order->type) === $key) selected @endif>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Státusz</label>
                                <select name="status" class="form-control">
                                    @foreach(\Weboldalnet\WebshopAiDefault\Models\WebshopOrder::STATUSES as $key => $label)
                                        <option value="{{ $key }}" @if(old('status', $order->status) === $key) selected @endif>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12 form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" name="is_completed" class="custom-control-input" id="isCompleted" value="1" @if(old('is_completed', $order->is_completed)) checked @endif>
                                    <label class="custom-control-label" for="isCompleted">Teljesített</label>
                                </div>
                                @if($order->completed_at)
                                    <small class="text-muted d-block mt-1">Teljesítés dátuma: {{ $order->completed_at->format('Y.m.d H:i') }}</small>
                                @endif
                            </div>
                            <div class="col-md-12 form-group">
                                <label>Admin megjegyzés</label>
                                <textarea name="admin_note" class="form-control" rows="2">{{ old('admin_note', $order->admin_note) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Vevő adatok -->
                    <h3 class="header-box product-info mb-3">Vevő adatok</h3>
                    <div class="content-box bordered mb-4">
                        <div class="row">
                            <div class="col-md-12 form-group">
                                <label>Vevő neve <span class="text-danger">*</span></label>
                                <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name', $order->customer_name) }}" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>E-mail <span class="text-danger">*</span></label>
                                <input type="email" name="customer_email" class="form-control" value="{{ old('customer_email', $order->customer_email) }}" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Telefonszám</label>
                                <input type="text" name="customer_phone" class="form-control" value="{{ old('customer_phone', $order->customer_phone) }}">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Cégnév</label>
                                <input type="text" name="customer_company" class="form-control" value="{{ old('customer_company', $order->customer_company) }}">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Adószám</label>
                                <input type="text" name="customer_tax_number" class="form-control" value="{{ old('customer_tax_number', $order->customer_tax_number) }}">
                            </div>
                            <div class="col-md-12 form-group">
                                <label>Vevő megjegyzése</label>
                                <textarea name="note" class="form-control" rows="2">{{ old('note', $order->note) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Jobb oszlop -->
                <div class="col-lg-6">
                    <!-- Számlázási adatok -->
                    <h3 class="header-box product-info mb-3">Számlázási adatok</h3>
                    <div class="content-box bordered mb-4">
                        <div class="row">
                            <div class="col-md-12 form-group">
                                <label>Számlázási név</label>
                                <input type="text" name="billing[name]" class="form-control" value="{{ old('billing.name', $billingData['name'] ?? '') }}">
                            </div>
                            <div class="col-md-3 form-group">
                                <label>Irsz.</label>
                                <input type="text" name="billing[zip]" class="form-control" value="{{ old('billing.zip', $billingData['zip'] ?? '') }}">
                            </div>
                            <div class="col-md-9 form-group">
                                <label>Város</label>
                                <input type="text" name="billing[city]" class="form-control" value="{{ old('billing.city', $billingData['city'] ?? '') }}">
                            </div>
                            <div class="col-md-12 form-group">
                                <label>Utca, házszám</label>
                                <input type="text" name="billing[address]" class="form-control" value="{{ old('billing.address', $billingData['address'] ?? '') }}">
                            </div>
                        </div>
                    </div>

                    <!-- Szállítási adatok -->
                    <h3 class="header-box product-info mb-3">Szállítási adatok</h3>
                    <div class="content-box bordered mb-4">
                        <div class="row">
                            <div class="col-md-3 form-group">
                                <label>Irsz.</label>
                                <input type="text" name="shipping[zip]" class="form-control" value="{{ old('shipping.zip', $shippingData['zip'] ?? '') }}">
                            </div>
                            <div class="col-md-9 form-group">
                                <label>Város</label>
                                <input type="text" name="shipping[city]" class="form-control" value="{{ old('shipping.city', $shippingData['city'] ?? '') }}">
                            </div>
                            <div class="col-md-12 form-group">
                                <label>Utca, házszám</label>
                                <input type="text" name="shipping[address]" class="form-control" value="{{ old('shipping.address', $shippingData['address'] ?? '') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rendelési tételek (Read-only) -->
            <h3 class="header-box product-info mb-3">Rendelési tételek</h3>
            <div class="content-box bordered mb-4">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="thead-light">
                            <tr>
                                <th>Termék</th>
                                <th style="width: 120px;">Darabszám</th>
                                @if($pricesVisible)
                                    <th style="width: 180px;">Egységár</th>
                                    <th style="width: 180px;">Összesen</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                <tr>
                                    <td>{{ $item->product_name }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    @if($pricesVisible)
                                        <td>{{ hufFormat($item->unit_price) }}</td>
                                        <td class="font-weight-bold">{{ hufFormat($item->total_price) }}</td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                        @if($pricesVisible)
                            <tfoot>
                                <tr class="bg-light">
                                    <th colspan="3" class="text-right h5 font-weight-bold">Mindösszesen:</th>
                                    <th class="h5 font-weight-bold text-primary">{{ hufFormat($order->total_price) }}</th>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            <div class="d-flex justify-content-between mb-5">
                <a href="{{ route('admin.webshop.orders.index') }}" class="btn btn-secondary">
                    <i class="fa fa-arrow-left mr-1"></i> Vissza a listához
                </a>
                <button type="submit" class="btn btn-primary px-5 font-weight-bold shadow">
                    <i class="fa fa-save mr-1"></i> Módosítások mentése
                </button>
            </div>
        </form>
    </div>
    <link rel="stylesheet" href="/packages/webshop/admin/css/webshop-admin.css">
@endsection

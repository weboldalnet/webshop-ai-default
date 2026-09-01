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

            <!-- Commerce blokkok (Fizetés / Számla / Szállítás) -->
            @if($order->payment_method || $order->payment_status !== 'unpaid' || $order->shipping_method)
            <div class="row mt-2">
                <!-- Fizetés blokk -->
                <div class="col-lg-4 mb-4">
                    <h3 class="header-box product-info mb-3"><i class="fa fa-credit-card mr-1"></i> Fizetés</h3>
                    <div class="content-box bordered h-100">
                        <dl class="row mb-1">
                            @if($order->payment_method)
                                <dt class="col-6 small text-muted">Fizetési mód:</dt>
                                <dd class="col-6 small">{{ \Weboldalnet\WebshopAiDefault\Services\Webshop\Commerce\WebshopCommerceService::getPaymentMethodLabel($order->payment_method) }}</dd>
                            @endif
                            <dt class="col-6 small text-muted">Fizetési státusz:</dt>
                            <dd class="col-6 small">
                                @php $ps = $order->payment_status; @endphp
                                <span class="badge badge-{{ $ps === 'paid' ? 'success' : ($ps === 'failed' ? 'danger' : ($ps === 'cancelled' ? 'warning' : 'secondary')) }}">
                                    {{ $order->payment_status_label }}
                                </span>
                            </dd>
                            @if($order->paid_at)
                                <dt class="col-6 small text-muted">Fizetve:</dt>
                                <dd class="col-6 small">{{ $order->paid_at->format('Y.m.d H:i') }}</dd>
                            @endif
                            @if($order->commerce_payment_transaction_id)
                                <dt class="col-6 small text-muted">Tranzakció ID:</dt>
                                <dd class="col-6 small">#{{ $order->commerce_payment_transaction_id }}</dd>
                            @endif
                        </dl>
                        {{-- Az űrlap a fő szerkesztő űrlapon KÍVÜL van (lásd az oldal alján),
                             mert az egymásba ágyazott form érvénytelen HTML. --}}
                        @if(in_array($order->payment_status, ['unpaid', 'pending', 'failed']))
                            <button type="button" class="btn btn-sm btn-success w-100 mt-2 js-confirm-action"
                                    data-confirm-form="markPaidForm"
                                    data-confirm-title="Fizetettre állítás"
                                    data-confirm-text="Biztosan manuálisan fizetettre állítod ezt a rendelést?">
                                <i class="fa fa-check mr-1"></i> Manuálisan fizetve
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Számla blokk -->
                <div class="col-lg-4 mb-4">
                    <h3 class="header-box product-info mb-3"><i class="fa fa-file-invoice mr-1"></i> Számla</h3>
                    <div class="content-box bordered h-100">
                        <dl class="row mb-1">
                            <dt class="col-6 small text-muted">Számla státusz:</dt>
                            <dd class="col-6 small">
                                @php $is = $order->invoice_status; @endphp
                                <span class="badge badge-{{ $is === 'invoiced' ? 'success' : ($is === 'failed' ? 'danger' : 'secondary') }}">
                                    {{ $order->invoice_status_label }}
                                </span>
                            </dd>
                            @if($order->invoiceDocument)
                                @php $inv = $order->invoiceDocument; @endphp
                                @if($inv->invoice_number)
                                    <dt class="col-6 small text-muted">Számlaszám:</dt>
                                    <dd class="col-6 small font-weight-bold">{{ $inv->invoice_number }}</dd>
                                @endif
                                <dt class="col-6 small text-muted">Kiállítva:</dt>
                                <dd class="col-6 small">{{ $inv->issued_at ? $inv->issued_at->format('Y.m.d H:i') : '—' }}</dd>
                                @if($inv->pdf_path)
                                    <dt class="col-6 small text-muted">Letöltés:</dt>
                                    <dd class="col-6 small">
                                        <a href="{{ route('admin.webshop.invoices.download', $inv->id) }}" class="btn btn-xs btn-outline-danger px-2 py-0">
                                            <i class="fa fa-file-pdf"></i> PDF
                                        </a>
                                    </dd>
                                @endif
                                @if($inv->error_message)
                                    <dt class="col-12 small text-danger mt-1">Hibaüzenet:</dt>
                                    <dd class="col-12 small text-danger font-italic">{{ $inv->error_message }}</dd>
                                @endif
                            @elseif($order->invoiced_at)
                                <dt class="col-6 small text-muted">Számlázva:</dt>
                                <dd class="col-6 small">{{ $order->invoiced_at->format('Y.m.d H:i') }}</dd>
                            @endif
                        </dl>
                        @if(in_array($order->invoice_status, ['not_required', 'failed', 'pending']))
                            <button type="button" class="btn btn-sm btn-outline-primary w-100 mt-2 js-confirm-action"
                                    data-confirm-form="createInvoiceForm"
                                    data-confirm-title="Számla készítése"
                                    data-confirm-text="A Számlázz.hu-n valódi, éles számla kerül kiállításra ehhez a rendeléshez. Biztosan folytatod?">
                                <i class="fa fa-file-invoice mr-1"></i> Számla készítése
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Szállítás blokk -->
                <div class="col-lg-4 mb-4">
                    <h3 class="header-box product-info mb-3"><i class="fa fa-truck mr-1"></i> Szállítás</h3>
                    <div class="content-box bordered h-100">
                        <dl class="row mb-1">
                            @if($order->shipping_method)
                                <dt class="col-6 small text-muted">Szállítási mód:</dt>
                                <dd class="col-6 small">{{ \Weboldalnet\WebshopAiDefault\Services\Webshop\Commerce\WebshopCommerceService::getShippingMethodLabel($order->shipping_method) }}</dd>
                            @endif
                            <dt class="col-6 small text-muted">Szállítás státusz:</dt>
                            <dd class="col-6 small">
                                @php $ss = $order->shipping_status; @endphp
                                <span class="badge badge-{{ in_array($ss, ['shipped','delivered']) ? 'success' : ($ss === 'failed' ? 'danger' : 'secondary') }}">
                                    {{ $order->shipping_status_label }}
                                </span>
                            </dd>
                            @if($order->shipped_at)
                                <dt class="col-6 small text-muted">Kiszállítva:</dt>
                                <dd class="col-6 small">{{ $order->shipped_at->format('Y.m.d H:i') }}</dd>
                            @endif
                            @if($order->commerce_shipment_id)
                                <dt class="col-6 small text-muted">Szállítmány ID:</dt>
                                <dd class="col-6 small">#{{ $order->commerce_shipment_id }}</dd>
                            @endif
                        </dl>
                        @if(in_array($order->shipping_status, ['not_required', 'pending', 'failed']))
                            <button type="button" class="btn btn-sm btn-outline-info w-100 mt-2 js-confirm-action"
                                    data-confirm-form="createShipmentForm"
                                    data-confirm-title="Szállítmány létrehozása"
                                    data-confirm-text="Biztosan létrehozod a szállítmányt ehhez a rendeléshez?">
                                <i class="fa fa-shipping-fast mr-1"></i> Szállítmány létrehozása
                            </button>
                        @endif
                    </div>
                </div>
            </div>
            @endif

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

        {{-- Commerce műveletek űrlapjai – szándékosan a fő szerkesztő űrlapon KÍVÜL,
             mert az egymásba ágyazott <form> érvénytelen HTML: a böngésző az első
             </form>-nál lezárja a külsőt, amitől a mentés gomb és a gombok elrontódnak. --}}
        @if(in_array($order->payment_status, ['unpaid', 'pending', 'failed']))
            <form id="markPaidForm" method="POST" action="{{ route('admin.webshop.orders.mark-paid', $order) }}" class="d-none">
                @csrf @method('PATCH')
            </form>
        @endif
        @if(in_array($order->invoice_status, ['not_required', 'failed', 'pending']))
            <form id="createInvoiceForm" method="POST" action="{{ route('admin.webshop.orders.create-invoice', $order) }}" class="d-none">
                @csrf
            </form>
        @endif
        @if(in_array($order->shipping_status, ['not_required', 'pending', 'failed']))
            <form id="createShipmentForm" method="POST" action="{{ route('admin.webshop.orders.create-shipment', $order) }}" class="d-none">
                @csrf
            </form>
        @endif
    </div>

    @include('admin.webshop.modals.action-confirm')

    <link rel="stylesheet" href="/packages/webshop/admin/css/webshop-admin.css">
@endsection

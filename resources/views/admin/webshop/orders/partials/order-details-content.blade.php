<div class="modal-header">
    <h5 class="modal-title">Rendelés részletei: {{ $order->order_number }}</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body">
    <div class="row">
        <!-- Alapadatok -->
        <div class="col-md-6 mb-3">
            <h6 class="font-weight-bold border-bottom pb-2">Rendelés adatai</h6>
            <table class="table table-sm table-borderless mb-0 table-text-left">
                <tr>
                    <td class="text-muted" style="width: 40%;">Dátum:</td>
                    <td>{{ $order->created_at->format('Y.m.d H:i') }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Típus:</td>
                    <td>{{ $order->type_label }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Státusz:</td>
                    <td><span class="badge badge-{{ $order->status === 'completed' ? 'success' : ($order->status === 'cancelled' ? 'danger' : 'warning') }}">{{ $order->status_label }}</span></td>
                </tr>
                @if($order->is_completed)
                    <tr>
                        <td class="text-muted">Teljesítve:</td>
                        <td>{{ $order->completed_at ? $order->completed_at->format('Y.m.d H:i') : 'Igen' }}</td>
                    </tr>
                @endif
            </table>
        </div>

        <!-- Vevő adatok -->
        <div class="col-md-6 mb-3">
            <h6 class="font-weight-bold border-bottom pb-2">Vevő adatai</h6>
            <table class="table table-sm table-borderless mb-0 table-text-left">
                <tr>
                    <td class="text-muted" style="width: 40%;">Név:</td>
                    <td>{{ $order->customer_name }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Email:</td>
                    <td><a href="mailto:{{ $order->customer_email }}">{{ $order->customer_email }}</a></td>
                </tr>
                @if($order->customer_phone)
                    <tr>
                        <td class="text-muted">Telefon:</td>
                        <td>{{ $order->customer_phone }}</td>
                    </tr>
                @endif
                @if($order->customer_company)
                    <tr>
                        <td class="text-muted">Cégnév:</td>
                        <td>{{ $order->customer_company }}</td>
                    </tr>
                @endif
                @if($order->customer_tax_number)
                    <tr>
                        <td class="text-muted">Adószám:</td>
                        <td>{{ $order->customer_tax_number }}</td>
                    </tr>
                @endif
            </table>
        </div>
    </div>

    <div class="row">
        <!-- Számlázási adatok -->
        <div class="col-md-6 mb-3">
            <h6 class="font-weight-bold border-bottom pb-2">Számlázási adatok</h6>
            @if($billingData)
                <ul class="list-unstyled mb-0">
                    <li>{{ $billingData['name'] ?? $order->customer_name }}</li>
                    <li>{{ $billingData['zip'] ?? '' }} {{ $billingData['city'] ?? '' }}</li>
                    <li>{{ $billingData['address'] ?? '' }}</li>
                    @if(!empty($billingData['tax_number']))
                        <li>Adószám: {{ $billingData['tax_number'] }}</li>
                    @endif
                </ul>
            @else
                <ul class="list-unstyled mb-0">
                    <li>{{ $order->customer_name }}</li>
                    @if($order->customer_company)
                        <li>{{ $order->customer_company }}</li>
                    @endif
                    <li class="text-muted italic">Nincs részletes számlázási cím megadva</li>
                </ul>
            @endif
        </div>

        <!-- Szállítási adatok -->
        <div class="col-md-6 mb-3">
            <h6 class="font-weight-bold border-bottom pb-2">Szállítási adatok</h6>
            @if($shippingData)
                <ul class="list-unstyled mb-0">
                    <li>{{ $shippingData['name'] ?? $order->customer_name }}</li>
                    <li>{{ $shippingData['zip'] ?? '' }} {{ $shippingData['city'] ?? '' }}</li>
                    <li>{{ $shippingData['address'] ?? '' }}</li>
                </ul>
            @else
                <ul class="list-unstyled mb-0">
                    <li>{{ $order->customer_name }}</li>
                    <li class="text-muted italic">Nincs külön szállítási cím megadva</li>
                </ul>
            @endif
        </div>
    </div>

    @if($order->note)
        <div class="row mb-3">
            <div class="col-12">
                <h6 class="font-weight-bold border-bottom pb-2">Vevő megjegyzése</h6>
                <div class="p-2 bg-light border rounded italic">
                    {{ $order->note }}
                </div>
            </div>
        </div>
    @endif

    <!-- Rendelési tételek -->
    <div class="row">
        <div class="col-12">
            <h6 class="font-weight-bold border-bottom pb-2">Rendelési tételek</h6>
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead class="thead-light">
                    <tr>
                        <th>Termék</th>
                        <th class="text-center">Mennyiség</th>
                        @if($pricesVisible)
                            <th class="text-right">Egységár</th>
                            <th class="text-right">Összesen</th>
                        @endif
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td>{{ $item->product_name }}</td>
                            <td class="text-center">{{ $item->quantity }} db</td>
                            @if($pricesVisible)
                                <td class="text-right">{{ hufFormat($item->unit_price) }}</td>
                                <td class="text-right">{{ hufFormat($item->total_price) }}</td>
                            @endif
                        </tr>
                    @endforeach
                    </tbody>
                    @if($pricesVisible)
                        <tfoot>
                        <tr>
                            <th colspan="3" class="text-right">Végösszeg:</th>
                            <th class="text-right text-primary h5">{{ hufFormat($order->total_price) }}</th>
                        </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <!-- Státusz módosítás -->
    <div class="row mt-3">
        <div class="col-12">
            <h6 class="font-weight-bold border-bottom pb-2">Műveletek</h6>
            <form action="{{ route('admin.webshop.orders.update-status', $order) }}" method="POST" class="js-order-status-form">
                @csrf
                @method('PATCH')
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>Státusz</label>
                        <select name="status" class="form-control">
                            @foreach(\Weboldalnet\WebshopAiDefault\Models\WebshopOrder::STATUSES as $key => $label)
                                <option value="{{ $key }}" {{ $order->status === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Admin megjegyzés</label>
                        <textarea name="admin_note" class="form-control" rows="1">{{ $order->admin_note }}</textarea>
                    </div>
                </div>
                <div class="text-right">
                    <button type="submit" class="btn btn-primary">Mentés</button>
                </div>
            </form>
        </div>
    </div>
</div>

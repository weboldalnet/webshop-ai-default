{{-- A vásárló rendelései az admin vásárló-adatlapján (a commerce-users ezt húzza be @includeIf-fel). --}}
@php
    $wsCustomerOrders = \Weboldalnet\WebshopAiDefault\Models\WebshopOrder::forCommerceUser($commerceUser)
        ->orderByDesc('created_at')->limit(20)->get();
@endphp
<div class="content-box bordered mb-3">
    <h4 class="fw-700 mb-3">Rendelések <span class="text-muted fs-16 fw-400">({{ $wsCustomerOrders->count() }}{{ $wsCustomerOrders->count() === 20 ? '+' : '' }})</span></h4>
    @if($wsCustomerOrders->isEmpty())
        <p class="text-muted mb-0">Még nincs rendelése.</p>
    @else
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead>
                <tr>
                    <th class="text-left">Rendelésszám</th>
                    <th>Dátum</th>
                    <th>Állapot</th>
                    <th class="text-right">Összeg</th>
                </tr>
                </thead>
                <tbody>
                @foreach($wsCustomerOrders as $wsOrder)
                    <tr>
                        <td class="text-left">
                            <a href="{{ route('admin.webshop.orders.edit', $wsOrder) }}" class="fw-600">{{ $wsOrder->order_number }}</a>
                            @unless($wsOrder->commerce_user_id)
                                <span class="d-block text-muted fs-13">vendégként, ezzel az e-mail-címmel</span>
                            @endunless
                        </td>
                        <td>{{ $wsOrder->created_at?->format('Y. m. d.') }}</td>
                        <td>{{ $wsOrder->status_label }}</td>
                        <td class="text-right">{{ hufFormat($wsOrder->total_price) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

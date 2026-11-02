{{-- Rendeléseim — a vásárlói fiók (weboldalnet/commerce-users) menüjében, annak elrendezésében. --}}
@extends('commerce-users::site.layouts.account')
@section('title', 'Rendeléseim')
@section('cu_title', 'Rendeléseim')

@section('cu_content')
    @php
        $wsStatusBadges = [
            'pending' => 'badge-warning', 'processing' => 'badge-info', 'shipped' => 'badge-primary',
            'completed' => 'badge-success', 'cancelled' => 'badge-secondary', 'failed' => 'badge-danger',
        ];
    @endphp

    @if($orders->isEmpty())
        <div class="cu-card">
            <div class="cu-empty">
                Még nincs rendelésed.
                <div class="mt-3"><a href="{{ route('site.webshop.categories.index') }}" class="btn btn-primary">Irány a webshop</a></div>
            </div>
        </div>
    @else
        @foreach($orders as $wsOrder)
            <div class="cu-card ws-account-order">
                <div class="cu-card-title">
                    <h2 class="fs-18">
                        <a href="{{ route('site.webshop.account.orders.show', $wsOrder->id) }}" class="text-reset">#{{ $wsOrder->order_number }}</a>
                    </h2>
                    <span class="badge {{ $wsStatusBadges[$wsOrder->status] ?? 'badge-secondary' }} fs-13">{{ $wsOrder->status_label }}</span>
                </div>
                <div class="d-flex flex-wrap justify-content-between align-items-end" style="gap: 12px 24px">
                    <div>
                        <div class="text-muted fs-14">{{ $wsOrder->created_at?->format('Y. m. d. H:i') }}
                            @if($wsOrder->type === \Weboldalnet\WebshopAiDefault\Models\WebshopOrder::TYPE_QUOTE) · ajánlatkérés @endif
                        </div>
                        <div class="mt-1">
                            {{ $wsOrder->items->take(3)->map(fn ($i) => $i->product_name . ' × ' . $i->quantity)->implode(', ') }}
                            @if($wsOrder->items->count() > 3)
                                <span class="text-muted">és még {{ $wsOrder->items->count() - 3 }} tétel</span>
                            @endif
                        </div>
                        @if($wsOrder->total_price > 0)
                            <div class="mt-1 fw-700">{{ hufFormat($wsOrder->total_price) }}</div>
                        @endif
                    </div>
                    <div class="d-flex flex-wrap" style="gap: 8px">
                        <a href="{{ route('site.webshop.account.orders.show', $wsOrder->id) }}" class="btn btn-sm btn-outline-primary">Részletek</a>
                        @if($reorderEnabled && $wsOrder->items->isNotEmpty())
                            <form method="POST" action="{{ route('site.webshop.account.orders.reorder', $wsOrder->id) }}" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-primary"><i class="fa-solid fa-rotate-right"></i> Újrarendelés</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach

        {{ $orders->links('pagination::bootstrap-4') }}
    @endif
@endsection

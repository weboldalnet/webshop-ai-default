@extends('admin.layouts.layout')
@section('title', 'Szállítmányok')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="header-box my-2">
                <div>
                    <h1 class="mb-0">Szállítmányok</h1>
                    <p class="text-muted small mb-0">Rendelésekhez tartozó futárszolgálati csomagok</p>
                </div>
            </div>

            @include('admin.webshop.partials.alerts')

            <div class="content-box bordered mb-4">
                <form method="GET" action="{{ route('admin.webshop.shipments.index') }}">
                    <div class="row align-items-end">
                        <div class="col-md-4 mb-2">
                            <label class="small fw-600">Keresés (csomagszám, rendelésszám, vevő)</label>
                            <input type="text" name="search" class="form-control"
                                   value="{{ request('search') }}" placeholder="Keresés...">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="small fw-600">Státusz</label>
                            <select name="status" class="form-control">
                                <option value="">Összes státusz</option>
                                @foreach($statusLabels as $code => $label)
                                    <option value="{{ $code }}" @if(request('status') === $code) selected @endif>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="small fw-600">Szállítási mód</label>
                            <select name="provider" class="form-control">
                                <option value="">Összes</option>
                                @foreach($shippingLabels as $code => $label)
                                    <option value="{{ $code }}" @if(request('provider') === $code) selected @endif>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <button type="submit" class="btn btn-primary btn-block font-weight-bold">
                                <i class="fa fa-search mr-1"></i> Szűrés
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Nincs overflow-hidden: az levágná a sorok legördülő menüjét. --}}
            <div class="content-box bordered p-0 mb-3">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Rendelésszám</th>
                            <th>Csomagszám</th>
                            <th>Vevő</th>
                            <th>Szállítási mód</th>
                            <th>Státusz</th>
                            <th>Létrehozva</th>
                            <th class="text-center">Címke</th>
                            <th class="text-right">Műveletek</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shipments as $shipment)
                            <tr>
                                <td class="align-middle">
                                    @if($shipment->order)
                                        <a href="{{ route('admin.webshop.orders.edit', $shipment->order->id) }}" class="font-weight-bold">
                                            {{ $shipment->order->order_number }}
                                        </a>
                                    @else
                                        <span class="text-muted">{{ $shipment->order_id ?: '—' }}</span>
                                    @endif
                                </td>
                                <td class="align-middle">
                                    @if($shipment->tracking_number)
                                        @if($shipment->tracking_url)
                                            <a href="{{ $shipment->tracking_url }}" target="_blank" rel="noopener">
                                                {{ $shipment->tracking_number }}
                                            </a>
                                        @else
                                            {{ $shipment->tracking_number }}
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="align-middle">
                                    {{ $shipment->order->customer_name ?? '—' }}<br>
                                    <small class="text-muted">{{ $shipment->order->customer_email ?? '' }}</small>
                                </td>
                                <td class="align-middle">
                                    {{ $shippingLabels[$shipment->shipping_method] ?? $shipment->shipping_method ?? $shipment->provider }}
                                </td>
                                <td class="align-middle">
                                    @php
                                        $badgeClass = 'secondary';
                                        if (in_array($shipment->status, ['shipped', 'delivered'])) $badgeClass = 'success';
                                        elseif ($shipment->status === 'failed') $badgeClass = 'danger';
                                        elseif ($shipment->status === 'prepared') $badgeClass = 'info';
                                    @endphp
                                    <span class="badge badge-{{ $badgeClass }} px-2 py-1">
                                        {{ $statusLabels[$shipment->status] ?? $shipment->status }}
                                    </span>
                                </td>
                                <td class="align-middle small">
                                    {{ $shipment->created_at ? $shipment->created_at->format('Y.m.d H:i') : '—' }}
                                </td>
                                <td class="align-middle text-center">
                                    @if($shipment->label_path)
                                        <a href="{{ route('admin.webshop.shipments.label', $shipment->id) }}"
                                           class="btn btn-sm btn-outline-danger" title="Címke letöltése">
                                            <i class="fa fa-file-pdf"></i>
                                        </a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="align-middle text-right">
                                    @if($shipment->status === 'failed')
                                        <form action="{{ route('admin.webshop.shipments.retry', $shipment->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="button" class="btn btn-sm btn-outline-primary js-confirm-action"
                                                    data-confirm-title="Szállítmány újrapróbálása"
                                                    data-confirm-text="Újra megpróbáljuk létrehozni a szállítmányt a futárszolgálatnál. Folytatod?">
                                                <i class="fa fa-redo mr-1"></i> Újra
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    Nem található a feltételeknek megfelelő szállítmány.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $shipments->links() }}
            </div>
        </div>
    </div>
</div>

@include('admin.webshop.modals.action-confirm')

<link rel="stylesheet" href="/packages/webshop/admin/css/webshop-admin.css">
@endsection

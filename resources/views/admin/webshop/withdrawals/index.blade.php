@extends('admin.layouts.layout')
@section('title', 'Elállások')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="header-box my-2">
                <div>
                    <h1 class="mb-0">Elállások</h1>
                    <p class="text-muted small mb-0">Vásárlói elállási kérelmek</p>
                </div>
            </div>

            @include('admin.webshop.partials.alerts')

            <div class="content-box bordered mb-3">
                <form method="GET" action="{{ route('admin.webshop.withdrawals.index') }}">
                    <div class="row align-items-end">
                        <div class="col-md-5 mb-2">
                            <label class="small fw-600">Keresés (rendelésszám, vevő, e-mail)</label>
                            <input type="text" name="search" class="form-control"
                                   value="{{ request('search') }}" placeholder="Keresés...">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="small fw-600">Státusz</label>
                            <select name="status" class="form-control">
                                <option value="">Összes státusz</option>
                                @foreach($statusLabels as $code => $label)
                                    <option value="{{ $code }}" @if(request('status') === $code) selected @endif>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <button type="submit" class="btn btn-primary btn-block font-weight-bold">
                                <i class="fa fa-search mr-1"></i> Szűrés
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="content-box bordered p-0 mb-3">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Rendelésszám</th>
                            <th>Vevő</th>
                            <th>Státusz</th>
                            <th>Dátum</th>
                            <th class="text-right">Műveletek</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($withdrawals as $withdrawal)
                            <tr>
                                <td class="align-middle">
                                    @if($withdrawal->order)
                                        <a href="{{ route('admin.webshop.orders.edit', $withdrawal->order_id) }}" class="font-weight-bold">
                                            {{ $withdrawal->order_number }}
                                        </a>
                                    @else
                                        {{-- A rendelést azóta törölhették – a kérelem így is beazonosítható --}}
                                        <span class="font-weight-bold">{{ $withdrawal->order_number }}</span>
                                        <br><small class="text-muted">törölt rendelés</small>
                                    @endif
                                </td>
                                <td class="align-middle">
                                    {{ $withdrawal->customer_name ?: '—' }}<br>
                                    <small class="text-muted">{{ $withdrawal->customer_email }}</small>
                                </td>
                                <td class="align-middle">
                                    <span class="badge badge-{{ $withdrawal->status_badge }} px-2 py-1">
                                        {{ $withdrawal->status_label }}
                                    </span>
                                    @if($withdrawal->is_full)
                                        <br><small class="text-muted">teljes rendelés</small>
                                    @endif
                                </td>
                                <td class="align-middle small">
                                    {{ $withdrawal->created_at ? $withdrawal->created_at->format('Y.m.d H:i') : '—' }}
                                </td>
                                <td class="align-middle text-right">
                                    <a href="{{ route('admin.webshop.withdrawals.show', $withdrawal) }}"
                                       class="btn btn-sm btn-outline-primary" title="Megtekintés">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger js-delete-btn"
                                            data-url="{{ route('admin.webshop.withdrawals.destroy', $withdrawal) }}"
                                            title="Törlés">
                                        <i class="fa fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    Nem található a feltételeknek megfelelő elállási kérelem.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $withdrawals->links() }}
            </div>
        </div>
    </div>
</div>

@include('admin.webshop.modals.delete-confirm')

<link rel="stylesheet" href="/packages/webshop/admin/css/webshop-admin.css">
<script src="/packages/webshop/admin/js/webshop-admin.js"></script>
<script>
    WebshopAdmin.initDeleteConfirm();
</script>
@endsection

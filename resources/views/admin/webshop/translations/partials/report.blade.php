{{-- Egy AI-fordítási futás riportja (WebshopTranslationController@showRun): tételenként az eredmény, visszavonással. --}}
@php
    $wsItemStatus = [
        'pending' => ['Függő', ''],
        'done' => ['Kész', 'is-good'],
        'error' => ['Hiba', 'is-warn'],
        'skipped' => ['Kihagyva', ''],
        'restored' => ['Visszavonva', ''],
    ];
@endphp
<div class="d-flex flex-wrap align-items-center mb-3" style="gap: 6px 10px">
    @include('admin.webshop.translations.partials.run-status', ['status' => $run->status])
    <span class="ws-chip"><i class="fa-regular fa-clock"></i> {{ $run->created_at?->format('Y.m.d. H:i') }}</span>
    <span class="ws-chip">{{ implode(', ', array_map(fn ($l) => $localeNames[$l] ?? strtoupper($l), $run->options['locales'] ?? [])) }}</span>
    <span class="ws-chip">{{ $modes[$run->options['mode'] ?? ''] ?? '' }}</span>
    <span class="ws-chip">{{ $run->done_items }} / {{ $run->total_items }} tétel kész · {{ $run->translated_fields }} mező · {{ $run->ai_calls }} AI-hívás</span>
    @if($run->error_items)
        <span class="ws-chip is-warn">{{ $run->error_items }} hibás tétel</span>
    @endif
</div>
@if($run->error)
    <div class="alert alert-warning py-2 fs-14">{{ $run->error }}</div>
@endif

<div class="table-responsive">
    <table class="table ws-list-table mb-0">
        <thead>
        <tr>
            <th>Tétel</th>
            <th style="width:80px">Nyelv</th>
            <th style="width:120px">Eredmény</th>
            <th style="width:90px">Mezők</th>
            <th style="width:120px"></th>
        </tr>
        </thead>
        <tbody>
        @forelse($items as $wsItem)
            <tr class="js-tr-report-item" data-id="{{ $wsItem['id'] }}">
                <td>
                    <span class="fw-600">{{ $wsItem['label'] }}</span>
                    <div class="text-muted fs-14">{{ $wsItem['type_label'] }}</div>
                    @if($wsItem['message'])
                        <div class="fs-14 text-muted mt-1">{{ $wsItem['message'] }}</div>
                    @endif
                </td>
                <td>{{ strtoupper($wsItem['locale']) }}</td>
                <td>
                    @php($wsStatus = $wsItemStatus[$wsItem['status']] ?? [$wsItem['status'], ''])
                    <span class="ws-chip {{ $wsStatus[1] }} js-tr-item-status">{{ $wsStatus[0] }}</span>
                </td>
                <td>{{ $wsItem['fields_done'] }}@if($wsItem['fields_error']) <span class="text-danger">/ {{ $wsItem['fields_error'] }} hiba</span>@endif</td>
                <td class="text-right text-nowrap">
                    <a href="{{ route('admin.webshop.translations.edit', [$wsItem['type'], $wsItem['record_id'], $wsItem['locale']]) }}" class="btn btn-sm btn-light border" title="Fordítás megnyitása">
                        <i class="fa-solid fa-pen"></i>
                    </a>
                    @if(in_array($wsItem['status'], ['done', 'error'], true) && $wsItem['fields_done'] > 0)
                        <button type="button" class="btn btn-sm btn-light border js-tr-restore-item" title="A tétel visszavonása"
                                data-url="{{ route('admin.webshop.translations.run-items.restore', $wsItem['id']) }}">
                            <i class="fa-solid fa-rotate-left"></i>
                        </button>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted py-3">Nem volt mit fordítani.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<span class="ws-hint">A visszavonás a futás előtti állapotot állítja vissza; azt a mezőt, amit azóta valaki átírt, nem bántja.</span>

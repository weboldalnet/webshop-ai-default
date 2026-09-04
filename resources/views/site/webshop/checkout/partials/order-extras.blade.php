{{--
    Rendelés adatlap / megjegyzés és a pénztárban kitöltött kérdőív.

    A kérdés szövege a rendeléssel együtt lett elmentve, ezért akkor is helyesen
    jelenik meg, ha a kérdőívet azóta átszerkesztették vagy törölték.
--}}
@if($order->note || !empty($order->qa_data['items']))
    <div class="row">
        @if($order->note)
            <div class="{{ !empty($order->qa_data['items']) ? 'col-lg-6' : 'col-12' }} mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-primary text-white font-weight-bold h5">Megjegyzésed</div>
                    <div class="card-body">
                        <p class="mb-0 ws-pre-line">{{ $order->note }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(!empty($order->qa_data['items']))
            <div class="{{ $order->note ? 'col-lg-6' : 'col-12' }} mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-primary text-white font-weight-bold h5">
                        {{ $order->qa_data['qa_name'] ?? 'Kérdőív' }}
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            @foreach($order->qa_data['items'] as $qaItem)
                                <dt class="col-sm-5 py-1">{{ $qaItem['question'] ?? '' }}</dt>
                                <dd class="col-sm-7 py-1 mb-0">
                                    @if(!empty($qaItem['answers']))
                                        {{ implode(', ', $qaItem['answers']) }}
                                    @else
                                        <span class="text-muted font-italic">nincs válasz</span>
                                    @endif
                                </dd>
                            @endforeach
                        </dl>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endif

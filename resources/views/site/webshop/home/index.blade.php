@extends('site.layouts.layout')
@section('title', $metaTitle)

@section('content')
    @include('site.webshop.partials.sticky-categories')

    {{-- A blokkok SZÁNDÉKOSAN nincsenek közös konténerben: mindegyik maga hozza
         a sajátját, hogy a háttérszíne teljes oldalszélességben futhasson. --}}
    <div class="ws-page-container ws-home-index pb-5">
        @if($h1)
            <div class="container-xl container-fluid pt-4">
                <div class="row">
                    <div class="col-lg-12">
                        <h1 class="h2 mb-lg-4 mb-3 font-weight-bold">{{ $h1 }}</h1>
                    </div>
                </div>
            </div>
        @endif

        @foreach($blocks as $entry)
            @include('site.webshop.home.partials.block', [
                'block' => $entry['block'],
                'items' => $entry['items'],
            ])
        @endforeach
    </div>

    @push('scripts')
        <script src="/packages/webshop/site/js/webshop-site.js"></script>
        @php($__scripts = \Weboldalnet\WebshopAiDefault\Models\WebshopTrackingScript::byPage('homepage')->active()->ordered()->get())
        @foreach($__scripts as $__s)
            {!! $__s->script !!}
        @endforeach
    @endpush
@endsection

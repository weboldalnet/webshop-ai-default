{{--
    Átirányítás utáni üzenetek a webshop gyűjtőoldalain (pl. lejárt elállási link,
    nem található rendelés). Ha nincs üzenet, semmit nem renderel.
--}}
@if(session('error') || session('warning') || session('success'))
    <div class="container-xl container-fluid pt-3 ws-flash-messages">
        @foreach(['error' => 'danger', 'warning' => 'warning', 'success' => 'success'] as $wsFlashKey => $wsFlashType)
            @if(session($wsFlashKey))
                <div class="alert alert-{{ $wsFlashType }} alert-dismissible fade show mb-0" role="alert">
                    {{ session($wsFlashKey) }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Bezárás">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
        @endforeach
    </div>
@endif

{{-- Vásárlói fiók a pénztárban (commerce-users): vendégnek belépési ajánlat, bejelentkezve egy sor
     arról, kinek a nevében rendel. Kikapcsolt fiókoknál semmi. --}}
@if($usersEnabled ?? false)
    <div class="row">
        <div class="col-12">
            @if(!empty($commerceUser))
                <div class="alert alert-light border d-flex flex-wrap align-items-center justify-content-between mb-4" style="gap: 8px 16px">
                    <span>
                        <i class="fa-solid fa-user-check mr-1 text-primary"></i>
                        Bejelentkezve: <strong>{{ $commerceUser->full_name }}</strong> ({{ $commerceUser->email }}).
                        Az adataidat a fiókodból töltöttük ki.
                    </span>
                    <a href="{{ route('commerce-users.account') }}" class="font-weight-bold">Fiókom</a>
                </div>
            @else
                <div class="alert alert-info d-flex flex-wrap align-items-center justify-content-between mb-4" style="gap: 8px 16px">
                    <span>
                        <i class="fa-solid fa-user mr-1"></i>
                        Van már fiókod? Jelentkezz be, és kitöltjük helyetted az adataidat és a címeidet.
                    </span>
                    <span class="d-flex flex-wrap" style="gap: 8px">
                        <a href="{{ route('commerce-users.login', ['redirect' => route('site.webshop.checkout.index', [], false)]) }}" class="btn btn-sm btn-primary">Bejelentkezés</a>
                        <a href="{{ route('commerce-users.register') }}" class="btn btn-sm btn-outline-primary">Regisztráció</a>
                    </span>
                </div>
            @endif
        </div>
    </div>
@endif

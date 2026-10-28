{{-- Kapcsoló-sor a webshop beállításaiban: ikon + név (+ súgó) balra, kapcsoló jobbra; az egész sor kattintható.
     Paraméterek: $name, $label, $checked, $icon (al-opciónál nincs), $hint (HTML is lehet — csak saját, fix szöveg),
     $sub (al-opció a csempén belül), $id (alapból a $name), $inputClass, $attrs (nyers attribútumok).
     A kapcsoló képét a Bootstrap .custom-control-label adja — itt span, mert label nem lehet label-ben.
     SOHA nem kap `disabled`-et: a be nem küldött jelölőnégyzetet a mentés kikapcsoltnak venné. --}}
<label class="ws-toggle-row @if(!empty($sub)) is-sub @endif @if($checked) is-on @endif" for="{{ $id ?? $name }}">
    @empty($sub)
        <span class="ws-toggle-icon"><i class="fa-solid {{ $icon ?? 'fa-toggle-on' }}"></i></span>
    @endempty
    <span class="ws-toggle-text">
        <span class="ws-toggle-name">{{ $label }}</span>
        @if(!empty($hint))
            <span class="ws-toggle-hint">{!! $hint !!}</span>
        @endif
    </span>
    <span class="custom-control custom-switch">
        <input type="checkbox"
               class="custom-control-input {{ $inputClass ?? '' }}"
               id="{{ $id ?? $name }}"
               name="{{ $name }}"
               @if($checked) checked @endif
               {!! $attrs ?? '' !!}
        >
        <span class="custom-control-label"></span>
    </span>
</label>

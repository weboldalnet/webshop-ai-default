{{--
    Pénztári értesítő doboz (adminban TinyMCE-vel szerkesztve).

    Paraméterek:
      $noticeContent — a szerkesztőből jövő HTML
      $noticeType    — Bootstrap alert-változat (info, warning, danger, …)
--}}
@php
    $wsNoticeHtml = trim((string) ($noticeContent ?? ''));

    /* A TinyMCE "kiürített" mezőt is <p>&nbsp;</p> alakban küld vissza, ezért a
       nyers hossz nem dönti el, van-e tartalom: a szöveget kell megnézni, a
       csak képet tartalmazó értesítőt viszont nem szabad eldobni. */
    $wsNoticePlain = str_replace(['&nbsp;', "\xc2\xa0"], '', strip_tags($wsNoticeHtml));
    $wsHasNotice = trim($wsNoticePlain) !== '' || str_contains($wsNoticeHtml, '<img');

    /* A típus adatbázisból jön, ezért nem tesszük közvetlenül osztálynévbe:
       csak az ismert alert-változatok engedettek, minden más info lesz. */
    $wsAllowedTypes = ['info', 'success', 'warning', 'danger', 'primary', 'secondary', 'light', 'dark'];
    $wsNoticeType = in_array($noticeType ?? null, $wsAllowedTypes, true) ? $noticeType : 'info';
@endphp

@if($wsHasNotice)
    <div class="alert alert-{{ $wsNoticeType }} ws-checkout-notice mb-4" role="alert">
        {!! $wsNoticeHtml !!}
    </div>
@endif

{{--
    Webshop mérőkódok (admin: Webshop → Extra beállítások → Scriptek) kiírása hozzájárulás szerint.

    A nem feltétlenül szükséges kódok csak a látogató hozzájárulásával futhatnak (ePrivacy, GDPR).
    Ezért a hozzájárulást igénylő kódot inert <template>-be tesszük, és csak akkor aktiváljuk,
    ha a core sütiablakában (wn_cookie_consent süti) a látogató az adott kategóriához hozzájárult –
    visszatérő látogatónál azonnal, új döntésnél a döntés pillanatában. Ha az oldalon nincs
    sütiablak, ezek a kódok nem futnak le (az admin ezt jelzi).

    Paraméter: $pageType – homepage / product_list / product / checkout / thank_you
--}}
@php
    $wsTrackingScripts = \Weboldalnet\WebshopAiDefault\Models\WebshopTrackingScript::byPage($pageType)->active()->ordered()->get();
    $wsTrackingNeedsConsent = $wsTrackingScripts->contains(fn ($script) => $script->requiresConsent());
@endphp

@foreach($wsTrackingScripts as $wsTrackingScript)
    @if($wsTrackingScript->requiresConsent())
        <template data-wn-consent="{{ $wsTrackingScript->consentCategory() }}">{!! $wsTrackingScript->script !!}</template>
    @else
        {!! $wsTrackingScript->script !!}
    @endif
@endforeach

@if($wsTrackingNeedsConsent)
    @once
        <script>
            (function () {
                if (window.wnWebshopConsentScripts) {
                    return;
                }
                window.wnWebshopConsentScripts = true;

                function readConsent() {
                    if (window.wnCookieConsent && typeof window.wnCookieConsent.get === 'function') {
                        return window.wnCookieConsent.get();
                    }
                    // Tartalék: a core süti közvetlen olvasása (ha a sütiablak scriptje nincs az oldalon)
                    var match = document.cookie.match(/(?:^|; )wn_cookie_consent=([^;]*)/);
                    if (!match) {
                        return null;
                    }
                    try { return JSON.parse(decodeURIComponent(match[1])); } catch (e) { return null; }
                }

                function activate(consent) {
                    if (!consent) {
                        return;
                    }
                    document.querySelectorAll('template[data-wn-consent]:not([data-wn-activated])').forEach(function (template) {
                        var category = template.getAttribute('data-wn-consent');
                        if (!consent[category]) {
                            return;
                        }
                        template.setAttribute('data-wn-activated', '1');
                        var fragment = template.content.cloneNode(true);
                        // A sablonból klónozott <script> nem fut le – újra kell létrehozni.
                        fragment.querySelectorAll('script').forEach(function (original) {
                            var script = document.createElement('script');
                            for (var i = 0; i < original.attributes.length; i++) {
                                script.setAttribute(original.attributes[i].name, original.attributes[i].value);
                            }
                            script.text = original.textContent;
                            original.parentNode.replaceChild(script, original);
                        });
                        template.parentNode.insertBefore(fragment, template.nextSibling);
                    });
                }

                // A sütiablak döntéskor a wnCookieConsent.apply()-t hívja – erre csatlakozunk,
                // a core módosítása nélkül.
                if (window.wnCookieConsent && typeof window.wnCookieConsent.apply === 'function') {
                    var originalApply = window.wnCookieConsent.apply;
                    window.wnCookieConsent.apply = function (consent) {
                        var result = originalApply.apply(this, arguments);
                        activate(consent);
                        return result;
                    };
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', function () { activate(readConsent()); });
                } else {
                    activate(readConsent());
                }
            })();
        </script>
    @endonce
@endif

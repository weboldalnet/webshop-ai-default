/**
 * Szabadszavas fejléc-kereső.
 *
 * A nemesmag projekt fejléc-keresőjének mintája: gépelés közben AJAX-szal
 * kér RENDERELT HTML-t, és azt teszi a lenyíló dobozba.
 *
 * Miért KÜLÖN fájl, és nem a webshop-site.js része:
 * a webshop-site.js NEM minden webshop-oldalon töltődik be – oldalanként,
 * kézzel van behúzva. Mérve: a /webshop/checkout/success/{id} és a
 * /webshop/payment/{id}/result oldalon a fejléc (és benne a kereső) renderelődik,
 * de a webshop-site.js nincs betöltve. Ha a keresőt oda tennénk, ezeken az
 * oldalakon némán nem működne. Ha viszont a webshop-site.js-t húznánk be a
 * fejlécbe, a többi aloldalon kétszer töltődne be, és duplán kötné az
 * eseménykezelőit.
 *
 * A STÍLUS ezzel szemben jó helyen van a webshop-site.css-ben: azt a site
 * layout ugyanazzal az @isset($ws) feltétellel tölti be, amivel a fejléc
 * gombsora (és így a kereső markupja) is kirenderelődik.
 *
 * Az osztálynevek ws- előtaggal mennek: ez a csomag több projektben fut, és
 * egy globális #search id ütközne a befogadó projekt saját keresőjével.
 */
(function () {
    'use strict';

    var MIN_LENGTH = 3;
    var DEBOUNCE_MS = 250;

    function bind(container) {
        var input = container.querySelector('.ws-search-input');
        var resultsBox = container.querySelector('.ws-search-results');
        var url = container.getAttribute('data-url');

        if (!input || !resultsBox || !url) {
            return;
        }

        var timer = null;
        var latestRequest = 0;

        function hide() {
            resultsBox.classList.add('d-none');
            resultsBox.innerHTML = '';
        }

        function search() {
            var term = input.value.trim();

            if (term.length < MIN_LENGTH) {
                hide();
                return;
            }

            // Sorszám: a lassabb, korábbi válasz ne írja felül a frissebbet
            var requestNumber = ++latestRequest;

            fetch(url + '?q=' + encodeURIComponent(term), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            })
                .then(function (response) { return response.ok ? response.text() : ''; })
                .then(function (html) {
                    if (requestNumber !== latestRequest) {
                        return;
                    }

                    if (!html) {
                        hide();
                        return;
                    }

                    resultsBox.innerHTML = html;
                    resultsBox.classList.remove('d-none');
                })
                .catch(function () {
                    // Átmeneti hálózati hiba: a következő gépelésnél újrapróbáljuk
                });
        }

        input.addEventListener('input', function () {
            clearTimeout(timer);
            timer = setTimeout(search, DEBOUNCE_MS);
        });

        // Visszatéréskor a korábbi találatok újra megjelenjenek
        input.addEventListener('focus', function () {
            if (input.value.trim().length >= MIN_LENGTH && resultsBox.innerHTML.trim()) {
                resultsBox.classList.remove('d-none');
            }
        });

        // Kattintás a keresőn kívülre bezárja a lenyílót
        document.addEventListener('click', function (e) {
            if (!container.contains(e.target)) {
                resultsBox.classList.add('d-none');
            }
        });

        input.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                resultsBox.classList.add('d-none');
                input.blur();
            }
        });
    }

    /**
     * MINDEN keresőt bekötünk, nem csak az elsőt: egyes elrendezésekben a
     * fejléc külön asztali és mobil változatban is kirenderelődik, és ott egy
     * querySelector() néma módon csak az egyiket élesítené.
     *
     * A már bekötött konténert megjelöljük, hogy kétszer ne fusson – így a
     * fájl akkor sem okoz dupla eseménykezelőt, ha valamiért kétszer töltődne be.
     */
    function init() {
        document.querySelectorAll('.ws-search-container').forEach(function (container) {
            if (container.dataset.wsSearchBound) {
                return;
            }

            container.dataset.wsSearchBound = '1';
            bind(container);
        });
    }

    /*
        A szkript a <head>-ből is betölthető, ilyenkor a fejléc markupja még
        nincs kész. A defer/DOMContentLoaded ág lefedi mindkét esetet.
    */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();

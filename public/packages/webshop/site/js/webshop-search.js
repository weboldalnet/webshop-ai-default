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

    var MIN_HOSSZ = 3;
    var VARAKOZAS_MS = 250;

    function bekot(konteneur) {
        var mezo = konteneur.querySelector('.ws-search-input');
        var doboz = konteneur.querySelector('.ws-search-results');
        var url = konteneur.getAttribute('data-url');

        if (!mezo || !doboz || !url) {
            return;
        }

        var idozito = null;
        var utolsoKeres = 0;

        function elrejt() {
            doboz.classList.add('d-none');
            doboz.innerHTML = '';
        }

        function keres() {
            var kifejezes = mezo.value.trim();

            if (kifejezes.length < MIN_HOSSZ) {
                elrejt();
                return;
            }

            // Sorszám: a lassabb, korábbi válasz ne írja felül a frissebbet
            var sajatSorszam = ++utolsoKeres;

            fetch(url + '?q=' + encodeURIComponent(kifejezes), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            })
                .then(function (valasz) { return valasz.ok ? valasz.text() : ''; })
                .then(function (html) {
                    if (sajatSorszam !== utolsoKeres) {
                        return;
                    }

                    if (!html) {
                        elrejt();
                        return;
                    }

                    doboz.innerHTML = html;
                    doboz.classList.remove('d-none');
                })
                .catch(function () {
                    // Átmeneti hálózati hiba: a következő gépelésnél újrapróbáljuk
                });
        }

        mezo.addEventListener('input', function () {
            clearTimeout(idozito);
            idozito = setTimeout(keres, VARAKOZAS_MS);
        });

        // Visszatéréskor a korábbi találatok újra megjelenjenek
        mezo.addEventListener('focus', function () {
            if (mezo.value.trim().length >= MIN_HOSSZ && doboz.innerHTML.trim()) {
                doboz.classList.remove('d-none');
            }
        });

        // Kattintás a keresőn kívülre bezárja a lenyílót
        document.addEventListener('click', function (e) {
            if (!konteneur.contains(e.target)) {
                doboz.classList.add('d-none');
            }
        });

        mezo.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                doboz.classList.add('d-none');
                mezo.blur();
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
    function inditas() {
        document.querySelectorAll('.ws-search-container').forEach(function (konteneur) {
            if (konteneur.dataset.wsSearchBound) {
                return;
            }

            konteneur.dataset.wsSearchBound = '1';
            bekot(konteneur);
        });
    }

    /*
        A szkript a <head>-ből is betölthető, ilyenkor a fejléc markupja még
        nincs kész. A defer/DOMContentLoaded ág lefedi mindkét esetet.
    */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', inditas);
    } else {
        inditas();
    }
})();

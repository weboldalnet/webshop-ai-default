/**
 * Egyedi kategória menüsor.
 *
 * Asztali nézet: a sáv gombja fölé húzva lenyílik a hozzá tartozó panel.
 * Mobil nézet:   a "Kategóriák" gomb bal oldalt kicsúsztat egy fiókot.
 *
 * Miért KÜLÖN fájl, és nem a webshop-site.js része:
 * a webshop-site.js oldalanként, kézzel van behúzva, és nem minden webshop-oldalon
 * töltődik be (pl. a rendelés-visszaigazolón és a fizetési eredmény oldalon nem) –
 * a menüsor viszont ott is megjelenik, tehát ott néma lenne. Fordítva, ha a
 * webshop-site.js-t húznánk be a fejlécbe, a többi aloldalon kétszer töltődne be.
 *
 * A mobil fiók collapse-ait a Bootstrap kezeli (data-toggle="collapse"), azzal itt
 * nem foglalkozunk.
 */
(function () {
    'use strict';

    /* A becsukás késleltetése: a gomb és a panel között van néhány képpontnyi rés,
       azon áthúzva a menü becsukódna, mielőtt az egér a panelre érne. */
    var BECSUKAS_KESLELTETES_MS = 180;

    var nyitottPanel = null;
    var becsukasIdozito = null;

    function panelNyit(panel) {
        clearTimeout(becsukasIdozito);

        if (nyitottPanel && nyitottPanel !== panel) {
            nyitottPanel.classList.remove('show');
        }

        nyitottPanel = panel;
        panel.classList.add('show');
    }

    function panelZarKesleltetve() {
        clearTimeout(becsukasIdozito);

        becsukasIdozito = setTimeout(function () {
            if (nyitottPanel) {
                nyitottPanel.classList.remove('show');
                nyitottPanel = null;
            }
        }, BECSUKAS_KESLELTETES_MS);
    }

    function asztaliBekot(menu) {
        menu.querySelectorAll('[data-ws-panel]').forEach(function (gomb) {
            var panel = menu.querySelector(gomb.getAttribute('data-ws-panel'));

            if (!panel) {
                return;
            }

            gomb.addEventListener('mouseenter', function () { panelNyit(panel); });
            gomb.addEventListener('mouseleave', panelZarKesleltetve);
            panel.addEventListener('mouseenter', function () { clearTimeout(becsukasIdozito); });
            panel.addEventListener('mouseleave', panelZarKesleltetve);

            /* Billentyűzettel is elérhető legyen: a gombra fókuszálva nyíljon,
               és a panelből kifókuszálva csukódjon. */
            gomb.addEventListener('focus', function () { panelNyit(panel); });
        });

        /* Escape zárja a nyitott panelt */
        document.addEventListener('keydown', function (esemeny) {
            if (esemeny.key === 'Escape' && nyitottPanel) {
                nyitottPanel.classList.remove('show');
                nyitottPanel = null;
            }
        });

        /* Ha a fókusz kikerül a menüsorból, csukjuk be */
        menu.addEventListener('focusout', function (esemeny) {
            if (!menu.contains(esemeny.relatedTarget)) {
                panelZarKesleltetve();
            }
        });
    }

    function mobilBekot() {
        var fiok = document.querySelector('.ws-cat-drawer');

        if (!fiok) {
            return;
        }

        function nyit() {
            fiok.classList.add('show');
            document.body.classList.add('overflow-hidden');
        }

        function zar() {
            fiok.classList.remove('show');
            document.body.classList.remove('overflow-hidden');
        }

        document.querySelectorAll('.js-ws-cat-drawer-open').forEach(function (gomb) {
            gomb.addEventListener('click', nyit);
        });

        document.querySelectorAll('.js-ws-cat-drawer-close').forEach(function (gomb) {
            gomb.addEventListener('click', zar);
        });

        /* Kattintás a dobozon KÍVÜLRE zárja – ugyanaz a minta, mint a szűrő fióknál */
        fiok.addEventListener('click', function (esemeny) {
            if (!esemeny.target.closest('.ws-cat-drawer-box')) {
                zar();
            }
        });

        document.addEventListener('keydown', function (esemeny) {
            if (esemeny.key === 'Escape') {
                zar();
            }
        });
    }

    function inditas() {
        var menu = document.querySelector('.ws-cat-menu');

        if (!menu || menu.dataset.wsCatMenuBound) {
            return;
        }

        menu.dataset.wsCatMenuBound = '1';

        asztaliBekot(menu);
        mobilBekot();
    }

    /* A szkript a <head>-ből is betölthető, ilyenkor a menü markupja még nincs kész. */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', inditas);
    } else {
        inditas();
    }
})();

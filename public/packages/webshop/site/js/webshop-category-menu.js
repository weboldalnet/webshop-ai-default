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
    var CLOSE_DELAY_MS = 180;

    var openedPanel = null;
    var closeTimer = null;

    function openPanel(panel) {
        clearTimeout(closeTimer);

        if (openedPanel && openedPanel !== panel) {
            openedPanel.classList.remove('show');
        }

        openedPanel = panel;
        panel.classList.add('show');
    }

    function closePanelDelayed() {
        clearTimeout(closeTimer);

        closeTimer = setTimeout(function () {
            if (openedPanel) {
                openedPanel.classList.remove('show');
                openedPanel = null;
            }
        }, CLOSE_DELAY_MS);
    }

    function bindDesktop(menu) {
        menu.querySelectorAll('[data-ws-panel]').forEach(function (button) {
            var panel = menu.querySelector(button.getAttribute('data-ws-panel'));

            if (!panel) {
                return;
            }

            button.addEventListener('mouseenter', function () { openPanel(panel); });
            button.addEventListener('mouseleave', closePanelDelayed);
            panel.addEventListener('mouseenter', function () { clearTimeout(closeTimer); });
            panel.addEventListener('mouseleave', closePanelDelayed);

            /* Billentyűzettel is elérhető legyen: a gombra fókuszálva nyíljon,
               és a panelből kifókuszálva csukódjon. */
            button.addEventListener('focus', function () { openPanel(panel); });
        });

        /* Escape zárja a nyitott panelt */
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && openedPanel) {
                openedPanel.classList.remove('show');
                openedPanel = null;
            }
        });

        /* Ha a fókusz kikerül a menüsorból, csukjuk be */
        menu.addEventListener('focusout', function (event) {
            if (!menu.contains(event.relatedTarget)) {
                closePanelDelayed();
            }
        });
    }

    function bindMobile() {
        var drawer = document.querySelector('.ws-cat-drawer');

        if (!drawer) {
            return;
        }

        function openDrawer() {
            drawer.classList.add('show');
            document.body.classList.add('overflow-hidden');
        }

        function closeDrawer() {
            drawer.classList.remove('show');
            document.body.classList.remove('overflow-hidden');
        }

        document.querySelectorAll('.js-ws-cat-drawer-open').forEach(function (button) {
            button.addEventListener('click', openDrawer);
        });

        document.querySelectorAll('.js-ws-cat-drawer-close').forEach(function (button) {
            button.addEventListener('click', closeDrawer);
        });

        /* Kattintás a dobozon KÍVÜLRE zárja – ugyanaz a minta, mint a szűrő fióknál */
        drawer.addEventListener('click', function (event) {
            if (!event.target.closest('.ws-cat-drawer-box')) {
                closeDrawer();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeDrawer();
            }
        });
    }

    function init() {
        var menu = document.querySelector('.ws-cat-menu');

        if (!menu || menu.dataset.wsCatMenuBound) {
            return;
        }

        menu.dataset.wsCatMenuBound = '1';

        bindDesktop(menu);
        bindMobile();
    }

    /* A szkript a <head>-ből is betölthető, ilyenkor a menü markupja még nincs kész. */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();

/*
 * Webshop › Fordítások (2026-11-03): az összesítő lista szűrése és kijelölése, valamint az AI-fordítás ablaka
 * (beállítások → becslés → lépésenkénti futás → riport → visszavonás). A futás állapota az adatbázisban van: a
 * böngésző csak lépteti (egy kérés = egy tétel), így szüneteltethető, és bezárt ablak után a riportból folytatható.
 *
 * A core admin segédeire épül (window.wnAdminRequest, wnConfirm, wnToast — public/js/admin.js); ha valamelyik
 * hiányzik, egyszerű tartalékkal megy.
 */
const WebshopTranslations = (function ($) {
    'use strict';

    const modalSelector = '#wsTranslationModal';
    let config = {};
    let current = null;     // a futó futás: {urls, paused, stopped, busy, pending}
    let changedSomething = false;

    function csrfToken() {
        return $('meta[name="csrf-token"]').attr('content') || '';
    }

    function request(url, data, method) {
        if (typeof window.wnAdminRequest === 'function') {
            return window.wnAdminRequest(url, data || {}, {method: method || 'POST', toast: false});
        }

        return new Promise(function (resolve, reject) {
            $.ajax({
                url: url,
                method: method || 'POST',
                data: data || {},
                dataType: 'json',
                headers: {'X-CSRF-TOKEN': csrfToken(), 'X-Requested-With': 'XMLHttpRequest'}
            }).done(resolve).fail(function (xhr) {
                const error = new Error((xhr.responseJSON && xhr.responseJSON.message) || 'A kérés nem sikerült.');
                error.status = xhr.status;
                reject(error);
            });
        });
    }

    function toast(message, type) {
        if (typeof window.wnToast === 'function') {
            window.wnToast(message, type || 'success');
        } else if (type === 'error') {
            alert(message);
        }
    }

    function confirmDialog(options) {
        if (typeof window.wnConfirm === 'function') {
            return window.wnConfirm(options);
        }

        return Promise.resolve(window.confirm(options.message || options.title));
    }

    function escapeHtml(value) {
        return String(value === null || value === undefined ? '' : value)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    // ------------------------------------------------------------------------------------------------ ablak

    function modal() {
        return $(modalSelector);
    }

    function setModal(bodyHtml, footerHtml) {
        modal().find('.js-tr-modal-body').html(bodyHtml);
        modal().find('.js-tr-modal-footer').html(footerHtml || '');
    }

    function showModal() {
        changedSomething = false;
        modal().modal('show');
    }

    /** A kijelölés kérés-paraméterei: {type, ids[]} vagy {type, all: 1, filters} */
    function openDialog(selection, preselectLocale) {
        current = null;
        setModal('<p class="text-muted mb-0"><i class="fa-solid fa-spinner fa-spin"></i> Betöltés…</p>', '');
        showModal();

        const params = $.extend({}, selection, preselectLocale ? {locale: preselectLocale} : {});
        request(config.dialogUrl + '?' + $.param(params), {}, 'GET').then(function (response) {
            renderDialog(response.html || '', selection);
        }).catch(function () {
            setModal('<div class="alert alert-danger mb-0">Az ablak nem tölthető be. Frissítsd az oldalt, és próbáld újra.</div>',
                '<button type="button" class="btn btn-secondary" data-dismiss="modal">Bezárás</button>');
        });
    }

    function renderDialog(html, selection) {
        setModal(html,
            '<button type="button" class="btn btn-secondary" data-dismiss="modal">Mégse</button>' +
            '<button type="button" class="btn btn-primary fw-600 js-tr-start" disabled><i class="fa-solid fa-language"></i> Fordítás indítása</button>');

        const $form = modal().find('.js-tr-options');
        let estimateTimer = null;

        function options() {
            return {
                locales: $form.find('[name="locales[]"]:checked').map(function () { return this.value; }).get(),
                mode: $form.find('[name="mode"]:checked').val(),
                groups: $form.find('[name="groups[]"]:checked').map(function () { return this.value; }).get(),
                keep_manual: $form.find('[name="keep_manual"]').is(':checked') ? 1 : 0
            };
        }

        function estimate() {
            const $box = modal().find('.js-tr-estimate');
            const opts = options();
            if (!opts.locales.length) {
                $box.html('<span class="text-danger">Jelölj ki legalább egy nyelvet.</span>');
                modal().find('.js-tr-start').prop('disabled', true);
                return;
            }
            $box.html('<i class="fa-solid fa-spinner fa-spin"></i> Becslés…');
            request(config.estimateUrl, $.extend({}, selection, {options: opts})).then(function (est) {
                if (!est.items) {
                    $box.html('<span class="text-muted">A kijelölésben ezekkel a beállításokkal nincs mit fordítani.</span>');
                    modal().find('.js-tr-start').prop('disabled', true);
                    return;
                }
                const minutes = Math.max(1, Math.round(est.seconds / 60));
                $box.html(
                    '<b>' + est.items + '</b> tétel · <b>' + est.fields + '</b> mező' + (est.slugs ? ' + ' + est.slugs + ' URL-szó' : '') +
                    ' · kb. ' + est.characters.toLocaleString('hu-HU') + ' karakter · <b>' + est.ai_calls + '</b> AI-hívás · ' +
                    'várhatóan ' + (est.seconds < 60 ? 'kevesebb mint egy perc' : 'kb. ' + minutes + ' perc')
                );
                modal().find('.js-tr-start').prop('disabled', false);
            }).catch(function (error) {
                $box.html('<span class="text-danger">' + escapeHtml(error.message) + '</span>');
            });
        }

        $form.on('change', 'input', function () {
            clearTimeout(estimateTimer);
            estimateTimer = setTimeout(estimate, 250);
        });
        estimate();

        modal().find('.js-tr-start').on('click', function () {
            const $btn = $(this).prop('disabled', true);
            request(config.storeUrl, $.extend({}, selection, {options: options()})).then(function (run) {
                startRun(run);
            }).catch(function (error) {
                $btn.prop('disabled', false);
                toast(error.message, 'error');
            });
        });
    }

    // ------------------------------------------------------------------------------------------------ futás

    function progressHtml() {
        return '' +
            '<div class="ws-tr-run">' +
            '  <div class="d-flex justify-content-between align-items-baseline mb-1">' +
            '    <span class="fw-600 js-tr-run-status">Fordítás folyamatban…</span>' +
            '    <span class="text-muted fs-14 js-tr-run-counter"></span>' +
            '  </div>' +
            '  <div class="progress ws-tr-progress mb-3"><div class="progress-bar progress-bar-striped progress-bar-animated js-tr-run-bar" style="width:0%"></div></div>' +
            '  <div class="alert alert-warning py-2 fs-14 js-tr-run-error" style="display:none"></div>' +
            '  <ul class="ws-tr-run-log js-tr-run-log"></ul>' +
            '</div>';
    }

    /**
     * A futás folyamatjelzője. `paused`: a listából megnyitott, félbemaradt futás — nem indul magától, a „Folytatás”
     * viszi tovább onnan, ahol abbamaradt.
     */
    function startRun(run, paused) {
        current = {urls: run.urls, paused: !!paused, stopped: false, busy: false, pending: null};
        changedSomething = true;
        setModal(progressHtml(),
            '<button type="button" class="btn btn-outline-secondary js-tr-pause"></button>' +
            '<button type="button" class="btn btn-outline-danger js-tr-cancel"><i class="fa-solid fa-stop"></i> Leállítás</button>');
        updateProgress(run);
        setPausedState(current.paused, run.status === 'paused' ? null : 'A futás félbemaradt — a „Folytatás” onnan viszi tovább, ahol abbamaradt.');
        if (run.error) {
            modal().find('.js-tr-run-error').text(run.error).show();
        }

        modal().find('.js-tr-pause').on('click', function () {
            const active = current;
            if (!active) return;
            active.paused = !active.paused;
            setPausedState(active.paused);
            if (active.paused) {
                pauseOnServer(active);
            } else {
                modal().find('.js-tr-run-error').hide();
                step();
            }
        });
        modal().find('.js-tr-cancel').on('click', function () {
            confirmDialog({title: 'A fordítás leállítása', message: 'A hátralévő tételek kimaradnak; a már lefordítottak élesek maradnak (a riportból visszavonhatók).', confirmLabel: 'Leállítás', danger: true})
                .then(function (ok) {
                    if (!ok || !current) return;
                    current.stopped = true;
                    request(current.urls.cancel).then(function (progress) { finish(progress); });
                });
        });

        if (run.finished) {
            finish(run);
        } else {
            step();
        }
    }

    function setPausedState(paused, message) {
        modal().find('.js-tr-pause').html(paused ? '<i class="fa-solid fa-play"></i> Folytatás' : '<i class="fa-solid fa-pause"></i> Szünet');
        modal().find('.js-tr-run-bar').toggleClass('progress-bar-animated', !paused);
        modal().find('.js-tr-run-status').text(paused ? (message || 'Szünetel — a már lefordított tételek élesek.') : 'Fordítás folyamatban…');
    }

    /**
     * A szünet a szerveren is rögzül, így a lista „Szünetel”-t mutat, és a futás csak a „Folytatás”-ra megy tovább.
     * A folyamatban lévő lépés után küldi (az ne írja vissza „Folyamatban”-ra); ha közben folytatták, nem küldi.
     */
    function pauseOnServer(run) {
        return Promise.resolve(run.pending).then(function () {
            if (!run.paused && !run.stopped) return null;
            return request(run.urls.pause);
        }).catch(function () {
            return null;
        });
    }

    /** Bezárt ablak: a futás megáll, a szerveren szünetelőre áll (a listából folytatható) */
    function stopCurrent() {
        const run = current;
        current = null;
        if (!run) return Promise.resolve();
        run.stopped = true;

        return pauseOnServer(run);
    }

    function step() {
        const run = current;
        if (!run || run.paused || run.stopped || run.busy) return;

        run.busy = true;
        run.pending = request(run.urls.step).then(function (progress) {
            run.busy = false;
            if (run.stopped || current !== run) return;
            updateProgress(progress);
            if (progress.item) logItem(progress.item);

            if (progress.paused) {
                // a szerver állította szünetelőre (pl. elfogyott az AI-kredit)
                run.paused = true;
                setPausedState(true, 'Szünetel');
                modal().find('.js-tr-run-error').text(progress.error || '').show();
                return;
            }
            if (progress.finished) {
                finish(progress);
                return;
            }
            step();
        }).catch(function (error) {
            // hálózati / időtúllépési hiba: szünet, az admin folytathatja
            run.busy = false;
            if (run.stopped || current !== run) return;
            run.paused = true;
            setPausedState(true, 'Szünetel');
            modal().find('.js-tr-run-error').text('A lépés nem sikerült (' + error.message + '). A „Folytatás” újrapróbálja.').show();
            pauseOnServer(run);
        });
    }

    function updateProgress(progress) {
        const percent = progress.total ? Math.round(progress.processed * 100 / progress.total) : 100;
        modal().find('.js-tr-run-bar').css('width', percent + '%');
        modal().find('.js-tr-run-counter').text(progress.processed + ' / ' + progress.total + ' tétel · ' + progress.fields + ' mező' +
            (progress.errors ? ' · ' + progress.errors + ' hiba' : ''));
    }

    function logItem(item) {
        const icon = item.status === 'done' ? 'fa-check text-success' : (item.status === 'error' ? 'fa-triangle-exclamation text-warning' : 'fa-minus text-muted');
        const $log = modal().find('.js-tr-run-log');
        $log.prepend('<li><i class="fa-solid ' + icon + '"></i> ' + escapeHtml(item.label) + ' <span class="text-muted">· ' +
            escapeHtml(item.type_label) + ' · ' + escapeHtml(String(item.locale).toUpperCase()) + ' · ' + item.fields_done + ' mező</span>' +
            (item.message ? '<div class="text-muted fs-14">' + escapeHtml(item.message) + '</div>' : '') + '</li>');
        $log.children().slice(30).remove();
    }

    function finish(progress) {
        const urls = (current && current.urls) || progress.urls;
        current = null;
        modal().find('.js-tr-run-bar').removeClass('progress-bar-animated progress-bar-striped');
        modal().find('.js-tr-run-status').text('Kész');
        showReport(urls.report, urls.restore);
    }

    // ----------------------------------------------------------------------------------------------- riport

    function showReport(reportUrl, restoreUrl) {
        request(reportUrl, {}, 'GET').then(function (response) {
            renderReport(response.html, restoreUrl);
        }).catch(function (error) {
            toast(error.message, 'error');
        });
    }

    function renderReport(html, restoreUrl) {
        setModal('<div class="js-tr-report" data-restore-run-url="' + escapeHtml(restoreUrl || '') + '">' + html + '</div>',
            (restoreUrl ? '<button type="button" class="btn btn-outline-danger mr-auto js-tr-restore-run"><i class="fa-solid fa-rotate-left"></i> A teljes futás visszavonása</button>' : '') +
            '<button type="button" class="btn btn-primary" data-dismiss="modal">Bezárás</button>');
        initReportActions(modal().find('.js-tr-report')[0], modal());
    }

    /** A lista „Riport” gombja: lezárt futásnál a riport, félbemaradtnál a szünetelő folyamatjelző */
    function openRun(reportUrl) {
        current = null;
        showModal();
        setModal('<p class="text-muted mb-0"><i class="fa-solid fa-spinner fa-spin"></i> Betöltés…</p>', '');

        request(reportUrl, {}, 'GET').then(function (response) {
            const run = response.run;
            if (run && !run.finished) {
                startRun(run, true);
            } else {
                renderReport(response.html, run ? run.urls.restore : reportUrl + '/restore');
            }
        }).catch(function (error) {
            setModal('<div class="alert alert-danger mb-0">' + escapeHtml(error.message) + '</div>',
                '<button type="button" class="btn btn-secondary" data-dismiss="modal">Bezárás</button>');
        });
    }

    /**
     * Futás közben bezárt vagy újratöltött fül: a futás a szerveren is szünetelőre áll (a listából folytatható). A
     * beforeunload még a navigációs kérés előtt fut, így az újratöltött lista már „Szünetel”-t mutat; a pagehide a
     * tartalék (mobilon a beforeunload elmaradhat). A kétszeri küldés ártalmatlan.
     */
    function pauseOnPageHide() {
        function sendPause() {
            if (!current || current.stopped || !current.urls.pause || !navigator.sendBeacon) return;
            const data = new FormData();
            data.append('_token', csrfToken());
            navigator.sendBeacon(current.urls.pause, data);
        }

        window.addEventListener('beforeunload', sendPause);
        window.addEventListener('pagehide', sendPause);
    }

    function initReportActions(container, $scope) {
        const $container = $(container);
        const $root = $scope || $container;

        $container.on('click', '.js-tr-restore-item', function () {
            const $btn = $(this);
            confirmDialog({title: 'A tétel visszavonása', message: 'A tétel fordítása visszaáll a futás előtti állapotra.', confirmLabel: 'Visszavonás', danger: true})
                .then(function (ok) {
                    if (!ok) return;
                    $btn.prop('disabled', true);
                    request($btn.data('url')).then(function (response) {
                        changedSomething = true;
                        $btn.closest('tr').find('.js-tr-item-status').attr('class', 'ws-chip js-tr-item-status').text('Visszavonva');
                        $btn.remove();
                        toast(response.message);
                    }).catch(function (error) {
                        $btn.prop('disabled', false);
                        toast(error.message, 'error');
                    });
                });
        });

        $root.on('click', '.js-tr-restore-run', function () {
            const url = $container.data('restore-run-url');
            if (!url) return;
            const $btn = $(this);
            confirmDialog({title: 'A teljes futás visszavonása', message: 'A futás minden lefordított tétele visszaáll a futás előtti állapotra. Azt a mezőt, amit azóta valaki átírt, nem bántja.', confirmLabel: 'Visszavonás', danger: true})
                .then(function (ok) {
                    if (!ok) return;
                    $btn.prop('disabled', true);
                    request(url).then(function (response) {
                        changedSomething = true;
                        toast(response.message);
                        $container.find('.js-tr-item-status').attr('class', 'ws-chip js-tr-item-status').text('Visszavonva');
                        $container.find('.js-tr-restore-item').remove();
                        $btn.remove();
                    }).catch(function (error) {
                        $btn.prop('disabled', false);
                        toast(error.message, 'error');
                    });
                });
        });
    }

    // ------------------------------------------------------------------------------------------- összesítő

    function initOverview(options) {
        config = options;
        const $filters = $('.js-tr-filters');
        let searchTimer = null;
        let loading = null;

        function filterParams(page) {
            const params = {};
            $filters.serializeArray().forEach(function (field) { params[field.name] = field.value; });
            if (page) params.page = page;
            return params;
        }

        function reload(page) {
            const params = filterParams(page);
            $('.js-tr-category-filter').toggle(params.type === 'product');
            if (loading) loading.abort();
            $('.js-tr-rows').css('opacity', .5);
            loading = $.ajax({url: config.listUrl, data: params, dataType: 'json', headers: {'X-Requested-With': 'XMLHttpRequest'}})
                .done(function (response) {
                    $('.js-tr-rows').html(response.rows).css('opacity', 1);
                    $('.js-tr-pagination').html(response.pagination);
                    $('.js-tr-total').text(response.total);
                    $('.js-tr-translate-all').prop('disabled', !response.total);
                    $('.js-tr-check-all').prop('checked', false);
                    updateSelection();
                    config.type = params.type;
                    if (window.history && window.history.replaceState) {
                        window.history.replaceState(null, '', config.listUrl + '?' + $.param(params));
                    }
                })
                .fail(function (xhr) {
                    if (xhr.statusText !== 'abort') {
                        $('.js-tr-rows').css('opacity', 1);
                        toast('A lista nem tölthető be. Próbáld újra.', 'error');
                    }
                });
        }

        function selectedIds() {
            return $('.js-tr-check:checked').map(function () { return this.value; }).get();
        }

        function updateSelection() {
            const count = selectedIds().length;
            $('.js-tr-selected-count').text(count);
            $('.js-tr-selected-info').toggle(count > 0);
            $('.js-tr-translate-selected').prop('disabled', count === 0);
        }

        $filters.on('change', 'select', function () { reload(); });
        $filters.on('input', 'input[type="search"]', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () { reload(); }, 300);
        });
        $filters.on('submit', function (e) { e.preventDefault(); reload(); });

        $(document).on('click', '.js-tr-pagination a', function (e) {
            e.preventDefault();
            const match = /[?&]page=(\d+)/.exec($(this).attr('href') || '');
            reload(match ? match[1] : 1);
        });

        $('.js-tr-state-link').on('click', function (e) {
            e.preventDefault();
            $('#trLocale').val($(this).data('locale'));
            $('#trState').val($(this).data('state'));
            reload();
        });

        $(document).on('change', '.js-tr-check', updateSelection);
        $(document).on('change', '.js-tr-check-all', function () {
            $('.js-tr-check').prop('checked', this.checked);
            updateSelection();
        });

        $('.js-tr-translate-selected').on('click', function () {
            openDialog({type: config.type, ids: selectedIds()}, $('#trLocale').val());
        });
        $('.js-tr-translate-all').on('click', function () {
            const params = filterParams();
            openDialog({type: config.type, all: 1, filters: params}, params.locale);
        });
        $(document).on('click', '.js-tr-translate-one', function () {
            openDialog({type: config.type, ids: [$(this).data('id')]}, $('#trLocale').val());
        });

        $(document).on('click', '.js-tr-open-report', function () {
            openRun($(this).data('url'));
        });

        modal().on('hidden.bs.modal', function () {
            stopCurrent().then(function () {
                if (changedSomething) reload();
            });
        });
        pauseOnPageHide();
    }

    /** A szerkesztők (és a fordítás-szerkesztő) „Fordítás AI-val” gombja: egy tételes futás */
    function initRecordButtons(options) {
        config = options;
        $(document).on('click', '.js-tr-translate-record', function () {
            openDialog({type: $(this).data('type'), ids: [$(this).data('id')]}, $(this).data('locale') || null);
        });
        modal().on('hidden.bs.modal', function () {
            stopCurrent().then(function () {
                if (changedSomething && config.reloadOnFinish) window.location.reload();
            });
        });
        pauseOnPageHide();
    }

    return {
        initOverview: initOverview,
        initRecordButtons: initRecordButtons,
        initReportActions: initReportActions
    };
})(jQuery);

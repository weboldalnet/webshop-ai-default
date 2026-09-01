{{--
    Általános megerősítő ablak űrlap-küldéshez, a natív confirm() helyett.

    Használat: a gombra tedd rá a js-confirm-action osztályt, és add meg
    a data-confirm-title / data-confirm-text attribútumokat:

    <button type="submit" class="btn ... js-confirm-action"
            data-confirm-title="Számla készítése"
            data-confirm-text="Valódi számla kerül kiállításra a Számlázz.hu-n.">
--}}
<div class="modal fade" id="actionConfirmModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="actionConfirmTitle">Megerősítés</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p id="actionConfirmText" class="mb-0"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Mégsem</button>
                <button type="button" class="btn btn-primary" id="actionConfirmOk">Rendben</button>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        var pendingForm = null;

        document.addEventListener('click', function (event) {
            var trigger = event.target.closest('.js-confirm-action');
            if (!trigger) {
                return;
            }

            // Ha már a modalból küldjük, engedjük tovább.
            if (trigger.dataset.confirmed === '1') {
                trigger.dataset.confirmed = '';
                return;
            }

            event.preventDefault();

            // Elsődlegesen a data-confirm-form szerinti űrlap (a gomb és az űrlap
            // szándékosan külön van, hogy ne kerüljön form a formba).
            pendingForm = trigger.dataset.confirmForm
                ? document.getElementById(trigger.dataset.confirmForm)
                : trigger.closest('form');

            if (!pendingForm) {
                console.warn('action-confirm: nincs meg a célzott űrlap', trigger.dataset.confirmForm);
                return;
            }

            document.getElementById('actionConfirmTitle').textContent =
                trigger.dataset.confirmTitle || 'Megerősítés';
            document.getElementById('actionConfirmText').textContent =
                trigger.dataset.confirmText || 'Biztosan folytatod?';

            window.pendingConfirmTrigger = trigger;
            $('#actionConfirmModal').modal('show');
        });

        document.getElementById('actionConfirmOk').addEventListener('click', function () {
            $('#actionConfirmModal').modal('hide');

            if (window.pendingConfirmTrigger) {
                window.pendingConfirmTrigger.dataset.confirmed = '1';
            }
            if (pendingForm) {
                pendingForm.submit();
            }
        });
    })();
</script>

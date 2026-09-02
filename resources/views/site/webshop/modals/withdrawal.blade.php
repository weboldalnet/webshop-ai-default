{{--
    Elállás modal.

    A láblécből nyílik, ezért az oldal minden pontján elérhető. A vásárló a
    rendelésszámával azonosítja magát; ha az létezik, átirányítjuk az elállási
    űrlapra. A rendelésszám ellenőrzése a szerveren történik, korlátozott
    hívásszámmal – így nem lehet rendelésszámokat próbálgatni.
--}}
<div class="modal fade" id="wsWithdrawalModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title font-weight-bold">Elállás a vásárlástól</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="ws-withdrawal-lookup-form" action="{{ route('site.webshop.withdrawals.lookup') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <p class="text-muted">
                        Add meg a rendelésszámodat, és a következő lépésben kiválaszthatod,
                        mely termékektől szeretnél elállni. A rendelésszámot a visszaigazoló
                        e-mailben találod.
                    </p>

                    <div class="form-group mb-2">
                        <label for="ws-withdrawal-order-number" class="font-weight-bold">
                            Rendelésszám <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="order_number" id="ws-withdrawal-order-number"
                               class="form-control" placeholder="pl. 20260901-H8B0"
                               autocomplete="off" required>
                    </div>

                    <div id="ws-withdrawal-lookup-error" class="alert alert-danger py-2 px-3 small mb-0 d-none"></div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary font-weight-bold" data-dismiss="modal">Mégse</button>
                    <button type="submit" class="btn btn-primary font-weight-bold px-4" id="ws-withdrawal-lookup-btn">
                        Tovább <i class="fa fa-arrow-right ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function () {
        var form = document.getElementById('ws-withdrawal-lookup-form');
        if (!form) {
            return;
        }

        var errorBox = document.getElementById('ws-withdrawal-lookup-error');
        var submitBtn = document.getElementById('ws-withdrawal-lookup-btn');
        var input = document.getElementById('ws-withdrawal-order-number');

        function showError(message) {
            errorBox.textContent = message;
            errorBox.classList.remove('d-none');
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            errorBox.classList.add('d-none');
            submitBtn.disabled = true;

            fetch(form.getAttribute('action'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify({ order_number: input.value })
            })
            .then(function (response) {
                // A túl sok próbálkozást a szerver 429-cel utasítja el
                if (response.status === 429) {
                    showError('Túl sok próbálkozás. Kérjük, várj egy percet.');
                    return null;
                }
                return response.json();
            })
            .then(function (data) {
                if (!data) {
                    return;
                }
                if (data.success && data.redirect) {
                    window.location.href = data.redirect;
                    return;
                }
                showError(data.message || 'Nem sikerült ellenőrizni a rendelésszámot.');
            })
            .catch(function () {
                showError('Váratlan hiba történt. Kérjük, próbáld újra.');
            })
            .finally(function () {
                submitBtn.disabled = false;
            });
        });
    })();
</script>

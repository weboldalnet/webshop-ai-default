{{--
    Elállás modal („elállási funkció”).

    A láblécből nyílik, ezért az oldal minden pontján elérhető. A vásárló a
    rendelésszámával ÉS a rendeléskor megadott e-mail-címével azonosítja magát;
    ha a kettő együtt egyezik, aláírt linkkel átirányítjuk az elállási űrlapra.
    Az ellenőrzés a szerveren, korlátozott hívásszámmal történik.

    A címkék text-dark osztályt kapnak: a modal a lábléc markupjában ül, és a sötét
    láblécek világos label-színe különben fehér-a-fehéren jelenne meg.
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
                        Add meg a rendelésszámodat és a rendeléskor használt e-mail-címedet, és a
                        következő lépésben kiválaszthatod, mely termékektől szeretnél elállni.
                        A rendelésszámot a visszaigazoló e-mailben találod.
                    </p>

                    <div class="form-group mb-2">
                        <label for="ws-withdrawal-order-number" class="font-weight-bold text-dark">
                            Rendelésszám <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="order_number" id="ws-withdrawal-order-number"
                               class="form-control" placeholder="pl. 20260901-H8B0"
                               autocomplete="off" required>
                    </div>

                    <div class="form-group mb-2">
                        <label for="ws-withdrawal-email" class="font-weight-bold text-dark">
                            E-mail-cím <span class="text-danger">*</span>
                        </label>
                        <input type="email" name="email" id="ws-withdrawal-email"
                               class="form-control" placeholder="a rendeléskor megadott cím"
                               autocomplete="email" required>
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
        var emailInput = document.getElementById('ws-withdrawal-email');

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
                body: JSON.stringify({ order_number: input.value, email: emailInput ? emailInput.value : '' })
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
                // Validációs hiba (pl. hiányzó vagy hibás e-mail) Laravel-formátumban érkezik
                var firstError = data.errors ? Object.values(data.errors)[0] : null;
                showError(data.message && !data.errors ? data.message
                    : (firstError && firstError[0]) || 'Nem sikerült ellenőrizni az adatokat.');
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

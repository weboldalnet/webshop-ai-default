{{--
    Kuponbeváltó doboz a pénztáron.

    Csak akkor kerül ki, ha a kupon modul be van kapcsolva ÉS van most érvényes
    kupon – a feltétel a controllerben dől el ($couponAvailable), így kikapcsolt
    állapotban egyetlen kupon-lekérdezés sem fut le.

    A böngésző CSAK a kódot küldi; az összeget mindig a szerver számolja.
--}}
<div class="card shadow-sm border-0 h-100">
    <div class="card-header bg-primary text-white font-weight-bold h5">Kuponbeváltás</div>
    <div class="card-body">
        <label for="ws-coupon-code" class="sr-only">Kuponkód</label>

        <div class="input-group">
            <input type="text" class="form-control" id="ws-coupon-code"
                   placeholder="Kuponkód" autocomplete="off"
                   data-url="{{ route('site.webshop.coupon.activate') }}">
            <div class="input-group-append">
                <button type="button" class="btn btn-primary font-weight-bold" id="ws-coupon-apply">
                    Aktiválás
                </button>
            </div>
        </div>

        {{-- Kompakt hibaüzenet a dobozon belül --}}
        <div class="alert alert-danger py-2 px-3 small mt-2 mb-0 d-none" id="ws-coupon-error"></div>

        {{-- Sikeres aktiválás visszajelzése a dobozban is, nem csak a modalban --}}
        <div class="alert alert-success py-2 px-3 small mt-2 mb-0 d-none" id="ws-coupon-success"></div>
    </div>
</div>

{{-- Sikeres aktiválás modalja.
     Bezárható a jobb felső X-szel ÉS a lábléc gombjával is. --}}
<div class="modal fade" id="wsCouponModal" tabindex="-1" role="dialog" aria-labelledby="wsCouponModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="wsCouponModalLabel">
                    <i class="fa fa-check-circle text-success mr-1"></i> Sikeres kuponaktiválás
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Bezárás">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-1" id="ws-coupon-modal-name"></p>
                <p class="h4 mb-0">
                    Megtakarítás: <span class="text-success font-weight-bold" id="ws-coupon-modal-amount"></span>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Bezárás</button>
            </div>
        </div>
    </div>
</div>

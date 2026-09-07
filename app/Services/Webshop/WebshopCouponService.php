<?php

namespace Weboldalnet\WebshopAiDefault\Services\Webshop;

use Illuminate\Support\Facades\Session;

/**
 * Híd a webshop és a kupon modul között.
 *
 * A webshop NEM függ a kupon csomagtól: minden hívás class_exists() mögött van,
 * és a modul hiánya nem hiba, csak annyit jelent, hogy nincs kupon.
 *
 * A session CSAK a kuponkódot tárolja, az összeget SOHA. A kedvezményt minden
 * megjelenítésnél és a rendelés létrehozásakor is újraszámoljuk – így egy
 * időközben módosított kosár vagy lejárt kupon nem tud elavult összeget
 * felszámítani.
 */
class WebshopCouponService
{
    /** A pénztárban aktivált kuponkód a munkamenetben */
    const SESSION_KEY = 'ws_active_coupon_code';

    /** A kupon modul főkapcsolója a webshop beállításai közt */
    const SETTING_ENABLED = 'coupons_enabled';

    /** A kupon csomag szolgáltatása – csak stringként hivatkozva */
    const SERVICE_CLASS = '\Weboldalnet\CommerceCoupons\Services\CouponService';

    /**
     * Be van-e kapcsolva a kuponkezelés?
     *
     * A beállítást nézzük ELŐSZÖR: kikapcsolt állapotban egyetlen kupon-lekérdezés
     * sem futhat le, ahogy a követelmény előírja.
     */
    public static function isEnabled(): bool
    {
        if (!WebshopSettingsService::getBool(self::SETTING_ENABLED)) {
            return false;
        }

        return class_exists(self::SERVICE_CLASS);
    }

    /**
     * Megjelenjen-e a pénztáron a kuponbeváltó doboz?
     *
     * Csak akkor, ha be van kapcsolva ÉS van most érvényes kupon – különben
     * a vásárló olyan mezőt kapna, amibe semmit nem tud beírni.
     */
    public static function isAvailable(): bool
    {
        if (!self::isEnabled()) {
            return false;
        }

        $osztaly = self::SERVICE_CLASS;

        return $osztaly::hasValidCoupons();
    }

    /**
     * A kosár normalizált sorai a kupon modul számára.
     *
     * A kosár tételei már tartalmazzák a termék- és kategóriaazonosítót,
     * ezért ehhez nem kell adatbázishoz fordulni.
     */
    public static function cartLines(): array
    {
        $sorok = [];

        foreach (WebshopCartService::getContent() as $tetel) {
            $sorok[] = [
                'product_id' => (int) ($tetel['product_id'] ?? 0),
                'category_id' => isset($tetel['category_id']) ? (int) $tetel['category_id'] : null,
                'unit_price' => (float) ($tetel['price'] ?? 0),
                'quantity' => (int) ($tetel['quantity'] ?? 0),
            ];
        }

        return $sorok;
    }

    /**
     * Kupon aktiválása a pénztáron.
     *
     * Sikeres beváltásnál CSAK a kódot tesszük el; az összeg minden
     * megjelenítéskor újra kiszámolódik.
     *
     * @return array a CouponResult tömbösítve (success, message, discount, …)
     */
    public static function activate(string $kod): array
    {
        if (!self::isEnabled()) {
            return [
                'success' => false,
                'error_code' => 'disabled',
                'message' => 'A kuponbeváltás jelenleg nem elérhető.',
                'discount' => 0,
            ];
        }

        $osztaly = self::SERVICE_CLASS;
        $eredmeny = $osztaly::redeem($kod, self::cartLines());

        if ($eredmeny->success) {
            Session::put(self::SESSION_KEY, $eredmeny->couponCode);
        } else {
            self::clear();
        }

        return $eredmeny->toArray();
    }

    /** Az aktivált kuponkód, ha van */
    public static function activeCode(): ?string
    {
        return Session::get(self::SESSION_KEY);
    }

    /**
     * Az aktivált kupon FRISSEN újraszámolt eredménye.
     *
     * Ezt hívja a pénztár összesítője és a rendelés létrehozása is – így a
     * kiírt és a felszámított kedvezmény nem tud elcsúszni egymástól.
     * Ha időközben érvénytelenné vált (lejárt, kikerült a termék a kosárból),
     * a visszaadott eredmény sikertelen lesz.
     */
    public static function currentResult(): ?array
    {
        $kod = self::activeCode();

        if (!$kod || !self::isEnabled()) {
            return null;
        }

        $osztaly = self::SERVICE_CLASS;

        return $osztaly::redeem($kod, self::cartLines())->toArray();
    }

    /**
     * A pénztáron megjelenítendő kedvezmény összege.
     * Érvénytelenné vált kuponnál 0 – nem mutatunk olyan kedvezményt,
     * amit nem is számolnánk fel.
     */
    public static function currentDiscount(): float
    {
        $eredmeny = self::currentResult();

        return ($eredmeny && $eredmeny['success']) ? (float) $eredmeny['discount'] : 0.0;
    }

    /**
     * Az aktivált kupon törlése.
     *
     * A pénztár MINDEN megnyitásakor lefut: az aktiválás csak az adott
     * pénztár-látogatásra érvényes, ahogy a követelmény előírja.
     */
    public static function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }
}

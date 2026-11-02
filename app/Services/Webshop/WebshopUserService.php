<?php

namespace Weboldalnet\WebshopAiDefault\Services\Webshop;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Weboldalnet\WebshopAiDefault\Models\WebshopOrder;

/**
 * Híd a webshop és a felhasználó modul (weboldalnet/commerce-users) között.
 *
 * A modul önálló csomag — a fiók, a belépés és a címek az övé —, a webshop pedig innen
 * kapcsolja ki/be (Webshop beállítások › Vásárlói fiókok), és hozzáteszi a saját részeit:
 * Rendeléseim + újrarendelés, a pénztár kitöltése a fiókból, kötelező belépés a vásárláshoz.
 * Ha a csomag nincs telepítve, minden metódus „ki van kapcsolva” választ ad.
 */
class WebshopUserService
{
    public const MODULE_CLASS = '\Weboldalnet\CommerceUsers\CommerceUsers';
    public const ADDRESS_CLASS = '\Weboldalnet\CommerceUsers\Models\CommerceUserAddress';
    public const USER_CLASS = '\Weboldalnet\CommerceUsers\Models\CommerceUser';
    public const ADDRESS_SERVICE_CLASS = '\Weboldalnet\CommerceUsers\Services\AddressService';

    /** Beállítások (Webshop beállítások › Vásárlói fiókok) és az alapértékük */
    public const SETTING_ENABLED = 'users_enabled';
    public const SETTING_LOGIN_REQUIRED = 'users_checkout_login_required';
    public const SETTING_ORDERS = 'users_orders_enabled';
    public const SETTING_REORDER = 'users_reorder_enabled';
    public const SETTING_ADDRESSES = 'users_addresses_enabled';

    public const DEFAULTS = [
        self::SETTING_ENABLED => 'false',
        self::SETTING_LOGIN_REQUIRED => 'false',
        self::SETTING_ORDERS => 'true',
        self::SETTING_REORDER => 'true',
        self::SETTING_ADDRESSES => 'true',
    ];

    /** Telepítve van-e a felhasználó modul (a kapcsoló önmagában nem elég). */
    public static function isInstalled(): bool
    {
        return class_exists(self::MODULE_CLASS) && Route::has('commerce-users.login');
    }

    public static function setting(string $key): bool
    {
        return WebshopSettingsService::get($key, self::DEFAULTS[$key] ?? 'false') === 'true';
    }

    public static function isEnabled(): bool
    {
        return self::isInstalled() && self::setting(self::SETTING_ENABLED);
    }

    public static function loginRequired(): bool
    {
        return self::isEnabled() && self::setting(self::SETTING_LOGIN_REQUIRED);
    }

    public static function ordersEnabled(): bool
    {
        return self::isEnabled() && self::setting(self::SETTING_ORDERS);
    }

    public static function reorderEnabled(): bool
    {
        return self::ordersEnabled() && self::setting(self::SETTING_REORDER);
    }

    public static function addressesEnabled(): bool
    {
        return self::isEnabled() && self::setting(self::SETTING_ADDRESSES);
    }

    /** A bejelentkezett vásárló, vagy null (kikapcsolt / nem telepített modulnál is). */
    public static function user()
    {
        if (!self::isEnabled()) {
            return null;
        }

        $module = self::MODULE_CLASS;

        return $module::user();
    }

    /**
     * A webshop beállításait átadja a modulnak, és hozzáteszi a fiókmenühöz a Rendeléseimet.
     * A service provider boot-jából hívódik; a beállításokat csak kérésre olvassa (lusta).
     */
    public static function registerWithModule(): void
    {
        if (!class_exists(self::MODULE_CLASS)) {
            return;
        }

        $module = self::MODULE_CLASS;

        $module::resolveFeaturesUsing(function (string $feature) {
            return match ($feature) {
                'enabled' => self::setting(self::SETTING_ENABLED),
                'addresses' => self::setting(self::SETTING_ADDRESSES),
                default => null,
            };
        });

        $module::resolveLinksUsing(function (string $name) {
            return self::documentUrl($name);
        });

        $module::addAccountMenuItem('orders', 'Rendeléseim', 'fa-box', 'site.webshop.account.orders', 20,
            fn () => self::ordersEnabled(), ['site.webshop.account.orders*']);

        // Törölt fiók rendelései a megrendelő adataival megmaradnak, csak a fiók-kapcsolat szűnik meg.
        Event::listen('eloquent.deleted: ' . ltrim(self::USER_CLASS, '\\'), function ($user) {
            WebshopOrder::where('commerce_user_id', $user->id)->update(['commerce_user_id' => null]);
        });
    }

    /** Az adatvédelmi tájékoztató / ÁSZF címe a pénztár beállításaiból (ha meg van adva). */
    public static function documentUrl(string $name): ?string
    {
        $prefix = match ($name) {
            'privacy' => 'site_checkout_privacy',
            'terms' => 'site_checkout_tos',
            default => null,
        };

        if (!$prefix) {
            return null;
        }

        foreach (['_url', '_path'] as $suffix) {
            $value = trim((string) WebshopSettingsService::get($prefix . $suffix, ''));
            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    /**
     * A pénztár kitöltése a fiókból: név, e-mail, telefon, az elsődleges szállítási és
     * számlázási cím, valamint a választható mentett címek.
     */
    public static function checkoutPrefill($user): array
    {
        if (!$user) {
            return [];
        }

        $addressClass = self::ADDRESS_CLASS;
        $withAddresses = self::addressesEnabled();

        $shippingList = $withAddresses ? $user->shippingAddresses()->get() : collect();
        $billingList = $withAddresses ? $user->billingAddresses()->get() : collect();
        $shipping = $shippingList->first();
        $billing = $billingList->first();

        return [
            'name' => $user->full_name,
            'email' => $user->email,
            'phone' => $user->phone ?: ($shipping->phone ?? null),
            'company' => $billing->company ?? null,
            'tax_number' => $billing->tax_number ?? null,
            'shipping' => $shipping ? $shipping->toCheckoutArray() : [],
            'billing' => $billing ? $billing->toCheckoutArray() : [],
            // a számlázási cím csak akkor „egyezik a szállítással”, ha nincs külön mentve, vagy ugyanaz
            'billing_same_as_shipping' => !$billing || ($shipping && $billing->matches($shipping->toCheckoutArray())),
            'shipping_addresses' => $shippingList->map->toCheckoutArray()->values()->all(),
            'billing_addresses' => $billingList->map->toCheckoutArray()->values()->all(),
            'has_addresses' => $shippingList->isNotEmpty() || $billingList->isNotEmpty(),
            'addresses_enabled' => $withAddresses,
            'address_types' => [$addressClass::TYPE_SHIPPING, $addressClass::TYPE_BILLING],
        ];
    }

    /**
     * A pénztárban megadott címek mentése a fiókba („A címeimet mentsd el a fiókomba”).
     * Ami már szerepel a mentett címek között, nem kerül be még egyszer; az első mentett cím
     * lesz az elsődleges.
     */
    public static function saveCheckoutAddresses($user, array $data): void
    {
        if (!$user || !self::addressesEnabled()) {
            return;
        }

        $addressClass = self::ADDRESS_CLASS;
        $service = self::ADDRESS_SERVICE_CLASS;

        try {
            $shipping = $data['shipping'] ?? [];
            if (!empty($shipping['zip']) && !empty($shipping['city']) && !empty($shipping['address']) && empty($shipping['parcel_shop_id'])) {
                $shipping['name'] = $shipping['name'] ?? ($data['name'] ?? $user->full_name);
                $shipping['phone'] = $data['phone'] ?? null;
                self::saveIfNew($user, $addressClass::TYPE_SHIPPING, $shipping, $service);
            }

            $billing = empty($data['billing_same_as_shipping']) ? ($data['billing'] ?? []) : [];
            if (!empty($billing['name']) && !empty($billing['zip']) && !empty($billing['city']) && !empty($billing['address'])) {
                $billing['company'] = $data['company'] ?? null;
                $billing['tax_number'] = $data['tax_number'] ?? null;
                self::saveIfNew($user, $addressClass::TYPE_BILLING, $billing, $service);
            }
        } catch (\Throwable $e) {
            Log::warning('Webshop: a pénztári címek fiókba mentése nem sikerült: ' . $e->getMessage(), ['user_id' => $user->id]);
        }
    }

    private static function saveIfNew($user, string $type, array $data, string $service): void
    {
        foreach ($user->addresses()->where('type', $type)->get() as $existing) {
            if ($existing->matches($data)) {
                return;
            }
        }

        if ($service::countOfType($user, $type) >= $service::maxPerType()) {
            return;
        }

        $service::save($user, $type, $data);
    }

    /**
     * Újrarendelés: a rendelés tételei a kosárba, a mai áron és a mai készlet szerint.
     *
     * @return array{added: array, partial: array, unavailable: array}
     */
    public static function reorder(WebshopOrder $order): array
    {
        $result = ['added' => [], 'partial' => [], 'unavailable' => []];

        foreach ($order->items as $item) {
            $quantity = max(1, (int) $item->quantity);

            if (!$item->product_id) {
                $result['unavailable'][] = $item->product_name;
                continue;
            }

            $added = WebshopCartService::add((int) $item->product_id, $quantity);

            if ($added <= 0) {
                $result['unavailable'][] = $item->product_name;
            } elseif ($added < $quantity) {
                $result['partial'][] = $item->product_name . ' (' . $added . ' / ' . $quantity . ' db)';
            } else {
                $result['added'][] = $item->product_name;
            }
        }

        return $result;
    }
}

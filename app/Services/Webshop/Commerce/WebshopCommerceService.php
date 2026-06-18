<?php

namespace Weboldalnet\WebshopAiDefault\Services\Webshop\Commerce;

use Illuminate\Support\Facades\Log;
use Weboldalnet\WebshopAiDefault\Models\WebshopOrder;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopSettingsService;

/**
 * A commerce-core integrációs réteg biztonságos burkolója.
 * Ha a commerce-core nincs telepítve vagy konfigurálatlan, gracefully degradál.
 */
class WebshopCommerceService
{
    public static function isAvailable(): bool
    {
        return class_exists(\Weboldalnet\CommerceCore\Services\CommerceOrderProcessor::class);
    }

    /**
     * Visszaadja az engedélyezett fizetési módokat a commerce-core config vagy
     * a PaymentManager alapján, az admin beállítások szerint szűrve.
     * Ha a "Fizetési lehetőségek" nincs bekapcsolva, üres tömböt ad vissza.
     *
     * @param bool $includeOnSite A "Fizetés a helyszínen" opció is szerepeljen-e (személyes átvételnél JS szúrja be)
     */
    public static function getAvailablePaymentMethods(bool $includeOnSite = false): array
    {
        // Ha a fizetési lehetőségek nincs bekapcsolva, ne jelenjenek meg
        if (!WebshopSettingsService::getBool('site_checkout_payment_options_enabled')) {
            return [];
        }

        $allMethods = self::getAllPaymentMethods();
        $filtered = [];

        // Online fizetés (nem cod, bank_transfer, manual, pickup, on_site kódú providerek)
        $onlineCodes = self::getOnlinePaymentCodes($allMethods);
        if (WebshopSettingsService::getBool('site_checkout_payment_online_enabled')) {
            foreach ($onlineCodes as $code) {
                if (isset($allMethods[$code])) {
                    $filtered[$code] = $allMethods[$code];
                }
            }
        }

        // Utánvét
        if (WebshopSettingsService::getBool('site_checkout_payment_cod_enabled') && isset($allMethods['cod'])) {
            $filtered['cod'] = $allMethods['cod'];
        }

        // Előre utalással
        if (WebshopSettingsService::getBool('site_checkout_payment_bank_transfer_enabled') && isset($allMethods['bank_transfer'])) {
            $filtered['bank_transfer'] = $allMethods['bank_transfer'];
        }

        // Fizetés a helyszínen (csak ha kérjük és be van kapcsolva)
        if ($includeOnSite && WebshopSettingsService::getBool('site_checkout_payment_on_site_enabled')) {
            $filtered['on_site'] = 'Fizetés a helyszínen';
        }

        return $filtered;
    }

    /**
     * Összes elérhető fizetési mód lekérése (szűrés nélkül).
     */
    private static function getAllPaymentMethods(): array
    {
        if (!self::isAvailable()) {
            return self::getFallbackPaymentMethods();
        }

        try {
            $manager = app(\Weboldalnet\CommerceCore\Managers\PaymentManager::class);
            $providers = $manager->getEnabledProviders();
            $methods = [];
            foreach ($providers as $code => $provider) {
                $methods[$code] = $provider->getName();
            }
            return !empty($methods) ? $methods : self::getFallbackPaymentMethods();
        } catch (\Throwable $e) {
            Log::warning('WebshopCommerceService: Nem sikerült lekérni a payment providereket: ' . $e->getMessage());
            return self::getFallbackPaymentMethods();
        }
    }

    /**
     * Visszaadja az online fizetési kódokat az összes metódusból.
     */
    private static function getOnlinePaymentCodes(array $allMethods): array
    {
        $offlineCodes = ['cod', 'bank_transfer', 'manual', 'pickup', 'on_site'];
        return array_keys(array_diff_key($allMethods, array_flip($offlineCodes)));
    }

    /**
     * Visszaadja az engedélyezett szállítási módokat az admin beállítások szerint szűrve.
     * Ha a "Szállítási lehetőségek" nincs bekapcsolva, üres tömböt ad vissza.
     */
    public static function getAvailableShippingMethods(): array
    {
        // Ha a szállítási lehetőségek nincs bekapcsolva, ne jelenjenek meg
        if (!WebshopSettingsService::getBool('site_checkout_shipping_options_enabled')) {
            return [];
        }

        $filtered = [];

        // Házhoz szállítás (flat_rate kód)
        if (WebshopSettingsService::getBool('site_checkout_shipping_home_delivery_enabled')) {
            $filtered['flat_rate'] = 'Házhoz szállítás';
        }

        // Csomagpont automata (parcel_locker kód)
        if (WebshopSettingsService::getBool('site_checkout_shipping_parcel_locker_enabled')) {
            $filtered['parcel_locker'] = 'Csomagpont automata';
        }

        // Személyes átvétel (pickup kód)
        if (WebshopSettingsService::getBool('site_checkout_shipping_pickup_enabled')) {
            $filtered['pickup'] = 'Személyes átvétel';
        }

        return $filtered;
    }

    /**
     * Visszaadja az összes szállítási módot (szűrés nélkül, fallback-kel).
     */
    private static function getAllShippingMethods(): array
    {
        if (!self::isAvailable()) {
            return self::getFallbackShippingMethods();
        }

        try {
            $manager = app(\Weboldalnet\CommerceCore\Managers\ShippingManager::class);
            $providers = $manager->getEnabledProviders();
            $methods = [];
            foreach ($providers as $code => $provider) {
                $methods[$code] = $provider->getName();
            }
            return !empty($methods) ? $methods : self::getFallbackShippingMethods();
        } catch (\Throwable $e) {
            Log::warning('WebshopCommerceService: Nem sikerült lekérni a shipping providereket: ' . $e->getMessage());
            return self::getFallbackShippingMethods();
        }
    }

    /**
     * Elindítja a commerce payment folyamatot egy rendeléshez.
     * Visszatér egy result tömbbel (success, requiresRedirect, redirectUrl, stb.).
     */
    public static function processOrderPayment(WebshopOrder $order): array
    {
        // "Fizetés a helyszínen" offline kezelése – nincs commerce provider
        if ($order->payment_method === 'on_site') {
            return [
                'success' => true,
                'requiresRedirect' => false,
                'redirectUrl' => null,
                'paymentStatus' => WebshopOrder::PAYMENT_STATUS_PENDING,
                'orderStatusSuggestion' => WebshopOrder::STATUS_PROCESSING,
                'transactionId' => null,
                'message' => 'Fizetés a helyszínen – offline mód.',
                'rawResult' => null,
            ];
        }

        if (!self::isAvailable()) {
            return [
                'success' => true,
                'requiresRedirect' => false,
                'redirectUrl' => null,
                'paymentStatus' => WebshopOrder::PAYMENT_STATUS_PENDING,
                'orderStatusSuggestion' => WebshopOrder::STATUS_PROCESSING,
                'transactionId' => null,
                'message' => 'Commerce-core nem elérhető, offline mód.',
                'rawResult' => null,
            ];
        }

        try {
            $processor = app(\Weboldalnet\CommerceCore\Services\CommerceOrderProcessor::class);
            $requestData = WebshopPaymentRequestFactory::fromOrder($order);

            $result = $processor->process($requestData);

            return [
                'success' => $result['success'] ?? false,
                'requiresRedirect' => $result['requiresRedirect'] ?? false,
                'redirectUrl' => $result['redirectUrl'] ?? null,
                'paymentStatus' => $result['paymentStatus'] ?? WebshopOrder::PAYMENT_STATUS_PENDING,
                'orderStatusSuggestion' => $result['orderStatusSuggestion'] ?? WebshopOrder::STATUS_PROCESSING,
                'transactionId' => $result['transactionId'] ?? null,
                'message' => $result['message'] ?? null,
                'rawResult' => $result,
            ];
        } catch (\Throwable $e) {
            Log::error('WebshopCommerceService: Payment feldolgozási hiba: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'payment_method' => $order->payment_method,
            ]);
            return [
                'success' => false,
                'requiresRedirect' => false,
                'redirectUrl' => null,
                'paymentStatus' => WebshopOrder::PAYMENT_STATUS_FAILED,
                'orderStatusSuggestion' => WebshopOrder::STATUS_FAILED,
                'transactionId' => null,
                'message' => 'Fizetési folyamat indítása sikertelen: ' . $e->getMessage(),
                'rawResult' => null,
            ];
        }
    }

    /**
     * Elindítja a számlakészítési folyamatot egy rendeléshez.
     */
    public static function createInvoice(WebshopOrder $order): array
    {
        if (!self::isAvailable()) {
            return ['success' => false, 'message' => 'Commerce-core nem elérhető.'];
        }

        try {
            $manager = app(\Weboldalnet\CommerceCore\Managers\InvoiceManager::class);
            $requestData = WebshopInvoiceRequestFactory::fromOrder($order);
            $result = $manager->createInvoice($requestData);

            return [
                'success' => $result['success'] ?? false,
                'invoiceNumber' => $result['invoiceNumber'] ?? null,
                'invoiceId' => $result['invoiceId'] ?? null,
                'documentId' => $result['documentId'] ?? null,
                'message' => $result['message'] ?? null,
                'rawResult' => $result,
            ];
        } catch (\Throwable $e) {
            Log::error('WebshopCommerceService: Számlázási hiba: ' . $e->getMessage(), ['order_id' => $order->id]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Elindítja a szállítmányozási folyamatot egy rendeléshez.
     */
    public static function createShipment(WebshopOrder $order): array
    {
        if (!self::isAvailable()) {
            return ['success' => false, 'message' => 'Commerce-core nem elérhető.'];
        }

        try {
            $manager = app(\Weboldalnet\CommerceCore\Managers\ShippingManager::class);
            $requestData = WebshopShippingRequestFactory::fromOrder($order);
            $result = $manager->createShipment($requestData);

            return [
                'success' => $result['success'] ?? false,
                'trackingNumber' => $result['trackingNumber'] ?? null,
                'trackingUrl' => $result['trackingUrl'] ?? null,
                'shipmentId' => $result['shipmentId'] ?? null,
                'message' => $result['message'] ?? null,
                'rawResult' => $result,
            ];
        } catch (\Throwable $e) {
            Log::error('WebshopCommerceService: Szállítmányozási hiba: ' . $e->getMessage(), ['order_id' => $order->id]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Ellenőrzi, hogy a megadott fizetési mód online-e.
     */
    public static function isOnlinePaymentMethod(string $method): bool
    {
        if (!self::isAvailable()) {
            return false;
        }

        try {
            $manager = app(\Weboldalnet\CommerceCore\Managers\PaymentManager::class);
            $provider = $manager->getProvider($method);
            return $provider ? $provider->isOnline() : false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Visszaadja egy fizetési mód kód human-readable nevét.
     * Ha nincs label a kódhoz, a kódot adja vissza.
     */
    public static function getPaymentMethodLabel(string $code): string
    {
        $allMethods = array_merge(self::getFallbackPaymentMethods(), [
            'on_site' => 'Fizetés a helyszínen',
        ]);

        // Ha elérhető a commerce-core, lekéri a provider nevét
        if (self::isAvailable()) {
            try {
                $manager = app(\Weboldalnet\CommerceCore\Managers\PaymentManager::class);
                $providers = $manager->getEnabledProviders();
                foreach ($providers as $providerCode => $provider) {
                    $allMethods[$providerCode] = $provider->getName();
                }
            } catch (\Throwable $e) {
                // fallback
            }
        }

        return $allMethods[$code] ?? $code;
    }

    /**
     * Visszaadja egy szállítási mód kód human-readable nevét.
     * Ha nincs label a kódhoz, a kódot adja vissza.
     */
    public static function getShippingMethodLabel(string $code): string
    {
        $allMethods = self::getFallbackShippingMethods();

        // Ha elérhető a commerce-core, lekéri a provider nevét
        if (self::isAvailable()) {
            try {
                $manager = app(\Weboldalnet\CommerceCore\Managers\ShippingManager::class);
                $providers = $manager->getEnabledProviders();
                foreach ($providers as $providerCode => $provider) {
                    $allMethods[$providerCode] = $provider->getName();
                }
            } catch (\Throwable $e) {
                // fallback
            }
        }

        return $allMethods[$code] ?? $code;
    }

    /**
     * Visszaadja az összes ismert fizetési mód label mappingjét (kód => label).
     */
    public static function getAllPaymentMethodLabels(): array
    {
        $allMethods = array_merge(self::getFallbackPaymentMethods(), [
            'on_site' => 'Fizetés a helyszínen',
        ]);

        if (self::isAvailable()) {
            try {
                $manager = app(\Weboldalnet\CommerceCore\Managers\PaymentManager::class);
                $providers = $manager->getEnabledProviders();
                foreach ($providers as $code => $provider) {
                    $allMethods[$code] = $provider->getName();
                }
            } catch (\Throwable $e) {
                // fallback
            }
        }

        return $allMethods;
    }

    /**
     * Visszaadja az összes ismert szállítási mód label mappingjét (kód => label).
     */
    public static function getAllShippingMethodLabels(): array
    {
        $allMethods = self::getFallbackShippingMethods();

        if (self::isAvailable()) {
            try {
                $manager = app(\Weboldalnet\CommerceCore\Managers\ShippingManager::class);
                $providers = $manager->getEnabledProviders();
                foreach ($providers as $code => $provider) {
                    $allMethods[$code] = $provider->getName();
                }
            } catch (\Throwable $e) {
                // fallback
            }
        }

        return $allMethods;
    }

    /**
     * Visszaadja egy fizetési státusz kód human-readable magyar nevét.
     */
    public static function getPaymentStatusLabel(string $status): string
    {
        return self::getAllPaymentStatusLabels()[$status] ?? $status;
    }

    /**
     * Visszaadja egy szállítási státusz kód human-readable magyar nevét.
     */
    public static function getShippingStatusLabel(string $status): string
    {
        return self::getAllShippingStatusLabels()[$status] ?? $status;
    }

    /**
     * Visszaadja az összes fizetési státusz label mappingjét (kód => label).
     */
    public static function getAllPaymentStatusLabels(): array
    {
        return [
            'unpaid'    => 'Nem fizetett',
            'pending'   => 'Függőben',
            'paid'      => 'Fizetve',
            'failed'    => 'Sikertelen',
            'cancelled' => 'Megszakítva',
            'refunded'  => 'Visszatérítve',
        ];
    }

    /**
     * Visszaadja az összes szállítási státusz label mappingjét (kód => label).
     */
    public static function getAllShippingStatusLabels(): array
    {
        return [
            'not_required' => 'Nem szükséges',
            'pending'      => 'Függőben',
            'prepared'     => 'Előkészítve',
            'shipped'      => 'Elküldve',
            'delivered'    => 'Kézbesítve',
            'failed'       => 'Sikertelen',
        ];
    }

    private static function getFallbackPaymentMethods(): array
    {
        return [
            'bank_transfer' => 'Előre utalással',
            'cod' => 'Utánvét',
            'manual' => 'Manuális fizetés',
        ];
    }

    private static function getFallbackShippingMethods(): array
    {
        return [
            'manual' => 'Manuális szállítás',
            'pickup' => 'Személyes átvétel',
            'flat_rate' => 'Házhoz szállítás',
            'parcel_locker' => 'Csomagpont automata',
        ];
    }
}

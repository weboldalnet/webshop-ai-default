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
                    // Csak akkor adjuk hozzá, ha az egyedi checkbox is be van pipálva az adminon
                    if (WebshopSettingsService::getBool('site_checkout_payment_method_' . $code . '_enabled')) {
                        $filtered[$code] = $allMethods[$code];
                    }
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
     * A telepített fizetési integrációk (kód => metaadat: name, settings_url,
     * online, builtin, active) – a kikapcsoltak is benne vannak.
     */
    public static function getAvailablePaymentProviders(): array
    {
        if (!self::isAvailable()) {
            return [];
        }

        try {
            return app(\Weboldalnet\CommerceCore\Managers\PaymentManager::class)->getAvailableProviders();
        } catch (\Throwable $e) {
            Log::warning('WebshopCommerceService: nem sikerült lekérni a fizetési providereket: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * A beállítófelület online fizetési integrációi.
     *
     * A kikapcsolt modulok is szerepelnek – különben nem lenne honnan
     * visszakapcsolni őket, mert a beállítólinkjük sem jelenne meg.
     */
    public static function getOnlinePaymentProviders(): array
    {
        $providers = [];

        foreach (self::getAvailablePaymentProviders() as $code => $meta) {
            if (empty($meta['online'])) {
                continue;
            }

            $providers[$code] = array_merge($meta, [
                'code' => $code,
                'enabled' => WebshopSettingsService::getBool('site_checkout_payment_method_' . $code . '_enabled'),
            ]);
        }

        return $providers;
    }

    /**
     * Be van-e kapcsolva egy online fizetési mód a pénztárban.
     */
    public static function isOnlinePaymentMethodEnabled(string $code): bool
    {
        return WebshopSettingsService::getBool('site_checkout_payment_options_enabled')
            && WebshopSettingsService::getBool('site_checkout_payment_online_enabled')
            && WebshopSettingsService::getBool('site_checkout_payment_method_' . $code . '_enabled');
    }

    /**
     * Az oldalsávban megjelenítendő integrációs beállítás-linkek.
     *
     * Csak azoké az integrációké jelenik meg, amelyek a pénztárban be vannak
     * kapcsolva. Egy csomag több módot is regisztrálhat (pl. a GLS házhoz
     * szállítást és csomagpontot), ezért URL szerint egyszer szerepel.
     */
    public static function getIntegrationSettingsLinks(): array
    {
        $collect = function (array $providers, callable $isEnabled) {
            $links = [];

            foreach ($providers as $code => $meta) {
                if (empty($meta['settings_url']) || !$isEnabled($code)) {
                    continue;
                }

                $links[$meta['settings_url']] = [
                    'name' => $meta['settings_label'] ?? $meta['name'] ?? $code,
                    'url' => $meta['settings_url'],
                ];
            }

            return array_values($links);
        };

        return [
            'payment' => $collect(self::getAvailablePaymentProviders(), function ($code) {
                return self::isOnlinePaymentMethodEnabled($code);
            }),
            'shipping' => $collect(self::getAvailableShippingProviders(), function ($code) {
                return WebshopSettingsService::getBool('site_checkout_shipping_options_enabled')
                    && self::isShippingMethodEnabled($code);
            }),
        ];
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

        // A módokat a regisztrált providerektől kérdezzük – korábban bedrótozott
        // lista volt, ezért egy újonnan telepített provider (pl. GLS) meg sem
        // jelent volna a pénztárban.
        $labels = self::getAllShippingMethodLabels();
        $filtered = [];

        foreach (array_keys(self::getRegisteredShippingCodes()) as $code) {
            if (self::isShippingMethodEnabled($code)) {
                $filtered[$code] = $labels[$code] ?? $code;
            }
        }

        return $filtered;
    }

    /**
     * A commerce-core-ban ténylegesen regisztrált szállítási providerek.
     */
    private static function getRegisteredShippingCodes(): array
    {
        if (!self::isAvailable()) {
            return self::getFallbackShippingMethods();
        }

        try {
            $manager = app(\Weboldalnet\CommerceCore\Managers\ShippingManager::class);

            return $manager->getEnabledProviders();
        } catch (\Throwable $e) {
            Log::warning('WebshopCommerceService: nem sikerült lekérni a shipping providereket: ' . $e->getMessage());

            return self::getFallbackShippingMethods();
        }
    }

    /**
     * A szállítási módok csoportjai a pénztárban.
     *
     * Minden csoportnak van egy saját kapcsolója; a csoporton belül pedig a
     * telepített futárszolgálatok közül lehet válogatni (GLS, MPL, FoxPost…).
     * A "pickup" csoportban egyetlen mód van, ott a csoport kapcsolója egyben
     * a mód kapcsolója is.
     */
    public const SHIPPING_GROUPS = [
        'home_delivery' => [
            'label' => 'Házhoz szállítás',
            'key' => 'site_checkout_shipping_home_delivery_enabled',
            'has_providers' => true,
        ],
        'parcel_shop' => [
            'label' => 'Csomagpont, csomagautomata',
            'key' => 'site_checkout_shipping_parcel_locker_enabled',
            'has_providers' => true,
        ],
        'pickup' => [
            'label' => 'Személyes átvétel',
            'key' => 'site_checkout_shipping_pickup_enabled',
            'has_providers' => false,
        ],
    ];

    /**
     * A telepített szállítási integrációk (kód => metaadat: name, settings_url,
     * kind, builtin, active) – a kikapcsoltak is benne vannak.
     */
    public static function getAvailableShippingProviders(): array
    {
        if (!self::isAvailable()) {
            return [];
        }

        try {
            return app(\Weboldalnet\CommerceCore\Managers\ShippingManager::class)->getAvailableProviders();
        } catch (\Throwable $e) {
            Log::warning('WebshopCommerceService: nem sikerült lekérni a szállítási providereket: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Egy szállítási mód jellege (home_delivery / parcel_shop / pickup / manual).
     */
    public static function getShippingMethodKind(string $code): string
    {
        $providers = self::getAvailableShippingProviders();

        if (isset($providers[$code]['kind'])) {
            return $providers[$code]['kind'];
        }

        if (class_exists(\Weboldalnet\CommerceCore\Managers\ShippingManager::class)) {
            return \Weboldalnet\CommerceCore\Managers\ShippingManager::resolveKind($code);
        }

        return 'home_delivery';
    }

    /**
     * Átvevőpontos szállítási mód-e (csomagpont, csomagautomata)?
     *
     * A pénztár ez alapján dönti el, hogy kell-e átvevőpont-választó, kötelező-e
     * az azonosító, és elrejthető-e a vásárló saját szállítási címe. Provider-
     * független: minden csomagpontos futárszolgálatra ugyanígy működik.
     */
    public static function isParcelShopMethod(?string $code): bool
    {
        if (!$code) {
            return false;
        }

        return self::getShippingMethodKind($code) === 'parcel_shop';
    }

    /**
     * A pénztárban elérhető szállítási módok csoportosítva.
     *
     * A vásárló először a kézbesítés módját választja (házhoz szállítás,
     * csomagpont, személyes átvétel), és csak azon belül a futárszolgálatot –
     * így nem egy hosszú, vegyes listából kell választania.
     *
     * Csak azok a csoportok szerepelnek, amikben van elérhető mód. A csoporton
     * kívül eső (ismeretlen jellegű) módok külön, "egyéb" csoport nélkül,
     * a lista végén jelennek meg.
     *
     * @return array<string, array{label: string, methods: array<string, string>}>
     */
    public static function getAvailableShippingMethodsGrouped(): array
    {
        $methods = self::getAvailableShippingMethods();
        $groups = [];

        foreach (self::SHIPPING_GROUPS as $kind => $group) {
            $groups[$kind] = ['label' => $group['label'], 'methods' => []];
        }

        foreach ($methods as $code => $label) {
            $kind = self::getShippingMethodKind($code);

            if (!isset($groups[$kind])) {
                // Ismeretlen jellegű mód – saját csoportot kap a nevével.
                $groups[$kind] = ['label' => $label, 'methods' => []];
            }

            $groups[$kind]['methods'][$code] = $label;
        }

        // Az üres csoportokat nem mutatjuk
        return array_filter($groups, function ($group) {
            return !empty($group['methods']);
        });
    }

    /**
     * A pénztárban elérhető átvevőpontos szállítási módok kódjai.
     */
    public static function getParcelShopMethodCodes(): array
    {
        $codes = [];

        foreach (array_keys(self::getAvailableShippingMethods()) as $code) {
            if (self::isParcelShopMethod($code)) {
                $codes[] = $code;
            }
        }

        return $codes;
    }

    /**
     * Be van-e kapcsolva egy szállítási csoport (házhoz szállítás, csomagpont,
     * személyes átvétel). Az ismeretlen jellegű módokat nem korlátozzuk.
     */
    public static function isShippingGroupEnabled(string $kind): bool
    {
        if (!isset(self::SHIPPING_GROUPS[$kind])) {
            return true;
        }

        return WebshopSettingsService::getBool(self::SHIPPING_GROUPS[$kind]['key']);
    }

    /**
     * A beállítófelület szállítási csoportjai, a hozzájuk tartozó telepített
     * futárszolgálatokkal együtt.
     */
    public static function getShippingGroups(): array
    {
        $providers = self::getAvailableShippingProviders();
        $groups = [];

        foreach (self::SHIPPING_GROUPS as $kind => $group) {
            $methods = [];

            if ($group['has_providers']) {
                foreach ($providers as $code => $meta) {
                    if (($meta['kind'] ?? null) !== $kind) {
                        continue;
                    }

                    $methods[$code] = array_merge($meta, [
                        'code' => $code,
                        'enabled' => self::isShippingMethodChecked($code),
                    ]);
                }
            }

            $groups[$kind] = array_merge($group, [
                'kind' => $kind,
                'enabled' => WebshopSettingsService::getBool($group['key']),
                'methods' => $methods,
            ]);
        }

        return $groups;
    }

    /**
     * Be van-e pipálva maga a szállítási mód (a csoport kapcsolójától függetlenül).
     *
     * Az egységes kulcs: site_checkout_shipping_method_{kod}_enabled.
     * A régi, mód-specifikus kulcsokat visszafelé kompatibilisen elfogadjuk,
     * hogy a már beállított shopokban ne változzon a viselkedés.
     */
    public static function isShippingMethodChecked(string $code): bool
    {
        $key = 'site_checkout_shipping_method_' . $code . '_enabled';

        // Ha az egységes kulcs már létezik a beállításokban, az dönt.
        if (array_key_exists($key, WebshopSettingsService::all())) {
            return WebshopSettingsService::getBool($key);
        }

        $legacyKeys = [
            'flat_rate' => 'site_checkout_shipping_home_delivery_enabled',
            'pickup' => 'site_checkout_shipping_pickup_enabled',
            'parcel_locker' => 'site_checkout_shipping_parcel_locker_enabled',
        ];

        if (isset($legacyKeys[$code])) {
            return WebshopSettingsService::getBool($legacyKeys[$code]);
        }

        return WebshopSettingsService::getBool($key);
    }

    /**
     * Be van-e kapcsolva egy szállítási mód az adminon.
     *
     * Két feltétel: a mód csoportja (házhoz szállítás / csomagpont / személyes
     * átvétel) be legyen kapcsolva, és maga a mód is legyen kipipálva.
     */
    public static function isShippingMethodEnabled(string $code): bool
    {
        $kind = self::getShippingMethodKind($code);

        if (!self::isShippingGroupEnabled($kind)) {
            return false;
        }

        // A személyes átvételnél a csoport kapcsolója egyben a mód kapcsolója.
        if (isset(self::SHIPPING_GROUPS[$kind]) && !self::SHIPPING_GROUPS[$kind]['has_providers']) {
            return true;
        }

        return self::isShippingMethodChecked($code);
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
     * A telepített számlázó integrációk (kód => metaadat: name, settings_url, active).
     *
     * A listát a commerce-core InvoiceManager adja, ezért egy új számlázó
     * csomag bekötéséhez a webshopban nem kell semmit módosítani.
     */
    public static function getAvailableInvoiceProviders(): array
    {
        if (!self::isAvailable()) {
            return [];
        }

        try {
            return app(\Weboldalnet\CommerceCore\Managers\InvoiceManager::class)->getAvailableProviders();
        } catch (\Throwable $e) {
            Log::warning('WebshopCommerceService: nem sikerült lekérni a számlázó providereket: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * A kiválasztott számlázó integráció kódja, vagy null.
     */
    public static function getInvoicingProvider(): ?string
    {
        $available = self::getAvailableInvoiceProviders();

        if (empty($available)) {
            return null;
        }

        $selected = WebshopSettingsService::get('invoicing_provider');
        if ($selected && isset($available[$selected])) {
            return $selected;
        }

        // Nincs érvényes választás. Egy külön telepített integráció (pl.
        // Számlázz.hu) erősebb alapértelmezés, mint a commerce-core beépített
        // manuális számlázása – így a korábbi viselkedés marad érvényben.
        $firstActive = null;

        foreach ($available as $code => $meta) {
            if (empty($meta['active'])) {
                continue;
            }

            if (empty($meta['builtin'])) {
                return (string) $code;
            }

            $firstActive = $firstActive ?? (string) $code;
        }

        return $firstActive;
    }

    /**
     * A kiválasztott (vagy megadott) számlázó integráció metaadatai.
     */
    public static function getInvoiceProviderMeta(?string $code = null): ?array
    {
        $code = $code ?: self::getInvoicingProvider();

        if (!$code) {
            return null;
        }

        $available = self::getAvailableInvoiceProviders();

        return isset($available[$code]) ? array_merge(['code' => $code], $available[$code]) : null;
    }

    /**
     * Be van-e kapcsolva a számlázás a webshopban.
     *
     * A webshop beállításokban lévő főkapcsoló dönt; ehhez kell egy kiválasztott
     * (telepített) számlázó integráció is, különben a funkció használhatatlan.
     */
    public static function isInvoicingEnabled(): bool
    {
        $key = 'invoicing_enabled';

        if (array_key_exists($key, WebshopSettingsService::all())) {
            return WebshopSettingsService::getBool($key) && self::getInvoicingProvider() !== null;
        }

        // Visszafelé kompatibilitás: a főkapcsoló bevezetése előtt egy külön
        // telepített számlázó modul saját kapcsolója döntött. A commerce-core
        // beépített manuális providere itt nem számít – az mindig aktív, tehát
        // önmagában nem jelentene bekapcsolt számlázást.
        foreach (self::getAvailableInvoiceProviders() as $meta) {
            if (!empty($meta['active']) && empty($meta['builtin'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Elindítja a számlakészítési folyamatot egy rendeléshez.
     */
    public static function createInvoice(WebshopOrder $order): array
    {
        if (!self::isAvailable()) {
            return ['success' => false, 'message' => 'Commerce-core nem elérhető.'];
        }

        if (!self::isInvoicingEnabled()) {
            return ['success' => false, 'message' => 'A számlázás nincs bekapcsolva a webshop beállításokban.'];
        }

        try {
            // A kiválasztott számlázó integráció (Webshop beállítások → Számlázás)
            $providerCode = self::getInvoicingProvider();

            $service = app(\Weboldalnet\CommerceCore\Services\InvoiceService::class);
            $requestData = WebshopInvoiceRequestFactory::fromOrder($order);
            $dto = \Weboldalnet\CommerceCore\Data\InvoiceRequestData::fromArray($requestData);
            
            $result = $service->createInvoice($providerCode, $dto);

            return [
                'success' => $result->success,
                'invoiceNumber' => $result->invoiceNumber,
                'invoiceId' => $result->invoiceId,
                // A helyi commerce_invoice_documents sor azonosítója – ezt kell a
                // rendeléshez kötni, nem a szolgáltatói számlaszámot.
                'documentId' => $result->documentId,
                'pdfPath' => $result->pdfPath,
                'message' => $result->message,
                'rawResult' => $result->toArray(),
            ];
        } catch (\Throwable $e) {
            Log::error('WebshopCommerceService: Számlázási hiba: ' . $e->getMessage(), ['order_id' => $order->id]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Szállítási díj kiszámítása a választott szállítási módhoz.
     * A providertől kérdezzük – ez korábban sehol nem történt meg, ezért a díj
     * soha nem került bele a rendelés végösszegébe.
     *
     * Hibát nem dob: ha a provider nem elérhető, 0-t ad vissza, hogy a pénztár
     * működőképes maradjon.
     */
    public static function calculateShippingCost(?string $shippingMethod, float $itemsTotal, string $currency = 'HUF'): float
    {
        if (!$shippingMethod || !self::isAvailable()) {
            return 0.0;
        }

        try {
            $service = app(\Weboldalnet\CommerceCore\Services\ShippingService::class);
            $result = $service->calculate($shippingMethod, \Weboldalnet\CommerceCore\Data\ShippingRateRequestData::fromArray([
                'shipping_method' => $shippingMethod,
                'cart_total' => $itemsTotal,
                'currency' => $currency,
            ]));

            if (!$result || !$result->success) {
                return 0.0;
            }

            return (float) ($result->rate ?? 0);
        } catch (\Throwable $e) {
            Log::warning('WebshopCommerceService: szállítási díj számítása sikertelen.', [
                'shipping_method' => $shippingMethod,
                'error' => $e->getMessage(),
            ]);

            return 0.0;
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
            // A ShippingService végzi a perzisztálást, az idempotenciát és az eventeket
            // (a számlázási oldal InvoiceService-ének párja).
            $service = app(\Weboldalnet\CommerceCore\Services\ShippingService::class);
            $dto = \Weboldalnet\CommerceCore\Data\ShipmentRequestData::fromArray(
                WebshopShippingRequestFactory::fromOrder($order)
            );

            $result = $service->createShipment($order->shipping_method, $dto);

            return [
                'success' => $result->success,
                'trackingNumber' => $result->trackingNumber,
                'trackingUrl' => $result->trackingUrl,
                'labelPath' => $result->labelPath,
                // A helyi commerce_shipments sor azonosítója – ezt kell a
                // rendeléshez kötni, nem a futárszolgálati csomagszámot.
                'shipmentId' => $result->shipmentId,
                'message' => $result->message,
                'rawResult' => $result->toArray(),
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
    public static function getPaymentMethodLabel(?string $code): string
    {
        if ($code === null || $code === '') {
            return '';
        }

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
    public static function getShippingMethodLabel(?string $code): string
    {
        if ($code === null || $code === '') {
            return '';
        }

        return self::getAllShippingMethodLabels()[$code] ?? $code;
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

        // A telepített szállítási csomagok elnevezései akkor is kellenek, ha a
        // modul épp ki van kapcsolva – különben a korábbi szállítmányoknál nyers
        // kód látszana a listákban.
        foreach ([
            config('commerce-gls.provider_code') => config('commerce-gls.default_shipping_method_label'),
            config('commerce-gls.parcel_shop_code') => config('commerce-gls.parcel_shop_label'),
        ] as $code => $label) {
            if ($code && $label && !isset($allMethods[$code])) {
                $allMethods[$code] = $label;
            }
        }

        if (self::isAvailable()) {
            try {
                $manager = app(\Weboldalnet\CommerceCore\Managers\ShippingManager::class);
                $providers = $manager->getEnabledProviders();
                foreach ($providers as $code => $provider) {
                    // A webshop saját elnevezése az elsődleges (a checkout is ezt kínálja),
                    // a provider neve csak az itt még nem ismert kódokra vonatkozik.
                    if (!isset($allMethods[$code])) {
                        $allMethods[$code] = $provider->getName();
                    }
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

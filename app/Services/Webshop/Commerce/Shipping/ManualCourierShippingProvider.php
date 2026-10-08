<?php

namespace Weboldalnet\WebshopAiDefault\Services\Webshop\Commerce\Shipping;

use Weboldalnet\CommerceCore\Contracts\ShippingProviderInterface;
use Weboldalnet\CommerceCore\Data\ShipmentCreateResult;
use Weboldalnet\CommerceCore\Data\ShipmentRequestData;
use Weboldalnet\CommerceCore\Data\ShippingRateRequestData;
use Weboldalnet\CommerceCore\Data\ShippingRateResult;
use Weboldalnet\CommerceCore\Status\ShippingStatus;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopSettingsService;

/**
 * Manuálisan kezelt futárszolgálat (pl. MPL), külső API-kapcsolat nélkül.
 *
 * Nem külön csomag, mint a GLS vagy a Foxpost: a pénztárban a házhoz szállítás
 * egyik választható futárszolgálataként jelenik meg, szállítási címmel – úgy,
 * mint a saját futárszolgálat. A szállítmányt az ügyfélszolgálat KÉZZEL rögzíti
 * a futárszolgálat saját rendszerében; ez az osztály csak a díjat számolja, és a
 * rendelésnél „függő" szállítmányt hoz létre.
 *
 * A díj és az ingyenes-szállítási értékhatár a webshop beállításaiból jön
 * (site_checkout_shipping_{kód}_fee / _free_above), és LUSTÁN olvassuk: a
 * provider a service provider boot()-jában jön létre, ahol adatbázist olvasni
 * tilos (friss telepítésnél a migráció előtt még nincs beállítás-tábla).
 *
 * A webshop saját kapcsolója dönti el, hogy a pénztárban megjelenik-e
 * (site_checkout_shipping_method_{kód}_enabled, alapból kikapcsolva).
 */
class ManualCourierShippingProvider implements ShippingProviderInterface
{
    /** @var string */
    protected $code;

    /** @var string */
    protected $name;

    /** @var string|null  Nyomkövetési URL-sablon, a {code} helyére kerül a csomagszám */
    protected $trackingUrlTemplate;

    public function __construct(string $code, string $name, ?string $trackingUrlTemplate = null)
    {
        $this->code = $code;
        $this->name = $name;
        $this->trackingUrlTemplate = $trackingUrlTemplate;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * Házhoz szállítás: a pénztár ennek alapján kéri be a szállítási címet,
     * és ebbe a csoportba sorolja a módot.
     */
    public function getKind()
    {
        return 'home_delivery';
    }

    public function getRate(): float
    {
        return (float) WebshopSettingsService::get('site_checkout_shipping_' . $this->code . '_fee', 0);
    }

    /**
     * Az ingyenes szállítás értékhatára, vagy null, ha nincs.
     *
     * A 0-t és az üres mezőt egyaránt „nincs értékhatár"-nak vesszük: 0 mellett a
     * `kosár >= értékhatár` feltétel minden rendelést ingyenessé tenne.
     */
    public function getFreeAbove(): ?float
    {
        $value = WebshopSettingsService::get('site_checkout_shipping_' . $this->code . '_free_above', null);

        if ($value === null || $value === '' || (float) $value <= 0) {
            return null;
        }

        return (float) $value;
    }

    public function calculate(ShippingRateRequestData $data)
    {
        $freeAbove = $this->getFreeAbove();
        $isFree = $freeAbove !== null && $data->cartTotal >= $freeAbove;
        $rate = $isFree ? 0.0 : $this->getRate();

        return ShippingRateResult::success([
            'provider' => $this->code,
            'shipping_method' => $this->code,
            'rate' => $rate,
            'currency' => 'HUF',
            'is_free' => $isFree,
            'message' => $isFree ? 'Ingyenes szállítás.' : $this->name . ': ' . $rate . ' HUF',
        ]);
    }

    /**
     * Nincs API: a szállítmányt az ügyfélszolgálat rögzíti a futárszolgálat
     * rendszerében. Itt csak egy „függő" szállítmány jön létre, hogy a rendelés
     * szállítási állapota az adminban követhető legyen.
     */
    public function createShipment(ShipmentRequestData $data)
    {
        return ShipmentCreateResult::success([
            'status' => ShippingStatus::PENDING,
            'provider' => $this->code,
            'message' => 'A szállítmányt kézzel kell rögzíteni a(z) ' . $this->name . ' rendszerében.',
        ]);
    }

    public function getTrackingUrl($trackingNumber)
    {
        if (!$this->trackingUrlTemplate || !$trackingNumber) {
            return null;
        }

        return str_replace('{code}', rawurlencode((string) $trackingNumber), $this->trackingUrlTemplate);
    }
}

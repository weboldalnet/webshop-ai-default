<?php

namespace Weboldalnet\WebshopAiDefault\Http\Controllers\Site\Webshop;

use Weboldalnet\WebshopAiDefault\Helpers\WebshopUrl;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Weboldalnet\WebshopAiDefault\Mail\WebshopWithdrawalMail;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopSettingsService;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopWithdrawalService;

/**
 * Elállás a site oldalon („elállási funkció”).
 *
 * A vásárló bejelentkezés nélkül áll el: rendelésszámmal ÉS a rendeléskor megadott
 * e-mail-címmel azonosítja magát, vagy a rendelés-visszaigazoló levél linkjével
 * érkezik. Az elállási oldal és a beküldés csak aláírt URL-lel érhető el, így a
 * rendelésszám ismerete önmagában nem mutatja meg más vásárló adatait.
 *
 * Az elállás egyoldalú jog, nem kérelem: indoklás nem kötelező, és a beérkezésről
 * – a nyilatkozat tartalmával, dátumával és időpontjával – tartós adathordozón
 * (e-mailben) visszaigazolást küldünk.
 */
class WebshopWithdrawalController extends Controller
{
    /**
     * Közös üzenet érvénytelen vagy lejárt elállási linkre (metódus, mert a fordítás konstansban nem hívható).
     */
    private static function invalidLinkMessage(): string
    {
        return __('webshop::withdrawal.messages.invalid_link', ['link' => __('webshop::withdrawal.title')]);
    }

    /**
     * Rendelés azonosítása a láblécben lévő modalból (AJAX): rendelésszám + e-mail-cím.
     *
     * A két adatnak EGYÜTT kell egyeznie. Eltérésnél ugyanazt az üzenetet adjuk, mint
     * nem létező rendelésszámnál – különben a válaszból kiderülne, hogy egy kitalált
     * rendelésszám létezik-e.
     */
    public function lookup(Request $request)
    {
        $request->validate([
            'order_number' => 'required|string|max:64',
            'email' => 'required|email|max:255',
        ], [], ['order_number' => __('webshop::withdrawal.attributes.order_number'), 'email' => __('webshop::withdrawal.attributes.email')]);

        $order = WebshopWithdrawalService::findOrderByNumber($request->input('order_number'));

        if (!$order || !WebshopWithdrawalService::emailMatches($order, $request->input('email'))) {
            return response()->json([
                'success' => false,
                'message' => __('webshop::withdrawal.messages.not_found'),
            ]);
        }

        if (!WebshopWithdrawalService::hasRemainingItems($order)) {
            return response()->json([
                'success' => false,
                'message' => __('webshop::withdrawal.messages.nothing_left'),
            ]);
        }

        return response()->json([
            'success' => true,
            'redirect' => WebshopWithdrawalService::formUrl($order),
        ]);
    }

    /**
     * Elállási űrlap a rendelés tételeivel – csak aláírt URL-lel.
     */
    public function create(Request $request, $orderNumber)
    {
        if (!$request->hasValidSignature()) {
            return redirect()->to(WebshopUrl::route('site.webshop.categories.index'))->with('error', self::invalidLinkMessage());
        }

        $order = WebshopWithdrawalService::findOrderByNumber($orderNumber);

        if (!$order) {
            return redirect()->to(WebshopUrl::route('site.webshop.categories.index'))->with('error', self::invalidLinkMessage());
        }

        $remaining = WebshopWithdrawalService::remainingQuantities($order);

        // Közvetlenül egy sikeres beküldés után NEM irányítunk el akkor sem, ha már
        // semmi nem maradt: különben a második átirányítás elnyelné a sikerüzenetet,
        // és a vásárló a teljes elállás után hibaüzenetet látna.
        if (array_sum($remaining) < 1 && !session()->has('withdrawal_success')) {
            return redirect()->to(WebshopUrl::route('site.webshop.categories.index'))
                ->with('error', __('webshop::withdrawal.messages.nothing_left'));
        }

        return view('site.webshop.withdrawals.create', [
            'order' => $order,
            'remaining' => $remaining,
            'showPrices' => WebshopSettingsService::getBool('site_product_prices_visible', true),
            'storeUrl' => WebshopWithdrawalService::storeUrl($order),
        ]);
    }

    /**
     * Elállási nyilatkozat beküldése – csak aláírt URL-lel.
     *
     * Indoklás NEM kötelező: a fogyasztó 14 napon belül indokolás nélkül elállhat
     * (45/2014. Korm. rendelet). A megjegyzés-mező csak lehetőség.
     */
    public function store(Request $request, $orderNumber)
    {
        if (!$request->hasValidSignature()) {
            return redirect()->to(WebshopUrl::route('site.webshop.categories.index'))->with('error', self::invalidLinkMessage());
        }

        $order = WebshopWithdrawalService::findOrderByNumber($orderNumber);

        if (!$order) {
            return redirect()->to(WebshopUrl::route('site.webshop.categories.index'))->with('error', self::invalidLinkMessage());
        }

        $remaining = WebshopWithdrawalService::remainingQuantities($order);

        $rules = [
            'reason' => 'nullable|string|max:5000',
            'quantities' => 'required|array',
        ];

        // Tételenként a még elállható mennyiség a felső korlát
        foreach ($remaining as $orderItemId => $max) {
            $rules['quantities.' . $orderItemId] = 'nullable|integer|min:0|max:' . $max;
        }

        $validated = $request->validate($rules, [
            'quantities.*.max' => __('webshop::withdrawal.messages.quantity_max'),
        ], ['reason' => __('webshop::withdrawal.attributes.reason')]);

        $quantities = array_filter(
            array_map('intval', $validated['quantities']),
            function ($quantity) {
                return $quantity > 0;
            }
        );

        if (empty($quantities)) {
            return redirect()->back()
                ->withInput()
                ->with('error', __('webshop::withdrawal.messages.select_one'));
        }

        $withdrawal = WebshopWithdrawalService::create($order, $quantities, $validated['reason'] ?? null);

        $this->sendNotifications($withdrawal);

        return redirect()->to(WebshopWithdrawalService::formUrl($order))
            ->with('withdrawal_success', __('webshop::withdrawal.messages.success'));
    }

    /**
     * Értesítés a vásárlónak és a webshop címére.
     *
     * A levélküldés hibája nem buktathatja el a kérelmet – az már el van mentve.
     */
    private function sendNotifications($withdrawal): void
    {
        $shopEmail = config('app.shop_email');

        foreach ([[$withdrawal->customer_email, false], [$shopEmail, true]] as [$recipient, $isShopCopy]) {
            if (!$recipient || !filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            try {
                // a vásárlónak a rendelés nyelvén, a boltnak az alapnyelven
                $locale = $isShopCopy
                    ? \Weboldalnet\WebshopAiDefault\Helpers\WebshopUrl::defaultLocale()
                    : \Weboldalnet\WebshopAiDefault\Support\WebshopOrderLocale::of(\Weboldalnet\WebshopAiDefault\Models\WebshopOrder::query()->find($withdrawal->order_id));
                \Weboldalnet\WebshopAiDefault\Support\WebshopOrderLocale::mail($recipient, new WebshopWithdrawalMail($withdrawal, $isShopCopy), $locale);
            } catch (\Throwable $e) {
                Log::error('Elállási nyilatkozat visszaigazoló e-mailjének küldése sikertelen.', [
                    'withdrawal_id' => $withdrawal->id,
                    'recipient' => $recipient,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}

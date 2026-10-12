<?php

namespace Weboldalnet\WebshopAiDefault\Http\Controllers\Site\Webshop;

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
     * Közös üzenet érvénytelen vagy lejárt elállási linkre.
     */
    private const INVALID_LINK_MESSAGE = 'Az elállási link lejárt vagy érvénytelen. Kérjük, a lábléc „Elállás a vásárlástól” '
        . 'linkjén add meg újra a rendelésszámodat és az e-mail-címedet.';

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
        ], [], ['order_number' => 'rendelésszám', 'email' => 'e-mail-cím']);

        $order = WebshopWithdrawalService::findOrderByNumber($request->input('order_number'));

        if (!$order || !WebshopWithdrawalService::emailMatches($order, $request->input('email'))) {
            return response()->json([
                'success' => false,
                'message' => 'Ehhez a rendelésszámhoz és e-mail-címhez nem találtunk rendelést. '
                    . 'Kérjük, ellenőrizd az adatokat a visszaigazoló e-mailben.',
            ]);
        }

        if (!WebshopWithdrawalService::hasRemainingItems($order)) {
            return response()->json([
                'success' => false,
                'message' => 'Ehhez a rendeléshez már minden tételre beérkezett elállási nyilatkozat.',
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
            return redirect()->route('site.webshop.categories.index')->with('error', self::INVALID_LINK_MESSAGE);
        }

        $order = WebshopWithdrawalService::findOrderByNumber($orderNumber);

        if (!$order) {
            return redirect()->route('site.webshop.categories.index')->with('error', self::INVALID_LINK_MESSAGE);
        }

        $remaining = WebshopWithdrawalService::remainingQuantities($order);

        // Közvetlenül egy sikeres beküldés után NEM irányítunk el akkor sem, ha már
        // semmi nem maradt: különben a második átirányítás elnyelné a sikerüzenetet,
        // és a vásárló a teljes elállás után hibaüzenetet látna.
        if (array_sum($remaining) < 1 && !session()->has('withdrawal_success')) {
            return redirect()->route('site.webshop.categories.index')
                ->with('error', 'Ehhez a rendeléshez már minden tételre beérkezett elállási nyilatkozat.');
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
            return redirect()->route('site.webshop.categories.index')->with('error', self::INVALID_LINK_MESSAGE);
        }

        $order = WebshopWithdrawalService::findOrderByNumber($orderNumber);

        if (!$order) {
            return redirect()->route('site.webshop.categories.index')->with('error', self::INVALID_LINK_MESSAGE);
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
            'quantities.*.max' => 'A megadott mennyiség több, mint amennyitől még el lehet állni.',
        ], ['reason' => 'megjegyzés']);

        $quantities = array_filter(
            array_map('intval', $validated['quantities']),
            function ($quantity) {
                return $quantity > 0;
            }
        );

        if (empty($quantities)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Legalább egy terméket ki kell választani.');
        }

        $withdrawal = WebshopWithdrawalService::create($order, $quantities, $validated['reason'] ?? null);

        $this->sendNotifications($withdrawal);

        return redirect()->to(WebshopWithdrawalService::formUrl($order))
            ->with('withdrawal_success', 'Elállási nyilatkozatodat megkaptuk. A visszaigazolást elküldtük e-mailben.');
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
                Mail::to($recipient)->send(new WebshopWithdrawalMail($withdrawal, $isShopCopy));
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

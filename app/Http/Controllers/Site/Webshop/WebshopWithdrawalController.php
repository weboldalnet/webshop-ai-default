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
 * Elállási kérelem a site oldalon.
 *
 * A vásárló bejelentkezés nélkül, a rendelésszáma alapján állhat el – ez a
 * rendelésszám egyben az azonosítója is, ezért a keresést kívülről korlátozzuk
 * (throttle), hogy ne lehessen rendelésszámokat próbálgatni.
 */
class WebshopWithdrawalController extends Controller
{
    /**
     * Rendelésszám ellenőrzése a láblécben lévő modalból (AJAX).
     */
    public function lookup(Request $request)
    {
        $request->validate([
            'order_number' => 'required|string|max:64',
        ], [], ['order_number' => 'rendelésszám']);

        $order = WebshopWithdrawalService::findOrderByNumber($request->input('order_number'));

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Nem található ilyen rendelésszám. Kérjük, ellenőrizd a visszaigazoló e-mailben.',
            ]);
        }

        if (!WebshopWithdrawalService::hasRemainingItems($order)) {
            return response()->json([
                'success' => false,
                'message' => 'Ehhez a rendeléshez már minden tételre nyújtottak be elállási kérelmet.',
            ]);
        }

        return response()->json([
            'success' => true,
            'redirect' => route('site.webshop.withdrawals.create', ['orderNumber' => $order->order_number]),
        ]);
    }

    /**
     * Elállási űrlap a rendelés tételeivel.
     */
    public function create($orderNumber)
    {
        $order = WebshopWithdrawalService::findOrderByNumber($orderNumber);

        if (!$order) {
            return redirect()->route('site.webshop.categories.index')
                ->with('error', 'Nem található ilyen rendelésszám.');
        }

        $remaining = WebshopWithdrawalService::remainingQuantities($order);

        if (array_sum($remaining) < 1) {
            return redirect()->route('site.webshop.categories.index')
                ->with('error', 'Ehhez a rendeléshez már minden tételre nyújtottak be elállási kérelmet.');
        }

        return view('site.webshop.withdrawals.create', [
            'order' => $order,
            'remaining' => $remaining,
            'showPrices' => WebshopSettingsService::getBool('site_product_prices_visible', true),
        ]);
    }

    /**
     * Elállási kérelem beküldése.
     */
    public function store(Request $request, $orderNumber)
    {
        $order = WebshopWithdrawalService::findOrderByNumber($orderNumber);

        if (!$order) {
            return redirect()->route('site.webshop.categories.index')
                ->with('error', 'Nem található ilyen rendelésszám.');
        }

        $remaining = WebshopWithdrawalService::remainingQuantities($order);

        $rules = [
            'reason' => 'required|string|min:10|max:5000',
            'quantities' => 'required|array',
        ];

        // Tételenként a még elállható mennyiség a felső korlát
        foreach ($remaining as $orderItemId => $max) {
            $rules['quantities.' . $orderItemId] = 'nullable|integer|min:0|max:' . $max;
        }

        $validated = $request->validate($rules, [
            'reason.required' => 'Az elállás indoklása kötelező.',
            'reason.min' => 'Az indoklás legyen legalább 10 karakter.',
            'quantities.*.max' => 'A megadott mennyiség több, mint amennyitől még el lehet állni.',
        ], ['reason' => 'indoklás']);

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

        $withdrawal = WebshopWithdrawalService::create($order, $quantities, $validated['reason']);

        $this->sendNotifications($withdrawal);

        return redirect()->route('site.webshop.withdrawals.create', ['orderNumber' => $order->order_number])
            ->with('withdrawal_success', 'Elállási kérelmét megkaptuk. A visszaigazolást elküldtük e-mailben.');
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
                Log::error('Elállási kérelem e-mail küldése sikertelen.', [
                    'withdrawal_id' => $withdrawal->id,
                    'recipient' => $recipient,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}

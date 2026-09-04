<?php

namespace Weboldalnet\WebshopAiDefault\Http\Controllers\Site\Webshop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use \Illuminate\Support\Facades\Mail;
use Weboldalnet\WebshopAiDefault\Mail\WebshopOrderMail;
use Weboldalnet\WebshopAiDefault\Models\WebshopOrder;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopCartService;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopCheckoutQaService;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopCheckoutService;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopContentService;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopSettingsService;
use Weboldalnet\WebshopAiDefault\Services\Webshop\Commerce\WebshopCommerceService;

class WebshopCheckoutController extends Controller
{
    public function index()
    {
        $items = WebshopCartService::getContent();
        if (empty($items)) {
            return redirect()->route('site.webshop.categories.index')->with('error', 'A kosár üres.');
        }

        $checkoutMode = WebshopSettingsService::get('site_checkout_mode', 'order');
        $isQuoteMode = $checkoutMode === WebshopOrder::TYPE_QUOTE;

        $paymentMethods = [];
        $shippingMethods = [];

        $onSitePaymentEnabled = false;
        if (!$isQuoteMode) {
            $paymentMethods = WebshopCommerceService::getAvailablePaymentMethods();
            $shippingMethods = WebshopCommerceService::getAvailableShippingMethods();
            $onSitePaymentEnabled = WebshopSettingsService::getBool('site_checkout_payment_on_site_enabled')
                && WebshopSettingsService::getBool('site_checkout_payment_options_enabled')
                && WebshopSettingsService::getBool('site_checkout_shipping_pickup_enabled');
        }

        $cartTotal = WebshopCartService::getTotal();

        // A pénztár összesítője a szállítási módtól függően él: minden elérhető
        // módra előre kiszámoljuk a díjat, így a választáskor nem kell szerverhez
        // fordulni. Ugyanaz a forrás, mint amivel a rendelés is készül
        // (WebshopCheckoutService), tehát a kiírt és a felszámított díj nem térhet el.
        $shippingRates = [];
        foreach (array_keys($shippingMethods) as $code) {
            $shippingRates[$code] = WebshopCommerceService::calculateShippingCost($code, $cartTotal);
        }

        return view('site.webshop.checkout.index', [
            'items' => $items,
            'total' => $cartTotal,
            'shippingRates' => $shippingRates,
            // Utánvét-felár. Jelenleg nincs ilyen beállítás a rendszerben, ezért
            // alapból 0 – az összesítő ilyenkor nem is írja ki a sort.
            'codFee' => (float) WebshopSettingsService::get('site_checkout_payment_cod_fee', 0),
            'paymentMethods' => $paymentMethods,
            'shippingMethods' => $shippingMethods,
            'isQuoteMode' => $isQuoteMode,
            'onSitePaymentEnabled' => $onSitePaymentEnabled,
            // Átvevőpontos szállítási módok – ezeknél kell átvevőpont-választó.
            // Provider-független: a GLS és a FoxPost is ide tartozik.
            'parcelShopCodes' => WebshopCommerceService::getParcelShopMethodCodes(),
            // Csoportosított szállítási módok: előbb a kézbesítés módja, azon belül a futárszolgálat
            'shippingGroups' => WebshopCommerceService::getAvailableShippingMethodsGrouped(),
            'glsParcelShopCode' => config('commerce-gls.parcel_shop_code'),
            'glsCountry' => config('commerce-gls.country', 'hu'),
            'foxpostCode' => config('commerce-foxpost.provider_code'),
            // A pénztárhoz beállított kérdőív (nincs beállítva => null, a blokk kimarad)
            'checkoutQa' => WebshopCheckoutQaService::getCheckoutQa(),
        ]);
    }

    public function store(Request $request)
    {
        $items = WebshopCartService::getContent();
        if (empty($items)) {
            return redirect()->route('site.webshop.categories.index')->with('error', 'A kosár üres.');
        }

        $checkoutMode = WebshopSettingsService::get('site_checkout_mode', 'order');
        $isQuoteMode = $checkoutMode === WebshopOrder::TYPE_QUOTE;

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ];

        if (WebshopSettingsService::getBool('site_checkout_phone_enabled')) $rules['phone'] = 'required|string|max:20';
        if (WebshopSettingsService::getBool('site_checkout_company_enabled')) $rules['company'] = 'required|string|max:255';
        if (WebshopSettingsService::getBool('site_checkout_tax_number_enabled')) $rules['tax_number'] = 'required|string|max:20';

        if (WebshopSettingsService::getBool('site_checkout_billing_enabled')) {
            $isPickup = $request->input('shipping_method') === 'pickup';
            if ($isPickup) {
                // Személyes átvétel esetén a számlázási adatok kötelezőek
                $rules['billing.name'] = 'required|string|max:255';
                $rules['billing.zip'] = 'required|string|max:10';
                $rules['billing.city'] = 'required|string|max:100';
                $rules['billing.address'] = 'required|string|max:255';
                $request->merge(['billing_same_as_shipping' => null]);
            } else {
                if (!$request->has('billing_same_as_shipping')) {
                    $rules['billing.name'] = 'required|string|max:255';
                    $rules['billing.zip'] = 'required|string|max:10';
                    $rules['billing.city'] = 'required|string|max:100';
                    $rules['billing.address'] = 'required|string|max:255';
                }
            }
        }

        if (WebshopSettingsService::getBool('site_checkout_shipping_enabled')) {
            // Csomagpontos szállításnál sincs saját szállítási cím – a csomag az
            // átvevőpontra megy, azt a választó tölti ki.
            $isPickup = $request->input('shipping_method') === 'pickup'
                || WebshopCommerceService::isParcelShopMethod($request->input('shipping_method'));
            if (!$isPickup) {
                $rules['shipping.zip'] = 'required|string|max:10';
                $rules['shipping.city'] = 'required|string|max:100';
                $rules['shipping.address'] = 'required|string|max:255';
            }
        }

        if (!$isQuoteMode) {
            $availablePaymentMethods = array_keys(WebshopCommerceService::getAvailablePaymentMethods());
            $availableShippingMethods = array_keys(WebshopCommerceService::getAvailableShippingMethods());

            // "Fizetés a helyszínen" engedélyezése, ha be van kapcsolva és pickup szállítás érkezett
            $onSiteEnabled = WebshopSettingsService::getBool('site_checkout_payment_on_site_enabled')
                && WebshopSettingsService::getBool('site_checkout_payment_options_enabled')
                && WebshopSettingsService::getBool('site_checkout_shipping_pickup_enabled')
                && $request->input('shipping_method') === 'pickup';
            if ($onSiteEnabled && !in_array('on_site', $availablePaymentMethods)) {
                $availablePaymentMethods[] = 'on_site';
            }

            $rules['payment_method'] = 'required|string|in:' . implode(',', $availablePaymentMethods);
            if (!empty($availableShippingMethods)) {
                $rules['shipping_method'] = 'nullable|string|in:' . implode(',', $availableShippingMethods);
            }

            // Csomagpontos szállításnál kötelező a kiválasztott átvevőpont azonosítója,
            // e nélkül a futárszolgálati címke nem generálható.
            if (WebshopCommerceService::isParcelShopMethod($request->input('shipping_method'))) {
                $rules['shipping.parcel_shop_id'] = 'required|string|max:64';
            }
        }

        if (($wsTos = WebshopSettingsService::getBool('site_checkout_tos_enabled')) && !$request->boolean('accept_tos')) {
            return back()->withInput()->withErrors(['accept_tos' => 'Kérjük, fogadja el az Általános Szerződési Feltételeket.']);
        }
        if (($wsPrivacy = WebshopSettingsService::getBool('site_checkout_privacy_enabled')) && !$request->boolean('accept_privacy')) {
            return back()->withInput()->withErrors(['accept_privacy' => 'Kérjük, fogadja el az Adatvédelmi tájékoztatót.']);
        }

        $request->validate($rules);

        try {
            $checkoutService = new WebshopCheckoutService();
            $result = $checkoutService->process($request->all(), $items);
            $order = $result['order'];
            $commerceResult = $result['commerceResult'];

            // A rendelés adatlapja csak annak nyílik meg, aki le is adta.
            self::rememberOwnOrder($order);

            WebshopCartService::clear();

            // Online fizetés esetén redirect a fizetési oldalra
            if ($commerceResult && !empty($commerceResult['requiresRedirect']) && !empty($commerceResult['redirectUrl'])) {
                return redirect()->away($commerceResult['redirectUrl']);
            }

            // Ha commerce hiba volt, de rendelés létrejött
            if ($commerceResult && !$commerceResult['success']) {
                Log::warning('Checkout: Commerce payment sikertelen, de rendelés létrejött.', [
                    'order_id' => $order->id,
                    'message' => $commerceResult['message'] ?? '',
                ]);
                return redirect()->route('site.webshop.payment.result', $order)
                    ->with('warning', 'A rendelés létrejött, de a fizetés indítása sikertelen volt. Kérjük, próbálja újra.');
            }

            // Email küldése
            if ($order->customer_email) {
                try {
                    Mail::to($order->customer_email)->send(new WebshopOrderMail($order));
                } catch (\Exception $e) {
                    Log::error('Rendelés visszaigazoló email hiba: ' . $e->getMessage());
                }
            }

            return redirect()->route('site.webshop.checkout.success', $order)->with('success', 'Rendelés sikeresen leadva.');
        } catch (\Throwable $e) {
            Log::error('Checkout hiba: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Hiba történt a rendelés feldolgozása során. Kérjük, próbálja újra.');
        }
    }

    /** A munkamenetben megjegyzett saját rendelések kulcsa */
    const OWN_ORDERS_SESSION_KEY = 'ws_own_orders';

    /**
     * A leadott rendelés megjegyzése, hogy az adatlapja megnyitható legyen.
     */
    public static function rememberOwnOrder(WebshopOrder $order): void
    {
        $ids = session(self::OWN_ORDERS_SESSION_KEY, []);

        if (!in_array($order->id, $ids, true)) {
            $ids[] = $order->id;
            session([self::OWN_ORDERS_SESSION_KEY => $ids]);
        }
    }

    /**
     * A rendelés adatlapjai (visszaigazoló és fizetési eredmény) személyes
     * adatokat mutatnak: nevet, címet, telefonszámot, kérdőív-válaszokat.
     *
     * A rendelésazonosítók sorszámozottak, ezért ellenőrzés nélkül bárki
     * végigpörgethetné az összes rendelést. Csak az láthatja, aki le is adta
     * (munkamenet), vagy aki aláírt hivatkozást kapott (későbbi e-mailes
     * linkekhez), illetve a bejelentkezett admin.
     */
    protected function authorizeOrderView(WebshopOrder $order): void
    {
        if (in_array($order->id, session(self::OWN_ORDERS_SESSION_KEY, []), true)) {
            return;
        }

        if (request()->hasValidSignature()) {
            return;
        }

        if (auth('admin')->check()) {
            return;
        }

        abort(404);
    }

    public function success(WebshopOrder $order)
    {
        $this->authorizeOrderView($order);
        $customContent = WebshopContentService::getContent('thank_you', $order);
        $showPrices = WebshopSettingsService::getBool('site_product_prices_visible', true);

        return view('site.webshop.checkout.success', compact('order', 'customContent', 'showPrices'));
    }

    /**
     * Fizetési eredmény oldal (online fizetés visszatérése után).
     */
    public function paymentResult(WebshopOrder $order, Request $request)
    {
        $this->authorizeOrderView($order);

        // Ha online fizetés volt, megpróbáljuk szinkronizálni az állapotot a visszatéréskor
        if (WebshopCommerceService::isAvailable() && $order->payment_method) {
            try {
                $processor = app(\Weboldalnet\CommerceCore\Services\PaymentCallbackProcessor::class);
                // A Barion a query paraméterekben küldi a paymentId-t
                $processor->process($order->payment_method, $request->all());
            } catch (\Exception $e) {
                Log::error('Webshop paymentResult sync error: ' . $e->getMessage(), [
                    'order_id' => $order->id,
                    'payment_method' => $order->payment_method,
                ]);
            }
        }

        $order->refresh();
        return view('site.webshop.checkout.payment-result', compact('order'));
    }

    /**
     * A rendelés fizetési állapota JSON-ban, a fizetési eredmény oldal lekérdezéséhez.
     *
     * Ha a fizetés függőben van, meg is próbáljuk aktualizálni: megkérdezzük a
     * providert a tranzakció valódi állapotáról. Erre azért van szükség, mert az
     * IPN nem mindig ér el minket (fejlesztői gép), vagy késik – enélkül a
     * vásárló egy már teljesült fizetésnél is a "Fizetés folyamatban" szövegen ragadna.
     */
    public function paymentStatus(WebshopOrder $order)
    {
        $this->authorizeOrderView($order);

        if (!$order->isPaid()) {
            $this->refreshPendingPayment($order);
            $order->refresh();
        }

        return response()->json([
            'paid' => $order->isPaid(),
            'failed' => $order->isPaymentFailed(),
            'cancelled' => $order->isPaymentCancelled(),
            'pending' => $order->isPaymentPending(),
            'status' => $order->payment_status,
            'status_label' => $order->payment_status_label,
            'paid_at' => $order->paid_at ? $order->paid_at->format('Y.m.d H:i') : null,
            'order_status_label' => $order->status_label,
        ]);
    }

    /**
     * Függőben lévő fizetés állapotának lekérdezése a providertől.
     *
     * A queryStatus() nem része a PaymentProvider szerződésnek, ezért
     * method_exists()-tel nézzük meg – amelyik integráció nem tudja, ott
     * egyszerűen nem történik semmi. A tényleges feldolgozás (tranzakció
     * frissítése, eventek) ugyanazon a commerce-core úton megy, mint az IPN-é.
     */
    protected function refreshPendingPayment(WebshopOrder $order): void
    {
        if (!WebshopCommerceService::isAvailable() || !$order->payment_method) {
            return;
        }

        try {
            $manager = app(\Weboldalnet\CommerceCore\Managers\PaymentManager::class);
            $provider = $manager->getProvider($order->payment_method);

            if (!$provider || !method_exists($provider, 'queryStatus')) {
                return;
            }

            // A teljes tranzakciót adjuk át, mert providerenként MÁS mező az
            // azonosító: a SimplePay a mi orderRef-ünkre (transaction_id) kérdez,
            // a Barion a saját PaymentId-jára (provider_transaction_id). Ezért a
            // szűrés is csak azt nézi, hogy legyen VALAMELYIK azonosító.
            $transaction = \Weboldalnet\CommerceCore\Models\PaymentTransaction::where('order_id', $order->id)
                ->where('provider', $order->payment_method)
                ->where(function ($query) {
                    // Postgresben a NULL != '' nem igaz, ezért a null-t külön kizárjuk,
                    // különben a csak provider-azonosítóval rendelkező sor is kiesne.
                    $query->where(function ($q) {
                        $q->whereNotNull('transaction_id')->where('transaction_id', '!=', '');
                    })->orWhere(function ($q) {
                        $q->whereNotNull('provider_transaction_id')->where('provider_transaction_id', '!=', '');
                    });
                })
                ->orderByDesc('id')
                ->first();

            if (!$transaction) {
                return;
            }

            $result = $provider->queryStatus($transaction);
            if (!$result) {
                return;
            }

            app(\Weboldalnet\CommerceCore\Services\PaymentCallbackProcessor::class)
                ->processResult($order->payment_method, $result, ['source' => 'payment_status_poll']);
        } catch (\Throwable $e) {
            // A lekérdezés kiesése nem hiba a vásárló felé: marad a tárolt állapot.
            Log::warning('Webshop paymentStatus lekérdezés sikertelen: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'payment_method' => $order->payment_method,
            ]);
        }
    }
    /**
     * Fizetés újrapróbálása.
     */
    public function retryPayment(WebshopOrder $order)
    {
        $this->authorizeOrderView($order);

        if (!$order->isPaymentRetryable()) {
            return redirect()->route('site.webshop.checkout.success', $order)
                ->with('error', 'Ez a rendelés nem próbálható újra.');
        }

        try {
            $order->update(['payment_status' => WebshopOrder::PAYMENT_STATUS_PENDING]);
            $order->load('items');
            $commerceResult = WebshopCommerceService::processOrderPayment($order);

            if ($commerceResult['requiresRedirect'] && !empty($commerceResult['redirectUrl'])) {
                $order->update([
                    'payment_status' => WebshopOrder::PAYMENT_STATUS_PENDING,
                    'commerce_payment_transaction_id' => $commerceResult['transactionId'] ?? $order->commerce_payment_transaction_id,
                ]);
                return redirect()->away($commerceResult['redirectUrl']);
            }

            $order->update([
                'payment_status' => $commerceResult['paymentStatus'] ?? WebshopOrder::PAYMENT_STATUS_PENDING,
                'status' => $commerceResult['success']
                    ? ($commerceResult['orderStatusSuggestion'] ?? WebshopOrder::STATUS_PROCESSING)
                    : $order->status,
            ]);

            return redirect()->route('site.webshop.payment.result', $order)
                ->with($commerceResult['success'] ? 'success' : 'error',
                    $commerceResult['message'] ?? ($commerceResult['success'] ? 'Fizetés elindítva.' : 'Fizetés újraindítása sikertelen.'));
        } catch (\Throwable $e) {
            Log::error('Retry payment hiba: ' . $e->getMessage(), ['order_id' => $order->id]);
            return redirect()->route('site.webshop.payment.result', $order)
                ->with('error', 'Hiba történt a fizetés újraindításakor.');
        }
    }
}

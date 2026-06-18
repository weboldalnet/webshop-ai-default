<?php

namespace Weboldalnet\WebshopAiDefault\Http\Controllers\Site\Webshop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use \Illuminate\Support\Facades\Mail;
use Weboldalnet\WebshopAiDefault\Mail\WebshopOrderMail;
use Weboldalnet\WebshopAiDefault\Models\WebshopOrder;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopCartService;
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

        return view('site.webshop.checkout.index', [
            'items' => $items,
            'total' => WebshopCartService::getTotal(),
            'paymentMethods' => $paymentMethods,
            'shippingMethods' => $shippingMethods,
            'isQuoteMode' => $isQuoteMode,
            'onSitePaymentEnabled' => $onSitePaymentEnabled,
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
            $isPickup = $request->input('shipping_method') === 'pickup';
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

            return redirect()->route('site.webshop.checkout.success', $order)->with('success', 'Rendelés sikeresen leadva.');
        } catch (\Throwable $e) {
            Log::error('Checkout hiba: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Hiba történt a rendelés feldolgozása során. Kérjük, próbálja újra.');
        }
    }

    public function success(WebshopOrder $order)
    {
        // Email küldése
        if ($order->customer_email) {
            try {
                Mail::to($order->customer_email)->send(new WebshopOrderMail($order));
            } catch (\Exception $e) {
                Log::error('Rendelés visszaigazoló email hiba: ' . $e->getMessage());
            }
        }

        $customContent = WebshopContentService::getContent('thank_you', $order);
        $showPrices = WebshopSettingsService::getBool('site_show_prices', true);

        return view('site.webshop.checkout.success', compact('order', 'customContent', 'showPrices'));
    }

    /**
     * Fizetési eredmény oldal (online fizetés visszatérése után).
     */
    public function paymentResult(WebshopOrder $order)
    {
        $order->refresh();
        return view('site.webshop.checkout.payment-result', compact('order'));
    }

    /**
     * Fizetés újrapróbálása.
     */
    public function retryPayment(WebshopOrder $order)
    {
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

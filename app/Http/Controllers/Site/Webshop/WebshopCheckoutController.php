<?php

namespace Weboldalnet\WebshopAiDefault\Http\Controllers\Site\Webshop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Weboldalnet\WebshopAiDefault\Models\WebshopOrder;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopCartService;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopCheckoutService;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopSettingsService;

class WebshopCheckoutController extends Controller
{
    public function index()
    {
        $items = WebshopCartService::getContent();
        if (empty($items)) {
            return redirect()->route('site.webshop.categories.index')->with('error', 'A kosár üres.');
        }

        return view('site.webshop.checkout.index', [
            'items' => $items,
            'total' => WebshopCartService::getTotal(),
            'ws' => WebshopSettingsService::all()
        ]);
    }

    public function store(Request $request)
    {
        $items = WebshopCartService::getContent();
        if (empty($items)) {
            return redirect()->route('site.webshop.categories.index')->with('error', 'A kosár üres.');
        }

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ];

        if (WebshopSettingsService::getBool('site_checkout_phone_enabled')) $rules['phone'] = 'required|string|max:20';
        if (WebshopSettingsService::getBool('site_checkout_company_enabled')) $rules['company'] = 'required|string|max:255';
        if (WebshopSettingsService::getBool('site_checkout_tax_number_enabled')) $rules['tax_number'] = 'required|string|max:20';

        if (WebshopSettingsService::getBool('site_checkout_billing_enabled')) {
            $rules['billing.zip'] = 'required|string|max:10';
            $rules['billing.city'] = 'required|string|max:100';
            $rules['billing.address'] = 'required|string|max:255';
        }

        if (WebshopSettingsService::getBool('site_checkout_shipping_enabled')) {
            $rules['shipping.zip'] = 'required|string|max:10';
            $rules['shipping.city'] = 'required|string|max:100';
            $rules['shipping.address'] = 'required|string|max:255';
        }

        $request->validate($rules);

        $checkoutService = new WebshopCheckoutService();
        $order = $checkoutService->process($request->all(), $items);

        WebshopCartService::clear();

        return redirect()->route('site.webshop.checkout.success', $order)->with('success', 'Rendelés sikeresen leadva.');
    }

    public function success(WebshopOrder $order)
    {
        return view('site.webshop.checkout.success', compact('order'));
    }
}

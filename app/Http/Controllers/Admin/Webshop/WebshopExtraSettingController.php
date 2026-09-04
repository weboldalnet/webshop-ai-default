<?php

namespace Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop;

use App\Http\Controllers\Admin\AdminExtendedController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Weboldalnet\WebshopAiDefault\Models\WebshopCustomContent;
use Weboldalnet\WebshopAiDefault\Models\WebshopTrackingScript;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopCheckoutQaService;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopSettingsService;
use Weboldalnet\WebshopAiDefault\Services\Webshop\Commerce\WebshopCommerceService;

class WebshopExtraSettingController extends AdminExtendedController
{
    /** A pénztári értesítő dobozok Bootstrap alert-változatai */
    const NOTICE_TYPES = [
        'info' => 'Info (kék)',
        'success' => 'Siker (zöld)',
        'warning' => 'Figyelmeztetés (sárga)',
        'danger' => 'Veszély (piros)',
        'primary' => 'Elsődleges',
        'secondary' => 'Másodlagos',
        'light' => 'Világos',
        'dark' => 'Sötét',
    ];

    public function index()
    {
        return view('admin.webshop.extra-settings.index');
    }

    public function customContents(Request $request)
    {
        $type = $request->query('type', 'email');
        $contents = WebshopCustomContent::byType($type)->get();
        $paymentMethods = WebshopCommerceService::getAvailablePaymentMethods(true);
        $shippingMethods = WebshopCommerceService::getAvailableShippingMethods();

        return view('admin.webshop.extra-settings.custom-contents', [
            'type' => $type,
            'contents' => $contents,
            'paymentMethods' => $paymentMethods,
            'shippingMethods' => $shippingMethods,
            'ws' => WebshopSettingsService::all()
        ]);
    }

    public function storeCustomContent(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|string|in:email,thank_you',
            'checkout_mode' => 'nullable|string|in:order,quote',
            'payment_method' => 'nullable|string',
            'shipping_method' => 'nullable|string',
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $data['is_active'] = $request->has('is_active');

        WebshopCustomContent::updateOrCreate(
            [
                'type' => $data['type'],
                'checkout_mode' => $data['checkout_mode'],
                'payment_method' => $data['payment_method'],
                'shipping_method' => $data['shipping_method'],
            ],
            $data
        );

        return redirect()->back()->with('success', 'Tartalom elmentve.');
    }

    public function documents()
    {
        return view('admin.webshop.extra-settings.documents', [
            'ws' => WebshopSettingsService::all()
        ]);
    }

    public function storeDocument(Request $request)
    {
        $request->validate([
            'tos_label' => 'nullable|string|max:255',
            'tos_url' => 'nullable|string|max:255',
            'tos_file' => 'nullable|file|max:5120',
            'privacy_label' => 'nullable|string|max:255',
            'privacy_url' => 'nullable|string|max:255',
            'privacy_file' => 'nullable|file|max:5120',
        ]);

        $settings = [
            'site_checkout_tos_enabled' => $request->has('site_checkout_tos_enabled') ? 'true' : 'false',
            'site_checkout_privacy_enabled' => $request->has('site_checkout_privacy_enabled') ? 'true' : 'false',
            'site_checkout_tos_label' => $request->input('tos_label'),
            'site_checkout_tos_url' => $request->input('tos_url'),
            'site_checkout_privacy_label' => $request->input('privacy_label'),
            'site_checkout_privacy_url' => $request->input('privacy_url'),
        ];

        if ($request->hasFile('tos_file')) {
            $path = $request->file('tos_file')->storeAs('webshop/documents', 'aszf.' . $request->file('tos_file')->getClientOriginalExtension(), 'public');
            $settings['site_checkout_tos_path'] = '/storage/' . $path;
        }

        if ($request->hasFile('privacy_file')) {
            $path = $request->file('privacy_file')->storeAs('webshop/documents', 'adatvedelem.' . $request->file('privacy_file')->getClientOriginalExtension(), 'public');
            $settings['site_checkout_privacy_path'] = '/storage/' . $path;
        }

        WebshopSettingsService::save($settings);

        return redirect()->back()->with('success', 'Dokumentumok elmentve.');
    }

    /**
     * Pénztár kiegészítések: kérdőív választása és a két értesítő doboz.
     */
    public function checkoutExtras()
    {
        return view('admin.webshop.extra-settings.checkout-extras', [
            'ws' => WebshopSettingsService::all(),
            'qaSupported' => WebshopCheckoutQaService::isSupported(),
            'qaList' => WebshopCheckoutQaService::getSelectableQas(),
            'noticeTypes' => self::NOTICE_TYPES,
        ]);
    }

    public function storeCheckoutExtras(Request $request)
    {
        $request->validate([
            'site_checkout_qa_id' => 'nullable|integer',
            'site_checkout_notice_top_content' => 'nullable|string',
            'site_checkout_notice_bottom_content' => 'nullable|string',
        ]);

        // Az alert-változat adatbázisból kerül vissza a site-ra és osztálynévbe,
        // ezért csak az ismert értékeket engedjük be.
        $allowedTypes = array_keys(self::NOTICE_TYPES);
        $topType = $request->input('site_checkout_notice_top_type');
        $bottomType = $request->input('site_checkout_notice_bottom_type');

        WebshopSettingsService::save([
            'site_checkout_qa_id' => $request->input('site_checkout_qa_id') ?: '',
            'site_checkout_notice_top_type' => in_array($topType, $allowedTypes, true) ? $topType : 'info',
            'site_checkout_notice_top_content' => $request->input('site_checkout_notice_top_content') ?? '',
            'site_checkout_notice_bottom_type' => in_array($bottomType, $allowedTypes, true) ? $bottomType : 'info',
            'site_checkout_notice_bottom_content' => $request->input('site_checkout_notice_bottom_content') ?? '',
        ]);

        return redirect()->back()->with('success', 'Pénztár kiegészítések elmentve.');
    }

    public function scripts()
    {
        return view('admin.webshop.extra-settings.scripts', [
            'scripts' => WebshopTrackingScript::ordered()->get(),
            'pageTypes' => [
                'homepage' => 'Főoldal',
                'product_list' => 'Termék lista oldal',
                'product' => 'Termék oldal',
                'checkout' => 'Checkout oldal',
                'thank_you' => 'Köszönjük oldal',
            ]
        ]);
    }

    public function storeScript(Request $request)
    {
        $data = $request->validate([
            'page_type' => 'required|string',
            'name' => 'nullable|string|max:255',
            'script' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->has('is_active');
        $data['sort_order'] = (WebshopTrackingScript::max('sort_order') ?? 0) + 1;

        WebshopTrackingScript::create($data);

        return redirect()->back()->with('success', 'Script hozzáadva.');
    }

    public function updateScript(Request $request, WebshopTrackingScript $script)
    {
        $data = $request->validate([
            'page_type' => 'required|string',
            'name' => 'nullable|string|max:255',
            'script' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->has('is_active');

        $script->update($data);

        return redirect()->back()->with('success', 'Script módosítva.');
    }

    public function destroyScript(WebshopTrackingScript $script)
    {
        $script->delete();
        return redirect()->back()->with('success', 'Script törölve.');
    }
}

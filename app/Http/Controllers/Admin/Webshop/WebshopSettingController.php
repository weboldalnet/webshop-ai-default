<?php

namespace Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop;

use App\Http\Controllers\Admin\AdminExtendedController;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopSettingsService;
use Weboldalnet\WebshopAiDefault\Services\Webshop\Commerce\WebshopCommerceService;
use Weboldalnet\WebshopAiDefault\Services\Webshop\Commerce\WebshopInvoiceRequestFactory;
use Illuminate\Http\Request;

class WebshopSettingController extends AdminExtendedController
{
    public function index()
    {
        return view('admin.webshop.settings.index', [
            'ws' => WebshopSettingsService::all(),
            'paymentMethods' => WebshopCommerceService::getAllPaymentMethodLabels(),
            // Fizetési és szállítási integrációk: a listák a commerce-core
            // provider-nyilvántartásából jönnek, a kikapcsolt modulokkal együtt –
            // különben nem lenne honnan visszakapcsolni őket.
            'onlinePaymentProviders' => WebshopCommerceService::getOnlinePaymentProviders(),
            'shippingGroups' => WebshopCommerceService::getShippingGroups(),
            // Számlázás: a telepített integrációk listája a commerce-core-ból jön,
            // így egy új számlázó csomag külön módosítás nélkül megjelenik.
            'invoiceProviders' => WebshopCommerceService::getAvailableInvoiceProviders(),
            'invoicingProvider' => WebshopCommerceService::getInvoicingProvider(),
            'invoicingEnabled' => WebshopCommerceService::isInvoicingEnabled(),
            // A számla extra tételei (szállítás, utánvét, kupon) – a lista a
            // factory konstansából jön, így egy új tétel itt sem igényel módosítást.
            'invoiceExtraLines' => WebshopInvoiceRequestFactory::EXTRA_LINES,
            'invoiceVatKeys' => WebshopInvoiceRequestFactory::VAT_KEYS,
            'invoiceVatKey' => WebshopInvoiceRequestFactory::vatKey(),
        ]);
    }

    public function update(Request $request)
    {
        $checkboxKeys = [
            'category_icon_enabled', 'category_parent_enabled', 'category_related_enabled',
            // Egyedi kategória menüsor (csak többszintes kategóriarendszerrel értelmes)
            'category_custom_menu_enabled',
            'category_product_card_properties_enabled', 'product_stock_enabled', 'product_related_enabled',
            'product_price_enabled', 'product_gallery_enabled', 'product_variations_enabled',
            'admin_product_labels_enabled',
            'site_category_view_switcher_enabled', 'site_related_products_modal_enabled',
            'site_product_reviews_enabled', 'site_product_prices_visible',
            'site_checkout_phone_enabled', 'site_checkout_company_enabled',
            'site_checkout_tax_number_enabled', 'site_checkout_billing_enabled',
            'site_checkout_shipping_enabled', 'site_product_compare_enabled',
            'site_header_search_enabled',
            // Checkout fizetési és szállítási lehetőségek
            'site_checkout_payment_options_enabled',
            'site_checkout_payment_online_enabled',
            'site_checkout_payment_cod_enabled',
            'site_checkout_payment_bank_transfer_enabled',
            'site_checkout_payment_on_site_enabled',
            'site_checkout_shipping_options_enabled',
            'site_checkout_shipping_home_delivery_enabled',
            'site_checkout_shipping_parcel_locker_enabled',
            'site_checkout_shipping_pickup_enabled',
            // Új beállítások
            'product_extra_gallery_enabled',
            'product_document_upload_enabled',
            'product_secondary_name_enabled',
            'product_short_desc_instead_of_properties_enabled',
            'product_crm_id_enabled',
            'category_sizing_enabled',
            'category_list_image_enabled',
            'category_merchant_feed_enabled',
            // Számlázás főkapcsoló
            'invoicing_enabled',
            // Kupon modul főkapcsoló
            'coupons_enabled',
        ];

        // Dinamikusan hozzáadjuk az online fizetési módok checkbox kulcsait.
        // A telepített (akár épp kikapcsolt) integrációk is szerepelnek, mert a
        // beállítófelület is felkínálja őket.
        foreach (array_keys(WebshopCommerceService::getOnlinePaymentProviders()) as $code) {
            $checkboxKeys[] = 'site_checkout_payment_method_' . $code . '_enabled';
        }

        // Ugyanígy a szállítási csoportokon belüli futárszolgálatok kulcsait
        foreach (WebshopCommerceService::getShippingGroups() as $group) {
            foreach (array_keys($group['methods']) as $code) {
                $checkboxKeys[] = 'site_checkout_shipping_method_' . $code . '_enabled';
            }
        }

        // A számla extra tételei: tételenként egy be/ki kapcsoló és egy névmező.
        //
        // A kapcsolókat CSAK akkor dolgozzuk fel, ha az űrlapon ott volt a
        // jelölő mező. Egy be nem küldött checkbox ugyanis megkülönböztethetetlen
        // a kikapcsolttól: ha a blokk bármiért kimaradna a nézetből (pl. mert
        // egy feltétel köré kerül), minden mentés némán 'false'-ra állítaná
        // mind a hármat, és a számláról eltűnne a szállítási díj.
        if ($request->has('invoice_lines_present')) {
            foreach (array_keys(WebshopInvoiceRequestFactory::EXTRA_LINES) as $lineKey) {
                $checkboxKeys[] = 'invoice_line_' . $lineKey . '_enabled';
            }
        }

        $valueKeys = [
            'site_category_cards_per_row', 'site_checkout_mode', 'site_product_list_default_view',
            'admin_product_primary_image_mode',
            // Utánvét-felár: a pénztár összesítője és a rendelés végösszege is ebből dolgozik
            'site_checkout_payment_cod_fee',
        ];

        if ($request->has('invoice_lines_present')) {
            foreach (array_keys(WebshopInvoiceRequestFactory::EXTRA_LINES) as $lineKey) {
                $valueKeys[] = 'invoice_line_' . $lineKey . '_name';
            }
        }

        $settings = [];
        foreach ($checkboxKeys as $key) {
            $settings[$key] = $request->has($key) ? 'true' : 'false';
        }
        foreach ($valueKeys as $key) {
            $settings[$key] = $request->input($key);
        }

        // Az áfakulcsot NEM a $valueKeys ágon mentjük: egy ismeretlen érték
        // (elgépelés, kézzel összerakott POST) a számlázónál NÉMÁN 0% áfává
        // válna, mert a SzamlazzhuService is_numeric() vizsgálata a nem
        // numerikus kulcsot 0-nak veszi – egy "27%" 0 forint áfát adna.
        // Csak a felkínált kulcsok közül fogadunk el értéket; ismeretlennél
        // a korábbi beállítás marad érvényben.
        $vatKey = (string) $request->input(WebshopInvoiceRequestFactory::SETTING_VAT_KEY, '');
        if ($request->has('invoice_lines_present')
            && array_key_exists($vatKey, WebshopInvoiceRequestFactory::VAT_KEYS)) {
            $settings[WebshopInvoiceRequestFactory::SETTING_VAT_KEY] = $vatKey;
        }

        // A számlázó program rádiógombja nem érkezik be, ha egyetlen integráció
        // sincs telepítve – ilyenkor a korábbi választást ne írjuk felül null-lal.
        if ($request->filled('invoicing_provider')) {
            $settings['invoicing_provider'] = $request->input('invoicing_provider');
        }

        WebshopSettingsService::save($settings);
        return redirect()->route('admin.webshop.settings.index')->with('success', 'Beállítások sikeresen mentve.');
    }
}

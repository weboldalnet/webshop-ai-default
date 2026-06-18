<?php

namespace Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop;

use App\Http\Controllers\Admin\AdminExtendedController;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopSettingsService;
use Illuminate\Http\Request;

class WebshopSettingController extends AdminExtendedController
{
    public function index()
    {
        return view('admin.webshop.settings.index', ['ws' => WebshopSettingsService::all()]);
    }

    public function update(Request $request)
    {
        $checkboxKeys = [
            'category_icon_enabled', 'category_parent_enabled', 'category_related_enabled',
            'category_product_card_properties_enabled', 'product_stock_enabled', 'product_related_enabled',
            'product_price_enabled', 'product_gallery_enabled', 'product_variations_enabled',
            'admin_product_labels_enabled',
            'site_category_view_switcher_enabled', 'site_related_products_modal_enabled',
            'site_product_reviews_enabled', 'site_product_prices_visible',
            'site_checkout_phone_enabled', 'site_checkout_company_enabled',
            'site_checkout_tax_number_enabled', 'site_checkout_billing_enabled',
            'site_checkout_shipping_enabled', 'site_product_compare_enabled',
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
            'site_home_page_editor_enabled',
            'product_extra_gallery_enabled',
            'product_document_upload_enabled',
            'product_secondary_name_enabled',
            'product_short_desc_instead_of_properties_enabled',
            'product_crm_id_enabled',
            'category_sizing_enabled',
            'category_list_image_enabled',
            'category_merchant_feed_enabled',
        ];

        $valueKeys = [
            'site_category_cards_per_row', 'site_checkout_mode', 'site_product_list_default_view',
            'admin_product_primary_image_mode',
        ];

        $settings = [];
        foreach ($checkboxKeys as $key) {
            $settings[$key] = $request->has($key) ? 'true' : 'false';
        }
        foreach ($valueKeys as $key) {
            $settings[$key] = $request->input($key);
        }

        WebshopSettingsService::save($settings);
        return redirect()->route('admin.webshop.settings.index')->with('success', 'Beállítások sikeresen mentve.');
    }
}

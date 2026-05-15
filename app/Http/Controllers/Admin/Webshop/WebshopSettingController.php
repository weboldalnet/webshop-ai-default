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
        $keys = [
            'category_icon_enabled', 'category_parent_enabled', 'category_related_enabled',
            'category_product_card_properties_enabled', 'product_stock_enabled', 'product_related_enabled',
            'product_price_enabled', 'product_gallery_enabled', 'product_variations_enabled',
        ];
        $settings = [];
        foreach ($keys as $key) { $settings[$key] = $request->has($key) ? 'true' : 'false'; }
        WebshopSettingsService::save($settings);
        return redirect()->route('admin.webshop.settings.index')->with('success', 'Beállítások sikeresen mentve.');
    }
}

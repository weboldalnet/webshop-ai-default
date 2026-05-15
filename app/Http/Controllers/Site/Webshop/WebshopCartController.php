<?php

namespace Weboldalnet\WebshopAiDefault\Http\Controllers\Site\Webshop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopCartService;
use Weboldalnet\WebshopAiDefault\Services\Webshop\WebshopSettingsService;
use Weboldalnet\WebshopAiDefault\Models\WebshopProduct;

class WebshopCartController extends Controller
{
    public function add(Request $request)
    {
        $productId = $request->input('product_id');
        $quantity = (int) $request->input('quantity', 1);

        WebshopCartService::add($productId, $quantity);

        $product = WebshopProduct::find($productId);
        $relatedHtml = '';
        if (WebshopSettingsService::getBool('site_related_products_modal_enabled') && $product && $product->relatedProducts->isNotEmpty()) {
            $relatedHtml = view('site.webshop.modals.related-products', ['product' => $product, 'ws' => WebshopSettingsService::all()])->render();
        }

        return response()->json([
            'success' => true,
            'message' => 'A termék sikeresen kosárba került.',
            'count' => WebshopCartService::getCount(),
            'total' => WebshopCartService::getTotal(),
            'related_html' => $relatedHtml,
        ]);
    }

    public function dropdown()
    {
        return response()->json([
            'success' => true,
            'html' => view('site.webshop.cart.dropdown', [
                'items' => WebshopCartService::getContent(),
                'total' => WebshopCartService::getTotal(),
                'ws' => WebshopSettingsService::all()
            ])->render(),
            'count' => WebshopCartService::getCount()
        ]);
    }

    public function update(Request $request)
    {
        $productId = $request->input('product_id');
        $quantity = (int) $request->input('quantity');

        WebshopCartService::update($productId, $quantity);

        return response()->json([
            'success' => true,
            'count' => WebshopCartService::getCount(),
            'total' => WebshopCartService::getTotal(),
        ]);
    }

    public function remove(Request $request)
    {
        $productId = $request->input('product_id');
        WebshopCartService::remove($productId);

        return response()->json([
            'success' => true,
            'count' => WebshopCartService::getCount(),
            'total' => WebshopCartService::getTotal(),
        ]);
    }
}

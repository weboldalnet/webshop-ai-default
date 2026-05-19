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
        $showRelatedModal = $request->input('show_related_modal') === '1' || $request->input('show_related_modal') === true;

        WebshopCartService::add($productId, $quantity);

        $product = WebshopProduct::with(['relatedProducts' => fn($q) => $q->active()->with('category')])->find($productId);
        $relatedHtml = '';
        if ($showRelatedModal && WebshopSettingsService::getBool('site_related_products_modal_enabled') && $product && $product->relatedProducts->isNotEmpty()) {
            $relatedHtml = view('site.webshop.modals.related-products', [
                'product' => $product,
                'relatedProductsGrouped' => $product->relatedProducts->groupBy('category_id'),
                'ws' => WebshopSettingsService::all()
            ])->render();
        }

        return response()->json([
            'success' => true,
            'message' => 'A termék sikeresen kosárba került.',
            'count' => WebshopCartService::getCount(),
            'total' => WebshopCartService::getTotal(),
            'related_html' => $relatedHtml,
            'has_related_products' => !empty($relatedHtml),
        ]);
    }

    public function dropdown()
    {
        return response()->json([
            'success' => true,
            'html' => view('site.webshop.cart.dropdown', [
                'items' => WebshopCartService::getContent(),
                'total' => WebshopCartService::getTotal()
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
